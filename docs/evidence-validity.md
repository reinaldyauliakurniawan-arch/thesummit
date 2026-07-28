# Evidence Validity — Opportunity Model, Coverage Matrix, Missed Opportunity Tracking

> **Purpose**: Ensure every LRA assessment result is defensible because every participant
> had a fair opportunity to demonstrate each competency.
> **Status**: TASK 1 (Opportunity Model) + TASK 2 (Coverage Validation) + TASK 3 (Missed Opportunity) + TASK 4 (Assessment Fairness) Complete

---

## Core Principle

**No player should receive a low score simply because no opportunity existed.**

Three distinct states must be separated:
1. **No opportunity existed** → "Not enough evidence" (cannot score)
2. **Opportunity existed but player chose not to demonstrate** → low score is fair
3. **Opportunity existed and player demonstrated** → positive score is fair

Before this work, the system conflated states #1 and #2. Now they are tracked separately.

---

## TASK 1 — Opportunity Model

### Definition

For every LRA assessment item, we define:

| Field | Meaning |
|-------|---------|
| `cards_tagging` | How many unique cards tag this item (across all options) |
| `expected_per_game` | Expected opportunities in a typical 20-turn game |
| `min_opportunities` | Minimum card encounters before scoring is fair |
| `limited_coverage` | `true` if expected < 1.5 per game (card pool needs enrichment) |

### Calculation Formula

```
expected_per_game = Σ (turns_in_level × cards_tagging_item_in_level / total_cards_in_level)
```

Typical game: ~7 turns basecamp (20 cards), ~7 turns camp (20 cards), ~6 turns summit (24 cards).

### Opportunity Model Table

| LRA Item | Label | Cards | Expected/Game | Min Opp | Limited |
|----------|-------|-------|---------------|---------|---------|
| **PtP_M1** | Integritas di Bawah Tekanan | 19 | 5.6 | 3 | — |
| **PtP_M2** | Ego Rendah & Terbuka Input | 24 | 6.5 | 3 | — |
| **PtP_M3** | Belajar Terus | 11 | 3.0 | 2 | — |
| **PtP_M4** | Get Things Done | 12 | 3.3 | 2 | — |
| **PtP_M5** | Peduli Orang Lain | 45 | 11.5 | 3 | — |
| **PtP_S1** | Root Cause Analysis | 15 | 4.2 | 2 | — |
| **PtP_S2** | Komunikasi Asertif | 24 | 6.5 | 3 | — |
| **R1_M1** | Benchmark Pursuit | 6 | 2.1 | 2 | — |
| **R1_M2** | Target Ownership | 5 | 1.75 | 2 | — |
| **R1_S1** | Consistent Delivery | 4 | 1.4 | 2 | — |
| **R1_S2** | Proactive Reporting | 5 | 1.75 | 2 | — |
| **R1_S3** | Follow Systems | 2 | 0.7 | 2 | ⚠️ |
| **R1_S4** | Personal Work System | 2 | 0.7 | 2 | ⚠️ |
| **R2_M1** | Success Through Team | 17 | 4.2 | 2 | — |
| **R2_M2** | Value Managerial Work | 13 | 3.2 | 2 | — |
| **R2_S1** | Job Design & Delegation | 7 | 2.0 | 2 | — |
| **R2_S2** | Selecting/Deselecting | 8 | 2.3 | 2 | — |
| **R2_S3** | Performance Monitoring | 13 | 3.2 | 2 | — |
| **R2_S4** | Tough Conversations | 23 | 5.9 | 3 | — |
| **R2_S5** | Team Engagement | 29 | 7.2 | 3 | — |
| **R2_S6** | Coaching | 21 | 5.5 | 3 | — |
| **R2_S7** | Basic Budgeting | 4 | 1.0 | 2 | ⚠️ |
| **R2_S8** | Team Workflow/SOP | 18 | 4.8 | 2 | — |
| **R2_S9** | Upward/Cross Communication | 20 | 5.2 | 3 | — |
| **R3_M1** | Assess Leadership Quality | 10 | 2.5 | 2 | — |
| **R3_M2** | Decisive Under Uncertainty | 12 | 3.0 | 2 | — |
| **R3_S1** | Assessing Leadership | 2 | 0.5 | 2 | ⚠️ |
| **R3_S2** | Organizational Design | 4 | 1.0 | 2 | ⚠️ |
| **R3_S3** | Developing Leaders | 15 | 3.75 | 2 | — |
| **R3_S4** | Strategy Translation | 7 | 1.75 | 2 | — |
| **R3_S5** | Cross-Org Leadership | 17 | 4.25 | 2 | — |

### Limited Coverage Items (⚠️)

5 items have expected opportunities < 1.5 per game. These need card pool enrichment:

| LRA Item | Cards | Expected | Root Cause |
|----------|-------|----------|------------|
| R1_S3 | 2 | 0.7 | Only 2 basecamp cards tag it |
| R1_S4 | 2 | 0.7 | Only 2 basecamp cards tag it |
| R2_S7 | 4 | 1.0 | Only 4 cards (2 basecamp + 2 summit) tag it |
| R3_S1 | 2 | 0.5 | Only 2 summit cards tag it |
| R3_S2 | 4 | 1.0 | Only 4 cards tag it |

**Note**: These items are NOT unassessable — they just have a higher chance of receiving "Not enough evidence" in any single game session. Over multiple sessions, the coverage accumulates.

---

## TASK 2 — Coverage Matrix

### Coverage by Level

| LRA Item | Basecamp | Camp | Summit | Total | Status |
|----------|----------|------|--------|-------|--------|
| PtP_M1 | 6 | 2 | 11 | 19 | ✅ Strong |
| PtP_M2 | 12 | 3 | 9 | 24 | ✅ Strong |
| PtP_M3 | 9 | 2 | 0 | 11 | ✅ Adequate |
| PtP_M4 | 9 | 1 | 2 | 12 | ✅ Adequate |
| PtP_M5 | 9 | 15 | 21 | 45 | ✅ Very Strong |
| PtP_S1 | 4 | 3 | 8 | 15 | ✅ Strong |
| PtP_S2 | 13 | 6 | 5 | 24 | ✅ Strong |
| R1_M1 | 6 | 0 | 0 | 6 | ✅ Basecamp only |
| R1_M2 | 5 | 0 | 0 | 5 | ✅ Basecamp only |
| R1_S1 | 4 | 0 | 0 | 4 | ✅ Basecamp only |
| R1_S2 | 5 | 0 | 0 | 5 | ✅ Basecamp only |
| R1_S3 | 2 | 0 | 0 | 2 | ⚠️ Limited |
| R1_S4 | 2 | 0 | 0 | 2 | ⚠️ Limited |
| R2_M1 | 2 | 9 | 6 | 17 | ✅ Strong |
| R2_M2 | 2 | 3 | 8 | 13 | ✅ Adequate |
| R2_S1 | 0 | 7 | 0 | 7 | ✅ Camp only |
| R2_S2 | 0 | 4 | 4 | 8 | ✅ Adequate |
| R2_S3 | 0 | 7 | 6 | 13 | ✅ Adequate |
| R2_S4 | 3 | 9 | 11 | 23 | ✅ Strong |
| R2_S5 | 3 | 13 | 13 | 29 | ✅ Very Strong |
| R2_S6 | 0 | 10 | 11 | 21 | ✅ Strong |
| R2_S7 | 2 | 0 | 2 | 4 | ⚠️ Limited |
| R2_S8 | 0 | 12 | 6 | 18 | ✅ Strong |
| R2_S9 | 1 | 7 | 12 | 20 | ✅ Strong |
| R3_M1 | 0 | 0 | 10 | 10 | ✅ Summit only |
| R3_M2 | 1 | 3 | 8 | 12 | ✅ Adequate |
| R3_S1 | 0 | 0 | 2 | 2 | ⚠️ Limited |
| R3_S2 | 0 | 1 | 3 | 4 | ⚠️ Limited |
| R3_S3 | 0 | 1 | 14 | 15 | ✅ Strong |
| R3_S4 | 0 | 0 | 7 | 7 | ✅ Adequate |
| R3_S5 | 1 | 3 | 13 | 17 | ✅ Strong |

### Level Stratification (Design Correct)

- **PtP items** span all three levels (as designed — these are universal)
- **R1 items** appear only in basecamp (as designed — leading self)
- **R2 items** appear in camp and summit (as designed — leading others)
- **R3 items** appear primarily in summit (as designed — leading leaders)

### Coverage Distribution

- **Very Strong** (20+ cards): PtP_M5, PtP_M2, PtP_S2, R2_S5, R2_S4, R2_S6
- **Strong** (10-19): PtP_M1, PtP_S1, R2_M1, R2_S8, R2_S9, R3_S5, R3_S3
- **Adequate** (5-9): PtP_M3, PtP_M4, R1_M1, R2_M2, R2_S2, R2_S3, R3_M1, R3_M2, R3_S4
- **Limited** (2-4): R1_S1, R1_S2, R1_S3, R1_S4, R2_S1, R2_S7, R3_S1, R3_S2

### Untagged Cards

4 cards have NO LRA tags on either option:
- `basecamp_mindset_010` — no LRA tags
- `basecamp_skillset_010` — no LRA tags
- `camp_mindset_010` — no LRA tags
- `camp_skillset_010` — no LRA tags

These serve as tutorial/filler cards with no assessment value.

---

## TASK 3 — Missed Opportunity Tracking

### Concept

When a player makes a choice, the UNCHOSEN option's LRA tags represent **missed opportunities**.
This is evidence. Choosing not to delegate IS evidence about delegation.

### Implementation

```
For each turn:
  1. Player chooses option A
  2. System records A's lra_tags as behavioral evidence (proving/disproving)
  3. System checks option B's lra_tags
  4. For each LRA item on B NOT also on A:
     - Record as 'missed_opportunity' with signal 'missed_proving' or 'missed_disproving'
```

### Evidence Weight

Missed opportunities receive **half weight** (0.5) compared to chosen behaviors (1.0).
This is indirect evidence — the player made a different choice for valid reasons.

| Source | Weight | Rationale |
|--------|--------|-----------|
| `lra_tag` (chosen) | 1.0 | Direct evidence — player actively demonstrated |
| `missed_opportunity` | 0.5 | Indirect evidence — player passed up an opportunity |

### Example

Card: `camp_skillset_001` — "Bagikan tugas sprint"
- Option A: "Rotasi tugas membosankan" → tags R2_S1(proving), R2_S5(proving), PtP_M5(proving)
- Option B: "Assign berdasarkan keahlian" → tags R2_S1(proving), R2_S3(proving), R2_S5(disproving)

If player chooses A:
- Direct evidence: R2_S1(proving), R2_S5(proving), PtP_M5(proving)
- Missed opportunity: R2_S3(missed_proving) — player passed up performance monitoring opportunity

If player chooses B:
- Direct evidence: R2_S1(proving), R2_S3(proving), R2_S5(disproving)
- Missed opportunity: PtP_M5(missed_proving) — player passed up caring-for-others opportunity

### Guard: Not Double-Counting

If an LRA item appears on BOTH options (e.g., R2_S1 is "proving" on both A and B),
it is NOT recorded as a missed opportunity. The player encountered the competency
regardless of choice — only the signal differs.

---

## TASK 4 — Assessment Fairness

### Fairness Gate

Before assigning ANY score, the system checks:

```
IF opportunities_presented < min_opportunities:
    RETURN {
        suggested_score: null,
        fairness_status: "no_opportunity" OR "insufficient_opportunity",
        facilitator_explanation: "Not enough evidence. [reason]. Cannot assign a score."
    }
```

### Fairness Status Values

| Status | Meaning | Score |
|--------|---------|-------|
| `fair` | Sufficient opportunities existed | Score assigned normally |
| `no_opportunity` | Zero cards tested this competency | null (no score) |
| `insufficient_opportunity` | Some cards but below minimum | null (no score) |
| `insufficient_evidence` | Opportunities existed but too few observations | null (no score) |

### Facilitator-Defensible Output

Every assessment item now returns:

```json
{
  "label": "Integritas di Bawah Tekanan",
  "suggested_score": 4,
  "opportunities_presented": 5,
  "min_opportunities": 3,
  "proving_count": 4,
  "disproving_count": 1,
  "missed_proving_count": 2,
  "missed_disproving_count": 0,
  "effective_proving": 5.0,
  "effective_disproving": 1.0,
  "quality_level": "strong",
  "fairness_status": "fair",
  "defensible": true,
  "facilitator_explanation": "Evidence for 'Integritas di Bawah Tekanan': 4 of 5 observations support (80%) across 3 context type(s). Quality: strong. Direction: positive. Opportunities presented: 5. Missed proving opportunities: 2. Evidence: ✓ Turn 3 (basecamp_mindset_007), ✓ Turn 8 (camp_mindset_007), ✗ Turn 12 (summit_mindset_005), ✓ Turn 15 (summit_mindset_009), ⊘ Turn 18 (summit_mindset_012) [missed]."
}
```

### No Opportunity Example

```json
{
  "label": "Follow Systems",
  "suggested_score": null,
  "opportunities_presented": 0,
  "min_opportunities": 2,
  "fairness_status": "no_opportunity",
  "defensible": false,
  "facilitator_explanation": "Insufficient evidence for 'Follow Systems'. No card drawn tested this competency. Cannot assign a score — insufficient opportunity to demonstrate this competency."
}
```

---

## Data Model Changes

### New PlayerBehavior Source Types

| Source | Description | Score | Weight |
|--------|-------------|-------|--------|
| `lra_tag` | Chosen option's LRA tag | ±1 | Full |
| `missed_opportunity` | Unchosen option's LRA tag | ±1 | Half |
| `opportunity` | Counter: card presented this item | 0 | None (counter only) |

### Query Pattern

```php
// Count opportunities for an item
$opportunities = PlayerBehavior::where('game_player_id', $player->id)
    ->where('lra_item', 'PtP_M1')
    ->where('source', 'opportunity')
    ->count();

// Get behavioral evidence (chosen + missed)
$evidence = PlayerBehavior::where('game_player_id', $player->id)
    ->where('lra_item', 'PtP_M1')
    ->whereIn('source', ['lra_tag', 'missed_opportunity'])
    ->get();

// Get only chosen behavior
$chosenOnly = PlayerBehavior::where('game_player_id', $player->id)
    ->where('lra_item', 'PtP_M1')
    ->where('source', 'lra_tag')
    ->get();
```

---

## Implementation Files

| File | Changes |
|------|---------|
| `config/summit.php` | Added `lra.opportunity_model` for all 31 items |
| `app/Services/BehaviorTracker.php` | Added `trackLRAOpportunities()`, `trackMissedOpportunities()`, `buildCoverageReport()`, `buildOpportunitySummary()`, updated `getLRAAssessment()` and `assessLRAItem()` |
| `app/Services/ReflectionEngine.php` | Updated `generateLRANarrative()` with fairness summary, updated `findMissedOpportunities()` with LRA-specific misses |

---

## Success Criteria

✅ Every assessment result is defensible because:
1. The system can cite concrete gameplay evidence (which turn, which card)
2. The system can show how many opportunities the player had
3. The system distinguishes "no opportunity" from "chose not to demonstrate"
4. Missed opportunities are tracked and cited as evidence
5. A facilitator asking "Why did you conclude this?" gets concrete evidence
6. A facilitator asking "Was this fair?" gets opportunity counts
