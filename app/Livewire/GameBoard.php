<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\ExpeditionCard;
use App\Models\Promise;
use App\Models\Vote;
use App\Services\GameService;
use App\Enums\Level;
use Illuminate\Support\Facades\Auth;

class GameBoard extends Component
{
    public GameRoom $room;
    public GamePlayer $myPlayer;
    public bool $showDiscussion = false;

    public ?ExpeditionCard $currentCard = null;
    public bool $showCard = false;
    public bool $showRopeBridge = false;
    public bool $showEffects = false;

    public array $lastEffects = [];
    public $riskDieResult = null;
    public $dysfunctionTriggered = null;
    public string $message = '';
    public bool $isMyTurn = false;

    // V2 properties
    public array $triggeredConsequences = [];
    public array $createdConsequences = [];
    public array $crossPlayerEffects = [];
    public array $trackedBehaviors = [];
    public bool $wasHidden = false;
    public ?string $hiddenInfo = null;
    public array $activeConsequences = [];
    public array $activePromises = [];
    public array $activeVotes = [];

    // Promise/Vote UI state
    public bool $showPromiseModal = false;
    public bool $showVoteModal = false;
    public string $promiseType = '';
    public string $promiseDescription = '';
    public ?int $promiseRecipientId = null;
    public ?int $activeVoteId = null;
    public string $voteChoice = '';

    public function mount(GameRoom $room): void
    {
        // Hotseat mode: verify the authenticated user is the host, then
        // point myPlayer at whoever currently has the turn — the "active
        // player" concept replaces per-device auth entirely.
        if ($room->host_user_id !== Auth::id()) {
            abort(403);
        }

        $this->room = $room->load([
            'players.user',
            'currentPlayer.user',
            'turns.card',
            'turns.player.user',
        ]);
        $this->myPlayer = $this->room->currentPlayer
            ?? $this->room->players()->orderBy('turn_order')->firstOrFail();
        $this->checkTurn();
        $this->loadV2Data();
    }

    public function checkTurn(): void
    {
        $this->room->refresh();

        // Re-point myPlayer at the current turn holder every time the
        // board refreshes, since in hotseat mode the "active player"
        // changes every turn on the same device.
        $current = $this->room->currentPlayer;
        if ($current) {
            $this->myPlayer = $current;
        }
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
        $this->checkTurn();
        $this->loadV2Data();

        if ($this->room->status->value === 'finished') {
            $this->redirect(route('game.summary', $this->room));
        }
    }

    public function loadV2Data(): void
    {
        $this->activeConsequences = $this->room->consequences()
            ->where('is_triggered', false)
            ->where('is_hidden', false)
            ->where('game_player_id', $this->myPlayer->id)
            ->with(['originatingTurn.card'])
            ->get()
            ->toArray();

        $this->activePromises = $this->room->promises()
            ->active()
            ->with(['promiser.user', 'recipient.user'])
            ->get()
            ->toArray();

        $this->activeVotes = $this->room->votes()
            ->active()
            ->with(['triggeringPlayer.user'])
            ->get()
            ->toArray();
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

        // V2 data
        $this->triggeredConsequences = $result['triggered_consequences'] ?? [];
        $this->createdConsequences = collect($result['created_consequences'] ?? [])->map(fn($c) => [
            'description' => $c->description,
            'stat' => $c->stat,
            'delta' => $c->delta,
            'is_hidden' => $c->is_hidden,
        ])->toArray();
        $this->crossPlayerEffects = $result['cross_player_effects'] ?? [];
        $this->trackedBehaviors = $result['tracked_behaviors'] ?? [];
        $this->wasHidden = $result['was_hidden'] ?? false;
        $this->hiddenInfo = $result['hidden_info'] ?? null;

        // Check Rope Bridge
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

        // Show triggered consequence messages
        if (!empty($this->triggeredConsequences)) {
            $this->message .= ' Konsekuensi tertunda terpicu!';
        }

        $this->currentCard = null;
        $this->checkTurn();
        $this->loadV2Data();
    }

    public function attemptRopeBridge(GameService $gameService): void
    {
        if (!$this->showRopeBridge) {
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

    // ── Hotseat: optional discussion pause before handing off the turn ──

    public function toggleDiscussion(): void
    {
        $this->showDiscussion = !$this->showDiscussion;
    }

    public function nextTurn(): void
    {
        $this->showDiscussion = false;
        $this->refreshBoard();
    }

    // ── V2: Promise Methods ──

    public function showPromiseForm(): void
    {
        $this->showPromiseModal = true;
        $this->promiseType = '';
        $this->promiseDescription = '';
        $this->promiseRecipientId = null;
    }

    public function hidePromiseForm(): void
    {
        $this->showPromiseModal = false;
    }

    public function submitPromise(GameService $gameService): void
    {
        if (!$this->promiseType || !$this->promiseRecipientId || !$this->promiseDescription) {
            return;
        }

        $recipient = GamePlayer::find($this->promiseRecipientId);
        if (!$recipient || $recipient->game_room_id !== $this->room->id) {
            return;
        }

        $gameService->createPromise(
            $this->room,
            $this->myPlayer,
            $recipient,
            $this->promiseType,
            $this->promiseDescription
        );

        $this->showPromiseModal = false;
        $this->message = 'Janji dibuat! Ingat, janji tidak diwajibkan oleh sistem.';
        $this->loadV2Data();
    }

    // ── V2: Vote Methods ──

    public function castVoteOnActive(Vote $vote, GameService $gameService): void
    {
        if (!$this->voteChoice || $vote->game_room_id !== $this->room->id) {
            return;
        }

        $gameService->castVote($vote, $this->myPlayer, $this->voteChoice);
        $this->voteChoice = '';
        $this->loadV2Data();
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

        $otherPlayers = $players->filter(fn ($p) => $p->id !== $this->myPlayer->id);

        return view('livewire.game-board', compact('allTurns', 'players', 'otherPlayers'))
            ->layout('layouts.app');
    }
}
