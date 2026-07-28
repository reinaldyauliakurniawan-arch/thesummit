# The Summit v2 — Domain Model

## Purpose

Define every entity in the game system. Each entity has a clear responsibility, defined fields, relationships, and lifecycle. This model serves as the conceptual foundation for database design and service architecture. No implementation details.

---

## Entity Overview

```
Game
├── Room
│   ├── Player
│   │   ├── Turn
│   │   ├── Consequence
│   │   ├── Promise (made)
│   │   ├── Promise (received)
│   │   ├── Debt (owed)
│   │   ├── Debt (held)
│   │   ├── Relationship
│   │   ├── BehaviorEvidence
│   │   └── LeadershipProfile
│   ├── Card (drawn)
│   ├── Event
│   ├── EventLog
│   └── Vote
├── CardLibrary
│   └── Card
├── Challenge
└── Reflection
```

---

## Room

### Responsibility
Represents a single game session. Owns the game lifecycle (waiting → in_progress → final_round → finished).

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| code | string | Human-readable 6-character code for joining |
| status | enum | `waiting` \| `in_progress` \| `final_round` \| `finished` |
| host_id | PlayerId | The player who created the room |
| current_player_id | PlayerId? | Whose turn it is (null when finished) |
| turn_started_at | timestamp | When the current turn started (for timeout) |
| final_round_started_at | timestamp? | When final round was triggered (null until triggered) |
| current_round | integer | Monotonically increasing round counter |
| created_at | timestamp | When the room was created |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| host | 1:1 | Player | The room creator |
| players | 1:N | Player | All players in the room |
| current_player | 0:1 | Player | The active player (nullable) |
| turns | 1:N | Turn | All turns in chronological order |
| events | 1:N | Event | All pending scheduled events |
| event_log | 1:N | EventLog | Complete history of everything that happened |
| votes | 1:N | Vote | All vote events in this room |
| consequences | 1:N | Consequence | All active consequences |
| cards_drawn | 1:N | Card | Cards that have been drawn (for exclusion tracking) |

### Lifecycle

```
Created → Waiting → InProgress → FinalRound → Finished
                           ↘            ↗
                            ───────────
```

1. **Created**: `host_id` set. `status = waiting`. Code generated.
2. **Waiting**: Players join. Minimum 3 required to start.
3. **InProgress**: First turn begins. Turn order randomized. Current player set.
4. **FinalRound**: Triggered when any player meets final_win threshold. Each player gets 1 more turn.
5. **Finished**: All players have taken their final-round turn. Scores calculated. Profiles generated. Challenges created.

### Invariants

- A room in `finished` status cannot return to any other status.
- `current_player_id` must be null when `status = finished`.
- `current_round` must be > 0 when `status != waiting`.

---

## Player

### Responsibility
Represents a human participant in a room. Tracks their game state across all dimensions.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room this player is in |
| user_id | UserId | Which human user this player is |
| current_level | enum | `basecamp` \| `camp` \| `summit` |
| mp | integer | Mindset Points (0+), self-mastery progress |
| sp | integer | Skillset Points (0+), leading-others progress |
| tt | integer | Trust Tokens (0+), team trust |
| reputation | integer | Social standing (can go negative, -10 to +20) |
| resources | integer | Available assets (0+, consumable) |
| flexibility | integer | Option breadth (can go negative, -10 to +10) |
| turn_order | integer | Position in turn rotation (1-based, randomized at start) |
| is_active | boolean | Whether the player is still in the game |
| joined_at | timestamp | When the player joined the room |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room this player belongs to |
| user | N:1 | User | The human behind this player |
| turns | 1:N | Turn | All turns this player has taken |
| consequences | 1:N | Consequence | Pending consequences affecting this player |
| promises_made | 1:N | Promise | Promises this player has made |
| promises_received | 1:N | Promise | Promises made to this player |
| debts_owed | 1:N | Debt | Debts this player owes |
| debts_held | 1:N | Debt | Debts this player is owed |
| relationships | 1:N | Relationship | All relationships with other players |
| behavior_evidence | 1:N | BehaviorEvidence | All evidence points for this player |
| leadership_profile | 0:1 | LeadershipProfile | Generated at game end |
| result | 0:1 | Result | Final score and ranking |
| challenge | 0:1 | Challenge | Post-game real-world challenge |

### Lifecycle

```
Joined → Active → Finished
```

1. **Joined**: Player joins a room in `waiting` status. Turn order is 0 (unassigned).
2. **Active**: Game starts. Turn order assigned (randomized). Player takes turns.
3. **Finished**: Game ends. Profile, result, and challenge generated.

### Invariants

- `mp`, `sp`, `tt`, `resources` >= 0 always.
- `turn_order` > 0 only after game start.
- A player can only belong to one room at a time.
- A room can have 3-6 players.

---

## Card

### Responsibility
Represents a dilemma, crisis, event, or reflection card in the game library. Cards are pure data — defined by JSON, not code. The event engine interprets them.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | string | Unique identifier |
| version | string | Schema version |
| level | enum | `basecamp` \| `camp` \| `summit` |
| category | enum | `mindset` \| `skillset` |
| type | enum | `dilemma` \| `crisis` \| `event` \| `reflection` |
| metadata | object | Authoring metadata, tags, dysfunction link |
| narrative | object | All player-facing text |
| hidden_info | object? | Hidden information configuration |
| choices | object | Choice A and Choice B, each with effects |
| conditional_effects | object? | State-dependent effects |
| card_json | JSON | The complete card definition (source of truth) |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| card_library | N:1 | CardLibrary | The library this card belongs to |

### Key Design: Cards are JSON, Not Code

The `card_json` field stores the complete card definition as JSON, following the schema defined in `docs/card-schema.md`. All other fields (level, category, type, etc.) are derived from the JSON for query purposes.

The event engine reads `card_json` and executes the effects. No PHP logic is specific to any card.

### Invariants

- `level`, `category`, `type` must match the values in `card_json`.
- Every card must have exactly 2 choices.
- Every choice must have at least one effect.

---

## Turn

### Responsibility
An immutable record of a single player's decision in a single round. Turns are the atomic unit of gameplay and the primary evidence source for behavior analysis.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| player_id | PlayerId | Who took this turn |
| card_id | CardId | Which card was drawn |
| choice | enum | `A` \| `B` |
| round_number | integer | Which round this was |
| created_at | timestamp | When the turn was taken (no updated_at — immutable) |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room |
| player | N:1 | Player | The player |
| card | N:1 | Card | The card that was drawn |
| event_log | 1:N | EventLog | All events generated by this turn |
| behavior_evidence | 1:N | BehaviorEvidence | All evidence derived from this turn |

### Immutability

Turns are NEVER modified after creation. The `updated_at` column does not exist. If a turn needs to be corrected, a new compensating event is created instead.

**Why**: Turns are the source of truth for behavioral analysis. Modifying turns would corrupt the evidence chain. Compensating events preserve the full history while correcting the game state.

### Invariants

- A player can have at most one turn per round.
- `choice` must be A or B (never null).
- `created_at` is set once and never changed.

---

## Event

### Responsibility
A pending or executed event in the event engine. Events are generic — defined by type and parameters. The event engine processes them without knowledge of card specifics.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| parent_event_id | UUID? | For nested/chained events |
| type | string | The game grammar primitive (e.g., `modify_stat`) |
| target_ref | object | Generic target reference |
| params | object | Primitive-specific parameters |
| condition | object? | Optional condition for execution |
| priority | integer | Queue priority (0-5) |
| timing | enum | `immediate` \| `deferred` |
| source | object | What triggered this event |
| status | enum | `pending` \| `validated` \| `executed` \| `rejected` \| `rolled_back` |
| trigger_round | integer? | For deferred events: which round to fire |
| trigger_condition | object? | For conditional events: condition to evaluate |
| created_at | timestamp | When the event was queued |
| executed_at | timestamp? | When the event was executed |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room this event belongs to |
| parent_event | N:1 | Event | The event that spawned this (if chained) |
| child_events | 1:N | Event | Events spawned by this one |
| event_log | 0:1 | EventLog | The execution record (created after execution) |

### Lifecycle

```
Queued (pending) → Validated → Executed
                      ↘ Rejected
                      ↘ Rolled Back
```

### Invariants

- `status` transitions are one-way: pending → validated → executed (or rejected/rolled_back).
- A deferred event cannot have `executed_at` set until its trigger condition is met.
- `trigger_round` must be > `room.current_round` for deferred events.

---

## EventLog

### Responsibility
An immutable record of every event that has been executed. The log is the audit trail for the entire game.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| event_id | EventId | The event that was executed |
| room_id | RoomId | Which room |
| turn_id | TurnId? | Which turn generated this event |
| player_id | PlayerId? | Which player was affected |
| event_type | string | The event's type (denormalized for query speed) |
| target_type | string | What was targeted (player, team, etc.) |
| params | object | The event parameters (denormalized) |
| result_before | object? | State before the event (for relevant stat events) |
| result_after | object? | State after the event |
| description | string | Human-readable description for UI display |
| round_number | integer | Which round this happened |
| created_at | timestamp | When this was logged |

### Invariants

- EventLog entries are never modified or deleted.
- Every executed event must have exactly one EventLog entry.
- `result_before` and `result_after` are populated for `modify_stat` events only.

---

## Consequence

### Responsibility
A deferred or conditional effect that was created by a past decision and is waiting to trigger. Consequences are the persistence mechanism for PRD Feature 1.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| player_id | PlayerId | Which player is affected |
| originating_turn_id | TurnId | Which decision created this consequence |
| effect_type | enum | `delayed` \| `conditional` |
| trigger_type | enum | `after_rounds` \| `on_condition` \| `on_level_change` \| `on_event` |
| trigger_value | integer? | For `after_rounds`: the round number to fire |
| trigger_condition | object? | For `on_condition`: condition to evaluate |
| event | object | The event to execute when triggered (event engine format) |
| description | string | Human-readable label |
| is_hidden | boolean | Whether the target player can see this consequence |
| is_triggered | boolean | Whether the consequence has fired |
| triggered_at | timestamp? | When it was triggered |
| created_at | timestamp | When the consequence was created |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room |
| player | N:1 | Player | The affected player |
| originating_turn | N:1 | Turn | The decision that created this |
| originating_card | N:1 | Card | The card that was played (via turn) |

### Lifecycle

```
Created → Pending → Triggered
                   ↘ Expired (game ended before trigger)
```

### Invariants

- A consequence can only trigger once.
- `trigger_value` must be > `room.current_round` at creation time for `after_rounds` type.
- A player can have at most 5 pending consequences (configurable).

---

## Promise

### Responsibility
A social commitment between two players. Promises are tracked by the system but NOT enforced — players choose to fulfill or break them. Promise state affects reputation.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| promiser_id | PlayerId | Who made the promise |
| recipient_id | PlayerId | Who the promise was made to |
| promise_type | enum | `vote_for` \| `help_rescue` \| `share_resource` \| `support_bridge` \| `protect_trust` |
| description | string | What was promised |
| is_fulfilled | boolean | Whether the promiser fulfilled it |
| is_broken | boolean | Whether the promiser broke it |
| resolved_at | timestamp? | When it was resolved |
| created_at | timestamp | When the promise was made |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room |
| promiser | N:1 | Player | Who made the promise |
| recipient | N:1 | Player | Who received the promise |

### Lifecycle

```
Created → Active → Fulfilled
                 ↘ Broken (auto-break after N turns)
                 ↘ Expired (game ended)
```

### Invariants

- A promise must be in exactly one state: active, fulfilled, or broken.
- Auto-break occurs after 5 turns if unfulfilled.
- Fulfilling/breaking a promise modifies the promiser's reputation.
- A player cannot make a promise to themselves.

---

## Debt

### Responsibility
An obligation that must be resolved within a timeframe. Unlike promises (social), debts are game-mechanical obligations with automatic penalties.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| debtor_id | PlayerId | Who owes the debt |
| creditor_id | PlayerId? | Who is owed (can be the team/system) |
| debt_type | enum | `stat_owed` \| `action_owed` \| `promise_owed` |
| obligation | object | What is owed (stat amounts, action description) |
| resolve_by | object | When/how it must be resolved |
| penalty | object | Event to execute if not resolved |
| is_resolved | boolean | Whether it has been resolved |
| resolved_at | timestamp? | When it was resolved |
| created_at | timestamp | When the debt was created |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room |
| debtor | N:1 | Player | Who owes |
| creditor | N:1 | Player | Who is owed (nullable for team debts) |

### Invariants

- Unresolved debts auto-execute their penalty event when `resolve_by` is reached.
- A resolved debt cannot become unresolved.

---

## Vote

### Responsibility
A team-wide decision point where all players cast a choice. The engine aggregates votes and applies the result.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| triggering_player_id | PlayerId | Who initiated the vote |
| topic | string | The question being voted on |
| description | string | Detailed description |
| vote_type | enum | `single_choice` \| `approval` |
| options | string[] | Available choices |
| votes_cast | object | Map of player_id → choice |
| resolution | object | How the result is applied (event to execute) |
| is_resolved | boolean | Whether voting is complete |
| result | string? | The winning choice |
| expires_at | timestamp? | When the vote window closes |
| created_at | timestamp | When the vote was created |

### Lifecycle

```
Created → Active → Resolved (all players voted or timeout)
                   ↘ Expired (timeout, no quorum)
```

### Invariants

- A player can vote at most once.
- The vote is resolved when all active players have voted OR the timeout is reached.
- If expired with no votes, the vote is marked resolved with null result.

---

## Relationship

### Responsibility
Tracks the qualitative relationship between two players in a room. Affects cross-player effect magnitudes and social mechanics.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| player_a_id | PlayerId | First player |
| player_b_id | PlayerId | Second player |
| trust_level | integer | -5 to +5, trust between the two |
| alliance_active | boolean | Whether an alliance exists |
| created_at | timestamp | When first established |
| updated_at | timestamp | Last modification |

### Invariants

- There is exactly one Relationship record per ordered pair of players in a room.
- `player_a_id < player_b_id` (canonical ordering to prevent duplicates).
- `trust_level` changes are bounded to -5 and +5.

---

## BehaviorEvidence

### Responsibility
A single data point connecting a player's decision to a leadership dimension. This is the atomic unit of the leadership analytics framework.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| player_id | PlayerId | Which player |
| turn_id | TurnId | Which decision |
| dimension | enum | Which leadership dimension (from leadership-framework.md) |
| signal | enum | `positive` \| `negative` \| `neutral` |
| magnitude | integer | 1 or 2 (evidence strength) |
| source | enum | `explicit_tag` \| `structural_inference` \| `pattern_inference` |
| context | object | Game state at the time (level, round, card type, etc.) |
| created_at | timestamp | When the evidence was recorded |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| player | N:1 | Player | The player |
| turn | N:1 | Turn | The decision |

### Invariants

- Evidence is never modified after creation.
- Evidence is never deleted (even if the turn is rolled back — the decision was still made).
- Every evidence point must reference a valid turn and player.

---

## LeadershipProfile

### Responsibility
The output of the leadership analytics framework. Contains structured data (not narrative text) that the Reflection Engine consumes to generate the report.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| player_id | PlayerId | Which player |
| result_id | ResultId | Which game result |
| style_primary | string? | Primary leadership style (null if insufficient data) |
| style_secondary | string? | Secondary style |
| style_confidence | number | 0.0-1.0, overall confidence |
| dimension_scores | object | Map of dimension → { score, weight, confidence, consistency, evidence_count, classification } |
| strengths | object[] | Top 3 strengths with evidence references |
| blind_spots | object[] | Top 3 blind spots with evidence references |
| unexplored | string[] | Dimensions with insufficient data |
| tensions | object[] | Opposing dimensions with high scores on both |
| data_quality | object | { total_turns, evidence_count, overall_confidence } |
| created_at | timestamp | When generated |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| player | N:1 | Player | 1:1 relationship |
| result | N:1 | Result | The game result |

### Invariants

- A profile is only created at game end.
- A profile contains NO narrative text — only structured data.
- `style_primary` is null if overall_confidence < 0.25.

---

## Challenge

### Responsibility
A personalized real-world leadership challenge generated at game end. The player must complete it within one week. The next session checks for completion.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| player_id | PlayerId | Which player |
| result_id | ResultId | Which game result |
| challenge_type | enum | `delegate` \| `feedback` \| `conversation` \| `initiative` \| `reflection` |
| description | string | The specific challenge |
| rationale | string | Why this challenge was chosen (based on blind spots) |
| is_completed | boolean | Whether the player completed it |
| completion_notes | string? | Player's notes on completion |
| deadline | timestamp | One week from game end |
| created_at | timestamp | When generated |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| player | N:1 | Player | 1:1 relationship |
| result | N:1 | Result | The game result |

### Invariants

- A challenge is generated for every player at game end.
- The challenge type must correspond to the player's weakest leadership dimension (their primary blind spot).
- `deadline` is exactly 7 days after `created_at`.

---

## Result

### Responsibility
The final scoring and ranking record for a player at game end.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| room_id | RoomId | Which room |
| player_id | PlayerId | Which player |
| final_level | enum | `basecamp` \| `camp` \| `summit` |
| final_mp | integer | Final Mindset Points |
| final_sp | integer | Final Skillset Points |
| final_tt | integer | Final Trust Tokens |
| final_reputation | integer | Final reputation |
| final_resources | integer | Final resources |
| final_flexibility | integer | Final flexibility |
| score | integer | Calculated: (level_value * 10) + tt |
| badge | enum | `the_carrier` \| `solo_peak` \| `none` |
| rank | integer | Position in final ranking |
| created_at | timestamp | When generated |

### Relationships

| Relation | Cardinality | Target | Description |
|----------|-------------|--------|-------------|
| room | N:1 | Room | The room |
| player | N:1 | Player | 1:1 relationship |
| leadership_profile | 1:1 | LeadershipProfile | Generated alongside |
| challenge | 1:1 | Challenge | Generated alongside |

### Invariants

- Results are created only at game end.
- `rank` is unique per room (no ties — tiebreaker is badge → score → tt → turn_order).
- `badge` assignment: `the_carrier` (summit + tt >= 8) > `solo_peak` (summit + tt < 8) > `none` (not summit).

---

## User (External)

### Responsibility
A human user account. Not part of the game engine per se, but referenced by Player.

### Fields

| Field | Type | Description |
|-------|------|-------------|
| id | UUID | Unique identifier |
| name | string | Display name |
| email | string | Login email |
| is_admin | boolean | Whether user has admin access |
| has_seen_onboarding | boolean | Whether user has completed the tutorial |

### Invariants

- One user can be in at most one active room.
- Admin users can manage the card library.

---

## Entity Dependency Graph

```
User ─────1:1──── Player ─────1:N──── Turn
                      │                │
                      │                ├── EventLog (1:N via turn)
                      │                └── BehaviorEvidence (1:N via turn)
                      │
                      ├── Consequence (1:N)
                      ├── Promise made (1:N)
                      ├── Promise received (1:N)
                      ├── Debt owed (1:N)
                      ├── Debt held (1:N)
                      ├── Relationship (1:N via player pairs)
                      ├── LeadershipProfile (0:1)
                      ├── Result (0:1)
                      └── Challenge (0:1)

Room ─────1:N──── Player
  │
  ├── Turn (1:N)
  ├── Event (1:N)
  ├── EventLog (1:N)
  ├── Vote (1:N)
  ├── Consequence (1:N)
  └── Result (1:N via room)

Card ─────drawn in──── Turn (N:1)
Card ─────referenced by──── Consequence via Turn

Event ─────chains──── Event (self-referencing via parent_event_id)
```
