# The Summit v2 — Game Grammar

> **Version:** 2.0
> **Status:** Foundational Design Document
> **Audience:** Card designers, implementers, narrative designers, playtest coordinators

---

## Preamble: What This Document Is

The Summit v2 is a **leadership behavior change system disguised as a multiplayer async board game.** Players climb a metaphorical mountain through three leadership levels — Basecamp → Camp → Summit — by making dilemma-based decisions that reveal, test, and reshape their leadership patterns.

This document defines the **Game Grammar**: the complete set of atomic actions (primitives) from which every card, event, crisis, reward, and mechanic in The Summit is composed. No card or system behavior exists that cannot be expressed as a composition of these primitives.

### Design Principles

1. **Every decision has an opportunity cost.** Gaining something always costs something else.
2. **Short-term optimization should frequently lose to long-term thinking.** The game punishes greedy point-maxing.
3. **Players should remember emotional moments, not scores.** The emotional arc is the product.
4. **Winning is secondary to leadership insight.** The "winner" is the player who learns the most about themselves.
5. **Every mechanic must map to a real leadership behavior.** If a primitive has no real-world analogue, it must be removed.

### Success Criteria

- Players think in long-term trade-offs instead of optimizing immediate points.
- Players experience meaningful sacrifice.
- Players depend on other players to succeed.
- Players operate with incomplete information.
- Players reflect on leadership patterns.
- Every player leaves with one real-world leadership action.

---

## Stat System Reference

The Summit tracks six core stats. Every `modify_stat` primitive references one of these:

| Stat | Abbreviation | Description | Real-World Analogue |
|------|-------------|-------------|---------------------|
| Mindset Points | `MP` | Measures growth-oriented thinking, adaptability, and long-term vision. | Cognitive flexibility, willingness to change perspective. |
| Skillset Points | `SP` | Measures tactical competence and execution capability. | Professional competence, execution skills. |
| Trust Tokens | `TT` | Measures relational capital earned through kept promises and sacrifice. | Relational trust, social capital. |
| Reputation | `REP` | Measures how others perceive the player. Public-facing. | Organizational reputation, personal brand. |
| Resources | `RES` | Measures material and operational assets. | Budget, headcount, tools, access. |
| Flexibility | `FLEX` | Measures ability to pivot, adapt plans, absorb disruption. | Organizational agility, optionality. |

### Stat Bounds and Invariants

- All stats have a **minimum floor of 0** unless a specific primitive explicitly overrides this.
- `MP`, `SP`, and `FLEX` have a **soft cap of 20 per level** (Basecamp, Camp, Summit) — exceeding 20 in one level carries diminishing returns in the next.
- `TT` is **bond-specific**: Trust Tokens exist in the context of a player-pair relationship, not as a global pool.
- `REP` ranges from **-10 to +10** globally. Extreme negative Reputation triggers dysfunction events.
- `RES` has **no hard cap** but becomes subject to inflation — Resources gained late are worth less than Resources gained early.
- `FLEX` at **0 locks the player out of choice-dependent primitives** until restored above 0.

---

## Grammar Notation

Primitives are written in `snake_case`. Parameters are listed in parentheses with typed values. Compound behaviors are expressed using `→` for sequencing and `+` for simultaneity.

**Example composition:**
```
spawn_card(dilemma, target=self) → lock_choice(option_B) → schedule_event(on_resolve, trigger_final_round)
```

This reads: "Spawn a dilemma card for yourself, lock option B so it cannot be chosen, and when the card is resolved, trigger the final round."

---

# SECTION 1: STAT MODIFICATION

Stat modification primitives alter one or more player stats. They are the most frequently used primitives in the game.

---

### `modify_stat`

**Purpose:** Changes a single stat for a single player by a specified amount. This is the fundamental building block for all stat changes in the game.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `stat` | Enum: `MP`, `SP`, `TT`, `REP`, `RES`, `FLEX` | The stat to modify. |
| `target` | Player reference | The player whose stat is affected. |
| `amount` | Integer (can be negative) | The magnitude of change. |
| `source` | String (card ID, event ID, or `system`) | Where this modification originates. |
| `reason` | String | Human-readable description for the activity log. |
| `visible` | Boolean (default: `true`) | Whether the target sees this change. |
| `notify` | Boolean (default: `true`) | Whether other players are notified of this change. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot reduce a stat below 0 unless `allow_negative` flag is explicitly passed (only valid for `REP`).
- A single `modify_stat` call cannot change a stat by more than ±10 in one invocation. Chain multiple calls if a larger change is needed.
- Modifying `TT` requires a `bond_partner` parameter specifying which player-pair the Trust Token belongs to.

**Example:**
> A "Mentor Moment" card grants +3 MP to the playing player. The card's resolution executes: `modify_stat(stat=MP, target=self, amount=3, source="card:mentor_moment", reason="Recognized a growth opportunity in a colleague")`. The gain is visible to all players, reinforcing the social learning mechanic.

---

### `modify_stat_all`

**Purpose:** Applies the same stat modification to every player in the game simultaneously. Used for level-wide effects, environmental shifts, and shared consequences.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `stat` | Enum: `MP`, `SP`, `TT`, `REP`, `RES`, `FLEX` | The stat to modify. |
| `amount` | Integer (can be negative) | The magnitude of change applied to each player. |
| `source` | String | Origin of this modification. |
| `reason` | String | Human-readable description. |
| `exclude` | Player reference or list (optional) | Players to exclude from the effect. |
| `visible` | Boolean (default: `true`) | Visibility to affected players. |

**Execution Timing:** Immediate.

**Constraints:**
- If `stat` is `TT`, this primitive requires a `bond_context` parameter — Trust Tokens cannot be modified globally without specifying the relational context.
- The `exclude` list cannot result in zero affected players. Use `affect_player` instead for single-target effects.

**Example:**
> A "Blizzard" crisis event hits all players: `modify_stat_all(stat=RES, amount=-2, source="event:blizzard", reason="Supply lines disrupted by severe weather")`. Every player loses 2 Resources, creating a shared scarcity that forces cooperation or competition.

---

### `modify_stat_team`

**Purpose:** Modifies a stat for all members of a specified team. Teams are formed through alliances or level-based grouping.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `stat` | Enum: `MP`, `SP`, `TT`, `REP`, `RES`, `FLEX` | The stat to modify. |
| `team` | Team reference | The team whose members are affected. |
| `amount` | Integer | The magnitude of change. |
| `source` | String | Origin of this modification. |
| `reason` | String | Human-readable description. |
| `include_leader` | Boolean (default: `true`) | Whether the team leader is also affected. |

**Execution Timing:** Immediate.

**Constraints:**
- If no team exists with the given reference, the primitive fails silently and logs an error.
- A player cannot be on more than one team simultaneously. If a player belongs to multiple teams (due to a data error), the modification applies to all teams they are a member of.

**Example:**
> When a team successfully completes a "Basecamp Challenge," all members gain: `modify_stat_team(stat=SP, team=alpha, amount=+2, source="event:basecamp_challenge", reason="Team executed the crisis plan effectively")`. This reinforces collective skill-building over individual heroics.

---

### `transfer_stat`

**Purpose:** Moves a stat quantity from one player to another. The source player loses the amount; the target player gains it. This is the primitive for sacrifice, resource sharing, and exploitation.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `stat` | Enum: `MP`, `SP`, `TT`, `REP`, `RES`, `FLEX` | The stat to transfer. |
| `from` | Player reference | The source player. |
| `to` | Player reference | The recipient player. |
| `amount` | Integer (must be positive) | The quantity to transfer. |
| `source` | String | Origin of this transfer. |
| `reason` | String | Human-readable description. |
| `consent_required` | Boolean (default: varies by stat) | Whether the source player must agree. Default is `true` for `RES` and `FLEX`; `false` for `REP` and `TT`. |
| `visible` | Boolean (default: `true`) | Whether both parties see the transfer. |

**Execution Timing:** Immediate (if consent not required) or deferred (if consent required, pending player response).

**Constraints:**
- Cannot transfer more than the source player's current stat value. If `amount` exceeds the source's available stat, the transfer is capped at the source's available amount.
- Transferring `TT` between players A and B modifies the A-B bond. Transferring `TT` from player A to player C (where A and C have no bond) creates a new bond with initial trust equal to the transferred amount.
- Transferring `REP` is always public and cannot be hidden.

**Example:**
> Player A chooses the "Share Your Rations" option on a dilemma card, transferring Resources to Player B: `transfer_stat(stat=RES, from=self, to=player_B, amount=3, source="card:ration_dilemma", reason="Sacrificed personal supplies to support a struggling teammate")`. Player A loses 3 Resources; Player B gains 3. The consent is implied because this was Player A's chosen action.

---

# SECTION 2: TEMPORAL

Temporal primitives control when things happen. They are essential for creating delayed consequences, future obligations, and the long-term thinking the game demands.

---

### `schedule_event`

**Purpose:** Registers a future event that will fire at a specified time or condition. This is the core primitive for all "future consequences" — promises that come due, crises that loom, and rewards that arrive later.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `event_id` | String | Unique identifier for the scheduled event. |
| `trigger` | Enum: `on_round_start`, `on_round_end`, `on_level_advance`, `on_stat_threshold`, `on_card_resolve`, `on_timer`, `on_condition` | When the event fires. |
| `trigger_detail` | Context-dependent | Additional trigger specification (e.g., which round, which stat threshold, which condition). |
| `payload` | Action list | The primitives to execute when the event fires. Can be a single primitive or a composition. |
| `target` | Player reference or `all` (optional) | Who the event affects. |
| `visible` | Boolean (default: `false`) | Whether players know this event is scheduled. |
| `reminder` | Boolean (default: `false`) | Whether players receive a reminder when the event is one trigger away from firing. |
| `expires` | Integer or condition (optional) | When the scheduled event is removed from the queue if it hasn't fired. |

**Execution Timing:** Registration is immediate. Payload execution depends on trigger type.

**Constraints:**
- Two events cannot share the same `event_id`. If a duplicate ID is scheduled, the existing event is replaced (see `cancel_event` for explicit removal).
- The payload of a scheduled event can itself contain `schedule_event` calls, enabling event chains. However, nesting depth is limited to 5 levels to prevent infinite loops.
- If `visible` is `false` and the event fires, the payload's own visibility settings determine what players see — not the schedule's visibility.

**Example:**
> A "Delay the Hard Conversation" dilemma option lets a player avoid an immediate -3 REP penalty by scheduling it for later: `schedule_event(event_id="delayed_convo_42", trigger=on_round_start, trigger_detail=round:5, payload=modify_stat(stat=REP, target=self, amount=-3, source="card:hard_convo", reason="The delayed confrontation finally came due"), visible=true, reminder=true)`. The player gains short-term relief but knows the penalty is coming, creating psychological tension that mirrors real leadership avoidance.

---

### `cancel_event`

**Purpose:** Removes a previously scheduled event before it fires. This is how players can mitigate future consequences — by spending resources, burning Trust Tokens, or making different choices.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `event_id` | String | The identifier of the event to cancel. |
| `reason` | String | Why the event was cancelled. |
| `notify` | Boolean (default: `true`) | Whether the affected player(s) are notified. |
| `cost` | Stat modification list (optional) | What the cancelling player must pay. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot cancel events that have already fired.
- Cannot cancel system-critical events (those tagged `essential` in their schedule). Essential events represent structural game milestones (e.g., level advancement timers).
- If the event was `visible=true`, cancellation is always announced. If `visible=false`, the `notify` parameter controls announcement.
- Cancelling an event that was created by another player requires either an alliance relationship or the target's consent.

**Example:**
> Player A learns that a reputation penalty is scheduled against them (visible reminder received). They spend 2 Resources to cancel it: `cancel_event(event_id="delayed_convo_42", reason="Interpersonal repair effort made in advance", cost=modify_stat(stat=RES, target=self, amount=-2))`. This mirrors the real leadership behavior of investing in preventive relationship repair.

---

### `delay_event`

**Purpose:** Pushes a scheduled event's trigger condition further into the future, buying time at a cost. Distinct from `cancel_event` — the event still exists, just later.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `event_id` | String | The identifier of the event to delay. |
| `new_trigger_detail` | Context-dependent | The revised trigger specification. |
| `cost` | Stat modification list (optional) | What the delaying player must pay. |
| `reason` | String | Why the event was delayed. |
| `notify` | Boolean (default: `true`) | Whether affected players are notified. |
| `max_delays` | Integer (default: `1`) | Maximum number of times this specific event can be delayed. System default cap is 3. |

**Execution Timing:** Immediate (reschedules the event).

**Constraints:**
- The new trigger must be strictly later than the current trigger. An event cannot be delayed to a time that has already passed.
- Each specific event can be delayed at most 3 times total (hard system cap). After 3 delays, the event fires at its next trigger regardless.
- The delay cost increases with each successive delay: first delay costs the base cost, second costs 1.5x, third costs 2x.
- Events tagged `urgent` cannot be delayed at all.

**Example:**
> A "Regulatory Audit" crisis event is scheduled for Round 4. Player A delays it to Round 6: `delay_event(event_id="audit_17", new_trigger_detail=round:6, cost=modify_stat(stat=FLEX, target=self, amount=-1), reason="Filed an extension request")`. The audit still comes — it's just postponed, and the player spent Flexibility (organizational agility) to buy time. This teaches that delays are not free.

---

# SECTION 3: CONDITIONAL

Conditional primitives create branching logic — "if this, then that." They are the machinery behind dilemma cards, adaptive difficulty, and state-dependent game behavior.

---

### `conditional_trigger`

**Purpose:** Sets up a rule: when a specified condition becomes true, execute a specified action. Unlike `schedule_event` (which fires at a time), this fires on a state change.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `condition` | Condition expression (see `check_condition` syntax) | The condition to monitor. |
| `action` | Action list | Primitives to execute when the condition becomes true. |
| `fire_mode` | Enum: `once`, `every`, `while_true` | How often the trigger fires. `once` fires the first time the condition is met and then removes itself. `every` fires each time the condition transitions from false to true. `while_true` fires every turn the condition remains true. |
| `priority` | Integer (default: `0`) | Execution order when multiple triggers fire simultaneously. Higher priority fires first. |
| `expires` | Integer or condition (optional) | When this trigger is removed from the active list. |
| `source` | String | What created this conditional trigger. |

**Execution Timing:** On state change (checks after any stat modification, card resolution, or game flow event).

**Constraints:**
- A conditional trigger cannot directly modify the condition it monitors in its own action (no self-referential loops). For example, a trigger watching `MP < 5` cannot include `modify_stat(MP)` in its action.
- The system evaluates all active conditional triggers after every atomic state change. If multiple triggers fire, they execute in `priority` order.
- Maximum of 50 active conditional triggers per player at any time.

**Example:**
> A "Trust Watcher" system trigger monitors all players: `conditional_trigger(condition=stat(REP, target=any_player) < -5, action=trigger_dysfunction(type=paranoia, target=affected_player), fire_mode=every, source="system")`. When any player's Reputation drops below -5, a dysfunction event fires, simulating how organizational toxicity cascades.

---

### `check_condition`

**Purpose:** Evaluates a condition expression and returns a boolean result. Unlike `conditional_trigger` (which is persistent), this is a one-time query used within card resolutions and action compositions.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `condition` | Condition expression | The condition to evaluate. |
| `on_true` | Action list (optional) | Primitives to execute if the condition is true. |
| `on_false` | Action list (optional) | Primitives to execute if the condition is false. |
| `store_result` | String (optional) | Variable name to store the boolean result for use in later primitives in the same composition. |

**Execution Timing:** Immediate (inline evaluation).

**Condition Expression Syntax:**
```
stat(STAT, target=PLAYER) COMPARATOR VALUE
stat(STAT, target=PLAYER) COMPARATOR stat(STAT, target=PLAYER)
has_alliance(PLAYER_A, PLAYER_B)
has_promise(PLAYER, PROMISE_ID)
level == LEVEL_VALUE
round COMPARATOR VALUE
card_count(PLAYER, ZONE) COMPARATOR VALUE
event_scheduled(EVENT_ID)
random() < PROBABILITY
any(PLAYER_LIST, CONDITION)
all(PLAYER_LIST, CONDITION)
```

Where `COMPARATOR` is one of: `==`, `!=`, `>`, `<`, `>=`, `<=`.

**Constraints:**
- The condition expression must be deterministic within the current game state. No forward-looking conditions (e.g., "will have").
- If both `on_true` and `on_false` are omitted, the check is purely informational and its result can be stored or used in subsequent logic.
- Nested conditions are supported up to 3 levels deep.

**Example:**
> A "Crossroads" dilemma card uses conditional logic to branch: `check_condition(condition=stat(MP, target=self) >= stat(SP, target=self), on_true=modify_stat(stat=MP, target=self, amount=+2, reason="Growth mindset validated"), on_false=modify_stat(stat=SP, target=self, amount=+2, reason="Skill focus rewarded"))`. The player's dominant development area determines which stat grows — reinforcing self-awareness.

---

# SECTION 4: INFORMATION

Information primitives control what players know and don't know. They are the engine for asymmetric information, the fog of leadership, and trust-building through transparency.

---

### `reveal_information`

**Purpose:** Makes hidden game state fully visible to a specified player or players. This is how secrets, hidden stats, scheduled events, and face-down cards become known.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference or `all` | Who receives the information. |
| `subject` | Enum: `stat`, `card`, `event`, `alliance`, `promise`, `debt`, `player_identity`, `custom` | The type of information being revealed. |
| `subject_detail` | Context-dependent | Specific identifier (e.g., which stat, which card, which event). |
| `scope` | Enum: `full`, `value_only`, `existence_only` | How much is revealed. `full` shows everything. `value_only` shows the data but not the source. `existence_only` confirms something exists without showing details. |
| `source` | String | What triggered the reveal. |
| `persistent` | Boolean (default: `false`) | Whether the information remains visible permanently or just for the current moment. |
| `shared_with` | Player reference list (optional) | Additional players who also see the reveal. |

**Execution Timing:** Immediate.

**Constraints:**
- Revealing information to `all` always includes a log entry. Revealing to a single player does not log unless `persistent` is true.
- Cannot reveal information that doesn't exist (e.g., revealing a promise that was never created produces an error and no reveal).
- Revealing another player's secret stat values is a `REP`-impacting action: the revealer gains +1 REP (transparency rewarded) but the subject may react.

**Example:**
> A "Open Book Leadership" dilemma option reveals the player's current stat profile to all other players: `reveal_information(target=all, subject=stat, subject_detail=self, scope=full, source="card:open_book", persistent=true)`. This vulnerability can build Trust Tokens with others who respect the transparency — or it can be exploited by competitors. The player must weigh the risk, mirroring real leadership vulnerability.

---

### `hide_information`

**Purpose:** Conceals previously visible game state from a specified player or players. Used for secrets, hidden agendas, and information asymmetry.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference or `all` | Who loses access to the information. |
| `subject` | Enum: Same as `reveal_information` | The type of information being hidden. |
| `subject_detail` | Context-dependent | Specific identifier. |
| `reason` | String | Why the information is being hidden. |
| `duration` | Integer or condition (optional) | How long the information remains hidden. `null` means permanently until explicitly revealed again. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot hide information that the target never had access to — this is a no-op.
- Cannot hide system-critical information (current level, round number, own stat values). These are always visible to the owning player.
- Hiding another player's information from a third party requires the information to be within the hiding player's "visibility scope" (earned through alliances, Trust Tokens, or card effects).

**Example:**
> A "Closed Door Meeting" card hides the player's next card play from all others: `hide_information(target=all, subject=card, subject_detail=self:next_play, reason="Operating behind closed doors")`. This costs -1 TT with all bonded players (secrecy erodes trust) but may allow a strategic move. The tradeoff embodies the real tension between transparency and strategic confidentiality.

---

### `reveal_partial`

**Purpose:** Reveals a fraction or aspect of hidden information — not the full truth, but a clue. This creates inference, deduction, and the experience of operating with incomplete information.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference or `all` | Who receives the partial information. |
| `subject` | Enum: Same as `reveal_information` | The type of information being partially revealed. |
| `subject_detail` | Context-dependent | Specific identifier. |
| `reveal_type` | Enum: `range`, `direction`, `category`, `proportion`, `redacted` | How the partial reveal works. See below. |
| `reveal_detail` | Context-dependent | Specifics of the partial reveal (e.g., range bounds, direction indicator, category label). |
| `source` | String | What triggered the reveal. |
| `persistent` | Boolean (default: `false`) | Whether the partial information persists. |

**Execution Timing:** Immediate.

**Reveal Type Behaviors:**
- `range`: Shows that the value is within a bracket (e.g., "MP is between 5 and 10").
- `direction`: Shows only the trend (e.g., "Resources are decreasing" or "Reputation is stable").
- `category`: Shows a qualitative label instead of a number (e.g., "High Trust" instead of "TT: 7").
- `proportion`: Shows a percentage of the actual value (e.g., "This is 60% of the full amount").
- `redacted`: Shows the information exists but with key details blacked out (e.g., "A promise exists between Player A and Player _, regarding ____").

**Constraints:**
- `reveal_partial` cannot be chained to eventually reveal full information through repeated calls (anti-spoofing rule). Each partial reveal is independently capped at its reveal type.
- `redacted` reveals never expose the redacted elements, regardless of how many times called.

**Example:**
> A "Rumor Mill" event gives each player partial information about another player's Reputation: `reveal_partial(target=player_A, subject=stat, subject_detail=player_B:REP, reveal_type=direction, reveal_detail=trend, source="event:rumor_mill", persistent=false)`. Player A learns "Player B's Reputation is trending downward" without knowing the exact value. This mirrors how leaders often receive vague signals about team morale rather than precise data.

---

# SECTION 5: CHOICE

Choice primitives control what options are available to players. They are the machinery behind dilemmas — forcing players into hard tradeoffs by constraining their options.

---

### `lock_choice`

**Purpose:** Makes a specific choice option unavailable on a card or decision point. The locked option is visible (the player knows it exists) but cannot be selected.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The player whose choice is locked. |
| `card_id` | String | The card or decision point containing the choice. |
| `option` | String or option reference | Which option to lock. |
| `reason` | String | Why the option is locked (shown to the player). |
| `duration` | Integer or condition (optional) | How long the lock persists. Permanent if not specified. |
| `unlock_condition` | Condition (optional) | A condition that, if met, automatically unlocks the option. |

**Execution Timing:** Immediate (prevents selection on next interaction).

**Constraints:**
- Cannot lock all options on a card. At least one option must remain selectable. If locking would leave zero options, the lock fails and an error is logged.
- Locked options are always visible to the affected player with the reason displayed. The purpose is to create awareness of what was sacrificed, not to secretly remove options.
- Locking another player's choice requires either an alliance relationship, a dysfunction event, or a specific card effect that grants this authority.

**Example:**
> A "Burned Bridge" consequence locks the "Negotiate" option on the player's next dilemma card: `lock_choice(target=self, card_id=next_dilemma, option=negotiate, reason="Your previous aggressive move eliminated the negotiation path", duration=1_card)`. The player must choose between remaining options, experiencing the lasting cost of their prior decision. This teaches that leadership actions have constraining ripple effects.

---

### `unlock_choice`

**Purpose:** Removes a lock on a previously locked choice option, restoring it as selectable.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The player whose choice is unlocked. |
| `card_id` | String | The card or decision point. |
| `option` | String or option reference | Which option to unlock. |
| `reason` | String | Why the option is now available. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot unlock an option that was not previously locked by `lock_choice`. Options that are inherently unavailable (e.g., level-gated options) require `advance_level` or other structural changes, not unlocking.
- Unlocking an option for another player requires the same authority as locking.

**Example:**
> A "Reconciliation" event unlocks a previously locked option: `unlock_choice(target=player_B, card_id=current_dilemma, option=collaborate, reason="The conflict that blocked this path has been addressed through mediation")`. This represents how repairing relationships in leadership can reopen doors that seemed permanently closed.

---

### `restrict_choice`

**Purpose:** Narrows the set of available options on a card to a specific subset, overriding all other availability rules. More aggressive than `lock_choice` — instead of removing options one by one, it defines what IS available.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The player whose choices are restricted. |
| `card_id` | String | The card or decision point. |
| `allowed_options` | List of option references | The ONLY options that can be selected. |
| `reason` | String | Why choices are restricted. |
| `duration` | Integer or condition (optional) | How long the restriction lasts. |
| `override_locks` | Boolean (default: `false`) | Whether this restriction can override existing locks (i.e., re-enable an option that was locked). |

**Execution Timing:** Immediate.

**Constraints:**
- `allowed_options` must contain at least one option and cannot contain options that don't exist on the card.
- If `restrict_choice` and `lock_choice` conflict (e.g., restrict allows option X but option X is locked), the lock takes precedence unless `override_locks` is true.
- A player can only have one active `restrict_choice` per card. A new `restrict_choice` on the same card replaces the previous one.

**Example:**
> A "No Good Options" crisis card forces the player into a brutal binary: `restrict_choice(target=self, card_id=crisis:supply_chain, allowed_options=[sacrifice_team, sacrifice_deadline], reason="The situation has deteriorated beyond nuanced solutions")`. The player must choose between two painful options, experiencing the real leadership moment where there is no perfect answer — only consequences to own.

---

# SECTION 6: CARD

Card primitives manage the lifecycle of game cards: dilemmas, crises, rewards, events, and action cards.

---

### `spawn_card`

**Purpose:** Creates a new card instance and places it into a specified zone for a specified player. This is how dilemmas, crises, and events enter the game.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `card_type` | Enum: `dilemma`, `crisis`, `reward`, `event`, `action`, `reflection` | The type of card to spawn. |
| `template_id` | String | The card template to use. |
| `target` | Player reference or `all` | Who receives the card. |
| `zone` | Enum: `hand`, `board`, `pending`, `shared`, `draft` | Where the card is placed. |
| `face_up` | Boolean (default: varies by card_type) | Whether the card is visible. |
| `properties` | Key-value map (optional) | Overrides to the card template's default properties. |
| `attach_to` | Entity reference (optional) | If the card should be attached to another game entity (e.g., an alliance, a promise). |
| `trigger_on_spawn` | Boolean (default: `false`) | Whether the card's "on spawn" effects fire immediately. |

**Execution Timing:** Immediate (card enters the zone).

**Constraints:**
- Cannot spawn a card with a `template_id` that doesn't exist in the card database.
- `hand` zone is player-specific. `board` zone is visible to all. `pending` zone is visible only to the target. `shared` zone is visible to a specified group.
- A player's hand has a maximum capacity (default: 5). Spawning a card that would exceed hand capacity requires the player to discard first (or the spawn fails and the card enters a `queued` state).

**Example:**
> A level advancement event spawns a reflection card for all players: `spawn_card(card_type=reflection, template_id="leadership_audit", target=all, zone=pending, face_up=false, trigger_on_spawn=false)`. Each player receives a private reflection card they'll complete at their own pace, creating an async moment of leadership self-assessment.

---

### `remove_card`

**Purpose:** Permanently removes a card from the game. The card is not discarded (which might trigger effects) — it is eliminated entirely.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `card_id` | String | The specific card instance to remove. |
| `reason` | String | Why the card is being removed. |
| `trigger_on_remove` | Boolean (default: `true`) | Whether the card's "on remove" effects fire. |
| `notify` | Boolean (default: `true`) | Whether the card's owner is notified. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot remove cards that are currently being resolved (in mid-resolution state). Wait for resolution to complete or use `cancel_event` to cancel the resolution.
- System-essential cards (tagged `essential`) cannot be removed except by system-level game flow events.
- Removing a card that was attached to an entity (promise, alliance) severs the attachment and may trigger side effects defined by the entity type.

**Example:**
> A "Course Correction" action card removes a looming crisis card from the board before it resolves: `remove_card(card_id="crisis:compliance_breach_42", reason="Proactive compliance audit eliminated the vulnerability before it could escalate", trigger_on_remove=false)`. By setting `trigger_on_remove=false`, the crisis's penalty effects are avoided — this represents how proactive leadership can prevent problems rather than reacting to them.

---

### `draw_card`

**Purpose:** A player draws a card from a specified deck or pool. Unlike `spawn_card` (which creates from a template), this pulls from an existing card pool with randomness.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The player drawing the card. |
| `deck` | Enum: `dilemma`, `crisis`, `reward`, `event`, `action`, `reflection`, `custom` | Which deck to draw from. |
| `count` | Integer (default: `1`) | How many cards to draw. |
| `selection` | Enum: `random`, `top`, `choice`, `weighted` | How the card is selected from the deck. |
| `filter` | Condition (optional) | Only draw cards matching this condition. |
| `zone` | Enum: `hand`, `pending` (default: `hand`) | Where the drawn card goes. |
| `visible` | Boolean (default: varies by deck) | Whether the drawn card is immediately visible. |

**Execution Timing:** Immediate.

**Constraints:**
- If the deck is empty, `draw_card` fails and returns a `deck_empty` status. The calling composition must handle this (e.g., by reshuffling the discard pile or spawning a fallback card).
- `selection=choice` presents the top N cards (where N is specified in a `choice_count` parameter) and lets the player pick one. The others return to the deck.
- `filter` conditions that match zero cards behave the same as `deck_empty`.
- A player cannot draw more cards in a single turn than the per-turn draw limit (default: 3). `draw_card` calls that would exceed this limit are queued for the next turn.

**Example:**
> At the start of each round, every player draws a dilemma card: `draw_card(target=self, deck=dilemma, count=1, selection=random, filter=level(current_level), zone=hand)`. The filter ensures only dilemmas appropriate for the player's current level (Basecamp, Camp, or Summit) are drawn, maintaining progressive difficulty.

---

### `discard_card`

**Purpose:** Moves a card from a player's hand or zone to the discard pile. Unlike `remove_card`, discarded cards may trigger "on discard" effects and can potentially be reshuffled back into decks.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `card_id` | String | The card to discard. |
| `from` | Player reference | The player discarding the card. |
| `reason` | String | Why the card is being discarded. |
| `trigger_on_discard` | Boolean (default: `true`) | Whether the card's "on discard" effects fire. |
| `by_choice` | Boolean (default: `true`) | Whether this was a voluntary discard or forced. |

**Execution Timing:** Immediate.

**Constraints:**
- Cannot discard cards that are in the `board` zone (active resolution). Use `remove_card` or resolve the card first.
- If `by_choice` is false (forced discard), the system selects the card to discard. Selection priority: oldest cards first, then lowest stat-value cards.
- Discarded cards enter a shared discard pile visible to all players. Some card templates may specify `hidden_discard=true`, which places them in a hidden discard pile.

**Example:**
> A player voluntarily discards a "Safe Choice" action card to make room in their hand: `discard_card(card_id="action:safe_choice", from=self, reason="Making space for a higher-stakes opportunity", by_choice=true)`. The voluntary nature is important — choosing to discard a safety net is itself a leadership decision that the game's reflection system may surface later.

---

# SECTION 7: PROBABILITY

Probability primitives introduce chance and uncertainty. They model the unpredictability inherent in real leadership — outcomes are never guaranteed.

---

### `roll_dice`

**Purpose:** Generates a random number within a specified range. The simplest probability primitive — a pure random roll.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `sides` | Integer (default: `6`) | Number of sides on the die. |
| `count` | Integer (default: `1`) | Number of dice to roll. |
| `modifier` | Integer (default: `0`) | Added to or subtracted from the total result. |
| `target` | Player reference (optional) | Who is rolling. |
| `visible` | Boolean (default: `true`) | Whether the result is public. |
| `store_as` | String (optional) | Variable name to store the result for use in later primitives. |
| `explode` | Boolean (default: `false`) | If true, rolling the maximum value allows another roll and adds to the total. |
| `explode_cap` | Integer (default: `3`) | Maximum number of explosion rerolls. |

**Execution Timing:** Immediate.

**Constraints:**
- The random number generator is seeded per-game, not per-roll, ensuring reproducible sequences if the seed is known (for debugging and dispute resolution).
- `explode` chains cannot exceed `explode_cap` total rolls. After the cap, the final roll stands regardless of value.
- If `visible` is false, only the rolling player and the game log see the result. Other players see "A die was rolled" without the value.

**Example:**
> A "Weather Change" event requires a die roll to determine severity: `roll_dice(sides=6, count=1, modifier=0, visible=true, store_as="weather_roll")`. The stored result is then used in a conditional: if the roll is 1-2, mild weather; 3-4, moderate disruption; 5-6, severe storm. This models the uncontrollable external factors leaders face.

---

### `probability_check`

**Purpose:** Performs a probability test — rolls against a threshold and returns success or failure. More structured than `roll_dice` for binary outcomes.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `threshold` | Float between 0.0 and 1.0 | The probability of success. 0.7 means 70% chance of success. |
| `target` | Player reference | Who is attempting the check. |
| `modifiers` | Stat modification list (optional) | Stat-based adjustments to the threshold before rolling. |
| `on_success` | Action list | Primitives to execute on success. |
| `on_failure` | Action list | Primitives to execute on failure. |
| `visible` | Boolean (default: `true`) | Whether the outcome is public. |
| `store_as` | String (optional) | Variable name to store the boolean result. |

**Execution Timing:** Immediate.

**Constraints:**
- The effective threshold after all modifiers must be between 0.01 and 0.99. A threshold of 0.0 or 1.0 is disallowed — there must always be a chance of both outcomes (this is a core game philosophy: nothing is certain).
- Multiple `probability_check` calls in the same composition are independent — each generates its own random result.
- `modifiers` are applied additively to the threshold (e.g., +0.1 from having MP > 10). Negative modifiers can push the effective threshold below the minimum of 0.01.

**Example:**
> A "Bold Initiative" action card attempts a risky strategy: `probability_check(threshold=0.4, target=self, modifiers=[stat(MP, self, +0.1), stat(FLEX, self, +0.05)], on_success=modify_stat(stat=SP, target=self, amount=+4, reason="The bold initiative paid off"), on_failure=modify_stat(stat=REP, target=self, amount=-2, reason="The risky move backfired publicly"), visible=true)`. The base 40% success rate is boosted by Mindset and Flexibility — the game rewards players who invested in adaptive thinking before taking risks.

---

### `probability_modifier`

**Purpose:** Adjusts the probability threshold of a subsequent `probability_check` without immediately performing the check. Used for persistent buffs/debuffs to a player's luck.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | Whose probabilities are modified. |
| `check_type` | String or `any` | Which types of probability checks this affects. `any` modifies all checks. |
| `modifier` | Float between -0.3 and +0.3 | The adjustment to apply to the threshold. |
| `duration` | Integer or condition | How long the modifier persists. |
| `source` | String | What created this modifier. |
| `reason` | String | Human-readable description. |
| `stackable` | Boolean (default: `false`) | Whether multiple probability_modifiers of the same type can stack. |

**Execution Timing:** Registration is immediate. Effect applies at next `probability_check` execution.

**Constraints:**
- Total cumulative modifier from all active `probability_modifier` effects on a single player cannot exceed +0.3 or -0.3. If adding a new modifier would breach this cap, the excess is discarded.
- This primitive does not guarantee outcomes — it shifts probability. A player with +0.3 modifier on a 0.5 threshold check has an 0.8 effective threshold, meaning 80% success. The 20% failure chance still exists.
- `probability_modifier` effects are invisible to other players by default (players don't see each other's luck adjustments), reinforcing incomplete information.

**Example:**
> A "Prepared Leader" card grants a persistent probability buff: `probability_modifier(target=self, check_type=any, modifier=+0.1, duration=3_rounds, source="card:prepared_leader", reason="Thorough preparation improved odds of success")`. For the next 3 rounds, all of this player's probability checks have +10% success rate. This represents how preparation in real leadership doesn't guarantee success but meaningfully improves the odds.

---

# SECTION 8: RELATIONSHIP

Relationship primitives manage the social fabric between players — alliances, trust, and interpersonal dynamics.

---

### `relationship_change`

**Purpose:** Modifies the relational standing between two players. This is the generic relationship primitive that affects the invisible bond quality between any two players.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `player_a` | Player reference | First player in the relationship. |
| `player_b` | Player reference | Second player in the relationship. |
| `dimension` | Enum: `trust`, `respect`, `fear`, `gratitude`, `resentment`, ` indebtedness` | Which relational dimension to change. |
| `amount` | Integer (can be negative) | The magnitude of change. |
| `reason` | String | What caused this change. |
| `visible` | Boolean (default: varies by dimension) | Whether both parties see this change. `trust` changes are visible by default. `fear` changes are hidden by default. |
| `symmetric` | Boolean (default: `false`) | Whether the change applies in both directions (A→B and B→A). |

**Execution Timing:** Immediate.

**Constraints:**
- Relationship dimensions have a range of -10 to +10 each. Values cannot exceed these bounds.
- `trust` dimension changes are automatically mirrored (if A trusts B more, the system considers this bidirectional information) unless `symmetric=false` is explicitly set — asymmetry represents one-sided relationships.
- Relationship changes are logged in each player's private relationship journal (visible only to that player and revealed through `reveal_information`).

**Example:**
> Player A breaks a promise to Player B: `relationship_change(player_a=player_A, player_b=player_B, dimension=trust, amount=-3, reason="Failed to deliver on a committed resource transfer", visible=true, symmetric=false)`. The trust loss is asymmetric — Player B trusts Player A less, but Player A's trust in Player B is unchanged. This reflects how broken commitments damage the reputation of the breaker without necessarily changing their perception of others.

---

### `create_alliance`

**Purpose:** Formally establishes an alliance between two or more players. Alliances unlock cooperative mechanics, shared visibility, and joint stat effects.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `members` | Player reference list | Players joining the alliance. Minimum 2, maximum 4. |
| `name` | String | Alliance name (player-chosen or system-generated). |
| `type` | Enum: `formal`, `informal`, `strategic`, `survival` | The alliance type, which determines available mechanics. |
| `terms` | Key-value map (optional) | Custom terms (e.g., resource-sharing agreements, veto rights). |
| `duration` | Integer or condition (optional) | How long the alliance lasts. `null` means indefinite until broken. |
| `stat_sharing` | Boolean (default: `false`) | Whether alliance members can see each other's stats. |
| `joint_resolves` | Boolean (default: `false`) | Whether members can resolve dilemma cards jointly. |
| `announce` | Boolean (default: `true`) | Whether the alliance formation is public. |

**Execution Timing:** Immediate (alliance becomes active).

**Constraints:**
- A player can be in at most one formal alliance at a time. Creating a new formal alliance for a player who is already in one requires breaking the existing alliance first (or upgrading it to include new members).
- A player cannot form an alliance with themselves.
- `informal` alliances have fewer mechanical benefits than `formal` ones but are easier to form (no consent required from all parties — just mutual trust threshold).
- `survival` alliances can only form during crisis events and auto-dissolve when the crisis ends.

**Example:**
> Players A and C form a formal alliance to tackle a multi-round crisis: `create_alliance(members=[player_A, player_C], name="Summit Partners", type=formal, terms={resource_split: "50/50", veto: true}, stat_sharing=true, joint_resolves=true, announce=true)`. They can now see each other's stats, share resources evenly, and resolve certain dilemma cards together — gaining the power of collaboration at the cost of entangling their fates.

---

### `break_alliance`

**Purpose:** Dissolves an existing alliance. This is a high-stakes action with relational consequences.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `alliance_id` | String | The alliance to break. |
| `initiator` | Player reference | Who is breaking the alliance. |
| `reason` | String | Why the alliance is breaking. |
| `grace_period` | Integer (default: `0`) | Rounds before the break takes full effect. During grace period, alliance benefits persist but cannot be renewed. |
| `consequence_profile` | Enum: `amicable`, `bitter`, `betrayal`, `mutual` | How severe the relational fallout is. |

**Execution Timing:** Immediate (or deferred if `grace_period` > 0).

**Consequence Profiles:**

| Profile | Trust Impact | Reputation Impact | Visibility |
|---------|-------------|-------------------|------------|
| `amicable` | -1 TT between members | No change | Low — "The alliance has run its course." |
| `bitter` | -3 TT between members | -1 REP for initiator | Medium — "Disagreements led to dissolution." |
| `betrayal` | -5 TT between members | -3 REP for initiator | High — "The alliance was broken unilaterally." |
| `mutual` | -2 TT between members | -1 REP for all members | Medium — "Both parties agreed to part ways." |

**Constraints:**
- `break_alliance` during an active joint card resolution forces the resolution to complete under pre-break alliance rules. The break applies after the current resolution finishes.
- If `grace_period` is set, the alliance remains technically active (benefits persist) but cannot be extended, and all members are notified of the impending break. During grace period, members can negotiate (via promise primitives) to prevent the break.
- Breaking an alliance that was only 1 round old triggers an additional -1 REP penalty for both parties (the "reckless commitment" penalty).

**Example:**
> Player A breaks their alliance with Player C due to strategic divergence: `break_alliance(alliance_id="summit_partners", initiator=player_A, reason="Our leadership approaches are fundamentally incompatible for this climb", grace_period=1, consequence_profile=bitter)`. The 1-round grace period gives Player C a chance to negotiate or prepare for the fallout. The `bitter` profile costs Player A -3 TT and -1 REP, reflecting the organizational cost of severing a working relationship.

---

# SECTION 9: REPUTATION

Reputation is the public-facing dimension of a player's standing. It is distinct from private relationship dimensions — Reputation is what everyone sees.

---

### `reputation_change`

**Purpose:** Modifies a player's public Reputation score. A dedicated primitive because Reputation has unique visibility rules, triggers, and game-flow consequences.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The player whose Reputation changes. |
| `amount` | Integer (can be negative) | The magnitude of change. |
| `reason` | String | What caused this change — displayed publicly. |
| `source` | String | What triggered this change. |
| `visible_to` | Enum: `all`, `self`, `allies`, `custom` | Who sees the change and the reason. Default: `all`. |
| `permanent` | Boolean (default: `true`) | Whether this change persists or can be recovered. |
| `decay_rate` | Integer (optional) | How many rounds until the change begins to decay naturally. `0` means no decay. |

**Execution Timing:** Immediate.

**Constraints:**
- Reputation is bounded: -10 to +10. Changes that would exceed these bounds are capped.
- Reputation changes are always logged in the public game log, even if `visible_to` is restricted (the log shows "Player X's Reputation changed" but not the amount or reason if visibility is restricted).
- Reputation at -5 or below triggers a `conditional_trigger` that may fire dysfunction events (see conditional system).
- Reputation at +8 or above unlocks special "trusted leader" card effects that are only available to highly-reputed players.
- Multiple reputation changes in the same round stack before their combined effect is evaluated against thresholds. This prevents a player from dipping below -5 momentarily and immediately recovering within a single round.

**Example:**
> A player publicly takes the blame for a team failure: `reputation_change(target=self, amount=-3, reason="Took accountability for the missed deadline, shielding the team from consequences", source="card:shoulder_the_blame", visible_to=all, permanent=false, decay_rate=3)`. The -3 REP hits immediately, but because `permanent=false` and `decay_rate=3`, the loss begins to naturally recover after 3 rounds. The game teaches that taking responsibility has short-term costs but the Reputation damage is not permanent — a lesson in accountable leadership.

---

# SECTION 10: PROMISE & DEBT

Promise and Debt primitives are the heart of The Summit's inter-player obligation system. They transform abstract social contracts into trackable game mechanics that mirror real leadership commitments.

---

### `create_promise`

**Purpose:** Creates a formal promise — a commitment from one player to another that will be evaluated in the future. Promises are the game's primary mechanism for teaching follow-through and the weight of commitment.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `promisor` | Player reference | The player making the promise. |
| `promisee` | Player reference | The player receiving the promise. |
| `content` | Action list or condition | What the promisor commits to doing. Can be a specific action (e.g., transfer 3 RES by Round 5) or a condition to maintain (e.g., keep REP above 0). |
| `deadline` | Integer (round number) or condition | When the promise must be fulfilled. |
| `stake` | Stat modification list (optional) | What the promisor stakes as collateral. If the promise is broken, this is forfeited automatically. |
| `visibility` | Enum: `public`, `private`, `sealed` | Who knows about the promise. `public` — all players. `private` — promisor and promisee only. `sealed` — only the promisee knows; the promisor cannot see the terms after creation (high-trust variant). |
| `id` | String (optional) | Custom identifier. System generates one if not provided. |
| `breakable` | Boolean (default: `true`) | Whether the promisor can choose to break the promise. If `false`, the system enforces fulfillment (used for binding commitments). |

**Execution Timing:** Immediate (promise is created and tracked).

**Constraints:**
- A player can have a maximum of 5 active promises simultaneously. Creating a 6th promise requires fulfilling or breaking an existing one first.
- The `content` action list cannot include `create_promise` (no meta-promises).
- `sealed` visibility promises are the highest-trust variant: the promisor commits without being able to reference the exact terms later, simulating how real promises rely on memory and integrity rather than contractual reminders.
- Promises with `stake` require the promisor to have the staked stat available. The stake is not deducted immediately — it is held in escrow (visible in the promisor's stat display as "encumbered").

**Example:**
> Player A promises Player B: "I will transfer 3 Resources to you by Round 5." `create_promise(promisor=player_A, promisee=player_B, content=transfer_stat(stat=RES, from=player_A, to=player_B, amount=3), deadline=round:5, stake=[modify_stat(stat=TT, target=player_A, amount=-2, bond_partner=player_B)], visibility=public)`. The promise is public, so all players witness the commitment. If Player A fails, they automatically lose 2 Trust Tokens with Player B. The public nature creates social pressure — mirrors how leaders are held accountable by organizational visibility.

---

### `fulfill_promise`

**Purpose:** Marks a promise as fulfilled — the promisor has completed their commitment. This triggers positive consequences and removes the promise from the active list.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `promise_id` | String | The promise to fulfill. |
| `fulfiller` | Player reference | The player fulfilling the promise (must be the promisor). |
| `evidence` | Action list result (optional) | Proof of fulfillment — the system can verify that the committed actions were actually performed. |
| `partial` | Boolean (default: `false`) | Whether this is a partial fulfillment (e.g., transferred 2 of promised 3 RES). |

**Execution Timing:** Immediate.

**Fulfillment Consequences (automatic):**
- If `partial=false`: +2 TT between promisor and promisee. +1 REP for promisor. Stake (if any) is returned.
- If `partial=true`: +1 TT between promisor and promisee. No REP change. Stake proportion is returned.
- If the promise was `public`: An announcement is made, reinforcing the positive social signal.

**Constraints:**
- Only the promisor can fulfill the promise. The promisee cannot fulfill it on behalf of the promisor (even if the promisor completed the action — the act of claiming fulfillment is itself a leadership gesture).
- The system optionally verifies fulfillment against the `content` action list. If `evidence` is required and doesn't match, fulfillment is rejected and the promise remains active.
- A partially fulfilled promise cannot be partially fulfilled again — the next interaction must be a full fulfill or a break.

**Example:**
> Player A completes their promised resource transfer before the deadline: `fulfill_promise(promise_id="promise_A_B_1", fulfiller=player_A, partial=false)`. The system confirms the transfer occurred, awards +2 TT with Player B, returns the staked Trust Tokens, and announces the fulfillment publicly. The moment is logged in both players' leadership journals — a data point for the post-game reflection.

---

### `break_promise`

**Purpose:** Explicitly breaks a promise before its deadline. This is different from a promise expiring unfulfilled — breaking is a conscious decision, which has different consequences.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `promise_id` | String | The promise to break. |
| `breaker` | Player reference | The player breaking the promise (must be the promisor unless the promise is `breakable=false`, in which case only a system event can break it). |
| `reason` | String | Why the promise is being broken. |
| `negotiated` | Boolean (default: `false`) | Whether the promisee agreed to the break. |
| `offer` | Stat modification list (optional) | Compensation offered to the promisee as a consolation. |

**Execution Timing:** Immediate.

**Breaking Consequences (automatic):**
- If `negotiated=false`: -3 TT between breaker and promisee. -2 REP for breaker. Stake (if any) is forfeited to the promisee.
- If `negotiated=true`: -1 TT between breaker and promisee. No REP change. Stake (if any) is returned. The `offer` is applied.
- If the promise was `public`: A public announcement of the broken promise is made, amplifying the Reputation impact.

**Constraints:**
- Cannot break a promise with `breakable=false` through this primitive. Those promises can only be unfulfilled at deadline (which triggers automatic consequences).
- A player who breaks 3+ promises in a single game gains a "Promise Breaker" tag that is visible to all players and triggers additional dysfunction susceptibility.
- Breaking a promise and then immediately recreating a similar promise with the same promisee is flagged by the system (but not blocked) — other players may see this pattern in their information feeds.

**Example:**
> Player A realizes they cannot fulfill their resource promise and negotiates with Player B: `break_promise(promise_id="promise_A_B_1", breaker=player_A, reason="A crisis consumed all available Resources — I cannot deliver without jeopardizing the team", negotiated=true, offer=modify_stat(stat=SP, target=player_B, amount=+1))`. Because Player B agreed, the consequences are reduced: only -1 TT, no REP loss, and Player B receives a Skillset consolation. The negotiation itself was a leadership act — communication mitigates damage.

---

### `create_debt`

**Purpose:** Creates a debt obligation — one player owes something to another. Unlike promises (which are voluntary commitments), debts arise from game mechanics, crises, or forced exchanges.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `debtor` | Player reference | The player who owes. |
| `creditor` | Player reference | The player who is owed. |
| `amount` | Stat modification or value | What is owed. Can be a stat quantity, a future action, or a conditional obligation. |
| `interest` | Float (default: `0.1`) | Interest rate that increases the debt over time if unpaid. Applied per round after the grace period. |
| `grace_period` | Integer (default: `2`) | Rounds before interest begins accruing. |
| `deadline` | Integer (round number) or condition | When the debt must be resolved. |
| `enforcement` | Enum: `soft`, `medium`, `hard` | What happens if the debt goes unpaid at deadline. `soft` — automatic REP loss. `medium` — stat deduction. `hard` — forced card draw (usually a crisis). |
| `transferable` | Boolean (default: `false`) | Whether the debt can be transferred to another player. |
| `visibility` | Enum: `public`, `private`, `creditor_only` | Who knows about the debt. |
| `id` | String (optional) | Custom identifier. |

**Execution Timing:** Immediate.

**Constraints:**
- A player's total outstanding debt (summed across all debts) cannot exceed a soft cap of 10 "debt units." Each Resource owed = 1 unit. Each stat point owed = 2 units. Each action owed = 3 units. Exceeding the cap triggers a "Debt Burden" dysfunction.
- Interest compounds per round after the grace period. A debt of 3 RES with 10% interest after 3 rounds of non-payment becomes 3 × 1.1^3 = 3.99 RES owed.
- Debts are not alliances — a debtor and creditor can also be alliance members, but the debt persists independently of alliance status.

**Example:**
> A "Bailout" crisis forces Player D to accept Resources from the system pool, creating a debt: `create_debt(debtor=player_D, creditor=system, amount=stat(RES, 5), interest=0.15, grace_period=2, deadline=round:8, enforcement=hard, visibility=public)`. Player D receives 5 Resources now but owes 5 (plus 15% interest per round after grace) by Round 8. If unpaid, a hard crisis card is forced. This models organizational debt — bailout funding that comes with compounding obligations.

---

### `resolve_debt`

**Purpose:** Pays off or otherwise resolves a debt. The debt is removed from the active list.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `debt_id` | String | The debt to resolve. |
| `resolver` | Player reference | The player resolving the debt (typically the debtor). |
| `payment` | Stat modification list | What is being paid to settle the debt. |
| `early` | Boolean (auto-detected) | Whether the debt is resolved before its deadline (grants bonus). |
| `partial` | Boolean (default: `false`) | Whether this is a partial payment. |

**Execution Timing:** Immediate.

**Resolution Consequences (automatic):**
- Full payment, on time: Debt removed. +1 REP for debtor ("reliable"). No TT change.
- Full payment, early (before grace period ends): Debt removed. +2 REP for debtor ("proactive"). +1 TT with creditor.
- Partial payment: Debt reduced by payment amount. Interest continues on remaining balance. No REP change.
- Payment exceeds debt amount: Excess is lost (no change for creditor). Prevents "overpayment farming."

**Constraints:**
- Payment must be in the same stat type as the debt (cannot pay a RES debt with MP).
- If the debtor cannot afford the full payment, `resolve_debt` with `partial=false` fails. The debtor must use `partial=true` or find another way to generate the owed stat.

**Example:**
> Player D resolves their bailout debt early, before interest kicks in: `resolve_debt(debt_id="bailout_D_1", resolver=player_D, payment=modify_stat(stat=RES, target=system, amount=-5))`. The early resolution grants +2 REP, signaling reliability. The game rewards proactive debt management — a direct analogue to the real leadership lesson that addressing obligations promptly builds credibility.

---

### `transfer_debt`

**Purpose:** Moves a debt obligation from one player to another. The new debtor assumes the original terms, amount, and deadline.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `debt_id` | String | The debt to transfer. |
| `from` | Player reference | The current debtor. |
| `to` | Player reference | The new debtor. |
| `consent_new_debtor` | Boolean (default: `true`) | Whether the new debtor must agree. |
| `consent_creditor` | Boolean (default: `true`) | Whether the creditor must agree (if player, not system). |
| `fee` | Stat modification list (optional) | A transfer fee paid by the original debtor to the new debtor as incentive. |
| `reason` | String | Why the debt is being transferred. |

**Execution Timing:** Deferred if consent is required; immediate if both consents are pre-granted or waived.

**Constraints:**
- The debt must have `transferable=true` (set at creation). Non-transferable debts cannot be moved.
- The new debtor assumes the debt as-is — no renegotiation of terms, interest, or deadline. If they want different terms, they must resolve and recreate the debt.
- Transferring a debt to a player who is already at the debt cap (10 units) fails unless the transferred debt would replace an existing one (not additive).
- Transferring system debts to another player requires the new debtor's explicit consent. Transferring player-to-player debts requires both parties' consent.

**Example:**
> Player D transfers their bailout debt to Player A, who agrees as a favor: `transfer_debt(debt_id="bailout_D_1", from=player_D, to=player_A, consent_new_debtor=true, consent_creditor=false, fee=modify_stat(stat=TT, target=player_A, amount=+1, bond_partner=player_D), reason="Player A assumed Player D's debt burden in exchange for trust")`. Player A gains +1 TT from Player D for taking on the obligation. This models real leadership delegation — sometimes a leader absorbs another's burden, building immense relational capital.

---

# SECTION 11: PLAYER TARGETING

Player targeting primitives determine WHO is affected by an action. They are selectors — they feed into other primitives as the `target` parameter.

---

### `affect_player`

**Purpose:** Selects a single specific player as the target of an action. The most basic targeting primitive.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference | The specific player to affect. |
| `action` | Action list | The primitives to apply to the targeted player. |
| `exclude_self` | Boolean (default: `false`) | If true and target resolves to self, the action is skipped. |
| `require_awareness` | Boolean (default: `false`) | If true, the target must be aware of the source (i.e., not hidden by information primitives). |

**Execution Timing:** Immediate.

**Constraints:**
- `affect_player` cannot target a player who has been eliminated from the game. Use `check_condition(player_active, target)` to verify first.
- Targeting a player with `require_awareness=true` when the source is hidden fails silently and logs a "targeting blocked by information asymmetry" event.

**Example:**
> A "Direct Feedback" action card targets a specific player with a Reputation shift: `affect_player(target=player_B, action=[reputation_change(target=target, amount=+1, reason="Recognized publicly for their contribution")])`. Player B gains +1 REP. The targeted nature means the recognition is personal and specific, not generic.

---

### `affect_all_players`

**Purpose:** Applies an action to every active player in the game. The broadest targeting primitive.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `action` | Action list | The primitives to apply to each player. |
| `exclude` | Player reference list (optional) | Players to skip. |
| `order` | Enum: `simultaneous`, `ascending_rep`, `descending_rep`, `random`, `turn_order` | The order in which the action is applied. Default: `simultaneous`. |
| `cascade` | Boolean (default: `false`) | Whether the result of one player's action can affect subsequent players (e.g., if Player A loses RES, Player B's effect might change). `simultaneous` order ignores cascade even if true. |

**Execution Timing:** Immediate (order-dependent if not simultaneous).

**Constraints:**
- If all players are in the exclude list, the primitive is a no-op.
- `cascade=true` with `order=random` uses a single random order determined at execution time — each affected player gets one shot.
- When `cascade=true`, stat changes from earlier players are visible to later players' action resolution, enabling chain reactions.

**Example:**
> A "Team Retreat" event gives all players a small MP boost but in cascade order based on Reputation: `affect_all_players(action=modify_stat(stat=MP, target=target, amount=+1, reason="Team retreat sparked new perspectives"), order=descending_rep, cascade=false)`. Everyone gets the same boost simultaneously — no competition, just shared growth. The descending REP order is purely for narrative announcement sequence.

---

### `affect_team`

**Purpose:** Applies an action to all members of a specified team. Similar to `affect_all_players` but scoped to a team.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `team` | Team reference | The team whose members are affected. |
| `action` | Action list | The primitives to apply. |
| `include_leader` | Boolean (default: `true`) | Whether the team leader is included. |
| `require_active` | Boolean (default: `true`) | Whether inactive/disconnected team members are skipped. |

**Execution Timing:** Immediate.

**Constraints:**
- If the team reference is invalid or the team has been disbanded, the primitive fails and logs an error.
- Actions applied via `affect_team` are always logged in the team's shared event log (visible to all team members).

**Example:**
> A "Team Sync" action card boosts the entire team's Trust Tokens with each other: `affect_team(team=summit_partners, action=relationship_change(player_a=target, player_b=team_members, dimension=trust, amount=+1, reason="Regular sync meeting strengthened mutual understanding"), include_leader=true)`. Every team member gains +1 trust with every other team member — the compound effect of consistent communication.

---

### `affect_self`

**Purpose:** Applies an action to the current acting player only. A convenience primitive that makes card descriptions clearer.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `action` | Action list | The primitives to apply to self. |

**Execution Timing:** Immediate.

**Constraints:**
- In compositions, `affect_self` resolves to whoever initiated the action chain. In async play, "self" is the player whose turn or action triggered the primitive.
- Cannot be used in system-level events (no "self" context). Use `affect_player` with a specific target instead.

**Example:**
> A "Self-Reflection" card grants the playing player a private MP boost: `affect_self(action=[modify_stat(stat=MP, target=self, amount=+2, reason="Took time for structured self-reflection", visible=true)])`. The player gains insight into their own patterns — the game's core goal.

---

# SECTION 12: GAME FLOW

Game flow primitives control the macro-level progression of the game: levels, rounds, crises, rewards, and the endgame.

---

### `trigger_crisis`

**Purpose:** Initiates a crisis event — a disruptive game occurrence that demands response from one or more players. Crises are the primary vehicle for testing leadership under pressure.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `crisis_template` | String | The crisis template to instantiate. |
| `severity` | Enum: `minor`, `moderate`, `severe`, `catastrophic` | The crisis severity level. Affects stat penalties, response time, and emotional weight. |
| `target` | Player reference, team reference, or `all` | Who is affected by the crisis. |
| `response_window` | Integer (rounds) | How many rounds players have to respond before automatic consequences fire. |
| `escalation_rule` | Action list (optional) | What happens if the crisis is not addressed within the response window. |
| `visible` | Boolean (default: `true` for `severity >= moderate`) | Whether the crisis is publicly visible. `minor` crises can be hidden (personal struggles). |
| `resolvable` | Boolean (default: `true`) | Whether the crisis can be fully resolved. Some crises are `resolvable=false` — they can only be survived/mitigated. |
| `coop_required` | Boolean (default: `false`) | Whether this crisis requires cooperation (multiple players must contribute to resolution). |

**Execution Timing:** Immediate (crisis enters the active event queue).

**Constraints:**
- Only one `catastrophic` crisis can be active at a time. A new catastrophic crisis while one is active replaces it (the existing one is downgraded to `severe`).
- Crisis severity determines the maximum response window: `minor`=6 rounds, `moderate`=4 rounds, `severe`=3 rounds, `catastrophic`=2 rounds.
- If `coop_required=true` and only one player responds, the crisis is mitigated (severity reduced by one level) but not resolved.
- A player cannot be targeted by more than 2 crises simultaneously. If a third would target them, it is redirected to the player with the fewest active crises.

**Example:**
> A "Supply Chain Collapse" crisis hits the entire game: `trigger_crisis(crisis_template="supply_chain_collapse", severity=severe, target=all, response_window=3, escalation_rule=modify_stat_all(stat=RES, amount=-5, reason="Unresolved supply chain crisis devastated resource reserves"), coop_required=true)`. All players have 3 rounds to collectively address the crisis. If they don't cooperate, everyone loses 5 Resources — but if they do collaborate, the crisis can be resolved with smaller individual costs. This teaches that some leadership challenges cannot be solved alone.

---

### `trigger_reward`

**Purpose:** Grants a reward — a positive game occurrence that provides stat gains, cards, or mechanical advantages. Rewards balance crises and create incentive structures.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `reward_template` | String | The reward template to instantiate. |
| `target` | Player reference, team reference, or `all` | Who receives the reward. |
| `source` | String | What triggered this reward. |
| `shareable` | Boolean (default: `false`) | Whether the recipient can choose to share (transfer) some or all of the reward to others. |
| `decay` | Boolean (default: `false`) | Whether unclaimed rewards decay over time. |
| `claim_window` | Integer (rounds, optional) | How long the reward is available to be claimed. |
| `silent` | Boolean (default: `false`) | Whether the reward is granted without announcement. |

**Execution Timing:** Immediate (reward enters the target's pending rewards or is applied directly).

**Constraints:**
- Rewards cannot be triggered by the same source more than once per round per target (prevents farming).
- `shareable` rewards can be partially shared — the recipient decides how much to keep vs. give.
- `decay=true` rewards lose 25% of their value per unclaimed round. After 4 rounds, the decayed reward is removed.
- Rewards earned during crisis resolution are automatically tagged with the crisis context, making them more meaningful in the reflection system.

**Example:**
> After resolving the "Supply Chain Collapse," a reward triggers: `trigger_reward(reward_template="supply_chain_fixed", target=all, source="crisis_resolution:supply_chain_collapse", shareable=true)`. The reward is shareable, so a generous player might give part of their bonus to a player who contributed more to the resolution — reinforcing cooperative leadership behavior.

---

### `trigger_dysfunction`

**Purpose:** Activates a dysfunction — a negative behavioral pattern that impairs a player or team. Dysfunctions are the game's way of modeling organizational toxicity and individual blind spots.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | Enum: `paranoia`, `burnout`, `silo_thinking`, `groupthink`, `analysis_paralysis`, `scapegoating`, `micromanagement`, `avoidance`, `hero_complex`, `custom` | The dysfunction type. |
| `target` | Player reference or team reference | Who is affected. |
| `intensity` | Enum: `mild`, `moderate`, `severe` | The dysfunction's strength. |
| `source` | String | What triggered this dysfunction (can be a stat threshold, a relationship event, or another dysfunction). |
| `effects` | Effect list (optional) | Custom effects. If not provided, defaults for the dysfunction type are used. |
| `duration` | Integer (rounds) or condition | How long the dysfunction lasts. |
| `curable` | Boolean (default: `true`) | Whether the dysfunction can be actively cured (through specific card plays or actions). |
| `visible` | Enum: `self`, `team`, `all` | Who can see the dysfunction. Many dysfunctions are `self`-visible only — others see the behavioral effects but not the label. |

**Execution Timing:** Immediate (dysfunction becomes active).

**Default Dysfunction Effects:**

| Type | Effect |
|------|--------|
| `paranoia` | -1 FLEX per round. Card choices restricted to defensive options. |
| `burnout` | -1 SP per round. Cannot play action cards. |
| `silo_thinking` | Cannot use `affect_team` or `joint_resolves`. -1 TT per round with all bond partners. |
| `groupthink` | All choices must match the majority choice of the player's team. |
| `analysis_paralysis` | Response windows halved. 1 extra round required for decisions. |
| `scapegoating` | -2 REP per round. Tendency to blame others in choices. |
| `micromanagement` | Team members lose -1 FLEX per round. -1 TT with team per round. |
| `avoidance` | Locked out of crisis response. Cannot be `coop_required` target. |
| `hero_complex` | Must resolve crises alone. Cannot form or benefit from alliances. |

**Constraints:**
- A player can have a maximum of 2 active dysfunctions simultaneously. A third dysfunction either replaces the mildest existing one or is queued.
- Dysfunctions triggered by other dysfunctions (cascade) have their intensity capped at `moderate`.
- `curable=false` dysfunctions can only be removed by time expiration or level advancement.
- Dysfunctions are the primary source of "emotional moments" in the game — they create frustration, self-awareness, and growth when cured.

**Example:**
> Player A's Reputation drops below -5, triggering paranoia: `trigger_dysfunction(type=paranoia, target=player_A, intensity=moderate, source="stat_threshold:REP<-5", duration=3_rounds, curable=true, visible=self)`. Player A loses Flexibility each round and sees restricted options, but only they know the dysfunction's label. Teammates notice the behavioral change (avoidance, defensiveness) but must infer the cause — mirroring how real organizational dysfunctions are experienced.

---

### `advance_level`

**Purpose:** Progresses the game (or a specific player) to the next leadership level: Basecamp → Camp → Summit. This is a major game milestone that changes available cards, difficulty, and mechanics.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `target` | Player reference or `all` | Who advances. |
| `new_level` | Enum: `basecamp`, `camp`, `summit` | The level to advance to. Must be exactly one level above the current level. |
| `carry_over` | Stat carry-over rule (optional) | How stats transfer between levels. Default: all stats carry at 50% value (rounded down), minimum 1 for non-zero stats. |
| `reset` | Boolean (default: `false`) | Whether certain stats are reset for the new level. If true, RES and FLEX reset to level-appropriate baselines; MP, SP, TT, and REP carry over. |
| `unlock` | List of card types or mechanics (optional) | What new cards/mechanics are unlocked at this level. |
| `trigger_on_advance` | Action list (optional) | Custom effects to fire upon level advancement. |
| `announce` | Boolean (default: `true`) | Whether the advancement is publicly announced. |

**Execution Timing:** Immediate (but effects apply at the start of the next round).

**Level Definitions:**

| Level | Focus | Card Pool | Difficulty | Key Mechanic |
|-------|-------|-----------|------------|--------------|
| **Basecamp** | Individual awareness, basic trust | Simple dilemmas, foundational actions | Low | Personal reflection prompts |
| **Camp** | Team dynamics, collaboration | Complex dilemmas, alliance cards | Medium | Joint resolution, team crises |
| **Summit** | Systemic leadership, legacy | High-stakes dilemmas, legacy cards | High | Cross-team impact, final choices |

**Constraints:**
- Cannot skip levels (Basecamp → Summit directly is not allowed).
- Cannot reverse levels (no going back to Basecamp from Camp).
- Level advancement for `all` is synchronized — all players advance together unless the game is in "individual pacing" mode.
- Advancement carries over active promises and debts — they do not reset. This ensures continuity of consequences.
- Active dysfunctions are evaluated during advancement: `mild` dysfunctions are cleared; `moderate` and `severe` dysfunctions persist but their intensity is reduced by one level.

**Example:**
> All players collectively advance from Basecamp to Camp: `advance_level(target=all, new_level=camp, carry_over={MP: 50%, SP: 50%, TT: 100%, REP: 100%}, reset=true, unlock=[alliance_cards, joint_resolution, team_crises], announce=true)`. Resources and Flexibility reset to Camp baselines (representing a new environment's unfamiliarity), but Trust and Reputation carry fully — the relationships and perception built in Basecamp matter at Camp. New card types unlock, expanding strategic options.

---

### `trigger_final_round`

**Purpose:** Initiates the final round of the game — the Summit culmination. All pending events, promises, and debts enter their final evaluation. The game transitions from play to reflection.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `evaluation_mode` | Enum: `scoring`, `reflection`, `legacy` | What the final round focuses on. `scoring` = tally points. `reflection` = generate leadership insights. `legacy` = evaluate long-term impact of decisions. |
| `resolve_all` | Boolean (default: `true`) | Whether all pending promises and debts are force-resolved at end of final round. |
| `lock_new` | Boolean (default: `true`) | Whether new promises/debts/crises can be created during the final round. |
| `reflection_cards` | Integer (default: `1` per player) | How many reflection cards each player receives. |
| `duration` | Integer (real-time days, for async) or rounds | How long the final round lasts. |

**Execution Timing:** Immediate (marks game state as "final round").

**Constraints:**
- `trigger_final_round` can only be called once per game. A second call is a no-op.
- During the final round, `advance_level` is disabled — no further level progression.
- The final round cannot be extended once `duration` expires. Unresolved items at expiration are auto-resolved by the system.
- The final round always spawns at least one reflection card per player, regardless of `reflection_cards` parameter (minimum guarantee).

**Example:**
> After a dramatic summit event, the final round begins: `trigger_final_round(evaluation_mode=legacy, resolve_all=true, lock_new=true, reflection_cards=3, duration=3_rounds)`. Players have 3 rounds to finalize their commitments, settle debts, and prepare for the reflection phase. All unresolved promises are force-evaluated: fulfilled promises earn bonus legacy points; broken promises earn legacy deductions. The `legacy` mode means the game evaluates not just final stats but the entire journey — patterns, sacrifices, and growth.

---

# SECTION 13: EVENT

Event primitives manage the lifecycle of game events — spawning, chaining, and cancelling. Events are broader than cards: they include environmental changes, narrative moments, and system-level occurrences.

---

### `spawn_event`

**Purpose:** Creates and activates a new game event. Events are persistent game state changes that may have ongoing effects, conditions, and durations.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `event_template` | String | The event template to instantiate. |
| `target` | Player reference, team reference, or `all` | Who is affected. |
| `duration` | Integer (rounds) or condition | How long the event persists. `permanent` events last until cancelled. |
| `effects` | Effect list | Ongoing effects applied each round while the event is active. |
| `on_spawn` | Action list | Effects that fire once when the event is created. |
| `on_expire` | Action list | Effects that fire once when the event ends (natural expiry or cancellation). |
| `stackable` | Boolean (default: `false`) | Whether multiple instances of this event type can be active simultaneously. |
| `priority` | Integer (default: `0`) | Resolution order relative to other active events. |
| `tags` | String list | Categorization tags for filtering and system queries. |
| `visible` | Boolean (default: `true`) | Whether the event is visible to affected players. |
| `narrative` | String (optional) | Flavor text describing the event. |

**Execution Timing:** Immediate (event becomes active; `on_spawn` effects fire).

**Constraints:**
- Maximum 20 active events per player at any time. System events (tagged `system`) do not count against this limit.
- Events without a `duration` or `on_expire` that are also not `permanent` default to lasting the remainder of the current level.
- `stackable=false` events: if an identical event is already active on the target, the new spawn refreshes its duration instead of creating a second instance.
- Events are evaluated in `priority` order each round. Higher priority events' effects apply first.

**Example:**
> A "Culture Shift" event creates an ongoing atmospheric change: `spawn_event(event_template="culture_shift", target=all, duration=5_rounds, effects=[modify_stat_all(stat=MP, amount=+1, reason="Open culture encourages growth thinking")], on_spawn=reveal_information(target=all, subject=custom, subject_detail="The organizational culture is shifting toward openness", scope=full), on_expire=modify_stat_all(stat=MP, amount=-1, reason="The culture shift has ended; some gains were ephemeral"), stackable=false, narrative="A groundswell of openness is reshaping how people communicate.")`. For 5 rounds, all players gain +1 MP per round. When it ends, they lose 1 MP — not a full reversal, but a reminder that cultural gains require sustained effort.

---

### `chain_event`

**Purpose:** Links two events such that the resolution of the first automatically triggers the second. Creates narrative and mechanical continuity.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `source_event` | String or event reference | The event whose resolution triggers the chain. |
| `target_event` | Event template or action list | What fires when the source event resolves. Can be a template (for structured chaining) or a custom action list. |
| `delay` | Integer (rounds, default: `0`) | How many rounds after the source event resolves before the target fires. |
| `condition` | Condition (optional) | Additional condition that must be true for the chain to fire. If the condition is false when the source resolves, the chain is broken. |
| `mutable` | Boolean (default: `true`) | Whether the target event's parameters can be modified between the chain's creation and its firing (e.g., by other events or player actions). |
| `description` | String (optional) | Human-readable description of the chain link. |

**Execution Timing:** Registration is immediate. Firing is deferred (upon source event resolution + delay).

**Constraints:**
- Chain depth is limited to 5 links (A → B → C → D → E → F). A 6th link cannot be created.
- If the source event is cancelled (not naturally resolved), the chain does not fire. Chain events only fire on natural resolution or explicit trigger.
- If `mutable=true` and the target event is a template, player actions between chain creation and firing can modify the template's parameters (e.g., changing severity, target). This creates emergent gameplay where anticipation and manipulation of future events matters.
- Circular chains (A chains to B, B chains to A) are detected and blocked at creation time.

**Example:**
> A "Market Disruption" event chains to a "Recovery Effort" event: `chain_event(source_event="market_disruption", target_event="recovery_effort", delay=2, condition=stat(RES, target=all) < 10, mutable=true, description="If the disruption leaves the organization resource-strapped, a recovery effort will be needed.")`. If the market disruption resolves and total resources are low, 2 rounds later a recovery effort event fires — giving players time to prepare. The mutable flag means players can influence how the recovery effort manifests, adding strategic depth.

---

### `cancel_event`

*(See Section 2: Temporal — `cancel_event` is defined there. It is listed here for completeness as an event lifecycle primitive.)*

---

# SECTION 14: COMPOSITION RULES

This section defines how primitives combine into compound behaviors. Every card, crisis, reward, and game mechanic in The Summit is a composition of these primitives following these rules.

## Sequencing

Primitives in a composition execute in the order listed unless specified otherwise. Use `→` notation for explicit sequencing:

```
spawn_card(dilemma, target=self) → reveal_information(subject=card) → schedule_event(on_resolve, advance_level)
```

## Parallelism

Use `+` to denote simultaneous execution:

```
modify_stat(MP, self, +3) + modify_stat(REP, self, +1) + reveal_information(subject=stat, scope=full)
```

Parallel primitives within a composition execute atomically — they all succeed or all fail together.

## Conditioning

Embed `check_condition` within compositions for branching:

```
check_condition(
  condition=stat(MP, self) > 8,
  on_true=modify_stat(SP, self, +3, reason="Growth mindset unlocked skill mastery"),
  on_false=modify_stat(MP, self, +2, reason="Reinforced growth orientation")
)
```

## Targeting

Player targeting primitives feed into other primitives as parameter sources:

```
affect_player(target=player_with_lowest_REP, action=[
  trigger_dysfunction(type=scapegoating, target=target, intensity=mild)
])
```

## Event-Driven Composition

Scheduled events and conditional triggers can contain full compositions as payloads:

```
schedule_event(
  event_id="rep_checkpoint",
  trigger=on_round_end,
  trigger_detail=round:10,
  payload=affect_all_players(
    action=check_condition(
      condition=stat(REP, target) < 0,
      on_true=spawn_card(reflection, template="trust_repair", target=target, zone=pending),
      on_false=spawn_card(reflection, template="leadership_growth", target=target, zone=pending)
    )
  )
)
```

## Composition Constraints

1. **Maximum depth:** No composition can exceed 10 levels of nesting. Flat compositions are preferred for readability and debuggability.
2. **No self-reference:** A primitive cannot modify the condition or event that triggered it.
3. **Atomicity:** Parallel primitives (`+`) succeed or fail as a group. Sequenced primitives (`→`) can have individual failure handling.
4. **Side-effect isolation:** Stat modifications in one branch of a conditional do not affect the evaluation of other branches (branches are evaluated speculatively).
5. **Ordering guarantee:** Within a single round, all player-triggered compositions resolve before system-triggered compositions, ensuring player agency takes precedence.

---

# SECTION 15: CARD DESIGNER'S REFERENCE

This section provides quick-reference templates for common card patterns. Card designers should compose these from the primitives above.

## Dilemma Card Template

A dilemma card presents 2-4 choices, each with distinct outcomes:

```
spawn_card(card_type=dilemma, template_id="example_dilemma", target=self, zone=hand)

Card Resolution:
  Option A: modify_stat(MP, self, +2) + modify_stat(RES, self, -2, reason="Invested in learning at the cost of material resources")
  Option B: modify_stat(SP, self, +2) + modify_stat(TT, self, -1, bond=team, reason="Focused on execution but neglected relationships")
  Option C: check_condition(
    condition=stat(TT, self, bond=specific_player) > 3,
    on_true=transfer_stat(RES, self, specific_player, 3, reason="Shared resources with a trusted partner") + relationship_change(trust, +2),
    on_false=modify_stat(REP, self, -1, reason="Attempted to share but lacked sufficient trust — appeared opportunistic")
  )
```

## Crisis Card Template

A crisis card demands response within a time window:

```
trigger_crisis(crisis_template="example_crisis", severity=moderate, target=all, response_window=3,
  escalation_rule=modify_stat_all(RES, -3, reason="Crisis unresolved — resources depleted"),
  coop_required=false
)

Resolution Paths:
  Individual response: modify_stat(SP, self, -1) + modify_stat(RES, self, -1, reason="Absorbed the crisis personally")
  Collaborative response: (requires 2+ players) modify_stat(TT, self, +1, bond=responder_group) + modify_stat(RES, self, -1, reason="Shared the burden")
  Ignore: (no action within window) automatic escalation_rule fires
```

## Reflection Card Template

Reflection cards don't modify stats — they generate insights:

```
spawn_card(card_type=reflection, template_id="example_reflection", target=self, zone=pending, face_up=false)

On Open:
  reveal_information(target=self, subject=stat, subject_detail=self, scope=full, persistent=true)
  check_condition(
    condition=stat(MP, self) > stat(SP, self),
    on_true=prompt("You consistently prioritized growth over execution. What real-world habit would you change?"),
    on_false=prompt("You consistently prioritized execution over growth. What real-world learning would you commit to?")
  )
```

## Action Card Template

Action cards provide players with active abilities:

```
draw_card(target=self, deck=action, count=1, selection=choice, choice_count=3)

Card Effect:
  probability_check(threshold=0.5, target=self,
    modifiers=[stat(MP, self, +0.1)],
    on_success=modify_stat(FLEX, self, +2, reason="Adaptive thinking enabled a successful pivot"),
    on_failure=modify_stat(FLEX, self, -1, reason="Rigid approach failed under uncertainty")
  )
```

## Legacy Card Template (Summit Level Only)

Legacy cards evaluate the player's entire journey:

```
spawn_card(card_type=reflection, template_id="legacy_audit", target=self, zone=pending)

On Open:
  check_condition(
    condition=count(fulfilled_promises, self) / count(total_promises, self) > 0.7,
    on_true=prompt("You kept more than 70% of your commitments. This reliability is your leadership signature. What's one promise you'll make to your real team this week?"),
    on_false=prompt("You broke more than 30% of your commitments. What pattern caused this? Name one structural change you'd make to improve follow-through.")
  )
```

---

# SECTION 16: STAT INTERACTION MATRIX

This matrix shows how each stat interacts with others — which stat changes trigger consequences in other stats. Card designers must respect these interactions.

| Source Change | Triggered Consequence |
|---------------|----------------------|
| MP reaches 15+ (within a level) | Unlock "visionary" card variants. SP gains from action cards are +1 (rounded up). |
| SP reaches 15+ (within a level) | Crisis response window extends by 1 round. Team members gain +0.5 SP (rounded up) per round (mentorship effect). |
| TT reaches 8+ (with a bond partner) | Unlock joint resolution for dilemma cards with that partner. Promise stakes between partners are halved. |
| REP drops below -5 | `trigger_dysfunction(type=paranoia, intensity=moderate)`. Card draw pool includes "reputation repair" variants. |
| REP exceeds +8 | Unlock "trusted leader" card variants. Alliance formation costs no stake. |
| RES drops to 0 | `trigger_dysfunction(type=avoidance, intensity=mild)`. Cannot be target of resource-transfer requests. |
| RES exceeds 20 | "Resource hoarding" flag: -1 TT per round with all bond partners (others notice the greed). |
| FLEX drops to 0 | All choice-dependent primitives restricted to first listed option only. Cannot use `restrict_choice` (already restricted). |
| FLEX exceeds 10 | Unlock "adaptive" card variants. Dice roll modifiers improved by +1 (via `probability_modifier`). |

---

# SECTION 17: IMPLEMENTATION NOTES

This section provides guidance for implementers translating the Game Grammar into code.

## Primitive as Interface Contract

Each primitive defines a formal interface: name, parameters, timing, and constraints. An implementation must:

1. Accept all defined parameters.
2. Enforce all listed constraints.
3. Respect the declared execution timing.
4. Log all primitive executions to the game audit trail.
5. Emit events for any state change that other primitives might observe (for `conditional_trigger` evaluation).

## Extensibility

New primitives can be added to the Game Grammar by:

1. Defining the primitive following the standard format (name, purpose, parameters, timing, constraints, example).
2. Ensuring the new primitive is expressible as a composition of existing primitives OR is genuinely atomic (cannot be decomposed).
3. Adding the primitive to this document and updating the composition rules if needed.
4. If the primitive affects stats, update the Stat Interaction Matrix.

## Testing Protocol

Every primitive must have:

- **Happy path tests:** The primitive executes correctly with valid parameters.
- **Constraint tests:** The primitive rejects or gracefully handles invalid inputs.
- **Timing tests:** The primitive fires at the correct time (immediate, deferred, conditional).
- **Composition tests:** The primitive works correctly within multi-primitive compositions.
- **Edge case tests:** Boundary values, empty targets, maximum limits.

---

# APPENDIX A: COMPLETE PRIMITIVE INDEX

| Primitive | Section | Category |
|-----------|---------|----------|
| `modify_stat` | 1 | Stat Modification |
| `modify_stat_all` | 1 | Stat Modification |
| `modify_stat_team` | 1 | Stat Modification |
| `transfer_stat` | 1 | Stat Modification |
| `schedule_event` | 2 | Temporal |
| `cancel_event` | 2 / 13 | Temporal / Event |
| `delay_event` | 2 | Temporal |
| `conditional_trigger` | 3 | Conditional |
| `check_condition` | 3 | Conditional |
| `reveal_information` | 4 | Information |
| `hide_information` | 4 | Information |
| `reveal_partial` | 4 | Information |
| `lock_choice` | 5 | Choice |
| `unlock_choice` | 5 | Choice |
| `restrict_choice` | 5 | Choice |
| `spawn_card` | 6 | Card |
| `remove_card` | 6 | Card |
| `draw_card` | 6 | Card |
| `discard_card` | 6 | Card |
| `roll_dice` | 7 | Probability |
| `probability_check` | 7 | Probability |
| `probability_modifier` | 7 | Probability |
| `relationship_change` | 8 | Relationship |
| `create_alliance` | 8 | Relationship |
| `break_alliance` | 8 | Relationship |
| `reputation_change` | 9 | Reputation |
| `create_promise` | 10 | Promise & Debt |
| `fulfill_promise` | 10 | Promise & Debt |
| `break_promise` | 10 | Promise & Debt |
| `create_debt` | 10 | Promise & Debt |
| `resolve_debt` | 10 | Promise & Debt |
| `transfer_debt` | 10 | Promise & Debt |
| `affect_player` | 11 | Player Targeting |
| `affect_all_players` | 11 | Player Targeting |
| `affect_team` | 11 | Player Targeting |
| `affect_self` | 11 | Player Targeting |
| `trigger_crisis` | 12 | Game Flow |
| `trigger_reward` | 12 | Game Flow |
| `trigger_dysfunction` | 12 | Game Flow |
| `advance_level` | 13 | Game Flow |
| `trigger_final_round` | 13 | Game Flow |
| `spawn_event` | 13 | Event |
| `chain_event` | 13 | Event |

---

# APPENDIX B: DESIGN PRINCIPLE COMPLIANCE CHECK

Every primitive has been evaluated against the five design principles:

| Principle | How It Is Enforced |
|-----------|-------------------|
| **Every decision has an opportunity cost** | `modify_stat` always trades one stat for another via card designs. `transfer_stat` explicitly moves resources. `restrict_choice` forces tradeoffs. `create_promise` stakes resources as collateral. |
| **Short-term optimization loses to long-term thinking** | `schedule_event` creates delayed consequences. `create_debt` accrues interest. `advance_level` carries stats at 50%, rewarding sustained investment. `trigger_final_round` evaluates legacy, not snapshot scores. |
| **Players remember emotional moments, not scores** | `trigger_dysfunction` creates visceral impairment. `break_promise` with `consequence_profile=betrayal` is intentionally painful. `reveal_partial` creates mystery and surprise. `chain_event` builds narrative momentum. |
| **Winning is secondary to leadership insight** | `trigger_final_round` with `evaluation_mode=legacy` evaluates the journey. Reflection cards (`spawn_card(card_type=reflection)`) generate personal insights. `create_promise` fulfillment rate is tracked as a leadership metric, not a score. |
| **Every mechanic maps to a real leadership behavior** | Each primitive's purpose explicitly states its real-world analogue. The Stat System Reference maps each stat to a leadership quality. The Stat Interaction Matrix models organizational dynamics. |

---

# APPENDIX C: GLOSSARY

| Term | Definition |
|------|-----------|
| **Atomic Primitive** | A single, indivisible game action that cannot be decomposed into smaller actions. |
| **Composition** | A combination of two or more primitives to express a complex game behavior. |
| **Bond** | The relational context between two specific players, tracked via Trust Tokens. |
| **Dilemma Card** | A card presenting a choice between two or more options, each with distinct stat consequences. |
| **Dysfunction** | A negative status effect that impairs a player or team, modeling organizational toxicity. |
| **Legacy** | The long-term evaluation of a player's decisions across the entire game, not just final stats. |
| **Promise** | A formal commitment from one player to another, with stakes and deadlines. |
| **Reflection Card** | A card that prompts leadership self-assessment without stat modification. |
| **Soft Cap** | A stat threshold where further gains have diminishing returns rather than hard limits. |
| **Stake** | Resources or stats pledged as collateral for a promise, forfeited if the promise is broken. |
| **Stat** | One of the six tracked player attributes: MP, SP, TT, REP, RES, FLEX. |

---

*This document is the foundational grammar of The Summit v2. Every card, event, crisis, reward, and mechanic must be expressible as a composition of these primitives. If you find yourself needing a behavior that cannot be composed from these atoms, propose a new primitive following the format in Section 17.*
