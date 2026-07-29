<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\User;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Services\GameService;
use App\Enums\GameStatus;
use Illuminate\Support\Facades\DB;

class GameFinishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed card data required by GameService operations
        $this->seed(\Database\Seeders\ExpeditionCardSeeder::class);
        $this->seed(\Database\Seeders\CardJsonSeeder::class);
        $this->seed(\Database\Seeders\V2CardEnhancementSeeder::class);

        // Prevent real notification dispatching
        Notification::fake();
    }

    public function test_finish_game_generates_results_profiles_and_challenges(): void
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

        $gameService = app(GameService::class);
        $gameService->startGame($room);
        $room->refresh();

        // Force all players to a winning state to directly exercise finishGame()
        // without depending on random-play convergence.
        foreach ($room->players()->get() as $p) {
            $p->current_level = 'summit';
            $p->mp = 20;
            $p->sp = 20;
            $p->tt = 10;
            $p->reputation = 2;
            $p->save();
        }

        $room->status = GameStatus::FinalRound;
        $room->final_round_started_at = now()->subMinute();
        $room->save();

        // This must NOT throw.
        $gameService->finishGame($room);

        $room->refresh();
        $this->assertEquals(GameStatus::Finished, $room->status);

        $results = DB::table('game_results')->where('game_room_id', $room->id)->get();
        $this->assertCount(3, $results, 'Expected one game_results row per player');

        foreach ($results as $r) {
            $profile = DB::table('leadership_profiles')->where('game_result_id', $r->id)->first();
            $this->assertNotNull($profile, "leadership_profiles row missing for game_result {$r->id}");

            $challenge = DB::table('real_world_challenges')->where('game_result_id', $r->id)->first();
            $this->assertNotNull($challenge, "real_world_challenges row missing for game_result {$r->id}");
        }
    }
}
