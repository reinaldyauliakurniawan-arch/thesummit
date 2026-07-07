<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\ExpeditionCard;
use App\Services\GameService;
use App\Enums\Level;
use Illuminate\Support\Facades\Auth;

class GameBoard extends Component
{
    public GameRoom $room;
    public GamePlayer $myPlayer;

    public ?ExpeditionCard $currentCard = null;
    public bool $showCard = false;
    public bool $showRopeBridge = false;
    public bool $showEffects = false;

    public array $lastEffects = [];
    public $riskDieResult = null;
    public $dysfunctionTriggered = null;
    public string $message = '';
    public bool $isMyTurn = false;

    public function mount(GameRoom $room): void
    {
        $this->room = $room->load([
            'players.user',
            'currentPlayer.user',
            'turns.card',
            'turns.player.user',
        ]);
        $this->myPlayer = $room->players()
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $this->checkTurn();
    }

    public function checkTurn(): void
    {
        $this->room->refresh();
        $this->myPlayer->refresh();
        $this->isMyTurn = (
            $this->room->current_turn_player_id === $this->myPlayer->id
            && in_array($this->room->status->value, ['in_progress', 'final_round'])
        );
    }

    public function refreshBoard(): void
    {
        $this->room->load([
            'players.user',
            'currentPlayer.user',
            'turns.card',
            'turns.player.user',
        ]);
        $this->myPlayer->refresh();
        $this->checkTurn();

        if ($this->room->status->value === 'finished') {
            $this->redirect(route('game.summary', $this->room));
        }
    }

    public function drawCard(GameService $gameService): void
    {
        if (!$this->isMyTurn) {
            return;
        }

        $turnNumber = $this->myPlayer->turns()->count() + 1;
        $this->currentCard = $gameService->drawCard($this->myPlayer, $turnNumber);
        $this->showCard = true;
        $this->showEffects = false;
    }

    public function chooseOption(string $option, GameService $gameService): void
    {
        if (!$this->isMyTurn || !$this->currentCard) {
            return;
        }

        if (!in_array(strtoupper($option), ['A', 'B'], true)) {
            return;
        }

        $result = $gameService->processTurn($this->myPlayer, $option, $this->currentCard);

        $this->lastEffects = $result['effects'];
        $this->riskDieResult = $result['risk_die'];
        $this->dysfunctionTriggered = $result['dysfunction'];
        $this->showCard = false;
        $this->showEffects = true;

        // Check if player should be offered a Rope Bridge
        $freshPlayer = $this->myPlayer->fresh();
        $currentLevel = Level::from($freshPlayer->current_level);
        $nextLevel = $currentLevel->next();

        if ($nextLevel) {
            $thresholdKey = 'to_' . $nextLevel->value;
            if ($freshPlayer->meetsThreshold($thresholdKey)) {
                $this->showRopeBridge = true;
            }
        }

        if ($result['triggered_final_round']) {
            $this->message = 'Final Round! Semua pemain dapat 1 giliran terakhir.';
        }

        $this->currentCard = null;
        $this->checkTurn();
    }

    public function attemptRopeBridge(GameService $gameService): void
    {
        if (!$this->isMyTurn) {
            return;
        }

        $result = $gameService->attemptRopeBridge($this->myPlayer);
        $freshPlayer = $this->myPlayer->fresh();

        if ($result['result'] === 'success') {
            $this->message = 'Rope Bridge berhasil! Naik ke ' . ucfirst($freshPlayer->current_level) . '!';
        } else {
            $this->message = 'Rope Bridge gagal. Ambil 1 kartu tambahan.';
        }

        $this->showRopeBridge = false;

        if ($result['triggered_final_round']) {
            $this->message .= ' Final Round triggered!';
        }

        $this->room->refresh();
        $this->myPlayer->refresh();
        $this->checkTurn();
    }

    public function skipRopeBridge(): void
    {
        $this->showRopeBridge = false;
    }

    public function render()
    {
        $allTurns = $this->room->turns()
            ->with(['card', 'player.user'])
            ->latest()
            ->take(20)
            ->get()
            ->reverse();

        $players = $this->room->players()
            ->with('user')
            ->orderBy('turn_order')
            ->get();

        return view('livewire.game-board', compact('allTurns', 'players'))
            ->layout('layouts.app');
    }
}