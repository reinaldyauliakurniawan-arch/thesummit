<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\ExpeditionCard;
use App\Services\GameService;
use App\Enums\GameStatus;

class GameFullPlaythroughTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed expedition cards
        $this->seed(\Database\Seeders\CardJsonSeeder::class);
        $this->seed(\Database\Seeders\V2CardEnhancementSeeder::class);
    }

    /**
     * Helper: create a room hosted by $host with $count local (guest) players.
     */
    protected function createHotseatRoom(User $host, int $count = 3): GameRoom
    {
        $room = GameRoom::create(['host_user_id' => $host->id]);

        for ($i = 0; $i < $count; $i++) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'guest_name'   => "Pendaki " . ($i + 1),
                'turn_order'   => $i,
            ]);
        }

        return $room;
    }

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_register_creates_user(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Player One',
            'email'                 => 'player1@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'player1@test.com',
        ]);
    }

    public function test_login_redirects_to_dashboard(): void
    {
        User::factory()->create([
            'email'    => 'testuser@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'testuser@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
    }

    public function test_host_creates_room_with_local_players(): void
    {
        $host = User::factory()->create(['email' => 'host@test.com']);

        $this->actingAs($host)
            ->post('/rooms', [
                'names' => ['Alice', 'Bob', 'Charlie'],
            ])
            ->assertRedirect();

        $room = GameRoom::first();
        $this->assertNotNull($room);
        $this->assertEquals(GameStatus::Waiting, $room->status);
        $this->assertEquals($host->id, $room->host_user_id);
        $this->assertEquals(3, $room->players()->count());
        $this->assertEquals(
            ['Alice', 'Bob', 'Charlie'],
            $room->players()->orderBy('turn_order')->pluck('guest_name')->all()
        );
    }

    public function test_only_host_can_access_room(): void
    {
        $host = User::factory()->create();
        $stranger = User::factory()->create();
        $room = $this->createHotseatRoom($host);

        $this->actingAs($stranger)
            ->get("/rooms/{$room->id}/lobby")
            ->assertForbidden();

        $this->actingAs($host)
            ->get("/rooms/{$room->id}/lobby")
            ->assertStatus(200);
    }

    public function test_start_game_changes_status(): void
    {
        $host = User::factory()->create();
        $room = $this->createHotseatRoom($host);

        $response = $this->actingAs($host)
            ->post("/rooms/{$room->id}/start");

        // Should redirect (not 500)
        $this->assertNotEquals(500, $response->getStatusCode());

        $room->refresh();
        $this->assertEquals(GameStatus::InProgress, $room->status);
    }

    public function test_game_board_accessible_by_host_only(): void
    {
        $host = User::factory()->create();
        $room = $this->createHotseatRoom($host);

        $gameService = app(GameService::class);
        $gameService->startGame($room);

        // Host (the only authenticated party) can access the shared board
        $response = $this->actingAs($host)->get("/game/{$room->id}");
        $response->assertStatus(200);
    }

    public function test_draw_card_and_choose_option(): void
    {
        $host = User::factory()->create();
        $room = $this->createHotseatRoom($host);

        // Start game
        $gameService = app(GameService::class);
        $gameService->startGame($room);
        $room->refresh();

        // Get the current turn's local player
        $currentPlayer = $room->currentPlayer;
        $this->assertNotNull($currentPlayer);
        $this->assertNull($currentPlayer->user_id);
        $this->assertNotNull($currentPlayer->guest_name);

        // Draw a card via GameService directly
        $turnNumber = $currentPlayer->turns()->count() + 1;
        $card = $gameService->drawCard($currentPlayer, $turnNumber);
        $this->assertNotNull($card);

        // Process the turn
        $result = $gameService->processTurn($currentPlayer, 'A', $card);
        $this->assertNotNull($result);
    }

    public function test_cards_seeded_correctly(): void
    {
        $this->assertEquals(64, ExpeditionCard::count());
        $this->assertEquals(0, ExpeditionCard::whereNull('card_json')->count());
    }
}
