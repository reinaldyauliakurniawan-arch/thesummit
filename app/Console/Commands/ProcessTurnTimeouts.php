<?php
namespace App\Console\Commands;use App\Models\GameRoom;use App\Services\GameService;use Illuminate\Console\Command;
class ProcessTurnTimeouts extends Command{protected $signature='turns:process-timeout';protected $description='Auto-play timed-out turns';
public function handle(GameService $gs):int{$rooms=GameRoom::whereIn('status',['in_progress','final_round'])->whereNotNull('current_turn_started_at')->get();foreach($rooms as $r)$gs->processTimeout($r);return 0;}}

