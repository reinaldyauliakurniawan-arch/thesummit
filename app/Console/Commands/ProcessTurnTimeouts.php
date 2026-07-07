<?php

namespace App\Console\Commands;

use App\Models\GameRoom;
use App\Services\GameService;
use Illuminate\Console\Command;

class ProcessTurnTimeouts extends Command
{
    protected $signature = 'turns:process-timeout';
    protected $description = 'Auto-play turns that have exceeded the timeout period.';

    public function handle(GameService $gameService): int
    {
        $rooms = GameRoom::whereIn('status', ['in_progress', 'final_round'])
            ->whereNotNull('current_turn_started_at')
            ->get();

        foreach ($rooms as $room) {
            $gameService->processTimeout($room);
        }

        return 0;
    }
}