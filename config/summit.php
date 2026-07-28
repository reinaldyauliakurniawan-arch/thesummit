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
        'formula'      => '(level_reached * 10) + (final_tt * 1.5, max 15) + reputation(capped ±5) + leadership_diversity(0-5) - selfish_tax(0-10)',
        'level_values' => [
            'basecamp' => 1,
            'camp'     => 2,
            'summit'   => 3,
        ],
        'tt_weight'                  => 1.5,
        'tt_bonus_cap'               => 15,
        'reputation_cap'             => 5,
        'leadership_diversity_max'   => 5,
        'selfish_tax_per_promise'    => 2,
        'selfish_tax_cap'            => 10,
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
        'the_carrier'    => ['label' => 'The Carrier',    'description' => 'Summit + TT>=8 + rep>=0 + promises_kept >= broken'],
        'the_catalyst'   => ['label' => 'The Catalyst',   'description' => 'Highest TT + positive cross-player effects (did not summit)'],
        'the_strategist' => ['label' => 'The Strategist', 'description' => 'Most diverse leadership behaviors (4+ dimensions)'],
        'solo_peak'      => ['label' => 'Solo Peak',      'description' => 'Summit + TT<8 or rep<0 or net negative promises'],
        'none'           => ['label' => 'Climber',        'description' => 'Default badge'],
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

    // ── LRA: Leadership Role Assessment Mapping ──
    // Single source of truth: every observable assessment item and its game-level mapping.
    // Do NOT invent additional dimensions. Only items listed here are tracked.
    'lra' => [
        // Evidence quality thresholds
        'min_observations_for_medium' => 3,
        'min_observations_for_strong' => 5,
        'min_context_types_for_medium' => 2,
        'min_confidence_for_assessment' => 0.50,
        'insufficient_label' => 'Insufficient evidence',

        // Context weights for evidence quality
        'context_weights' => [
            'neutral_basecamp' => 0.8,
            'crisis_basecamp'   => 1.2,
            'neutral_camp'      => 1.0,
            'crisis_camp'       => 1.4,
            'neutral_summit'    => 1.2,
            'crisis_summit'     => 1.6,
            'social_promise'    => 1.3,
            'cross_player'      => 1.3,
            'consequence_delayed' => 1.1,
        ],

        // Score mapping from evidence pattern
        'score_mapping' => [
            'role_model'       => ['min_positive_pct' => 0.80, 'min_quality' => 'strong'],
            'exceeds'          => ['min_positive_pct' => 0.70, 'min_quality' => 'strong'],
            'meets'            => ['min_positive_pct' => 0.60, 'min_quality' => 'medium'],
            'below'            => ['min_positive_pct' => 0.50, 'min_quality' => 'weak'],
            'not_meeting'      => ['min_positive_pct' => 0.00, 'any_quality' => true],
        ],

        // All 31 observable assessment items
        // Structure: label, tier, category, description (for facilitator reference)
        'items' => [
            // ── Permission to Play ──
            'PtP_M1' => ['label' => 'Integritas di Bawah Tekanan', 'tier' => 'PtP', 'category' => 'MINDSET', 'description' => 'Memilih opsi etis ketika ada biaya personal'],
            'PtP_M2' => ['label' => 'Ego Rendah & Terbuka Input', 'tier' => 'PtP', 'category' => 'MINDSET', 'description' => 'Memilih opsi yang mencari/menerima masukan dari orang lain'],
            'PtP_M3' => ['label' => 'Belajar Terus', 'tier' => 'PtP', 'category' => 'MINDSET', 'description' => 'Investasi dalam kesempatan belajar meskipun ada biaya'],
            'PtP_M4' => ['label' => 'Get Things Done', 'tier' => 'PtP', 'category' => 'MINDSET', 'description' => 'Persistensi menyelesaikan tugas di tengah hambatan'],
            'PtP_M5' => ['label' => 'Peduli Orang Lain', 'tier' => 'PtP', 'category' => 'MINDSET', 'description' => 'Investasi sumber daya pribadi untuk pengembangan orang lain'],
            'PtP_S1' => ['label' => 'Root Cause Analysis', 'tier' => 'PtP', 'category' => 'SKILLSET', 'description' => 'Menyelidiki penyebab akar vs memperbaiki gejala'],
            'PtP_S2' => ['label' => 'Komunikasi Asertif', 'tier' => 'PtP', 'category' => 'SKILLSET', 'description' => 'Menyampaikan pendapat langsung termasuk topik yang tidak populer'],

            // ── Role 1: Individual Contributor ──
            'R1_M1'  => ['label' => 'Benchmark Pursuit', 'tier' => 'R1', 'category' => 'MINDSET', 'description' => 'Mengejar standar eksternal yang terukur'],
            'R1_M2'  => ['label' => 'Target Ownership', 'tier' => 'R1', 'category' => 'MINDSET', 'description' => 'Inisiatif tanpa diminta, mengambil tanggung jawab'],
            'R1_S1'  => ['label' => 'Consistent Delivery', 'tier' => 'R1', 'category' => 'SKILLSET', 'description' => 'Konsistensi hasil (systemic: game completion, stat floors)'],
            'R1_S2'  => ['label' => 'Proactive Reporting', 'tier' => 'R1', 'category' => 'SKILLSET', 'description' => 'Komunikasi proaktif ke atas tanpa diminta'],
            'R1_S3'  => ['label' => 'Follow Systems', 'tier' => 'R1', 'category' => 'SKILLSET', 'description' => 'Mengikuti sistem/prosedur yang sudah ada'],
            'R1_S4'  => ['label' => 'Personal Work System', 'tier' => 'R1', 'category' => 'SKILLSET', 'description' => 'Membangun sistem kerja pribadi yang reusable'],

            // ── Role 2: Leading Others ──
            'R2_M1'  => ['label' => 'Success Through Team', 'tier' => 'R2', 'category' => 'MINDSET', 'description' => 'Mendelegasikan vs mengerjakan sendiri'],
            'R2_M2'  => ['label' => 'Value Managerial Work', 'tier' => 'R2', 'category' => 'MINDSET', 'description' => 'Membangun sistem/proses vs pekerjaan teknis'],
            'R2_S1'  => ['label' => 'Job Design & Delegation', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Menempatkan orang yang tepat di peran yang tepat'],
            'R2_S2'  => ['label' => 'Selecting/Deselecting', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Keputusan seleksi dan deseleksi berbasis kriteria'],
            'R2_S3'  => ['label' => 'Performance Monitoring', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Memantau performa secara sistematis'],
            'R2_S4'  => ['label' => 'Tough Conversations', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Berani mengangkat isu yang tidak nyaman'],
            'R2_S5'  => ['label' => 'Team Engagement', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Membangun lingkungan aman dan produktif'],
            'R2_S6'  => ['label' => 'Coaching', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Mengembangkan orang lain melalui pertanyaan dan feedback'],
            'R2_S7'  => ['label' => 'Basic Budgeting', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Mengelola sumber daya secara bijak'],
            'R2_S8'  => ['label' => 'Team Workflow/SOP', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Membangun proses kerja tim yang berkelanjutan'],
            'R2_S9'  => ['label' => 'Upward/Cross Communication', 'tier' => 'R2', 'category' => 'SKILLSET', 'description' => 'Komunikasi proaktif ke atas dan lintas tim'],

            // ── Role 3: Leading Leaders ──
            'R3_M1'  => ['label' => 'Assess Leadership Quality', 'tier' => 'R3', 'category' => 'MINDSET', 'description' => 'Menilai bawahan dari kualitas kepemimpinan, bukan hanya output'],
            'R3_M2'  => ['label' => 'Decisive Under Uncertainty', 'tier' => 'R3', 'category' => 'MINDSET', 'description' => 'Mengambil keputusan berdasarkan info terbaik tanpa menunggu sempurna'],
            'R3_S1'  => ['label' => 'Assessing Leadership', 'tier' => 'R3', 'category' => 'SKILLSET', 'description' => 'Assessment kepemimpinan bawahan yang terstruktur'],
            'R3_S2'  => ['label' => 'Organizational Design', 'tier' => 'R3', 'category' => 'SKILLSET', 'description' => 'Mendesain struktur organisasi yang jelas'],
            'R3_S3'  => ['label' => 'Developing Leaders', 'tier' => 'R3', 'category' => 'SKILLSET', 'description' => 'Membangun sistem pengembangan pemimpin'],
            'R3_S4'  => ['label' => 'Strategy Translation', 'tier' => 'R3', 'category' => 'SKILLSET', 'description' => 'Menerjemahkan strategi menjadi rencana operasional'],
            'R3_S5'  => ['label' => 'Cross-Org Leadership', 'tier' => 'R3', 'category' => 'SKILLSET', 'description' => 'Memfasilitasi kolaborasi lintas organisasi'],
        ],

        // Tier groupings for summary display
        'tiers' => [
            'PtP' => ['label' => 'Permission to Play', 'gate_score' => 3.5],
            'R1'  => ['label' => 'Individual Contributor'],
            'R2'  => ['label' => 'Leading Others'],
            'R3'  => ['label' => 'Leading Leaders'],
        ],
    ],
];
