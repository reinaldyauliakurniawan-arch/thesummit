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

    /**
     * Hotseat mode: one authenticated host drives the shared device;
     * the other players are local (guest_name) rows with no User account.
     */
    private function createHotseatRoom(int $count = 3): array
    {
        $host = User::factory()->create(['email' => 'livewirehost@test.com']);
        $room = GameRoom::create(['host_user_id' => $host->id]);

        for ($i = 0; $i < $count; $i++) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'guest_name'   => "Pendaki " . ($i + 1),
                'turn_order'   => $i,
            ]);
        }

        return [$room, $host];
    }

    public function test_player_can_draw_and_choose_through_the_real_livewire_component(): void
    {
        [$room, $host] = $this->createHotseatRoom();
        app(GameService::class)->startGame($room);
        $room->refresh();

        // In hotseat mode the host's authenticated session drives the board
        // regardless of whose in-game turn it is.
        Livewire::actingAs($host)
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
        [$room, $host] = $this->createHotseatRoom();
        app(GameService::class)->startGame($room);
        $room->refresh();

        $maxTurns = 300;
        $turnsPlayed = 0;

        for ($i = 0; $i < $maxTurns; $i++) {
            $room->refresh();
            if ($room->status === GameStatus::Finished) {
                break;
            }

            if (!$room->currentPlayer) {
                break;
            }
            $choice = $i % 2 === 0 ? 'A' : 'B';

            $testable = Livewire::actingAs($host)
                ->test(GameBoard::class, ['room' => $room])
                ->call('drawCard')
                ->call('chooseOption', $choice);

            // A real player attempts the Rope Bridge whenever the UI offers it.
            // Without this call, current_level never advances past Basecamp,
            // so the Summit-level final-win condition can never be met —
            // this was the actual cause of the previous 300-turn timeout,
            // not a game-balance or turn-loop bug.
            if ($testable->get('showRopeBridge')) {
                $testable->call('attemptRopeBridge');
            }

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

        $viewerResult = $results->first();
        $summaryStyle = $viewerResult->leadershipProfile->leadership_style;

        // Only the host has an authenticated session; the summary page
        // shows every player's reflection report on the shared device.
        Livewire::actingAs($host)
            ->test(GameSummary::class, ['room' => $room])
            ->assertSee($summaryStyle);
    }
}
