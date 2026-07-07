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
     * 'tt_required' => false means TT is NOT a gate for that transition
     * (e.g. to_camp only checks MP+SP, not TT).
     * When tt_required is false, the 'tt' key is ignored by meetsThreshold().
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
        'dysfunction_range'     => [1, 2],
        'neutral_range'         => [3, 4],
        'bonus_range'           => [5, 6],
        'dysfunction_tt_penalty' => -2,
        'bonus_tt_reward'        => 1,
    ],

    'dysfunctions' => [
        'absence_of_trust'             => 'Absence of Trust',
        'fear_of_conflict'             => 'Fear of Conflict',
        'lack_of_commitment'           => 'Lack of Commitment',
        'avoidance_of_accountability'  => 'Avoidance of Accountability',
        'inattention_to_results'       => 'Inattention to Results',
    ],

    'badges' => [
        'the_carrier' => ['label' => 'The Carrier'],
        'solo_peak'   => ['label' => 'Solo Peak'],
        'none'        => ['label' => 'Climber'],
    ],
];