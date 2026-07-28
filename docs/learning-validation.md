# The Summit v2 — Learning Validation Framework

> **Purpose:** Define how The Summit validates that genuine leadership learning has occurred — not just that a player completed the game, but that observable behavioral evidence supports the conclusion that a competency was practiced, internalized, or transferred.
> **Audience:** Facilitators, HR/L&D professionals, product owners, curriculum designers, quality assurance

---

## Overview

Learning validation in The Summit operates on three levels:

| Level | Question | Mechanism |
|-------|----------|----------|
| **Evidence** | Did the player encounter and respond to situations requiring the competency? | BehaviorTracker evidence events |
| **Confidence** | Is there enough data to conclude anything meaningful? | Confidence scoring with thresholds |
| **Transfer** | Did the player commit to applying the learning in real life? | Real-world challenge + follow-up system |

A learning outcome is only considered "validated" when all three levels produce affirmative signals. Evidence without confidence is noise. Confidence without transfer is intellectual understanding without behavior change.

---

## Level 1: Evidence Validation

### What Counts as Evidence

The Summit does not validate learning through self-reported surveys or post-game questionnaires. Every conclusion must be traceable to an observable, system-recorded decision.

An evidence event is a tuple:

```
Evidence = {
  decision_id:        GameTurn.id,
  dimension:          string,
  signal:             positive | negative | neutral,
  magnitude:          1 | 2,
  context:            { level, turn_number, crisis, opposite_option_delta },
  timestamp:          GameTurn.created_at
}
```

### Evidence Source Hierarchy

Not all evidence is equal. The system assigns reliability weights to prevent weak signals from drowning strong ones.

| Source | Reliability | Weight | Example |
|--------|-------------|--------|--------|
| **Explicit tag** | High | 1.0 | Card author declared `behavior_tags: ["empathy", "collaboration"]` for the chosen option |
| **Game event** | Medium | 0.8 | Observable system events: promise kept/broken, cross-player effects applied, timeout/auto-play |
| **Pattern detection** | Low | 0.4 | Sequences of decisions: same option chosen 5+ consecutive turns → adaptability negative signal |

### Structural Inference Removal

The current implementation (BehaviorTracker Task 3 Rewrite) explicitly removed structural inference from stat deltas. The original approach of deriving behavior from MP/SP/TT changes was classified as "garbage in" — a player choosing TT+2 may have done so because it was mathematically optimal, not because they value collaboration.

Evidence must come from:
1. **What the card author intended** (explicit tags)
2. **What the player actually did in the game world** (game events)
3. **Extreme patterns that are unambiguously meaningful** (same-option repetition)

### Validation Criteria per Evidence Type

#### Explicit Tags (Reliability: 1.0)

| Criterion | Validation Check |
|-----------|-----------------|
| Tag exists on chosen option | Card JSON `options[N].behavior_tags` contains the dimension |
| Tag is signed | Tag has a positive or negative value (not just present) |
| Card is not trivial | Both options have non-identical outcomes |

**Failure mode:** A card author tags every option with every positive behavior. **Mitigation:** Card audit process (see Card Audit Report) validates that trade-offs are genuine.

#### Game Events (Reliability: 0.8)

| Event Type | Evidence Generated | Validation |
|------------|-------------------|------------|
| Promise created | `collaboration: positive` (magnitude 1) | System-generated, cannot be faked |
| Promise fulfilled | `collaboration: positive` (magnitude 2) | Requires previous promise + player action |
| Promise broken | `collaboration: negative` (magnitude 2) | System-detected failure |
| Cross-player positive effect | `collaboration: positive` or `coaching: positive` (magnitude 2) | Requires card to define cross-player effects |
| Cross-player negative effect | `empathy: negative` or `collaboration: negative` (magnitude 2) | Same |
| Timeout / auto-play | `decisiveness: negative` (magnitude 2) | System-generated, reliable |

#### Pattern Detection (Reliability: 0.4)

| Pattern | Evidence Generated | Validation |
|---------|-------------------|------------|
| Same option letter 5+ consecutive turns | `adaptability: negative` (magnitude 2) | Extremely reliable — no legitimate reason for this |
| Strategy shift after dysfunction | `adaptability: positive` (magnitude 2) | Requires before/after comparison |

Pattern inference is deliberately limited. The system only detects patterns that are unambiguously meaningful. Speculative patterns ("player seems to prefer MP over SP") are not used.

### Contextual Modifiers

Evidence is not interpreted in a vacuum. Context modifiers adjust the effective magnitude:

| Context | Modifier | Rationale |
|---------|----------|-----------|
| Crisis card (krisis type) | 1.5x | Decisions under pressure reveal more about character |
| Summit level | 1.2x | Higher-stakes decisions are more diagnostic |
| Turn progression | 1.0–1.5x | Later decisions are more revealing (patterns established) |
| Opposite option gap | Proportional | Choosing A when B had +3 more TT = stronger signal than when B had +1 more |

---

## Level 2: Confidence Validation

### Why Confidence Matters

A player who completes 3 turns has made 3 decisions. Even if all 3 showed empathy, concluding "this player is empathetic" would be statistically irresponsible. The confidence system prevents the game from making claims it cannot support.

### Confidence Calculation

```
evidence_weight = sum of (magnitude * source_reliability * context_modifier) for all evidence in dimension
min_weight_for_confidence = 4.0

raw_confidence = min(1.0, evidence_weight / min_weight_for_confidence)
```

### Consistency Adjustment

A dimension with contradictory evidence has reduced confidence even if total evidence weight is high.

```
positive_weight = sum of evidence_weight for positive signals
negative_weight = sum of evidence_weight for negative signals
total_weight = positive_weight + negative_weight

consistency = abs(positive_weight - negative_weight) / total_weight
effective_confidence = raw_confidence * (0.5 + 0.5 * consistency)
```

A dimension where positive and negative evidence are perfectly balanced (consistency = 0) caps at 50% confidence regardless of volume.

### Confidence Tiers and Reporting Language

| Confidence | Label | Reporting Language | Implication for Validation |
|------------|-------|-------------------|--------------------------|
| 0.0 – 0.25 | Speculative | "Insufficient evidence" | **NOT validated.** Do not report as a finding. |
| 0.25 – 0.5 | Emerging | "Suggests" | Partially validated. Useful for discussion, not conclusions. |
| 0.5 – 0.75 | Established | "Indicates" | **Validated.** Can be reported as a finding with appropriate hedging. |
| 0.75 – 1.0 | Confident | "Demonstrates" | **Strongly validated.** High confidence in the finding. |

### Minimum Data Requirements

| Requirement | Threshold | Rationale |
|-------------|-----------|-----------|
| Minimum turns for any profile | 5 turns | Fewer than 5 turns cannot establish a pattern |
| Minimum evidence for strength/blind spot label | 2 evidence points | Prevents single-event classification |
| Minimum weight for confidence | 4.0 | Ensures sufficient evidence volume and quality |
| Minimum consistency for classification | > 0.3 | Contradictory evidence below this prevents labeling |

### Edge Cases

| Edge Case | Handling | Validation Implication |
|-----------|----------|----------------------|
| < 5 turns played | Profile = "Insufficient Data," no classifications | Learning cannot be validated with insufficient data |
| Consistency < 0.3 | Dimension = "Complex/Inconsistent" | Learning occurred but is contradictory — report the tension |
| All dimensions speculative | Overall profile = "Emerging" | No validated learning outcomes, but data exists for future sessions |
| Identical option outcomes | No evidence generated | Card failed to create a learning moment (card quality issue) |
| Timeout/auto-play | Only decisiveness evidence generated | Player's choice was not their own — other dimensions cannot be evaluated |

---

## Level 3: Transfer Validation

### The Transfer Problem

Leadership development fails at the transfer stage more than any other. A player can demonstrate empathy in a game and immediately revert to authoritarian behavior at work. The Summit's transfer validation attempts to bridge this gap.

### Transfer Mechanisms

#### 3A. Real-World Challenge

At game end, each player receives a specific, time-bound challenge tied to their blind spots:

| Blind Spot | Example Challenge | Timeframe |
|------------|-----------------|-----------|
| risk_taking | "Identify one decision you've been postponing. Make it with 70% of the data this week." | 1 week |
| collaboration | "Before your next team decision, ask 3 people for input. Listen without defending." | 1 week |
| empathy | "Have a 15-minute conversation with a colleague focused entirely on their experience." | 1 week |
| coaching | "Assign one stretch task to a team member who's ready for the next level." | 1 week |
| decisiveness | "Make one important decision within 24 hours without requesting additional input." | 1 week |
| control | "Before your next decision, ask 3 team members for their opinion — and listen." | 1 week |
| adaptability | "Change one work process or routine that's been running for 6+ months. Try it for a week." | 1 week |

**Validation criterion:** The challenge is specific, actionable, time-bound, and tied directly to the player's demonstrated behavior in the game.

#### 3B. Challenge Follow-Up (ChallengeFollowUpService)

The system tracks follow-up on real-world challenges through the `RealWorldChallenge` model:

| Field | Purpose |
|-------|---------|
| `challenge_text` | The specific challenge given to the player |
| `status` | `pending` → `attempted` → `completed` / `skipped` |
| `player_reflection` | Free-text reflection on what happened |
| `follow_up_at` | Scheduled check-in date (typically 1 week after game) |

**Validation criterion:** A challenge is only considered "transferred" when the player reports `attempted` or `completed` AND provides a reflection.

#### 3C. Vocabulary Transfer

Even without explicit follow-up, the game creates vocabulary for self-reflection:

- The player learns to label their behavior: "I default to control under pressure."
- The player can use this vocabulary in coaching conversations.
- The player has a concrete reference point: "In the game, when I chose to report my colleague's error, it cost me TT-2 but I felt it was right."

**Validation criterion:** Vocabulary transfer is validated if the player uses dimension-specific language in their post-game reflection or coaching conversation.

### Transfer Validation Matrix

| Transfer Signal | Evidence | Weight |
|----------------|----------|--------|
| Challenge accepted (not skipped) | `RealWorldChallenge.status` = `attempted` or `completed` | High |
| Reflection provided | `RealWorldChallenge.player_reflection` is non-empty | High |
| Reflection uses dimension language | Reflection contains words matching behavioral dimensions | Medium |
| Challenge completed on time | `RealWorldChallenge.follow_up_at` <= scheduled date | Medium |
| Player requests follow-up game | Player creates or joins a new room within 30 days | Low (correlational) |

---

## Curriculum Coverage Validation

### Coverage Standards

Every leadership competency must be tested at every game level to ensure the spiraling curriculum works:

| Requirement | Minimum | Current Status |
|-------------|---------|----------------|
| Cards per competency at Basecamp | 3 | All pass (range: 3–5) |
| Cards per competency at Camp | 3 | All pass (range: 3–5) |
| Cards per competency at Summit | 2 | All pass (range: 2–4) |
| Total cards per competency | 8 | All pass (range: 33–38) |

### Coverage by Competency

| Competency | Basecamp | Camp | Summit | Total | Validated |
|-----------|----------|------|--------|-------|----------|
| Self-Awareness | 5 | 3 | 2 | 9 (33 indirect) | Yes |
| Decision-Making Under Uncertainty | 3 | 4 | 4 | 11 (34 total) | Yes |
| Empathy & Human-Centered Leadership | 2 | 4 | 3 | 10 (37 total) | Yes |
| Team Development & Coaching | 2 | 5 | 4 | 12 (38 total) | Yes |
| Conflict Navigation | 2 | 4 | 4 | 11 (38 total) | Yes |
| Accountability & Integrity | 3 | 3 | 3 | 12 (34 total) | Yes |
| Strategic Thinking | 3 | 3 | 4 | 12 (34 total) | Yes |
| Change Leadership | 3 | 3 | 3 | 11 (34 total) | Yes |

### Card Quality Validation

Coverage alone is insufficient. Cards must also pass quality validation (from Card Audit Report):

| Quality Criterion | Target | Current |
|-------------------|--------|---------|
| Cards passing all 5 audit questions | 100% | 6.7% (4/60) |
| Cards with genuine trade-offs | 100% | 63.3% (38/60) |
| Cards with TT impact on both options | 100% | 47% (28/60) |
| Cards with extra/consequence data | 100% | 0% (0/60) |
| Cards with cross-player effects | 100% | 0% (0/60) |
| Cards with hidden information | 100% | 0% (0/60) |

**Gap:** While curriculum coverage is validated (every competency appears enough times), card quality validation reveals that many cards do not create genuine learning moments. A "Moral Beauty Contest" card (where the "right" answer is mathematically obvious) generates evidence but does not validate learning — the player optimized, not reflected.

### Card Quality Audit Framework

Every card is evaluated against 5 questions:

| # | Question | Learning Validation Implication |
|---|----------|------------------------------|
| Q1 | What leadership behavior is tested? | Must map to a specific dimension, not a generic soft skill |
| Q2 | What is the real trade-off? | Both options must have genuine opportunity cost — no "good vs slightly less good" |
| Q3 | What evidence is generated? | Choice must produce traceable evidence events, not just stat deltas |
| Q4 | Can it be solved mathematically? | If an optimizer can find the "correct" answer by summing deltas → learning is not validated |
| Q5 | Would a real leader struggle? | If an experienced leader would choose instantly → the dilemma is too shallow |

A card fails learning validation if it scores Fail on Q2, Q4, or Q5.

---

## Dimension-Level Validation

### Per-Dimension Evidence Requirements

Each of the 7 leadership dimensions has specific evidence requirements:

| Dimension | Minimum Evidence for Strength | Minimum Evidence for Blind Spot | Key Validation Check |
|-----------|-------------------------------|-------------------------------|---------------------|
| risk_taking | Confidence >= 0.5, score > 0, top N/2 | Confidence >= 0.5, score <= -1, 2+ negative points | Did the player face actual risky choices? |
| collaboration | Same formula | Same formula | Did the player have opportunities to affect others? |
| empathy | Same formula | Same formula | Did the player face situations requiring personal sacrifice for others? |
| decisiveness | Same formula | Same formula | Did the player encounter time pressure or incomplete information? |
| coaching | Same formula | Same formula | Did the player have team members to develop? |
| control | Same formula | Same formula | Did the player face situations where control was a viable option? |
| adaptability | Same formula | Same formula | Did the player encounter situations requiring approach changes? |

### Opposing Dimension Validation

Some dimensions naturally oppose each other. When both are high, the system validates the tension rather than forcing a single label:

| Tension | What It Means | Validation Approach |
|---------|--------------|-------------------|
| risk_taking vs decisiveness | Bold but impulsive, or bold and calculated? | Check if risk_taking choices also had high magnitude deltas (deliberate) |
| collaboration vs control | Empowering or directive? | Check if control signals coincide with cross-player negative effects |
| empathy vs control | Servant leadership or benevolent authoritarianism? | Check if empathy signals involved personal sacrifice |
| coaching vs decisiveness | Developer or director? | Check if coaching signals involved investing in others vs. directing outcomes |

---

## Anti-Gaming Validation

### The Optimizer Problem

A player who treats The Summit as an optimization problem ("maximize MP+SP+TT") rather than a learning experience will generate misleading evidence. The system includes multiple guards against this:

| Guard | Mechanism | Effectiveness |
|-------|-----------|-------------|
| **Math-optimizable card rejection** | Card audit flags cards where delta-sum determines the winner | Prevents evidence from non-dilemma cards (once redesigned) |
| **Moral beauty contest rejection** | Card audit flags cards where one option is unambiguously "better" | Prevents evidence from compliance, not choice |
| **Hidden information** | Cards with hidden_info prevent pure optimization | Forces judgment, not calculation |
| **Delayed consequences** | schedule_event effects create future costs invisible at decision time | Punishes short-term optimization |
| **Conditional triggers** | Effects that depend on current stats (e.g., "if TT <= 3, reputation -2") | Makes the "best" choice context-dependent |
| **Cross-player effects** | Choices that affect other players | Introduces social consequences that pure optimizers ignore |
| **Pattern detection** | Same-option repetition flagged regardless of stat outcomes | Catches "always A" strategies |
| **Consistency factor** | Contradictory evidence reduces confidence | A player who switches between optimizing and reflecting gets lower confidence |

### Validation of Anti-Gaming Measures

| Measure | Status | Gap |
|---------|--------|-----|
| Hidden information on cards | Not implemented (0/60 cards) | High — all information is currently open |
| Delayed consequences on cards | Not implemented (0/60 cards) | High — no future costs exist |
| Cross-player effects on cards | Not implemented (0/60 cards) | High — single-player dynamics in a multiplayer game |
| Conditional triggers on cards | Partially implemented | Medium — some cards have conditional effects |
| Math-optimizable card identification | Complete (card audit) | Low — identified but cards not yet redesigned |
| Pattern detection in BehaviorTracker | Implemented | Low — working as designed |
| Consistency factor in scoring | Implemented | Low — working as designed |

---

## Validation Reporting

### Post-Game Validation Report

After each game, the system generates a structured validation report:

```json
{
  "validation": {
    "evidence_quality": {
      "total_turns": 12,
      "total_evidence_events": 28,
      "explicit_tag_evidence": 18,
      "game_event_evidence": 7,
      "pattern_evidence": 3,
      "turns_with_no_meaningful_choice": 1
    },
    "confidence_summary": {
      "overall_confidence": 0.72,
      "dimensions_at_confident": 3,
      "dimensions_at_established": 2,
      "dimensions_at_emerging": 1,
      "dimensions_at_speculative": 1
    },
    "learning_outcomes_validated": [
      {
        "competency": "Empathy & Human-Centered Leadership",
        "dimension": "empathy",
        "classification": "strength",
        "confidence": 0.8,
        "evidence_count": 5,
        "validation_level": "strongly_validated"
      }
    ],
    "learning_outcomes_partial": [
      {
        "competency": "Decision-Making Under Uncertainty",
        "dimension": "decisiveness",
        "classification": "strength",
        "confidence": 0.45,
        "evidence_count": 3,
        "validation_level": "partially_validated"
      }
    ],
    "learning_outcomes_insufficient": [
      {
        "competency": "Team Development & Coaching",
        "dimension": "coaching",
        "classification": "unexplored",
        "confidence": 0.15,
        "evidence_count": 1,
        "validation_level": "insufficient_data"
      }
    ],
    "transfer_status": {
      "challenge_assigned": true,
      "challenge_type": "empathy",
      "follow_up_scheduled": true,
      "follow_up_completed": false
    },
    "card_quality_flags": {
      "cards_with_no_trade_off": 2,
      "cards_math_optimizable": 1,
      "cards_identical_outcomes": 0
    }
  }
}
```

### Validation Tiers Summary

| Tier | Label | Criteria | Facilitator Action |
|------|-------|----------|-------------------|
| 1 | **Not validated** | Confidence < 0.25 or < 5 turns | Do not report. More data needed. |
| 2 | **Partially validated** | Confidence 0.25–0.5 | Report with heavy hedging. Useful for discussion prompts. |
| 3 | **Validated** | Confidence 0.5–0.75 | Report as finding. Include evidence count and top evidence. |
| 4 | **Strongly validated** | Confidence 0.75–1.0 | Report with confidence. Can be used for coaching plans. |

---

## Validation Gaps and Remediation Plan

### Current Gaps

| Gap | Severity | Impact on Learning Validation | Remediation |
|-----|----------|------------------------------|------------|
| No hidden information on any card | High | Players can optimize instead of judge | Add hidden_info to all neutral cards |
| No delayed consequences on any card | High | No cost for short-term optimization | Add schedule_event to crisis cards |
| No cross-player effects on any card | High | Social learning is absent | Add cross_player effects to at least 1 card per level |
| 22/60 cards fail audit | High | 37% of turns generate weak or no evidence | Redesign failing cards per Card Audit Report |
| 68% of cards have an option with TT=0 | Medium | Many choices have no social consequence | Ensure every option has TT delta != 0 |
| 7 moral beauty contest cards | Medium | Players comply rather than choose | Redesign so both options have legitimate trade-offs |
| Transfer follow-up not validated empirically | Medium | No data on whether real-world change occurs | Add pre/post self-assessment or 360 feedback |
| No baseline assessment | Low | Cannot measure growth, only current state | Add pre-game leadership self-assessment |

### Validation Roadmap

| Phase | Action | Validation Improvement |
|-------|--------|----------------------|
| Phase 1 | Redesign 22 failing cards | +37% of turns generate valid evidence |
| Phase 2 | Add hidden_info to neutral cards | Players cannot optimize; must judge |
| Phase 3 | Add cross_player effects | Social learning becomes possible |
| Phase 4 | Add schedule_event consequences | Short-term optimization has future costs |
| Phase 5 | Implement transfer follow-up measurement | Transfer validation moves from theoretical to empirical |
| Phase 6 | Add pre-game baseline assessment | Growth measurement becomes possible |

---

## Relationship to Other Documents

| Document | Relationship |
|----------|-------------|
| [Leadership Framework](leadership-framework.md) | Defines the evidence model and scoring methodology that this document validates |
| [Leadership Curriculum](leadership-curriculum.md) | Defines the competency map that this document checks for coverage |
| [Player Journey](player-journey.md) | Defines the emotional/cognitive journey that this document validates is occurring |
| [Card Audit Report](card-audit-report.md) | Provides the card quality data that this document uses for evidence quality validation |
| [Domain Model](domain-model.md) | Defines the data structures (PlayerBehavior, LeadershipProfile) that store validation data |
| [Event Engine](event-engine.md) | Defines the system that records evidence events for validation |

---

## Summary

The Summit's learning validation system answers one question: **"Did this player actually learn leadership, or did they just play a game?"**

The answer comes from three layers:

1. **Evidence**: Observable decisions that map to leadership dimensions
2. **Confidence**: Statistical confidence that the evidence represents a real pattern, not noise
3. **Transfer**: A commitment to real-world application, tracked over time

The system is honest about its limitations. It reports "insufficient evidence" when data is lacking. It reports tensions when evidence is contradictory. It does not force a single leadership label when the data supports complexity.

The current implementation has strong theoretical foundations (evidence model, confidence scoring, coverage validation) but significant practical gaps (card quality, missing game mechanics, no empirical transfer measurement). The remediation plan addresses these gaps in priority order.
