<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Services\GameService;
use App\Enums\GameStatus;
use App\Livewire\GameBoard;
use App\Livewire\GameSummary;

class GameLivewirePlaythroughTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CardJsonSeeder::class);
        $this->seed(\Database\Seeders\V2CardEnhancementSeeder::class);
    }

    private function createRoomWithPlayers(int $count = 3): GameRoom
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[$i] = User::factory()->create(['email' => "livewireplayer{$i}@test.com"]);
        }

        $room = GameRoom::create(['host_user_id' => $users[1]->id]);
        foreach ($users as $user) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'user_id'      => $user->id,
                'turn_order'   => 0,
            ]);
        }

        return $room;
    }

    public function test_player_can_draw_and_choose_through_the_real_livewire_component(): void
    {
        $room = $this->createRoomWithPlayers();
        app(GameService::class)->startGame($room);
        $room->refresh();

        $currentUser = $room->currentPlayer->user;

        Livewire::actingAs($currentUser)
            ->test(GameBoard::class, ['room' => $room])
            ->assertSet('isMyTurn', true)
            ->call('drawCard')
            ->assertSet('showCard', true)
            ->call('chooseOption', 'A')
            ->assertSet('showEffects', true)
            ->assertSet('showCard', false);
    }

    public function test_full_game_reaches_finished_status_through_livewire_and_summary_shows_reports(): void
    {
        $room = $this->createRoomWithPlayers();
        app(GameService::class)->startGame($room);
        $room->refresh();

        $maxTurns = 300;
        $turnsPlayed = 0;

        for ($i = 0; $i < $maxTurns; $i++) {
            $room->refresh();
            if ($room->status === GameStatus::Finished) {
                break;
            }

            $currentPlayer = $room->currentPlayer;
            if (!$currentPlayer) {
                break;
            }
            $currentUser = $currentPlayer->user;
            $choice = $i % 2 === 0 ? 'A' : 'B';

            Livewire::actingAs($currentUser)
                ->test(GameBoard::class, ['room' => $room])
                ->call('drawCard')
                ->call('chooseOption', $choice);

            $turnsPlayed++;
        }

        $room->refresh();
        $this->assertEquals(
            GameStatus::Finished,
            $room->status,
            "Game did not reach Finished status after {$turnsPlayed} simulated Livewire turns (cap: {$maxTurns})."
        );

        $results = $room->results()->with(['leadershipProfile', 'realWorldChallenge'])->get();
        $this->assertCount(3, $results, 'Expected one game_results row per player after finish.');

        foreach ($results as $result) {
            $this->assertNotNull($result->leadershipProfile, "Missing leadership profile for result id {$result->id}");
            $this->assertNotNull($result->realWorldChallenge, "Missing real world challenge for result id {$result->id}");
        }

        $viewerUser = $room->players()->first()->user;
        $summaryStyle = $results->first()->leadershipProfile->leadership_style;

        Livewire::actingAs($viewerUser)
            ->test(GameSummary::class, ['room' => $room])
            ->assertSee($summaryStyle);
    }
}
