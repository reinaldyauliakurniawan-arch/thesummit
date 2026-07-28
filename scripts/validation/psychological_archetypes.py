"""
The Summit — psychologically realistic player archetypes.

Replaces the optimization-based strategy profiles with decision-making models
that mirror how real humans actually behave in leadership simulations.

Each archetype uses heuristics, biases, and context-dependent rules rather
than pure utility maximization. Key differences from optimizer profiles:

  - Inconsistency: Real humans don't always pick the "best" option
  - Emotional bias: Stress level, current standing, and recent events affect decisions
  - Social awareness: Some archetypes consider what others might think
  - Pattern rigidity: Some archetypes repeat the same behavior regardless of context
  - Growth ceiling: Some archetypes can't learn or adapt mid-game

Archetypes:
  1. conflict_avoider  — Always avoids options with confrontation cost
  2. people_pleaser     — Picks the option that benefits others most
  3. micromanager       — Prefers options with control/oversight tags
  4. controller         — Dominant, picks high-mp options, ignores TT
  5. hero_syndrome      — Takes the risky/momentum option, especially in crisis
  6. political_player   — Maximizes reputation, avoids risk of blame
  7. servant_leader     — Balanced team-first, but with boundaries
  8. perfectionist      — Seeks the "safest" option with highest consistency
  9. opportunist        — Switches strategy based on what's working
  10. consensus_seeker   — Alternates between A and B to show flexibility

Design principles:
  - Each archetype should produce a DISTINCT LRA fingerprint
  - No archetype should be "best" — they have different blind spots
  - The assessment system should be able to TELL THEM APART
"""
from __future__ import annotations
import random
import math
from typing import Callable

from .cards_loader import Card
from .game_state import Player

StrategyFn = Callable[[Player, Card, random.Random], str]


def _stat_deltas(option: str, card: Card) -> dict[str, int]:
    return card.stat_deltas(option)


def _total(option: str, card: Card) -> int:
    d = _stat_deltas(option, card)
    return d["mp"] + d["sp"] + d["tt"]


def _weighted(option: str, card: Card,
              w_mp: float, w_sp: float, w_tt: float,
              w_rep: float = 0.0) -> float:
    d = _stat_deltas(option, card)
    return w_mp * d["mp"] + w_sp * d["sp"] + w_tt * d["tt"] + w_rep * d["reputation"]


def _behavior_tags(option: str, card: Card) -> dict[str, int]:
    return card.option_behavior_tags(option)


def _lra_tags(option: str, card: Card) -> dict[str, str]:
    return card.option_lra_tags(option)


def _has_tag(option: str, card: Card, dim: str) -> bool:
    tags = _behavior_tags(option, card)
    return dim in tags and abs(tags[dim]) >= 1


def _pick_with_noise(card: Card, scorer: Callable[[str], float],
                     rng: random.Random, noise_std: float = 0.3,
                     inconsistency_pct: float = 0.15) -> str:
    """Pick best option but with psychological noise and random inconsistency.
    
    Args:
        noise_std: Gaussian noise added to scores (models imperfect evaluation)
        inconsistency_pct: Chance of completely random choice (models distraction)
    """
    # Inconsistency: sometimes humans just pick randomly
    if rng.random() < inconsistency_pct:
        return rng.choice(["A", "B"])
    
    a_score = scorer("A") + rng.gauss(0, noise_std)
    b_score = scorer("B") + rng.gauss(0, noise_std)
    return "A" if a_score >= b_score else "B"


def _stress_factor(player: Player) -> float:
    """Higher stress when stats are imbalanced or level is higher."""
    # Stress increases at higher levels (more pressure)
    level_stress = {"basecamp": 0.0, "camp": 0.3, "summit": 0.6}.get(
        player.current_level, 0.0)
    # Stress when any stat is critically low
    stat_stress = 0.0
    if player.mp < 5:
        stat_stress += 0.2
    if player.sp < 5:
        stat_stress += 0.2
    if player.tt < 3:
        stat_stress += 0.3
    return min(level_stress + stat_stress, 1.0)


def _recent_outcome(player: Player, lookback: int = 3) -> float:
    """Average total stat gain from last N turns. Negative = bad recent outcomes."""
    recent = player.turns[-lookback:] if len(player.turns) >= lookback else player.turns
    if not recent:
        return 0.0
    return sum(_total(t.chosen_option, t.card) for t in recent) / len(recent)


# ── ARCHETYPE 1: CONFLICT AVOIDER ──────────────────────────────

def archetype_conflict_avoider(player: Player, card: Card, rng: random.Random) -> str:
    """Avoids options that involve confrontation, tough conversations, or risk.
    
    Decision heuristic:
    - Strongly penalize options tagged with 'decisiveness' or 'risk_taking'
    - Prefer options with high empathy/collaboration tags
    - Under stress: even more avoidance (nearly always picks the "safe" option)
    - Inconsistency: 20% random choice (can't decide under pressure)
    """
    stress = _stress_factor(player)
    
    def score(o: str) -> float:
        tags = _behavior_tags(o, card)
        base = _total(o, card) * 0.3  # Stats matter less
        
        # Strong preference for empathy/collaboration
        if _has_tag(o, card, "empathy"):
            base += 3.0
        if _has_tag(o, card, "collaboration"):
            base += 2.0
        
        # Strong avoidance of confrontation
        if _has_tag(o, card, "decisiveness"):
            base -= 4.0 * (1.0 + stress)  # Worse under stress
        if _has_tag(o, card, "risk_taking"):
            base -= 3.0 * (1.0 + stress)
        
        # Avoid TT loss (fear of team disapproval)
        tt = _stat_deltas(o, card)["tt"]
        if tt < 0:
            base -= 2.0
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.5, inconsistency_pct=0.20)


# ── ARCHETYPE 2: PEOPLE PLEASER ─────────────────────────────────

def archetype_people_pleaser(player: Player, card: Card, rng: random.Random) -> str:
    """Always picks the option that benefits others, often at personal cost.
    
    Decision heuristic:
    - Maximizes cross-player TT gain
    - Prioritizes empathy and coaching tags
    - Neglects own MP/SP progression
    - Inconsistency: 10% (usually very consistent — people pleasers are predictable)
    - Never changes behavior based on personal standing
    """
    def score(o: str) -> float:
        tags = _behavior_tags(o, card)
        d = _stat_deltas(o, card)
        base = 0.0
        
        # Cross-player benefit is paramount
        cross_tt = card.cross_player_tt_delta(o)
        base += cross_tt * 4.0
        
        # People-pleasing tags
        if _has_tag(o, card, "empathy"):
            base += 3.0
        if _has_tag(o, card, "coaching"):
            base += 2.5
        if _has_tag(o, card, "collaboration"):
            base += 2.0
        
        # Own progression is secondary (only matters a little)
        base += d["mp"] * 0.2 + d["sp"] * 0.2
        # TT self-gain is nice but not primary
        base += d["tt"] * 0.5
        
        # Avoid options that look "selfish" (high personal gain, no team benefit)
        if d["mp"] + d["sp"] > 4 and cross_tt <= 0:
            base -= 1.0
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.3, inconsistency_pct=0.10)


# ── ARCHETYPE 3: MICROMANAGER ───────────────────────────────────

def archetype_micromanager(player: Player, card: Card, rng: random.Random) -> str:
    """Prefers options involving control, oversight, and direct management.
    
    Decision heuristic:
    - Strongly prefers options tagged with 'control'
    - Values performance monitoring and coaching (but as control, not development)
    - Dislikes delegation (sees it as loss of control)
    - Inconsistency: 15% (second-guesses themselves)
    - Gets MORE controlling under stress
    """
    stress = _stress_factor(player)
    
    def score(o: str) -> float:
        tags = _behavior_tags(o, card)
        d = _stat_deltas(o, card)
        base = d["mp"] * 0.5 + d["sp"] * 0.3  # Some stat awareness
        
        # Control is the primary driver
        if _has_tag(o, card, "control"):
            base += 4.0 * (1.0 + stress * 0.5)  # More controlling under stress
        if _has_tag(o, card, "coaching"):
            base += 2.0  # Coaching as a form of control
        if _has_tag(o, card, "decisiveness"):
            base += 1.5  # Decisive = in charge
        
        # Dislikes delegation/collaboration (loss of control)
        if _has_tag(o, card, "collaboration"):
            base -= 1.5
        if _has_tag(o, card, "adaptability"):
            base -= 1.0  # Adaptability = unpredictability = loss of control
        
        # TT gain is only valued if it comes from their direct action
        base += d["tt"] * 0.3
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.4, inconsistency_pct=0.15)


# ── ARCHETYPE 4: CONTROLLER ─────────────────────────────────────

def archetype_controller(player: Player, card: Card, rng: random.Random) -> str:
    """Dominant leader who maximizes own stats, sees TT as secondary.
    
    Decision heuristic:
    - Maximizes MP (mindset = personal capability)
    - SP is secondary
    - TT is barely considered ("I'll lead, they'll follow")
    - Reputation matters (wants to look good)
    - Inconsistency: 5% (very confident, rarely second-guesses)
    """
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        base = d["mp"] * 2.0 + d["sp"] * 1.0 + d["tt"] * 0.1  # TT barely matters
        
        # Reputation is important for image
        base += d["reputation"] * 1.5
        
        # Dislikes options that give others credit
        cross_tt = card.cross_player_tt_delta(o)
        if cross_tt > 0:
            base -= 0.5  # Mild dislike of sharing credit
        
        # Tags: decisiveness is valued, empathy is seen as weakness
        tags = _behavior_tags(o, card)
        if _has_tag(o, card, "decisiveness"):
            base += 1.0
        if _has_tag(o, card, "empathy"):
            base -= 0.5  # "Soft" — not bad, but not valued
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.2, inconsistency_pct=0.05)


# ── ARCHETYPE 5: HERO SYNDROME ──────────────────────────────────

def archetype_hero_syndrome(player: Player, card: Card, rng: random.Random) -> str:
    """Takes the dramatic/risky option, especially when things are going badly.
    
    Decision heuristic:
    - When team is struggling (recent outcomes negative): picks highest-risk option
    - When things are going well: slightly more balanced
    - Prefers options with high variance
    - Values risk_taking tag highly
    - Inconsistency: 25% (impulsive)
    - Gets MORE impulsive under stress
    """
    stress = _stress_factor(player)
    recent = _recent_outcome(player)
    
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        base = 0.0
        
        # Base stat consideration
        base += d["mp"] * 0.3 + d["sp"] * 0.3 + d["tt"] * 0.3
        
        # Risk preference scales with stress + recent bad outcomes
        risk_appetite = 0.5 + stress * 0.5 + (0.3 if recent < 0 else 0)
        
        # Variance as proxy for "heroic potential"
        mp, sp, tt = d["mp"], d["sp"], d["tt"]
        mean = (mp + sp + tt) / 3
        variance = ((mp - mean) ** 2 + (sp - mean) ** 2 + (tt - mean) ** 2) ** 0.5
        base += variance * risk_appetite * 2.0
        
        # Risk-taking tag is exciting
        if _has_tag(o, card, "risk_taking"):
            base += 3.0 * risk_appetite
        
        # Decisiveness = heroic action
        if _has_tag(o, card, "decisiveness"):
            base += 1.5 * risk_appetite
        
        # Cross-player benefit = hero saving others
        cross_tt = card.cross_player_tt_delta(o)
        if cross_tt > 0:
            base += cross_tt * 2.0 * risk_appetite
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.6, inconsistency_pct=0.25)


# ── ARCHETYPE 6: POLITICAL PLAYER ──────────────────────────────

def archetype_political_player(player: Player, card: Card, rng: random.Random) -> str:
    """Maximizes reputation, avoids anything that could cause blame.
    
    Decision heuristic:
    - Reputation is the primary metric
    - Avoids options with TT cost (visible to team)
    - Avoids options with high risk_taking tag (risk of visible failure)
    - Cross-player effects are valued only if they improve reputation
    - Inconsistency: 10% (calculating, but can misread situations)
    - Very consistent once they find a "safe" pattern
    """
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        tags = _behavior_tags(o, card)
        base = 0.0
        
        # Reputation is king
        base += d["reputation"] * 4.0
        
        # TT matters for visibility (high TT = trusted = good politics)
        if d["tt"] >= 0:
            base += d["tt"] * 1.5
        else:
            base += d["tt"] * 2.0  # TT loss is doubly bad politically
        
        # MP/SP are background — only care if they're visibly low
        if player.mp < 8:
            base += d["mp"] * 1.0  # Need to look capable
        if player.sp < 8:
            base += d["sp"] * 1.0
        else:
            base += d["mp"] * 0.2 + d["sp"] * 0.2
        
        # Avoid visible risk
        if _has_tag(o, card, "risk_taking"):
            base -= 2.0  # Risk of visible failure
        
        # Collaboration and empathy are politically valuable
        if _has_tag(o, card, "collaboration"):
            base += 1.0
        if _has_tag(o, card, "empathy"):
            base += 1.0
        
        # Cross-player benefit = political capital
        cross_tt = card.cross_player_tt_delta(o)
        base += cross_tt * 2.0
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.3, inconsistency_pct=0.10)


# ── ARCHETYPE 7: SERVANT LEADER ─────────────────────────────────

def archetype_servant_leader(player: Player, card: Card, rng: random.Random) -> str:
    """Team-first but with healthy boundaries — the ideal leadership model.
    
    Decision heuristic:
    - Balances team benefit with personal capability
    - Values TT and cross-player effects equally
    - MP and SP are maintained (you can't serve if you're incapable)
    - Under stress: briefly shifts to self-preservation, then returns to team
    - Inconsistency: 12% (reflective, occasionally unsure)
    - Adapts strategy based on level (more team-focused at higher levels)
    """
    stress = _stress_factor(player)
    level = player.current_level
    
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        tags = _behavior_tags(o, card)
        base = 0.0
        
        # Level-aware weighting
        if level == "basecamp":
            # Focus on building own capability first
            base += d["mp"] * 1.0 + d["sp"] * 1.0 + d["tt"] * 0.5
        elif level == "camp":
            # Balance self and team
            base += d["mp"] * 0.6 + d["sp"] * 0.6 + d["tt"] * 1.0
        else:  # summit
            # Team is primary at the top
            base += d["mp"] * 0.3 + d["sp"] * 0.3 + d["tt"] * 1.5
        
        # Cross-player benefit
        cross_tt = card.cross_player_tt_delta(o)
        base += cross_tt * 2.0
        
        # Leadership tags
        if _has_tag(o, card, "coaching"):
            base += 2.0
        if _has_tag(o, card, "collaboration"):
            base += 1.5
        if _has_tag(o, card, "empathy"):
            base += 1.5
        if _has_tag(o, card, "adaptability"):
            base += 1.0
        
        # Under stress: brief self-preservation shift
        if stress > 0.5:
            if d["mp"] < 0 or d["sp"] < 0:
                base -= 1.0  # Self-preservation kicks in
            if d["tt"] > 0:
                base += 0.5  # Still values team
        
        # Reputation matters but isn't primary
        base += d["reputation"] * 0.5
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.3, inconsistency_pct=0.12)


# ── ARCHETYPE 8: PERFECTIONIST ──────────────────────────────────

def archetype_perfectionist(player: Player, card: Card, rng: random.Random) -> str:
    """Seeks the "safest" option with the most predictable positive outcome.
    
    Decision heuristic:
    - Minimizes variance (wants predictable outcomes)
    - Penalizes any option that involves stat loss
    - Over-analyzes: takes longer to decide (represented by higher noise)
    - Inconsistency: 18% (analysis paralysis — sometimes picks randomly because overthinking)
    - Under stress: becomes even more risk-averse
    """
    stress = _stress_factor(player)
    
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        base = 0.0
        
        # All-positive options are strongly preferred
        all_positive = (d["mp"] >= 0 and d["sp"] >= 0 and d["tt"] >= 0)
        if all_positive:
            base += 3.0
        else:
            # Penalize any stat loss heavily
            if d["mp"] < 0:
                base += d["mp"] * 2.0  # Double penalty
            if d["sp"] < 0:
                base += d["sp"] * 2.0
            if d["tt"] < 0:
                base += d["tt"] * 2.5  # TT loss is worst (team impact)
        
        # Low variance preferred
        mp, sp, tt = d["mp"], d["sp"], d["tt"]
        mean = (mp + sp + tt) / 3
        variance = ((mp - mean) ** 2 + (sp - mean) ** 2 + (tt - mean) ** 2) ** 0.5
        base -= variance * (1.5 + stress)  # Even more risk-averse under stress
        
        # Base stat accumulation (safe growth)
        base += (d["mp"] + d["sp"]) * 0.5 + d["tt"] * 0.5
        
        # Tags: control and coaching are "methodical"
        if _has_tag(o, card, "control"):
            base += 1.0
        if _has_tag(o, card, "coaching"):
            base += 0.5
        
        # Risk-taking is terrifying
        if _has_tag(o, card, "risk_taking"):
            base -= 2.0 * (1.0 + stress)
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.5, inconsistency_pct=0.18)


# ── ARCHETYPE 9: OPPORTUNIST ───────────────────────────────────

def archetype_opportunist(player: Player, card: Card, rng: random.Random) -> str:
    """Switches strategy based on what's working — adapts but lacks consistency.
    
    Decision heuristic:
    - Tracks recent outcomes: if current approach is working, continue
    - If struggling: switch to whatever the card offers most of
    - No consistent leadership pattern — follows the path of least resistance
    - Inconsistency: 30% (highest of all archetypes — inherently inconsistent)
    - LRA fingerprint should show "contradictory" on many items
    """
    recent = _recent_outcome(player)
    turns = len(player.turns)
    
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        tags = _behavior_tags(o, card)
        base = 0.0
        
        # If recent outcomes are good, lean toward options similar to recent choices
        if recent > 2:
            # Things are going well — pick the "safe" option (continue the pattern)
            base += (d["mp"] + d["sp"] + d["tt"]) * 0.3
            if d["tt"] >= 0:
                base += 1.0
        elif recent < -1:
            # Things are going badly — pivot to whatever looks best right now
            base += max(d["mp"], d["sp"], d["tt"]) * 1.5  # Chase the biggest single gain
        else:
            # Neutral — pick whatever has the most total
            base += (d["mp"] + d["sp"] + d["tt"]) * 0.5
        
        # Reputation opportunism
        base += d["reputation"] * 0.8
        
        # Cross-player benefit is nice if it doesn't cost much
        cross_tt = card.cross_player_tt_delta(o)
        if cross_tt > 0 and d["tt"] >= 0:
            base += cross_tt * 1.0
        elif cross_tt > 0 and d["tt"] < 0:
            base += cross_tt * 0.2  # Only if cheap
        
        # No consistent tag preference — opportunists follow the situation
        # This means they'll pick up various tags inconsistently
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.7, inconsistency_pct=0.30)


# ── ARCHETYPE 10: CONSENSUS SEEKER ──────────────────────────────

def archetype_consensus_seeker(player: Player, card: Card, rng: random.Random) -> str:
    """Alternates between options to show "balanced" decision-making.
    
    Decision heuristic:
    - Tends to alternate A/B (avoids always picking same option)
    - Slight preference for options with collaboration tag
    - Wants to be seen as fair and balanced
    - Inconsistency: 35% (highest — genuinely unsure, seeks input)
    - Pattern: will sometimes pick the "worse" option to show flexibility
    """
    # Count recent A vs B choices
    recent_opts = [t.chosen_option for t in player.turns[-5:]]
    a_count = recent_opts.count("A")
    b_count = recent_opts.count("B")
    
    # Bias toward the less-chosen option (consensus = balance)
    balance_bias = 0.0
    if a_count > b_count:
        balance_bias = 1.5  # Lean toward B
    elif b_count > a_count:
        balance_bias = -1.5  # Lean toward A
    
    def score(o: str) -> float:
        d = _stat_deltas(o, card)
        tags = _behavior_tags(o, card)
        base = 0.0
        
        # Stats matter somewhat
        base += d["mp"] * 0.4 + d["sp"] * 0.4 + d["tt"] * 0.4
        
        # Balance pressure
        if o == "A":
            base += balance_bias
        else:
            base -= balance_bias
        
        # Collaboration is valued
        if _has_tag(o, card, "collaboration"):
            base += 2.0
        if _has_tag(o, card, "empathy"):
            base += 1.5
        
        # Decisiveness is slightly uncomfortable (too assertive)
        if _has_tag(o, card, "decisiveness"):
            base -= 0.5
        
        # Cross-player benefit is good (shows concern for others)
        cross_tt = card.cross_player_tt_delta(o)
        base += cross_tt * 1.5
        
        # Avoid extreme options (too risky OR too selfish)
        total = d["mp"] + d["sp"] + d["tt"]
        if total > 8:
            base -= 0.5  # "Too good to be true"
        if total < 0:
            base -= 1.0  # Too costly
        
        return base
    
    return _pick_with_noise(card, score, rng, noise_std=0.6, inconsistency_pct=0.35)


# ── REGISTRY ────────────────────────────────────────────────────

PSYCHOLOGICAL_ARCHETYPES: dict[str, StrategyFn] = {
    "conflict_avoider":    archetype_conflict_avoider,
    "people_pleaser":      archetype_people_pleaser,
    "micromanager":         archetype_micromanager,
    "controller":          archetype_controller,
    "hero_syndrome":        archetype_hero_syndrome,
    "political_player":    archetype_political_player,
    "servant_leader":       archetype_servant_leader,
    "perfectionist":         archetype_perfectionist,
    "opportunist":          archetype_opportunist,
    "consensus_seeker":      archetype_consensus_seeker,
}

# Expected LRA fingerprints (which items each archetype should score high/low on)
# Used by discriminative power analysis to verify the assessment can tell them apart
ARCHETYPE_LRA_EXPECTATIONS: dict[str, dict[str, list[str]]] = {
    "conflict_avoider": {
        "expected_high": ["PtP_M5", "PtP_M2", "R2_S5"],   # empathy-heavy
        "expected_low": ["PtP_S2", "R2_S4"],              # avoids tough conversations
        "expected_contradictory": ["R3_M2"],                # avoids decisive under uncertainty
    },
    "people_pleaser": {
        "expected_high": ["PtP_M5", "R2_S5", "R2_S6"],    # care + engagement + coaching
        "expected_low": ["PtP_M4", "R1_M1"],              # neglects own progression
        "expected_contradictory": ["R3_M2"],                # not decisive
    },
    "micromanager": {
        "expected_high": ["R2_S3", "R2_S6", "PtP_S1"],   # monitoring + coaching + RCA
        "expected_low": ["R2_M1", "R2_S1"],                # delegation = loss of control
        "expected_contradictory": ["R3_S5"],                # cross-org = loss of control
    },
    "controller": {
        "expected_high": ["PtP_M4", "R1_M1", "R1_M2"],    # get things done + ownership
        "expected_low": ["PtP_M5", "R2_S5"],              # neglects team
        "expected_contradictory": ["R2_M1"],                # delegation vs control
    },
    "hero_syndrome": {
        "expected_high": ["R3_M2", "PtP_M1"],            # decisive under uncertainty
        "expected_low": ["R2_S8", "R1_S3"],              # no systems/process
        "expected_contradictory": ["R2_S3"],              # monitoring vs heroic action
    },
    "political_player": {
        "expected_high": ["R2_S5", "R2_S9", "PtP_M2"],   # engagement + upward comm + open input
        "expected_low": ["PtP_M1", "R3_M2"],              # avoids risk
        "expected_contradictory": ["R2_S4"],                # tough conversations = political risk
    },
    "servant_leader": {
        "expected_high": ["PtP_M5", "R2_M1", "R2_S5", "R2_S6"],  # care + team + engagement + coaching
        "expected_low": [],                                # no clear blind spot
        "expected_contradictory": [],                        # consistent pattern
    },
    "perfectionist": {
        "expected_high": ["R1_S1", "R2_S3", "R2_S8"],    # consistent delivery + monitoring + SOPs
        "expected_low": ["PtP_M3", "R3_M2"],              # avoids learning opportunities + uncertainty
        "expected_contradictory": ["PtP_M1"],               # integrity under pressure vs safety
    },
    "opportunist": {
        "expected_high": [],                                # no consistent high
        "expected_low": [],                                # no consistent low
        "expected_contradictory": [                          # many contradictions expected
            "PtP_M1", "PtP_M2", "R1_M1", "R2_M1", "R2_S4",
        ],
    },
    "consensus_seeker": {
        "expected_high": ["PtP_M2", "R2_S5"],             # open input + engagement
        "expected_low": ["PtP_S2", "R3_M2"],             # avoids assertive + decisive
        "expected_contradictory": ["R1_M2"],                # initiative vs consensus
    },
}


def get_archetype(name: str) -> StrategyFn:
    if name not in PSYCHOLOGICAL_ARCHETYPES:
        raise ValueError(
            f"Unknown archetype: {name}. "
            f"Available: {list(PSYCHOLOGICAL_ARCHETYPES.keys())}"
        )
    return PSYCHOLOGICAL_ARCHETYPES[name]
