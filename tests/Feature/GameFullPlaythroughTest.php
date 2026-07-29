<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\User;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\ExpeditionCard;
use App\Services\GameService;
use App\Enums\GameStatus;
use Illuminate\Support\Facades\Auth;

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

    public function test_create_room_and_join(): void
    {
        // Create 3 users
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[$i] = User::factory()->create([
                'email' => "player{$i}@test.com",
            ]);
        }

        // User 1 creates a room
        $room = $this->actingAs($users[1])
            ->post('/rooms')
            ->assertRedirect();

        $room = GameRoom::first();
        $this->assertNotNull($room);
        $this->assertEquals(GameStatus::Waiting, $room->status);
        $this->assertEquals(1, $room->players()->count());

        // User 2 joins
        $this->actingAs($users[2])
            ->get("/rooms/join/{$room->code}")
            ->assertRedirect();

        $this->assertEquals(2, $room->fresh()->players()->count());

        // User 3 joins
        $user3 = User::factory()->create();
        $this->actingAs($user3)
            ->get("/rooms/join/{$room->code}")
            ->assertRedirect();

        $this->assertEquals(3, $room->fresh()->players()->count());
    }

    public function test_start_game_changes_status(): void
    {
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[$i] = User::factory()->create(['email' => "player{$i}@test.com"]);
        }

        // Create room and add 3 players
        $room = GameRoom::create(['host_user_id' => $users[1]->id]);
        foreach ($users as $user) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'user_id'      => $user->id,
                'turn_order'   => 0,
            ]);
        }

        // Start game
        $response = $this->actingAs($users[1])
            ->post("/rooms/{$room->id}/start");

        // Should redirect (not 500)
        $this->assertNotEquals(500, $response->getStatusCode());

        $room->refresh();
        $this->assertEquals(GameStatus::InProgress, $room->status);
    }

    public function test_game_board_accessible_after_start(): void
    {
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[$i] = User::factory()->create(['email' => "player{$i}@test.com"]);
        }

        $room = GameRoom::create(['host_user_id' => $users[1]->id]);
        foreach ($users as $user) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'user_id'      => $user->id,
                'turn_order'   => 0,
            ]);
        }

        // Start game
        $gameService = app(GameService::class);
        $gameService->startGame($room);

        // Each user can access the game board
        foreach ($users as $user) {
            $response = $this->actingAs($user)
                ->get("/game/{$room->id}");
            $response->assertStatus(200);
        }
    }

    public function test_draw_card_and_choose_option(): void
    {
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[$i] = User::factory()->create();
        }

        $room = GameRoom::create(['host_user_id' => $users[1]->id]);
        foreach ($users as $user) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'user_id'      => $user->id,
                'turn_order'   => 0,
            ]);
        }

        // Start game
        $gameService = app(GameService::class);
        $gameService->startGame($room);
        $room->refresh();

        // Get the current player's user
        $currentPlayer = $room->players()->first();
        $currentUser = $currentPlayer->user;

        // Draw a card via GameService directly (bypass Livewire auth requirement)
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
