"""
The Summit — validation config.

Ports config/summit.php to Python so simulations use the same constants
as the production Laravel app. Single source of truth: edit here AND in
config/summit.php together.
"""
from __future__ import annotations

# ── Player count ──────────────────────────────────────────────
MIN_PLAYERS = 3
MAX_PLAYERS = 6
TURN_TIMEOUT_HOURS = 24

# ── Levels ────────────────────────────────────────────────────
LEVELS = {
    "basecamp": {"label": "Basecamp", "subtitle": "Leading Self", "order": 1},
    "camp":     {"label": "Camp",     "subtitle": "Leading Others", "order": 2},
    "summit":   {"label": "Summit",   "subtitle": "Leading Leaders", "order": 3},
}

LEVEL_ORDER = ["basecamp", "camp", "summit"]
LEVEL_VALUE = {"basecamp": 1, "camp": 2, "summit": 3}

# ── Rope Bridge thresholds ───────────────────────────────────
THRESHOLDS = {
    "to_camp":   {"mp": 8,  "sp": 8,  "tt": 0, "tt_required": False},
    "to_summit": {"mp": 12, "sp": 12, "tt": 5, "tt_required": True},
    "final_win": {"mp": 15, "sp": 15, "tt": 8, "tt_required": True},
}

# ── Scoring ──────────────────────────────────────────────────
SCORING = {
    "formula": "(level*10) + (tt*1.5, cap 15) + rep(±5) + diversity(0-5) - selfish_tax(0-10)",
    "level_values":     LEVEL_VALUE,
    "tt_weight":        1.5,
    "tt_bonus_cap":     15,
    "reputation_cap":   5,
    "diversity_max":    5,
    "selfish_tax_per":  2,
    "selfish_tax_cap":  10,
}

# ── Risk Die ─────────────────────────────────────────────────
RISK_DIE = {
    "dysfunction_range":      [1, 2],
    "neutral_range":          [3, 4],
    "bonus_range":            [5, 6],
    "dysfunction_tt_penalty": -2,
    "bonus_tt_reward":        1,
}

DYSFUNCTIONS = [
    "absence_of_trust",
    "fear_of_conflict",
    "lack_of_commitment",
    "avoidance_of_accountability",
    "inattention_to_results",
]

# ── Badges (priority order) ──────────────────────────────────
BADGE_PRIORITY = {
    "the_carrier":    5,
    "the_catalyst":   4,
    "the_strategist": 3,
    "solo_peak":      2,
    "none":           1,
}

# ── LRA: Leadership Role Assessment ──────────────────────────
LRA_MIN_OBS_MEDIUM  = 3
LRA_MIN_OBS_STRONG  = 5
LRA_MIN_CONTEXTS_MED = 2
LRA_MIN_CONFIDENCE  = 0.50
INSUFFICIENT_LABEL  = "Insufficient evidence"

# LRA context weights — match config/summit.php
LRA_CONTEXT_WEIGHTS = {
    "neutral_basecamp":    0.8,
    "crisis_basecamp":     1.2,
    "neutral_camp":        1.0,
    "crisis_camp":         1.4,
    "neutral_summit":      1.2,
    "crisis_summit":       1.6,
    "social_promise":      1.3,
    "cross_player":        1.3,
    "consequence_delayed": 1.1,
}

# Score mapping from evidence pattern → 1-5 score
SCORE_MAPPING = [
    ("role_model",  0.80, "strong",  5),
    ("exceeds",     0.70, "strong",  4),
    ("meets",       0.60, "medium",  3),
    ("below",       0.50, "weak",    2),
    ("not_meeting", 0.00, "any",     1),
]

# ── LRA Opportunity Model (TASK 1) ───────────────────────────
# Single source of truth: same data lives in config/summit.php.
# Edit both together. The Python copy is for offline validation.
OPPORTUNITY_MODEL = {
    # Permission to Play
    "PtP_M1": {"cards_tagging": 19, "expected_per_game": 5.6,  "min_opportunities": 3},
    "PtP_M2": {"cards_tagging": 24, "expected_per_game": 6.5,  "min_opportunities": 3},
    "PtP_M3": {"cards_tagging": 11, "expected_per_game": 3.0,  "min_opportunities": 2},
    "PtP_M4": {"cards_tagging": 12, "expected_per_game": 3.3,  "min_opportunities": 2},
    "PtP_M5": {"cards_tagging": 45, "expected_per_game": 11.5, "min_opportunities": 3},
    "PtP_S1": {"cards_tagging": 15, "expected_per_game": 4.2,  "min_opportunities": 2},
    "PtP_S2": {"cards_tagging": 24, "expected_per_game": 6.5,  "min_opportunities": 3},
    # Role 1 — Individual Contributor
    "R1_M1":  {"cards_tagging":  6, "expected_per_game": 2.1,  "min_opportunities": 2},
    "R1_M2":  {"cards_tagging":  5, "expected_per_game": 1.75, "min_opportunities": 2},
    "R1_S1":  {"cards_tagging":  4, "expected_per_game": 1.4,  "min_opportunities": 2},
    "R1_S2":  {"cards_tagging":  5, "expected_per_game": 1.75, "min_opportunities": 2},
    "R1_S3":  {"cards_tagging":  2, "expected_per_game": 0.7,  "min_opportunities": 2, "limited_coverage": True},
    "R1_S4":  {"cards_tagging":  2, "expected_per_game": 0.7,  "min_opportunities": 2, "limited_coverage": True},
    # Role 2 — Leading Others
    "R2_M1":  {"cards_tagging": 17, "expected_per_game": 4.2,  "min_opportunities": 2},
    "R2_M2":  {"cards_tagging": 13, "expected_per_game": 3.2,  "min_opportunities": 2},
    "R2_S1":  {"cards_tagging":  7, "expected_per_game": 2.0,  "min_opportunities": 2},
    "R2_S2":  {"cards_tagging":  8, "expected_per_game": 2.3,  "min_opportunities": 2},
    "R2_S3":  {"cards_tagging": 13, "expected_per_game": 3.2,  "min_opportunities": 2},
    "R2_S4":  {"cards_tagging": 23, "expected_per_game": 5.9,  "min_opportunities": 3},
    "R2_S5":  {"cards_tagging": 29, "expected_per_game": 7.2,  "min_opportunities": 3},
    "R2_S6":  {"cards_tagging": 21, "expected_per_game": 5.5,  "min_opportunities": 3},
    "R2_S7":  {"cards_tagging":  4, "expected_per_game": 1.0,  "min_opportunities": 2, "limited_coverage": True},
    "R2_S8":  {"cards_tagging": 18, "expected_per_game": 4.8,  "min_opportunities": 2},
    "R2_S9":  {"cards_tagging": 20, "expected_per_game": 5.2,  "min_opportunities": 3},
    # Role 3 — Leading Leaders
    "R3_M1":  {"cards_tagging": 10, "expected_per_game": 2.5,  "min_opportunities": 2},
    "R3_M2":  {"cards_tagging": 12, "expected_per_game": 3.0,  "min_opportunities": 2},
    "R3_S1":  {"cards_tagging":  2, "expected_per_game": 0.5,  "min_opportunities": 2, "limited_coverage": True},
    "R3_S2":  {"cards_tagging":  4, "expected_per_game": 1.0,  "min_opportunities": 2, "limited_coverage": True},
    "R3_S3":  {"cards_tagging": 15, "expected_per_game": 3.75, "min_opportunities": 2},
    "R3_S4":  {"cards_tagging":  7, "expected_per_game": 1.75, "min_opportunities": 2},
    "R3_S5":  {"cards_tagging": 17, "expected_per_game": 4.25, "min_opportunities": 2},
}

# LRA item metadata
LRA_ITEMS = {
    # Permission to Play
    "PtP_M1": {"label": "Integritas di Bawah Tekanan",    "tier": "PtP", "category": "MINDSET",  "description": "Memilih opsi etis ketika ada biaya personal"},
    "PtP_M2": {"label": "Ego Rendah & Terbuka Input",     "tier": "PtP", "category": "MINDSET",  "description": "Mencari/menerima masukan dari orang lain"},
    "PtP_M3": {"label": "Belajar Terus",                  "tier": "PtP", "category": "MINDSET",  "description": "Investasi dalam kesempatan belajar meskipun ada biaya"},
    "PtP_M4": {"label": "Get Things Done",                "tier": "PtP", "category": "MINDSET",  "description": "Persistensi menyelesaikan tugas di tengah hambatan"},
    "PtP_M5": {"label": "Peduli Orang Lain",              "tier": "PtP", "category": "MINDSET",  "description": "Investasi sumber daya pribadi untuk pengembangan orang lain"},
    "PtP_S1": {"label": "Root Cause Analysis",            "tier": "PtP", "category": "SKILLSET", "description": "Menyelidiki penyebab akar vs memperbaiki gejala"},
    "PtP_S2": {"label": "Komunikasi Asertif",             "tier": "PtP", "category": "SKILLSET", "description": "Menyampaikan pendapat langsung termasuk topik yang tidak populer"},
    # Role 1 — Individual Contributor
    "R1_M1":  {"label": "Benchmark Pursuit",              "tier": "R1",  "category": "MINDSET",  "description": "Mengejar standar eksternal yang terukur"},
    "R1_M2":  {"label": "Target Ownership",               "tier": "R1",  "category": "MINDSET",  "description": "Inisiatif tanpa diminta, mengambil tanggung jawab"},
    "R1_S1":  {"label": "Consistent Delivery",            "tier": "R1",  "category": "SKILLSET", "description": "Konsistensi hasil"},
    "R1_S2":  {"label": "Proactive Reporting",            "tier": "R1",  "category": "SKILLSET", "description": "Komunikasi proaktif ke atas tanpa diminta"},
    "R1_S3":  {"label": "Follow Systems",                 "tier": "R1",  "category": "SKILLSET", "description": "Mengikuti sistem/prosedur yang sudah ada"},
    "R1_S4":  {"label": "Personal Work System",           "tier": "R1",  "category": "SKILLSET", "description": "Membangun sistem kerja pribadi yang reusable"},
    # Role 2 — Leading Others
    "R2_M1":  {"label": "Success Through Team",           "tier": "R2",  "category": "MINDSET",  "description": "Mendelegasikan vs mengerjakan sendiri"},
    "R2_M2":  {"label": "Value Managerial Work",          "tier": "R2",  "category": "MINDSET",  "description": "Membangun sistem/proses vs pekerjaan teknis"},
    "R2_S1":  {"label": "Job Design & Delegation",        "tier": "R2",  "category": "SKILLSET", "description": "Menempatkan orang yang tepat di peran yang tepat"},
    "R2_S2":  {"label": "Selecting/Deselecting",          "tier": "R2",  "category": "SKILLSET", "description": "Keputusan seleksi dan deseleksi berbasis kriteria"},
    "R2_S3":  {"label": "Performance Monitoring",         "tier": "R2",  "category": "SKILLSET", "description": "Memantau performa secara sistematis"},
    "R2_S4":  {"label": "Tough Conversations",            "tier": "R2",  "category": "SKILLSET", "description": "Berani mengangkat isu yang tidak nyaman"},
    "R2_S5":  {"label": "Team Engagement",                "tier": "R2",  "category": "SKILLSET", "description": "Membangun lingkungan aman dan produktif"},
    "R2_S6":  {"label": "Coaching",                       "tier": "R2",  "category": "SKILLSET", "description": "Mengembangkan orang lain melalui pertanyaan dan feedback"},
    "R2_S7":  {"label": "Basic Budgeting",                "tier": "R2",  "category": "SKILLSET", "description": "Mengelola sumber daya secara bijak"},
    "R2_S8":  {"label": "Team Workflow/SOP",              "tier": "R2",  "category": "SKILLSET", "description": "Membangun proses kerja tim yang berkelanjutan"},
    "R2_S9":  {"label": "Upward/Cross Communication",     "tier": "R2",  "category": "SKILLSET", "description": "Komunikasi proaktif ke atas dan lintas tim"},
    # Role 3 — Leading Leaders
    "R3_M1":  {"label": "Assess Leadership Quality",      "tier": "R3",  "category": "MINDSET",  "description": "Menilai bawahan dari kualitas kepemimpinan, bukan hanya output"},
    "R3_M2":  {"label": "Decisive Under Uncertainty",     "tier": "R3",  "category": "MINDSET",  "description": "Mengambil keputusan berdasarkan info terbaik tanpa menunggu sempurna"},
    "R3_S1":  {"label": "Assessing Leadership",           "tier": "R3",  "category": "SKILLSET", "description": "Assessment kepemimpinan bawahan yang terstruktur"},
    "R3_S2":  {"label": "Organizational Design",          "tier": "R3",  "category": "SKILLSET", "description": "Mendesain struktur organisasi yang jelas"},
    "R3_S3":  {"label": "Developing Leaders",             "tier": "R3",  "category": "SKILLSET", "description": "Membangun sistem pengembangan pemimpin"},
    "R3_S4":  {"label": "Strategy Translation",           "tier": "R3",  "category": "SKILLSET", "description": "Menerjemahkan strategi menjadi rencana operasional"},
    "R3_S5":  {"label": "Cross-Org Leadership",           "tier": "R3",  "category": "SKILLSET", "description": "Memfasilitasi kolaborasi lintas organisasi"},
}

LRA_TIERS = {
    "PtP": {"label": "Permission to Play", "gate_score": 3.5},
    "R1":  {"label": "Individual Contributor"},
    "R2":  {"label": "Leading Others"},
    "R3":  {"label": "Leading Leaders"},
}

# ── Behavior dimensions ──────────────────────────────────────
BEHAVIOR_DIMENSIONS = {
    "risk_taking":   {"weight": 1.5},
    "collaboration": {"weight": 2.0},
    "empathy":       {"weight": 1.5},
    "decisiveness":  {"weight": 1.0},
    "coaching":      {"weight": 1.5},
    "control":       {"weight": 1.0},
    "adaptability":  {"weight": 1.0},
}

SOURCE_RELIABILITY = {
    "explicit":   1.0,
    "game_event": 0.8,
    "pattern":    0.4,
}

MIN_WEIGHT_FOR_CONFIDENCE = 4.0
MIN_EVIDENCE_FOR_LABEL = 2

# ── Game-flow constants (used by simulator) ──────────────────
MAX_TURNS_PER_PLAYER = 20       # Hard cap — game ends even if no one summits
FINAL_ROUND_TURNS = 1           # Each player gets 1 turn after first summit

# Card category alternation
def category_for_turn(turn_number: int) -> str:
    return "mindset" if turn_number % 2 == 1 else "skillset"
