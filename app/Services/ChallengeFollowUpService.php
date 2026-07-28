<?php

namespace App\Services;

use App\Models\User;
use App\Models\GamePlayer;
use App\Models\RealWorldChallenge;
use Illuminate\Support\Facades\Log;

/**
 * ChallengeFollowUpService — PRD Feature 8: Real-World Action Loop.
 *
 * When a player starts a new game session, check for any unresolved
 * RealWorldChallenge records that are past deadline. Surface a prompt
 * asking whether the challenge was completed.
 *
 * This is a non-blocking check — it does not prevent game participation.
 * It serves as a reflective bridge between game sessions.
 */
class ChallengeFollowUpService
{
    /**
     * Check for unresolved challenges for a user.
     *
     * Returns a list of challenges that:
     * - Are not completed (is_completed = false)
     * - Have passed their deadline
     * - Belong to game players linked to this user
     *
     * @return array List of unresolved challenges with metadata
     */
    public function getUnresolvedChallenges(User $user): array
    {
        $players = GamePlayer::where('user_id', $user->id)->pluck('id');

        $challenges = RealWorldChallenge::whereIn('game_player_id', $players)
            ->where('is_completed', false)
            ->where('deadline', '<', now())
            ->with(['player.room', 'result'])
            ->orderBy('deadline', 'asc')
            ->get();

        $unresolved = [];
        foreach ($challenges as $challenge) {
            $daysOverdue = now()->diffInDays($challenge->deadline);
            $unresolved[] = [
                'id' => $challenge->id,
                'challenge' => $challenge->challenge,
                'challenge_type' => $challenge->challenge_type,
                'why_this_challenge' => $challenge->why_this_challenge,
                'deadline' => $challenge->deadline->toIso8601String(),
                'days_overdue' => $daysOverdue,
                'game_played_at' => optional($challenge->result)->created_at?->toIso8601String(),
                'completion_notes' => null, // to be filled by user
            ];
        }

        return $unresolved;
    }

    /**
     * Mark a challenge as completed with optional notes.
     *
     * @return bool
     */
    public function markChallengeCompleted(int $challengeId, string $notes = ''): bool
    {
        $challenge = RealWorldChallenge::find($challengeId);
        if (!$challenge) {
            return false;
        }

        $challenge->is_completed = true;
        $challenge->completed_at = now();
        $challenge->completion_notes = $notes;
        $challenge->save();

        Log::info('challenge_follow_up:completed', [
            'challenge_id' => $challengeId,
            'completed_at' => now()->toIso8601String(),
            'notes' => $notes,
        ]);

        return true;
    }

    /**
     * Mark a challenge as explicitly not completed (player acknowledged it wasn't done).
     *
     * This is different from leaving it unresolved — it's an explicit acknowledgment.
     *
     * @return bool
     */
    public function markChallengeSkipped(int $challengeId, string $reason = ''): bool
    {
        $challenge = RealWorldChallenge::find($challengeId);
        if (!$challenge) {
            return false;
        }

        $challenge->is_completed = false;
        $challenge->completion_notes = "Skipped: {$reason}";
        $challenge->acknowledged_at = now();
        $challenge->save();

        Log::info('challenge_follow_up:skipped', [
            'challenge_id' => $challengeId,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Check if a user has any unresolved challenges that should be surfaced.
     * Used by controllers/middleware to decide whether to show the follow-up prompt.
     *
     * @return bool
     */
    public function hasUnresolvedChallenges(User $user): bool
    {
        return count($this->getUnresolvedChallenges($user)) > 0;
    }
}
