# The Summit v2 — Game Economy

## Purpose

Define the mathematical and emotional curves that make The Summit feel meaningful. Every mechanic must explain WHY it exists. The economy ensures no dominant strategy, every session has memorable dilemmas, and the emotional arc keeps players engaged.

---

## Core Gameplay Loop

### Per-Turn Cycle

```
Draw Card → Read Dilemma → Choose → Observe Effects → React to Consequences
     ↑                                                        │
     └────────────── Next Player's Turn ←─────────────────────┘
```

### Decision Frequency

| Level | Average Turns per Player | Average Decisions | Session Duration (async) |
|-------|-------------------------|-------------------|--------------------------|
| Basecamp | 6-8 | 6-8 | 1-2 days |
| Camp | 8-12 | 8-12 | 2-3 days |
| Summit | 4-6 | 4-6 | 1-2 days |
| **Total** | **18-26** | **18-26** | **4-7 days** |

**Why**: This range ensures enough decisions for behavioral analysis (minimum 15 turns per player for reliable profiles) without causing decision fatigue. Async play means players can take time to consider each choice.

### Decision Complexity

Decisions should feel genuinely hard approximately 60% of the time. If players find decisions easy more than 40% of the time, the card pool needs rebalancing.

**Why**: Easy decisions don't generate meaningful behavioral evidence or emotional engagement. Hard decisions are where leadership character is revealed.

---

## Stat Economy

### Core Stats

| Stat | Purpose | Starting Value | Soft Cap | Hard Cap | Floor |
|------|---------|---------------|----------|----------|-------|
| **MP** (Mindset) | Leading Self progress | 0 | 15 | 20 | 0 |
| **SP** (Skillset) | Leading Others progress | 0 | 15 | 20 | 0 |
| **TT** (Trust Token) | Team trust | 0 | 10 | 15 | 0 |
| **Reputation** | Social standing | 0 | 10 | 20 | -10 |
| **Resources** | Available assets | 2 | 8 | 15 | 0 |
| **Flexibility** | Option breadth | 0 | N/A | 10 | -10 |

**Why these caps**: Soft caps create diminishing returns without hard walls. Hard caps prevent runaway values. Negative floors on reputation and flexibility allow meaningful punishment without player elimination.

### Stat Interdependencies

Stats are not independent. Changes in one stat create pressure on others:

```
MP/SP accumulation → Rope Bridge thresholds → Level advancement → harder cards → TT pressure
TT accumulation → Trust economy → Cross-player effects → Social mechanics
Reputation → Promise credibility → Social influence effectiveness
Resources → Available options → Flexibility → Future choices
Flexibility reduction → Fewer card options → Harder dilemmas
```

**Why**: Interdependencies prevent min-maxing a single stat. Players must balance the whole system, just as real leaders must balance competing priorities.

---

## Risk Curve

### Definition

Risk is the expected variance in outcome. Higher risk means the gap between best-case and worst-case outcomes is larger.

### Curve by Level

```
Risk
  │
  │      ╭────╮
  │     ╱      ╲
  │    ╱        ╲────── Summit (crisis cards, hidden info, high variance)
  │   ╱        ╱
  │  ╱────────╱
  │ ╱        ╱
  │╱────────╱────── Camp (mixed, some crisis, conditional effects)
  │
  │╲────────╲
  │  ╲       ╲
  │   ╲───────╲──── Basecamp (mostly netral, low variance)
  │
  └──────────────────── Turns
```

### Risk Parameters by Level

| Level | Netral Cards | Crisis Cards | Hidden Info | Avg Stat Variance | Risk Die Frequency |
|-------|-------------|-------------|-------------|-------------------|-------------------|
| Basecamp | 70% | 30% | 10% | ±2 | ~30% of turns |
| Camp | 50% | 50% | 25% | ±3 | ~50% of turns |
| Summit | 30% | 70% | 40% | ±4 | ~70% of turns |

**Why increasing risk**: Leaders at higher levels face more uncertainty, just like real organizational leadership. Basecamp is about self-mastery (lower external risk). Summit is about leading leaders (maximum ambiguity).

### Risk Die Impact

| Roll | Probability | Effect | Emotional Impact |
|------|------------|--------|------------------|
| 1-2 | 33% | Dysfunction (TT -2, shared penalty) | Tension, frustration |
| 3-4 | 33% | Neutral | Relief |
| 5-6 | 33% | Bonus (TT +1) | Satisfaction |

**Why 33/33/33**: Equal probabilities prevent strategic optimization. The dysfunction trigger is punishing enough to create tension without being so frequent that players avoid all crisis cards.

---

## Reward Curve

### Definition

Rewards are the positive outcomes from decisions. The reward curve defines how payoff scales with progression.

### Curve Shape

```
Reward/Decision
  │
  │                    ╭──── (diminishing returns)
  │                  ╱
  │               ╱
  │           ╱
  │      ╱  (accelerating returns for early game)
  │  ╱
  │╱
  └────────────────────── Turns
    Basecamp    Camp    Summit
```

### Reward Parameters

| Phase | Avg MP Gain/Turn | Avg SP Gain/Turn | Avg TT Gain/Turn | Total Stat Gain |
|-------|-----------------|------------------|------------------|------------------|
| Early (turns 1-6) | +1.5 | +1.0 | +0.5 | +3.0 per turn |
| Mid (turns 7-16) | +1.0 | +1.0 | +0.8 | +2.8 per turn |
| Late (turns 17-26) | +0.5 | +0.5 | +1.0 | +2.0 per turn |

**Why this shape**: Early game rewards individual progress (MP/SP heavy). Late game rewards team behavior (TT heavy). This forces a shift from self-optimization to team-orientation, mirroring real leadership development.

### Scoring Formula

```
Score = (level_value * 10) + final_tt

level_value: basecamp=1, camp=2, summit=3
```

**Why this formula**: Level is the primary driver (0-30 points), TT is the tiebreaker (0-15 points). This means reaching summit matters more than TT optimization — but TT determines badge type (The Carrier requires TT >= 8 at summit). The formula rewards progression over hoarding.

---

## Tension Curve

### Definition

Tension is the emotional pressure felt by players during the game. It should follow a narrative arc with peaks and valleys.

### Target Tension Profile

```
Tension
  │
  │    ╭╮                    ╭╮                    ╭────╮
  │   ╭╯╰╮                 ╭╯╰╮                 ╭╯    ╰╮
  │  ╭╯  ╰╮               ╭╯  ╰╮               ╭╯      ╰────
  │ ╭╯    ╰───────╮       ╭╯    ╰───────╮       ╭╯
  │╭╯              ╰───────╯              ╰───────╯
  │
  └──────────────────────────────────────────────────── Turns
    B1  B2  B3  B4  C1  C2  C3  C4  S1  S2  S3  S4

  B = Basecamp   C = Camp   S = Summit
  Peaks = Rope Bridge attempts, crisis cards, dysfunction triggers
  Valleys = Safe card draws, successful consequences
```

### Tension Mechanics

| Mechanic | Tension Effect | Placement |
|----------|----------------|-----------|
| Crisis card draw | +3 tension | Mid-turn, unpredictable |
| Risk die result 1-2 | +5 tension (peak) | Mid-turn, after crisis choice |
| Dysfunction trigger | +4 tension | After risk die, shared penalty |
| Rope Bridge offer | +2 tension | After exceeding threshold |
| Rope Bridge success | -3 tension (relief) | After choosing to attempt |
| Hidden info reveal | +1 tension (curiosity/anxiety) | After choosing on hidden-info card |
| Promise broken | +3 tension (social conflict) | Between turns, announced |
| Vote event | +2 tension (social pressure) | Before next turn |
| Consequence triggered (negative) | +2 tension | At turn start |
| Consequence triggered (positive) | -2 tension (relief) | At turn start |
| Cross-player penalty received | +2 tension | After other player's turn |
| Cross-player bonus received | -1 tension (gratitude) | After other player's turn |

**Why this pacing**: The tension curve creates memorable peaks (dysfunction, rope bridge, social conflict) and necessary valleys (successful consequences, team bonuses) for emotional recovery. Without valleys, players burn out. Without peaks, the session is forgettable.

### Emotional Peak Placement

Per the PRD: "Players should remember emotional moments, not scores."

The game must create at least **3 memorable moments** per session:

1. **First Crisis** (Basecamp turns 3-5): Player's first encounter with real risk. The dysfunction trigger creates shared team tension and prompts first social interaction.

2. **First Rope Bridge** (Basecamp-Camp transition): The first major milestone. The choice to attempt or skip creates a dilemma about self-advancement vs. team readiness.

3. **Summit Crisis Chain** (Summit turns 2-4): Multiple crisis cards in succession with team effects. The culmination of accumulated consequences and social dynamics.

**Why these peaks**: They correspond to natural leadership inflection points — first risk, first advancement decision, and final test.

---

## Difficulty Progression

### Level Difficulty Multipliers

| Level | Card Variance | Threshold Requirements | Hidden Info % | Crisis % |
|-------|-------------|----------------------|---------------|----------|
| Basecamp | Low (±2) | MP 8, SP 8 (no TT) | 10% | 30% |
| Camp | Medium (±3) | MP 12, SP 12, TT 5 | 25% | 50% |
| Summit | High (±4) | MP 15, SP 15, TT 8 | 40% | 70% |

**Why**: Each level should feel meaningfully harder than the last. Higher variance, stricter thresholds, and more hidden information create increasing ambiguity — just like real leadership at higher levels of scope.

### Difficulty Spikes

Within each level, difficulty should spike at specific points:

| Level | Spike Point | Mechanic |
|-------|------------|----------|
| Basecamp | Turn 5 | First mandatory crisis card in the level |
| Camp | Turn 3 | Consequences from basecamp decisions start triggering |
| Camp | Turn 8 | First vote event |
| Summit | Turn 2 | All basecamp consequences resolve |
| Summit | Turn 4 | Final round pressure (1 turn left) |

**Why**: Spikes create urgency and prevent complacency. They also serve as natural inflection points for behavioral analysis — how does a player respond under increased pressure?

---

## Comeback Mechanics

### Problem

Without comeback mechanics, early mistakes compound and late players feel hopeless. This reduces engagement and produces behavioral data that reflects resignation rather than leadership.

### Comeback Mechanisms

| Mechanic | How It Works | When It Activates |
|----------|-------------|-------------------|
| **Cooperative Recovery** | Players can boost a struggling teammate's stats through cross-player effects | When any player has TT < 3 |
| **Accumulated Consequences Resolve** | Delayed positive effects from earlier decisions provide unexpected boosts | At scheduled trigger rounds |
| **Final Round Equalizer** | All players get exactly 1 more turn, regardless of position | When first player hits final win threshold |
| **Trust Recovery Loop** | Helping a teammate builds your own TT and reputation | Any cross-player positive effect |
| **Dysfunction Purification** | A shared team crisis can actually unify the team (TT boost for all who cooperated in response) | When a dysfunction affects all players |

**Why these work**: They are all **player-driven** comebacks, not system gifts. A struggling player recovers because teammates choose to help, because their earlier long-term decisions pay off, or because they pivot their strategy. This ensures the comeback itself generates meaningful behavioral evidence.

### Anti-Runaway (Anti-Snowball)

| Anti-Snowball Mechanic | Why It Exists |
|-----------------------|---------------|
| TT sharing on dysfunction | A leading player's crisis hurts teammates, creating incentive to help |
| Reputation penalty for selfish play | Pure self-optimization reduces social capital |
| Flexibility reduction | Players who max one stat lose future options |
| Consequence accumulation | Aggressive play creates future liabilities |
| Shared objectives | Individual scoring doesn't guarantee team win |

**Why no single dominant strategy**: If a player tries to min-max MP/SP, they lose TT (team trust) and reputation (social capital). If they try to hoard TT, they fall behind on progression thresholds. If they try to play it safe, they miss long-term benefits from risky choices. Every strategy has a counter.

---

## Dilemma Density

### Definition

Dilemma density is the proportion of turns where the player faces a genuinely hard choice (both options have meaningful trade-offs).

### Target Density

| Level | Target Dilemma Density | Rationale |
|-------|----------------------|-----------|
| Basecamp | 50% | Players are learning the system. Some easy choices build confidence. |
| Camp | 70% | Players understand mechanics. Most choices should be hard. |
| Summit | 85% | At the top, almost every choice should be genuinely difficult. |

### What Makes a Dilemma "Hard"

A dilemma is hard when:
1. Both options sacrifice something of value
2. The optimal choice depends on context (team state, hidden info, future plans)
3. Neither option is clearly "correct" in all scenarios

### Validation

During playtesting, if any option is chosen more than 70% of the time across multiple sessions, that dilemma needs rebalancing. The target split for any card should be 40-60%.

---

## Information Availability

### What Players See

| Information | Availability | Why |
|-------------|-------------|-----|
| Own stats (MP, SP, TT) | Full | Players need their own state for decision-making |
| Own reputation, resources, flexibility | Full | Players need to understand their social position |
| Other players' stats | Partial (current values only, no history) | Simulates real leadership — you see output, not process |
| Other players' reputation | Full | Reputation is public by nature |
| Active consequences on self | Full | Players must plan around pending effects |
| Active consequences on others | Hidden | Consequences are private unless shared |
| Hidden info on chosen cards | Revealed after choice | Simulates leadership uncertainty |
| Card draw pool | Hidden (don't know what's coming) | Prevents gaming the system |
| Other players' card content | Hidden | Prevents coaching from meta-knowledge |

**Why this asymmetry**: Real leaders rarely have complete information. They see results, make inferences, and act under uncertainty. Hiding information from players while revealing it gradually through consequences mirrors this reality.

---

## Player Interaction Frequency

### Interaction Types

| Interaction Type | Frequency | When |
|-----------------|-----------|-----|
| Indirect (cross-player effects) | Every 2-3 turns | When a card has team effects |
| Social (promise/vote) | Every 4-6 turns | Triggered by specific cards or player initiative |
| Cooperative (helping mechanic) | Optional, 1-3 times per session | Player chooses to help struggling teammate |
| Competitive (limited resources) | Every 3-5 turns | Cards with resource costs or shared penalties |
| Communicative (between turns) | Continuous | Players discuss in chat outside the system |

**Why this frequency**: Too much forced interaction feels artificial. Too little interaction means the team interdependency PRD requirement fails. The target is that players naturally negotiate 2-3 times per session without being prompted.

---

## Economic Balance Summary

| Dimension | Target | Anti-Pattern |
|-----------|--------|-------------|
| No dominant strategy | Every stat maximization has a counter | One stat path always wins |
| Meaningful sacrifice | 60% of choices have hard trade-offs | All choices have a clear winner |
| Team dependency | Selfish play reduces win probability | Selfish play is equally viable |
| Emotional peaks | 3+ memorable moments per session | Flat emotional experience |
| Comeback possibility | Struggling players can recover through team help | Early mistakes guarantee loss |
| Information asymmetry | Players cannot solve the game through arithmetic | All information is visible |
| Decision frequency | 18-26 decisions per player per session | Too few for behavioral analysis |

---

## Validation Checklist

Use these questions to validate any card or mechanic change:

1. Does this create a meaningful sacrifice? (If no one would ever choose this, it's not a real dilemma.)
2. Does this have a counter-strategy? (If one approach always wins, the game is broken.)
3. Does this map to a real leadership behavior? (If not, it doesn't belong.)
4. Does this create an emotional moment? (If the player feels nothing, the mechanic failed.)
5. Does this generate behavioral evidence? (If the choice reveals nothing about the player's leadership, redesign it.)
