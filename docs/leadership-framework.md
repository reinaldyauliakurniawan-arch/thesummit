# The Summit v2 — Leadership Analytics Framework

## Purpose

This document defines the formal framework for inferring leadership behaviors from player decisions in The Summit. Every conclusion in the leadership profile must be traceable to specific, observable decisions. The framework is structural — it defines *how* behaviors are measured, not *what* descriptions to output.

---

## Design Principles

1. **Evidence-first**: No behavior score is generated without a corresponding decision.
2. **Contextual**: The same decision can be positive evidence for one dimension and negative for another.
3. **Statistical**: Profiles carry confidence scores. Low-data profiles are marked speculative.
4. **Traceable**: Every claim in the final report must reference specific evidence.
5. **Extensible**: New dimensions can be added without modifying existing scoring logic.

---

## Evidence Model

### What Counts as Evidence

An evidence point is a tuple:

```
Evidence = {
  decision_id:        GameTurn.id
  dimension:          string
  signal:             positive | negative | neutral
  magnitude:          1 | 2
  context:            { ... }
  timestamp:          GameTurn.created_at
}
```

### Signal Source Hierarchy

Evidence signals come from three sources, in order of reliability:

| Source | Reliability | Description |
|--------|-------------|-------------|
| **Explicit tag** | High | Card author declared a behavior tag for the chosen option |
| **Structural inference** | Medium | Derived from the stat deltas, targets, and timing of the decision |
| **Pattern inference** | Low | Derived from sequences of decisions across multiple turns |

### Signal Magnitude

| Magnitude | Criteria |
|-----------|----------|
| 2 (strong) | The decision was unambiguous — clear, costly, and deliberate |
| 1 (moderate) | The decision was suggestive but could have other interpretations |

A strong signal requires at least one of:
- The player sacrificed a significant stat (delta <= -2) for the behavior
- The decision triggered a cross-player effect benefiting others
- The decision contradicted the player's established pattern (pattern break)

### Contextual Modifiers

Context affects signal interpretation. Every evidence point carries context that can modify its effective magnitude:

| Context Factor | Effect | Example |
|----------------|--------|---------|
| **Crisis mode** | Signals during crisis count at 1.5x magnitude | Risk-taking during krisis card is amplified |
| **Level** | Higher levels amplify leadership signals | Summit decisions count at 1.2x vs basecamp |
| **Turn number** | Later decisions are more revealing (player has established patterns) | Turn 1 decisions are less diagnostic than turn 8 |
| **Opposite option delta** | The gap between chosen and unchosen option affects weight | Choosing -2 TT when alternative was +3 TT = strong signal |

---

## Dimension Definitions

### 1. risk_taking

**Definition**: The willingness to accept uncertain outcomes or sacrifice guaranteed gains for potentially higher rewards.

**Observable in-game behaviors**:
- Choosing options with high stat variance (one stat very high, another very low)
- Choosing options that trigger Risk Die rolls
- Choosing options with hidden consequences
- Choosing options with delayed negative effects for immediate positive effects

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has hidden consequences | 2 | Explicit tag |
| Chosen option sacrifices TT >= 2 for MP or SP gain | 2 | Structural |
| Chosen option has delayed negative effect for immediate positive | 1 | Structural |
| Chosen option has higher variance than alternative | 1 | Structural |
| Player chose option tagged `risk_taking` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Consistently chose the safer option (higher TT) when krisis card present | 2 | Pattern |
| Chose option with guaranteed stats over probabilistic option | 1 | Structural |
| Player chose option tagged `risk_taking` with negative value | 2 | Explicit |

**Weight**: 1.5

---

### 2. collaboration

**Definition**: The tendency to prioritize team outcomes over individual advancement, and to invest in other players' success.

**Observable in-game behaviors**:
- Choosing options with positive cross-player effects
- Choosing options that boost TT (team trust)
- Choosing options that sacrifice personal MP/SP for team benefit
- Fulfilling promises made to other players
- Creating promises that help others

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has cross_player effects targeting other players with positive delta | 2 | Structural |
| Chosen option boosts TT by sacrificing MP or SP | 2 | Structural |
| Player fulfilled a promise | 2 | Explicit (system event) |
| Player chose option tagged `collaboration` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has cross_player effects with negative delta to others | 2 | Structural |
| Consistently chose options with zero cross-player effects when alternatives had positive | 1 | Pattern |
| Player broke a promise | 2 | Explicit (system event) |
| Player chose option tagged `collaboration` with negative value | 2 | Explicit |

**Weight**: 2.0

---

### 3. empathy

**Definition**: The ability to recognize and respond to the emotional states and needs of other players, even at personal cost.

**Observable in-game behaviors**:
- Choosing options that protect other players from harm
- Choosing options that acknowledge team dynamics over efficiency
- Choosing options that prevent shared penalties
- Choosing options with reputation gains tied to social perception

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option prevents shared penalty to team | 2 | Structural |
| Chosen option has positive effect on player with lowest TT | 2 | Structural |
| Chosen option sacrifices personal stat to protect another player | 2 | Structural |
| Player chose option tagged `empathy` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option causes shared penalty to team when alternative didn't | 2 | Structural |
| Chosen option has negative cross_player effect on lowest TT player | 2 | Structural |
| Consistently chose options optimizing personal stats with no team benefit | 1 | Pattern |
| Player chose option tagged `empathy` with negative value | 2 | Explicit |

**Weight**: 1.5

---

### 4. decisiveness

**Definition**: The ability to make firm, clear decisions quickly, especially under pressure or with incomplete information.

**Observable in-game behaviors**:
- Choosing options with strong directional trade-offs (clear winner in one area)
- Choosing options on krisis cards (forced decision under pressure)
- Choosing options with hidden information (deciding without full data)
- Consistency in decision-making patterns

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has max absolute stat delta >= 3 | 2 | Structural |
| Chose decisively on krisis card (didn't time out) | 1 | Structural |
| Chosen option has hidden consequences (decided without full info) | 1 | Structural |
| Player chose option tagged `decisiveness` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Player timed out (auto-played safer option) | 2 | Explicit |
| Consistently chose options with minimal delta (both options near zero) | 1 | Pattern |
| Player chose option tagged `decisiveness` with negative value | 2 | Explicit |

**Weight**: 1.0

---

### 5. coaching

**Definition**: The investment in developing other players' capabilities, even at the expense of one's own progress.

**Observable in-game behaviors**:
- Choosing options that boost other players' SP or MP
- Choosing options that unlock opportunities for other players
- Choosing options that sacrifice personal SP for cross-player skill gains
- Creating promises aimed at helping others develop

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has cross_player effect boosting SP of another player, while sacrificing own SP | 2 | Structural |
| Chosen option has cross_player effect boosting MP of another player, while sacrificing own MP | 2 | Structural |
| Chosen option creates delayed positive effect for team | 1 | Structural |
| Player chose option tagged `coaching` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option has cross_player effect reducing another player's SP or MP | 2 | Structural |
| Never chose an option with positive cross_player skill effect across 5+ turns | 1 | Pattern |
| Player chose option tagged `coaching` with negative value | 2 | Explicit |

**Weight**: 1.5

---

### 6. control

**Definition**: The tendency to direct, constrain, or dominate outcomes, maintaining authority over decisions and resources.

**Observable in-game behaviors**:
- Choosing options that maximize personal stats regardless of team cost
- Choosing options that reduce flexibility (locking in strategies)
- Choosing options that concentrate resources rather than distributing them
- Choosing options that prioritize MP/SP over TT consistently

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option maximizes personal MP+SP at expense of TT (TT delta < -1) | 2 | Structural |
| Chosen option reduces flexibility | 1 | Structural |
| Chosen option concentrates resources (positive resource delta, no cross-player) | 1 | Structural |
| Player chose option tagged `control` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chosen option distributes resources to other players | 2 | Structural |
| Chosen option increases flexibility for team | 1 | Structural |
| Consistently chose TT-positive options when alternatives had higher MP/SP | 1 | Pattern |
| Player chose option tagged `control` with negative value | 2 | Explicit |

**Weight**: 1.0

---

### 7. adaptability

**Definition**: The willingness and ability to change approach based on context, feedback, or new information.

**Observable in-game behaviors**:
- Alternating between different option strategies across turns
- Choosing options with flexibility gains
- Responding differently to similar situations based on new context
- Shifting behavior after receiving negative feedback (dysfunction triggers)

**Positive evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Player chose different option letter (A vs B) than in 2 of last 3 turns | 1 | Pattern |
| Chosen option increases flexibility | 2 | Structural |
| Player shifted strategy after a dysfunction trigger (changed dominant option type) | 2 | Pattern |
| Player chose option tagged `adaptability` | 2 | Explicit |

**Negative evidence signals**:
| Behavior | Magnitude | Source |
|----------|-----------|--------|
| Chose same option letter for 5+ consecutive turns | 2 | Pattern |
| Chosen option reduces flexibility | 1 | Structural |
| Player chose option tagged `adaptability` with negative value | 2 | Explicit |

**Weight**: 1.0

---

## Scoring Methodology

### Dimension Score

The aggregate score for a dimension is a weighted sum of evidence signals, normalized by a confidence factor.

```
dimension_score = (sum of (signal * magnitude * context_modifier * source_reliability))
                  / max(1, evidence_count)
```

Where:
- `signal`: +1 for positive, -1 for negative
- `magnitude`: 1 or 2
- `context_modifier`: 1.0 (default), 1.5 (crisis), 1.2 (summit level), 1.0-1.5 (turn progression)
- `source_reliability`: 1.0 (explicit), 0.7 (structural), 0.4 (pattern)

### Confidence Calculation

Confidence is computed per-dimension and indicates how much data supports the conclusion.

```
evidence_weight = sum of (magnitude * source_reliability * context_modifier) for all evidence in dimension
min_weight_for_confidence = 4.0

confidence = min(1.0, evidence_weight / min_weight_for_confidence)
```

| Confidence Range | Label | Implication |
|------------------|-------|-------------|
| 0.0 - 0.25 | Speculative | Too few data points. Report as "insufficient evidence." |
| 0.25 - 0.5 | Emerging | Direction visible but not reliable. Report as "suggests." |
| 0.5 - 0.75 | Established | Clear pattern. Report as "indicates." |
| 0.75 - 1.0 | Confident | Strong evidence base. Report as "demonstrates." |

### Consistency Factor

A dimension with contradictory evidence should have reduced confidence even if total evidence weight is high.

```
positive_weight = sum of evidence_weight for positive signals
negative_weight = sum of evidence_weight for negative signals
total_weight = positive_weight + negative_weight

consistency = abs(positive_weight - negative_weight) / total_weight
```

The effective confidence is then:

```
effective_confidence = confidence * (0.5 + 0.5 * consistency)
```

A dimension where positive and negative evidence are perfectly balanced (consistency = 0) caps at 50% confidence regardless of volume.

---

## Profile Generation

### Composite Style

The leadership style is derived from the weighted combination of dimension scores.

```
style_vector = {
  risk_taking:    dimension_score * weight,
  collaboration:  dimension_score * weight,
  empathy:        dimension_score * weight,
  decisiveness:   dimension_score * weight,
  coaching:       dimension_score * weight,
  control:        dimension_score * weight,
  adaptability:   dimension_score * weight,
}
```

The primary style is the dimension with the highest weighted score (where confidence >= 0.5). If no dimension meets the confidence threshold, the style is reported as "emerging."

### Secondary Style

The secondary style is the dimension with the second-highest weighted score. This provides nuance — a leader might be "collaborative-empathetic" or "decisive-controlling."

### Opposing Dimensions

Some dimensions are naturally opposing. Recognizing these tensions adds depth to the profile:

| Pair | Tension |
|------|---------|
| risk_taking ↔ decisiveness | Both require firm action, but one seeks uncertainty, the other seeks resolution |
| collaboration ↔ control | Both involve influencing others, but one empowers, the other directs |
| empathy ↔ control | Both involve awareness of others, but one serves, the other manages |
| coaching ↔ decisiveness | Both involve guiding action, but one develops, the other directs |

When opposing dimensions both have high scores, the profile should note the tension rather than forcing a single label.

---

## Strength Detection

### Formal Definition

A strength is a dimension where:
1. Confidence >= 0.5 (established or confident)
2. Dimension score > 0 (positive aggregate)
3. The dimension is in the top N/2 dimensions by score (where N = number of dimensions with confidence >= 0.5)

### Ranking

Strengths are ranked by: `(dimension_score * confidence * weight)`, descending.

### Output

The profile reports up to 3 strengths, each with:
- Dimension name
- Score and confidence
- Count of supporting evidence points
- 2 strongest individual evidence points (for traceability)

---

## Blind Spot Detection

### Formal Definition

A blind spot is a dimension where:
1. Confidence >= 0.5 (the system has enough data)
2. Dimension score <= -1 (negative aggregate — the player demonstrably avoids this behavior)
3. The dimension has at least 2 negative evidence points

### Edge Case: Low Activity

If a dimension has very few evidence points (confidence < 0.25), it is NOT classified as a blind spot. It is classified as "unexplored." This prevents false negatives from lack of opportunity rather than avoidance.

### Ranking

Blind spots are ranked by: `abs(dimension_score * confidence)`, descending.

### Output

The profile reports up to 3 blind spots, each with:
- Dimension name
- Score and confidence
- Count of negative evidence points
- The strongest negative evidence point

---

## Calibration

### Avoiding False Positives

A false positive occurs when the system attributes a behavior to a player who didn't actually exhibit it. Mitigation:

1. **Minimum evidence threshold**: No dimension can be classified as a strength or blind spot with fewer than 2 evidence points.
2. **Source discounting**: Pattern-based evidence (lowest reliability) contributes only 40% of its nominal weight.
3. **Contradiction penalty**: When a dimension has both strong positive and negative evidence, the consistency factor reduces confidence.

### Avoiding False Negatives

A false negative occurs when the system fails to detect a behavior the player actually exhibited. Mitigation:

1. **Structural inference**: Even without explicit tags, the system derives signals from stat deltas and cross-player effects.
2. **Pattern detection**: Sequences of decisions are analyzed for trends, not just individual turns.
3. **Low confidence ≠ absence**: Low-confidence dimensions are reported as "unexplored" rather than "absent."

### Recency Weight

More recent decisions carry slightly more weight than older ones. This accounts for learning and adaptation within the game.

```
recency_factor = 1.0 + (0.1 * min(turn_index / total_turns, 1.0))
```

The most recent turn has a factor of 1.1, the earliest has 1.0. This is a subtle adjustment, not a dramatic one.

---

## Edge Cases

### Insufficient Data (< 5 turns played)

When a player has fewer than 5 turns:
- Report overall profile as "Insufficient Data"
- List individual evidence points without aggregation
- Do NOT classify any dimension as strength or blind spot
- Do NOT generate a leadership style label

### Contradictory Evidence (consistency < 0.3)

When a dimension has nearly equal positive and negative evidence:
- Report the dimension as "Complex" or "Inconsistent"
- Note the tension explicitly
- Provide both the strongest positive and negative evidence
- Do NOT classify as a simple strength or blind spot

### Single-Option Cards (no real choice)

When a card presents options that are effectively identical (both have near-zero deltas and no cross-player effects):
- Generate no evidence from this turn
- Note in the evidence log: "No meaningful choice detected"

### Timeout / Auto-play

When a player times out and the system auto-plays:
- Generate evidence only for `decisiveness` (negative signal: failed to decide)
- Do NOT generate evidence for any other dimension (the decision wasn't the player's)
- Note in evidence: "Auto-played due to timeout"

### All Players Selfish

When all players in a room have low collaboration scores:
- This is valid data, not an error
- The reflection report should note: "This team exhibited low collaborative behavior overall"
- Do NOT normalize or curve scores

---

## Leadership Profile Output Structure

The final profile output (consumed by the Reflection Engine) has this shape:

```json
{
  "profile": {
    "style": {
      "primary": "collaborative",
      "secondary": "empathetic",
      "confidence": 0.75,
      "tensions": ["collaboration vs control"]
    },
    "dimensions": {
      "risk_taking": {
        "score": 2.3,
        "weight": 1.5,
        "confidence": 0.6,
        "consistency": 0.7,
        "evidence_count": 4,
        "classification": "strength"
      }
    },
    "strengths": [
      {
        "dimension": "collaboration",
        "score": 3.1,
        "confidence": 0.8,
        "evidence_count": 5,
        "top_evidence": [...]
      }
    ],
    "blind_spots": [
      {
        "dimension": "risk_taking",
        "score": -2.1,
        "confidence": 0.6,
        "evidence_count": 3,
        "top_evidence": [...]
      }
    ],
    "unexplored": ["coaching"],
    "data_quality": {
      "total_turns": 12,
      "evidence_count": 28,
      "overall_confidence": 0.72
    }
  }
}
```

This structure is consumed by the Reflection Engine (Phase 7 / docs/event-engine.md) to generate the narrative report. The profile contains NO narrative text — only structured data. Narrative generation is a separate concern.
