# The Summit v2 — Strategy Validation

## Purpose

Validate that The Summit's game design has no dominant strategy, that every strategy has a meaningful counter, and that the game forces genuine leadership dilemmas rather than mathematical optimization. This document serves as the definitive checklist for Milestone 11 (Anti-Dominant-Strategy Validation) and any future balance pass.

> **Audience:** Playtest coordinators, card designers, game balance engineers
> **Triggered by:** Milestone 11 in `docs/implementation-roadmap.md`
> **Companion docs:** `docs/game-economy.md`, `docs/game-grammar.md`, `docs/card-audit-report.md`

---

## Validation Framework Overview

Strategy validation operates at four layers:

```
Layer 1: Card-Level — Does each individual card force a genuine dilemma?
Layer 2: Stat-Economy — Does the stat system prevent min-maxing?
Layer 3: Cross-Mechanic — Do mechanics interact to block dominant paths?
Layer 4: Session-Level — Does a full game session produce the intended experience?
```

Each layer has specific testable criteria. All four layers must pass for the game to be considered strategically sound.

---

## Layer 1: Card-Level Validation

### 1.1 Dilemma Integrity

Every card must present a **genuine dilemma**: both options must gain something AND lose something. Neither option may be unambiguously "correct."

| Criterion | Test Method | Pass Threshold |
|-----------|-------------|----------------|
| Both options have a net cost | Sum stat deltas per option; at least one negative delta on each | 100% of cards |
| Neither option dominates all stats | No option has ≥ all stats higher than the other | 100% of cards |
| No "moral beauty contest" | The "morally correct" option must have a meaningful cost | 0 moral beauty contests |
| No identical outcomes | Both options must differ in at least 2 stat dimensions | 100% of cards |
| Non-optimizable by simple arithmetic | Total stat sum must not determine the "better" option | 100% of cards |

### 1.2 Selection Split Validation

During playtesting, every card's option selection should fall within an acceptable range.

| Metric | Target | Red Flag |
|--------|--------|----------|
| Option A selection rate | 40–60% | > 70% or < 30% indicates imbalance |
| Consistency across sessions | Same player doesn't always pick same option | > 90% same-option rate across 5+ sessions |
| Context-sensitivity | Selection changes based on game state | Selection rate doesn't vary > 5% regardless of state |

### 1.3 Post-Redesign Audit Status

The original card audit (`docs/card-audit-report.md`) found 22/60 cards failing. The gameplay-first redesign (`docs/gameplay-first.md` TASK 2) addressed these. Validation must confirm:

- [ ] All 22 previously failing cards now pass the 5-question audit framework
- [ ] No new cards were introduced that reintroduce "fake dilemma" or "math-optimizable" patterns
- [ ] Every card has at least one option with TT delta ≠ 0 (fixes Issue 2 from audit)
- [ ] No card has `extra=null` (fixes Issue 1 from audit)
- [ ] At least 1 card per level has a cross-player effect (fixes Issue 4 from audit)
- [ ] At least 30% of neutral cards have hidden information (fixes Issue 3 from audit)

### 1.4 Card-Level Test Cases

```
Test CL-01: For each of 60 cards, verify both options have at least one negative delta.
Test CL-02: For each card, verify no option dominates the other in ALL stat dimensions.
Test CL-03: Play 10 sessions. For each card drawn, record selection rate.
  Expected: No card shows > 70/30 split.
Test CL-04: Identify the 6 "emotional peak" cards (gameplay-first.md TASK 5).
  Verify each produces a measurable emotional response (post-game survey).
Test CL-05: Verify all 60 cards have behavior_tags on at least one choice.
```

---

## Layer 2: Stat-Economy Validation

### 2.1 No Single-Stat Dominance

The stat system must prevent any single stat from being the optimal path to victory.

| Strategy | Expected Outcome | Why It Fails |
|----------|-----------------|--------------|
| Pure MP/SP hoarding | Fails to reach Camp threshold (TT 5 required) | Camp and Summit require TT for level advancement |
| Pure TT hoarding | Fails to meet MP/SP thresholds for any level | Level advancement requires MP AND SP thresholds |
| Reputation maxing | No direct progression benefit; game rewards level over reputation | Scoring formula: `level_value * 10 + final_tt`. Reputation doesn't score. |
| Resource hoarding | Flexibility decreases, limiting future card options | Resources spent to gain MP/SP/TT; hoarding means no progression |
| Flexibility maxing | No direct scoring benefit; flexibility only affects option availability | Flexibility is an enabler, not a score contributor |

### 2.2 Stat Interdependency Validation

Stats must create pressure on each other per `docs/game-economy.md`.

```
MP/SP accumulation → Rope Bridge thresholds → Level advancement → harder cards → TT pressure
TT accumulation → Trust economy → Cross-player effects → Social mechanics
Reputation → Promise credibility → Social influence effectiveness
Resources → Available options → Flexibility → Future choices
Flexibility reduction → Fewer card options → Harder dilemmas
```

**Validation Tests:**

- [ ] A player at MP 14, SP 14, TT 2 cannot reach Camp (blocked by TT 5 requirement)
- [ ] A player at MP 5, SP 5, TT 10 is stuck at Basecamp (blocked by MP/SP 8 threshold)
- [ ] Reputation below -5 causes promise creation to fail or promises to carry higher stakes
- [ ] Flexibility below -5 causes card options to be locked (fewer choices)
- [ ] Resources at 0 prevent choosing options that require resource expenditure

### 2.3 Soft Cap / Hard Cap Validation

| Stat | Soft Cap | Hard Cap | Floor | Validation |
|------|----------|----------|-------|------------|
| MP | 15 | 20 | 0 | Gains above 15 show diminishing returns (e.g., +1 becomes +0.5) |
| SP | 15 | 20 | 0 | Same as MP |
| TT | 10 | 15 | 0 | Team effects are the natural limiter; hard cap prevents exploitation |
| Reputation | 10 | 20 | -10 | Negative floor allows meaningful punishment without elimination |
| Resources | 8 | 15 | 0 | Scarcity creates dilemmas; floor prevents negative hoarding |
| Flexibility | N/A | 10 | -10 | Negative flexibility locks choices — natural punishment |

### 2.4 Stat-Economy Test Cases

```
Test SE-01: Simulate "MP/SP only" strategy for 26 turns.
  Expected: Player stalls at Basecamp (TT < 5 for Camp threshold).
Test SE-02: Simulate "TT only" strategy for 26 turns.
  Expected: Player stalls at Basecamp (MP/SP < 8 for Basecamp rope bridge).
Test SE-03: Simulate "balanced" strategy for 26 turns.
  Expected: Player reaches Camp or Summit. Higher win rate than pure strategies.
Test SE-04: Verify soft cap: player at MP 15 gains +2 MP → effective gain ≤ 1.
Test SE-05: Verify hard cap: player at MP 19 gains +5 MP → MP caps at 20.
Test SE-06: Verify floor: player at MP 1 loses -3 MP → MP floors at 0.
```

---

## Layer 3: Cross-Mechanic Validation

### 3.1 Anti-Snowball Mechanics

These mechanisms prevent early leaders from compounding their advantage.

| Mechanic | How It Counters Runaway | Validation |
|----------|------------------------|------------|
| TT sharing on dysfunction | A leading player's crisis hurts teammates, creating social pressure to help | Player A triggers dysfunction → all other players lose TT → social dynamics shift |
| Reputation penalty for selfish play | Pure self-optimization reduces social capital, limiting social mechanic access | Player with reputation < 0 cannot create effective promises |
| Flexibility reduction | Maxing one stat reduces future options, making late-game harder | Player with flexibility < -3 sees locked card options |
| Consequence accumulation | Aggressive play creates future liabilities that compound | Player who chose 5 risky options has 3+ pending negative consequences by Camp |
| Shared objectives | Individual scoring doesn't guarantee team win | Team with highest collective TT gets bonus at game end |

### 3.2 Comeback Mechanics

Struggling players must have a viable recovery path.

| Mechanic | Recovery Condition | Validation |
|----------|-------------------|------------|
| Cooperative Recovery | Teammates can boost a struggling player's stats | Player with TT < 3 receives help from other players |
| Accumulated Consequences Resolve | Delayed positive effects from earlier decisions | Player who chose long-term options receives unexpected boost at Camp |
| Trust Recovery Loop | Helping a teammate builds your own TT and reputation | Player who helps a struggling teammate gains TT +1, reputation +1 |
| Dysfunction Purification | A shared team crisis can unify the team | Dysfunction trigger + cooperative response → TT boost for all |
| Final Round Equalizer | All players get exactly 1 more turn | No player can be denied their final turn regardless of position |

### 3.3 Social Mechanics as Strategy Counters

| Dominant Tendency | Social Counter | How It Works |
|-------------------|---------------|---------------|
| Pure self-optimization | Reputation decay | Selfish choices reduce reputation → promises become unreliable → social benefits lost |
| Aggressive risk-taking | Promise/debt accumulation | Risky choices create debts → future turns constrained by obligations |
| Over-cautious play | Missed long-term consequences | Safe choices miss delayed positive effects → fall behind in late game |
| TT manipulation | Promise credibility | Breaking promises (for TT gain) destroys credibility → future social effects weakened |
| Stat hoarding | Flexibility reduction | Maxing stats reduces flexibility → fewer card options → forced into bad dilemmas |

### 3.4 Cross-Mechanic Test Cases

```
Test CM-01: Leading player (highest MP/SP) triggers dysfunction.
  Expected: All other players lose TT. Social dynamics shift against leader.
Test CM-02: Player with reputation -3 attempts to create a promise.
  Expected: Promise is created but with reduced credibility or higher stakes.
Test CM-03: Player with flexibility -5 draws a card with 2 options.
  Expected: One option is locked (grayed out). Player faces harder dilemma.
Test CM-04: Player chose 5 risky options in Basecamp. Enters Camp.
  Expected: 3+ pending negative consequences fire in Camp turns 1-3.
Test CM-05: Struggling player (lowest TT) receives help from teammate.
  Expected: Helper gains TT +1, reputation +1. Struggling player's TT increases.
Test CM-06: Simulate 10 games. Track the player in last place at turn 10.
  Expected: ≥ 30% of last-place players recover to finish in top half.
```

---

## Layer 4: Session-Level Validation

### 4.1 Experience Criteria

These are the success criteria from `docs/game-grammar.md` that must be validated through playtesting.

| # | Criterion | Validation Method | Target |
|---|-----------|-------------------|--------|
| 1 | Players think in long-term trade-offs instead of optimizing immediate points | Post-game interview: "Did you consider future consequences?" | ≥ 80% say yes |
| 2 | Players experience meaningful sacrifice | Post-game survey: "Did you have to give up something you wanted?" | ≥ 90% say yes |
| 3 | Players depend on other players to succeed | Post-game survey: "Did you need help from teammates?" | ≥ 70% say yes |
| 4 | Players operate with incomplete information | Observe: Do players discuss hidden info reveals? | ≥ 50% of sessions have hidden info discussion |
| 5 | Players reflect on leadership patterns | Post-game survey: "Did you learn something about your leadership?" | ≥ 80% say yes |
| 6 | Every player leaves with one real-world leadership action | Game summary includes challenge → Player marks it relevant | ≥ 70% accept the challenge |

### 4.2 Dilemma Density Validation

Per `docs/game-economy.md`, the proportion of genuinely hard choices must meet these targets:

| Level | Target Dilemma Density | Validation |
|-------|----------------------|------------|
| Basecamp | 50% | 3-4 of 6-8 decisions should feel hard |
| Camp | 70% | 6-8 of 8-12 decisions should feel hard |
| Summit | 85% | 4-5 of 4-6 decisions should feel hard |

**Measurement:** After each turn, players rate decision difficulty on a 1-5 scale. A "hard" decision scores ≥ 4.

### 4.3 Emotional Peak Validation

Per `docs/game-economy.md`, every session must produce at least 3 memorable moments:

| Peak | Expected Timing | What Creates It |
|------|----------------|----------------|
| First Crisis | Basecamp turns 3-5 | Player's first encounter with real risk and dysfunction trigger |
| First Rope Bridge | Basecamp-Camp transition | The choice to attempt or skip the bridge creates advancement dilemma |
| Summit Crisis Chain | Summit turns 2-4 | Multiple crisis cards with team effects, accumulated consequences resolving |

**Validation:** Post-game survey asks players to list their most memorable moments. ≥ 3 distinct moments should be named per session, matching the expected timing.

### 4.4 No Dominant Strategy — Session-Level Test

This is the core validation from Milestone 11.

```
Test SL-01: Run 10+ simulated games with different strategies.
  Strategies to test:
  A) Aggressive: Always choose highest MP/SP, ignore TT
  B) Social: Always choose highest TT, accept lower MP/SP
  C) Balanced: Weigh all stats equally
  D) Opportunistic: Choose based on game state (adaptive)
  E) Cautious: Minimize all risk, avoid crisis cards when possible

  Expected result:
  - No single strategy wins > 60% of games
  - Strategy D (adaptive) should have the highest win rate (rewards thinking)
  - Strategy A should fail to reach Summit (TT blocked)
  - Strategy B should fail to advance past Basecamp or early Camp (MP/SP blocked)
  - Strategy E should fall behind in late game (missed long-term benefits)

Test SL-02: Track the winner of each game.
  Expected: Different players win across sessions. No player dominates > 60%.

Test SL-03: In every session, verify ≥ 3 memorable dilemmas occurred.
  Method: Post-game survey + event log analysis.

Test SL-04: Verify selfish play results in lower win rate.
  Method: Track reputation trajectory vs final placement.
  Expected: Players ending with reputation < 0 finish lower on average.
```

### 4.5 Decision Frequency Validation

Per `docs/game-economy.md`, each player must make 18-26 decisions per session for reliable behavioral analysis.

| Metric | Target | Validation |
|--------|--------|------------|
| Decisions per player | 18-26 | Count game_turns per player across session |
| Session duration (async) | 4-7 days | Track from first turn to last turn |
| Minimum for reliable profile | 15 turns | Profiles with < 15 turns marked "Insufficient Data" |

---

## Cross-Document Consistency Checks

These checks ensure all design documents agree with each other. Inconsistencies indicate a design bug.

### CD-01: Economy ↔ Grammar Consistency

- [ ] All stat names in `game-economy.md` match primitives in `game-grammar.md`
- [ ] All effect types in `card-schema.md` map to handlers in `event-engine.md`
- [ ] Stat caps in `game-economy.md` match floor/ceiling rules in `event-engine.md`

### CD-02: Grammar ↔ Card Schema Consistency

- [ ] Every primitive in `game-grammar.md` has a JSON representation in `card-schema.md`
- [ ] Every event handler in `event-engine.md` has a corresponding schema definition
- [ ] Condition types in `card-schema.md` match condition evaluation in `event-engine.md`

### CD-03: Economy ↔ Domain Model Consistency

- [ ] All stats listed in `game-economy.md` exist as fields on the Player entity in `domain-model.md`
- [ ] All social mechanics (promises, debts, votes, relationships) in `game-economy.md` have entities in `domain-model.md`
- [ ] Scoring formula in `game-economy.md` matches `GameResult` computation in `domain-model.md`

### CD-04: Leadership Framework ↔ Card Content Consistency

- [ ] Every dimension in `leadership-framework.md` has at least 5 cards with explicit `behavior_tags` for that dimension
- [ ] Evidence sources (explicit, structural, pattern) in `leadership-framework.md` are all generatable from card effects defined in `card-schema.md`
- [ ] Contextual modifiers (crisis mode, level, turn number) in `leadership-framework.md` are all observable from game state in `domain-model.md`

### CD-05: Card Audit ↔ Redesign Verification

- [ ] All 22 cards listed in `card-audit-report.md` as needing major redesign have been updated
- [ ] All 5 systemic issues identified in the audit have been addressed
- [ ] No new cards introduced after the audit reintroduce any of the 5 failure categories

---

## Playtest Protocol

### Pre-Playtest Checklist

- [ ] All 60 cards pass CardValidator (schema validation)
- [ ] Event engine processes all card effects without errors
- [ ] All cross-mechanic tests (CM-01 through CM-06) pass in automated test suite
- [ ] All stat-economy tests (SE-01 through SE-06) pass in automated test suite

### During Playtest

For each session, collect:
1. **Event log** — Full event_log table dump for post-analysis
2. **Turn-by-turn stats** — Player stat snapshots at each turn
3. **Decision timing** — How long each player took per turn (engagement signal)
4. **Selection data** — Which option was chosen for every card drawn
5. **Social interaction log** — All promises, votes, debts, relationship changes
6. **Consequence resolution log** — When and how consequences fired

### Post-Playtest Analysis

1. Run selection split analysis on every card drawn
2. Identify cards with > 70/30 split — flag for rebalance
3. Calculate win rate per strategy archetype
4. Verify dilemma density targets per level
5. Verify emotional peak count per session
6. Check comeback mechanic activation rate
7. Generate per-player leadership profiles and verify confidence levels

### Post-Playtest Survey

Ask each player:
1. Rate each decision on difficulty (1-5)
2. List your 3 most memorable moments
3. Did you feel like you had to sacrifice something meaningful? (yes/no)
4. Did you need help from teammates? (yes/no)
5. Did you learn something about your leadership style? (yes/no)
6. Was there a point where you felt hopeless? (yes/no — comeback validation)
7. Was there a moment where you felt the game was unfair? (yes/no — balance check)

---

## Anti-Patterns to Watch For

| Anti-Pattern | Detection Method | Severity |
|--------------|-----------------|----------|
| **Dominant strategy emerges** | One strategy wins > 60% across 10+ games | CRITICAL — game is broken |
| **Card always chosen** | Any card shows > 70/30 split consistently | HIGH — card needs rebalance |
| **Players optimize instead of feeling** | Post-survey: players report "I just picked the best stats" | HIGH — dilemma density too low |
| **No social interaction** | Zero promises/votes/debts in a session | HIGH — social mechanics not triggering |
| **Early leader always wins** | Player in first place at turn 10 wins > 70% of sessions | MEDIUM — comeback mechanics insufficient |
| **Players feel hopeless** | Post-survey: > 30% report feeling hopeless | MEDIUM — comeback mechanics need strengthening |
| **Flat emotional experience** | Post-survey: < 2 memorable moments named | HIGH — emotional peak placement needs adjustment |
| **Analytics seem random** | Player profiles don't match self-reported leadership style | MEDIUM — evidence model needs calibration |
| **Selfish play wins** | Players with lowest reputation win > 50% of sessions | CRITICAL — anti-snowball mechanics failed |
| **All players reach Summit** | 100% of players reach Summit in > 50% of sessions | MEDIUM — difficulty curve too easy |
| **No players reach Summit** | 0% of players reach Summit in > 50% of sessions | MEDIUM — difficulty curve too hard |

---

## Validation Checklist (Quick Reference)

This is the condensed checklist from `docs/game-economy.md`, expanded with test references.

| # | Question | Ref |
|---|----------|-----|
| 1 | Does this create a meaningful sacrifice? | CL-01, CL-02 |
| 2 | Does this have a counter-strategy? | SE-01–SE-03, SL-01 |
| 3 | Does this map to a real leadership behavior? | CD-04, card audit Q1 |
| 4 | Does this create an emotional moment? | SL-03, emotional peak validation |
| 5 | Does this generate behavioral evidence? | CL-05, CD-04 |
| 6 | Can a selfish player be stopped? | CM-01, CM-02, SL-04 |
| 7 | Can a struggling player recover? | CM-05, CM-06 |
| 8 | Is information incomplete enough to prevent pure optimization? | CD-05 Issue 3 |
| 9 | Do social mechanics activate naturally? | CM-01–CM-06, SL-01 |
| 10 | Does the difficulty curve escalate appropriately? | Dilemma density targets |

---

## Validation Gate for Milestone 11

Milestone 11 (Anti-Dominant-Strategy Validation) passes when ALL of the following are true:

- [ ] **10+ simulated games completed** — no single strategy wins > 60%
- [ ] **Every session has ≥ 3 memorable dilemmas** — verified by post-game survey
- [ ] **Selfish play has lower win rate** — verified by reputation vs placement correlation
- [ ] **Early-game MP/SP hoarding fails** — verified by SE-01
- [ ] **Pure TT hoarding fails** — verified by SE-02
- [ ] **No card has > 70/30 selection split** consistently — verified by CL-03
- [ ] **All 22 previously failing cards now pass** — verified by updated card audit
- [ ] **All 5 systemic audit issues resolved** — verified by CD-05
- [ ] **Comeback rate ≥ 30%** — verified by CM-06
- [ ] **All cross-document consistency checks pass** — verified by CD-01 through CD-04

---

## Appendix A: Strategy Archetypes for Simulation

### Archetype A: The Climber (Aggressive MP/SP)

```
Priority: MP > SP >> TT > Reputation > Resources > Flexibility
Behavior: Always choose the option with highest MP+SP total.
         Ignore TT costs. Accept reputation hits.
Expected outcome: Reaches Basecamp rope bridge but fails Camp threshold (TT < 5).
```

### Archetype B: The Connector (Aggressive TT)

```
Priority: TT >> MP ≈ SP > Reputation > Resources > Flexibility
Behavior: Always choose the option with highest TT.
         Accept lower MP/SP gains.
Expected outcome: Builds strong team relationships but stalls at Basecamp
         (MP/SP < 8 for rope bridge).
```

### Archetype C: The Diplomat (Balanced)

```
Priority: Balanced across all stats
Behavior: Choose the option that keeps stats closest to equilibrium.
         Avoid extreme deltas in any direction.
Expected outcome: Steady progression. Reaches Camp. May reach Summit
         with team support.
```

### Archetype D: The Adaptivist (Context-Aware)

```
Priority: Context-dependent
Behavior: Assess game state each turn. Choose based on:
         - What stat is closest to next threshold?
         - What are teammates' stats? (help the weak)
         - What consequences are pending?
         - What is the current dilemma about?
Expected outcome: Highest win rate. The game should reward thinking.
```

### Archetype E: The Guardian (Cautious)

```
Priority: Minimize risk. Avoid crisis cards when possible.
Behavior: Choose the option with lowest variance.
         Prefer guaranteed gains over probabilistic ones.
         Avoid options with delayed negative consequences.
Expected outcome: Stable but slow progression. Falls behind in late game
         because missed long-term benefits don't compound.
```

---

## Appendix B: Simulation Results Template

```
Simulation Run: <date>
Games Simulated: <N>
Seed: <RNG seed for reproducibility>

┌─────────────┬──────┬───────┬─────────┬──────────┬──────────────┐
│ Strategy    │ Games│ Wins  │ Win %   │ Avg Level│ Avg Final TT│
├─────────────┼──────┼───────┼─────────┼──────────┼──────────────┤
│ Climber     │      │       │         │          │              │
│ Connector   │      │       │         │          │              │
│ Diplomat    │      │       │         │          │              │
│ Adaptivist  │      │       │         │          │              │
│ Guardian    │      │       │         │          │              │
└─────────────┴──────┴───────┴─────────┴──────────┴──────────────┘

Card Selection Splits (cards with > 60/40 split flagged):
<Card ID> — A: __%  B: __%  [FLAG if > 70/30]
...

Emotional Peaks Per Session:
Session 1: <count> peaks at turns <list>
Session 2: <count> peaks at turns <list>
...

Comeback Events:
<player> was in last place at turn <N>, finished in position <P> [RECOVERED/STAYED]
...

Verdict: PASS / FAIL
Failure Reasons (if any):
  - ...
```
