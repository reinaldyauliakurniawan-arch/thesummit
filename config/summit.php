<?php

return [
    'min_players'        => 3,
    'max_players'        => 6,
    'turn_timeout_hours' => 24,

    'levels' => [
        'basecamp' => ['label' => 'Basecamp', 'subtitle' => 'Leading Self', 'order' => 1],
        'camp'     => ['label' => 'Camp',     'subtitle' => 'Leading Others', 'order' => 2],
        'summit'   => ['label' => 'Summit',   'subtitle' => 'Leading Leaders', 'order' => 3],
    ],

    /**
     * Thresholds for Rope Bridge checks and final win.
     */
    'thresholds' => [
        'to_camp'   => [
            'mp'         => 8,
            'sp'         => 8,
            'tt'         => 0,
            'tt_required' => false,
        ],
        'to_summit' => [
            'mp'          => 12,
            'sp'          => 12,
            'tt'          => 5,
            'tt_required' => true,
        ],
        'final_win' => [
            'mp'          => 15,
            'sp'          => 15,
            'tt'          => 8,
            'tt_required' => true,
        ],
    ],

    'scoring' => [
        'formula'      => '(level_reached * 10) + final_tt',
        'level_values' => [
            'basecamp' => 1,
            'camp'     => 2,
            'summit'   => 3,
        ],
    ],

    'risk_die' => [
        'dysfunction_range'      => [1, 2],
        'neutral_range'          => [3, 4],
        'bonus_range'            => [5, 6],
        'dysfunction_tt_penalty' => -2,
        'bonus_tt_reward'        => 1,
    ],

    'dysfunctions' => [
        'absence_of_trust'            => 'Absence of Trust',
        'fear_of_conflict'            => 'Fear of Conflict',
        'lack_of_commitment'          => 'Lack of Commitment',
        'avoidance_of_accountability' => 'Avoidance of Accountability',
        'inattention_to_results'      => 'Inattention to Results',
    ],

    'badges' => [
        'the_carrier' => ['label' => 'The Carrier'],
        'solo_peak'   => ['label' => 'Solo Peak'],
        'none'        => ['label' => 'Climber'],
    ],

    // ── V2: Persistent Consequences ──
    'consequences' => [
        'default_delay_rounds' => 2,       // Default rounds before delayed effect triggers
        'max_active_per_player' => 5,       // Max pending consequences per player
        'promise_expiry_turns'  => 5,       // Turns before an unfulfilled promise auto-breaks
    ],

    // ── V2: Team Interdependency ──
    'team' => [
        'shared_dysfunction_penalty' => 0.5, // Fraction of penalty shared to other players
        'cooperative_recovery_threshold' => 3, // TT threshold to trigger cooperative recovery
    ],

    // ── V2: Social Mechanics ──
    'social' => [
        'promise_reputation_gain' => 2,
        'promise_reputation_loss' => -3,
        'promise_tt_loss_on_break' => -1,
        'vote_timeout_seconds'    => 120,
    ],

    // ── V2: Leadership Identity ──
    'leadership' => [
        'behavior_types' => [
            'risk_taking', 'collaboration', 'empathy',
            'decisiveness', 'coaching', 'control', 'adaptability',
        ],
        'max_behavior_score' => 10,
        'min_behavior_score' => -10,
    ],

    // ── V2: Hidden Information ──
    'hidden_info' => [
        'enabled' => true,
        'reveal_probability' => 0.3,  // 30% of cards have hidden info
    ],
];
