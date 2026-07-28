# The Summit v2 — Card DSL Specification

## Purpose

Define a JSON schema capable of representing every card in The Summit without requiring custom PHP logic. Adding a new card should require only authoring a JSON file. The game engine interprets the JSON generically.

---

## Design Goals

1. **Data-driven**: Cards are pure data. The engine executes them without knowing what card they are.
2. **Composable**: Effects are built from primitives defined in `docs/game-grammar.md`.
3. **Extensible**: New effect types can be added to the schema without breaking existing cards.
4. **Validatable**: Every card can be validated against this schema before runtime.
5. **Human-writable**: Card authors should be able to create cards without reading engine code.

---

## Card Schema

### Root Structure

```jsonc
{
  "id": "basecamp_mindset_001",
  "version": "1.0",
  "level": "basecamp",
  "category": "mindset",
  "type": "dilemma",
  "metadata": {
    "author": "card_author_name",
    "created": "2026-07-28",
    "tags": ["trust", "accountability"],
    "dysfunction_tag": "absence_of_trust"
  },
  "narrative": {
    "situation": "...",
    "outcome_hint_a": "...",
    "outcome_hint_b": "..."
  },
  "hidden_info": { ... },
  "choices": { ... },
  "conditional_effects": { ... }
}
```

### Field Definitions

#### Root Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | Yes | Unique identifier. Convention: `{level}_{category}_{sequence}` |
| `version` | string | Yes | Schema version. Allows migration between schema versions. |
| `level` | enum | Yes | `basecamp` \| `camp` \| `summit` |
| `category` | enum | Yes | `mindset` \| `skillset` |
| `type` | enum | Yes | `dilemma` \| `crisis` \| `event` \| `reflection` |
| `metadata` | object | No | Authoring metadata. Not used by engine. |
| `narrative` | object | Yes | All player-facing text. |
| `hidden_info` | object | No | Hidden information system. |
| `choices` | object | Yes | The choices available to the player. |
| `conditional_effects` | object | No | Effects that depend on game state beyond the choice. |

#### `metadata`

```jsonc
{
  "author": "card_author_name",
  "created": "2026-07-28",
  "tags": ["trust", "accountability"],
  "dysfunction_tag": "absence_of_trust"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `author` | string | Who created this card. |
| `created` | date | ISO 8601 date. |
| `tags` | string[] | Freeform tags for searching/filtering. |
| `dysfunction_tag` | enum? | Links to one of the 5 Lencioni dysfunctions. |

#### `narrative`

```jsonc
{
  "situation": "Kamu mendapat tugas proyek baru yang menarik...",
  "outcome_hint_a": "Waktu terasa terbuang, tapi scope jadi jelas.",
  "outcome_hint_b": "Eksekusi cepat tapi ada risiko salah arah.",
  "hidden_reveal": "Ternyata atasan sedang memantau siapa yang proaktif."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `situation` | string | Yes | The dilemma text shown to all players. |
| `outcome_hint_a` | string | No | Brief flavor text shown after choosing A. |
| `outcome_hint_b` | string | No | Brief flavor text shown after choosing B. |
| `hidden_reveal` | string | No | Text revealed only if card has hidden info. |

---

### Hidden Information

```jsonc
{
  "hidden_info": {
    "enabled": true,
    "reveal_timing": "after_choice",
    "reveal_scope": "chooser",
    "conceal_from_effects": true,
    "narrative": {
      "situation_redacted": "Sebagian informasi disembunyikan..."
    }
  }
}
```

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `enabled` | boolean | false | Whether this card has hidden information. |
| `reveal_timing` | enum | `"after_choice"` | `before_choice` \| `after_choice` \| `after_rounds:3` |
| `reveal_scope` | enum | `"chooser"` | `chooser` \| `all_players` \| `none` (never revealed) |
| `conceal_from_effects` | boolean | true | If true, hidden info affects effects but player doesn't see the effect details. |
| `narrative.situation_redacted` | string? | null | If set, replaces `narrative.situation` when shown during choosing. |

---

### Choices

Each card defines 2 choices (A and B). Each choice contains the option text and a list of effects.

```jsonc
{
  "choices": {
    "A": {
      "text": "Minta klarifikasi scope dulu...",
      "effects": [ ... ],
      "behavior_tags": { ... },
      "locked_conditions": [ ... ]
    },
    "B": {
      "text": "Langsung eksekusi...",
      "effects": [ ... ],
      "behavior_tags": { ... },
      "locked_conditions": [ ... ]
    }
  }
}
```

#### Choice Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `text` | string | Yes | The option text shown to the player. |
| `effects` | Effect[] | Yes | Ordered list of effects to execute. |
| `behavior_tags` | object | No | Explicit behavior signals for leadership analytics. |
| `locked_conditions` | Condition[] | No | Conditions under which this choice is locked. |

---

### Effects

Every effect is an object with a `type` field that maps to a game grammar primitive. The engine dispatches each effect generically.

#### Effect Base Structure

```jsonc
{
  "type": "modify_stat",
  "target": "...",
  "params": { ... },
  "timing": "immediate",
  "condition": { ... },
  "description": "..."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | string | Yes | The game grammar primitive to execute. |
| `target` | TargetRef | Yes | Who/what the effect applies to. |
| `params` | object | Yes | Parameters for the primitive. |
| `timing` | enum | No | `immediate` (default) \| `delayed` \| `conditional` |
| `condition` | Condition? | No | If set, effect only executes when condition is met. |
| `description` | string | No | Human-readable description for the effect log. |

#### Target Reference

```jsonc
// Targeting the choosing player
{ "target": "self" }

// Targeting a specific stat
{ "target": "self", "stat": "mp" }

// Targeting all other players
{ "target": "other_players" }

// Targeting all players
{ "target": "all_players" }

// Targeting the player with lowest stat
{ "target": "min_stat_player", "stat": "tt" }

// Targeting a random other player
{ "target": "random_other" }

// Targeting adjacent players in turn order
{ "target": "adjacent_players" }
```

---

### Effect Types

#### 1. modify_stat

Modifies a stat on the target player(s).

```jsonc
{
  "type": "modify_stat",
  "target": "self",
  "params": {
    "stat": "mp",
    "delta": 2,
    "floor": 0,
    "ceiling": null
  },
  "description": "Scope yang jelas mempercepat kerja"
}
```

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `stat` | enum | — | `mp` \| `sp` \| `tt` \| `reputation` \| `resources` \| `flexibility` |
| `delta` | integer | — | Amount to add (can be negative). |
| `floor` | integer? | Depends on stat | Minimum value after modification. 0 for mp/sp/tt/resources, null for reputation/flexibility. |
| `ceiling` | integer? | null | Maximum value after modification. |

#### 2. schedule_event

Schedules a future event that triggers after N rounds.

```jsonc
{
  "type": "schedule_event",
  "target": "self",
  "params": {
    "event": {
      "type": "modify_stat",
      "target": "self",
      "params": { "stat": "mp", "delta": 1 }
    },
    "trigger_after_rounds": 3,
    "is_hidden": false,
    "label": "Scope benefit materializes"
  },
  "timing": "immediate",
  "description": "3 giliran lagi, scope yang jelas mulai terasa manfaatnya"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `event` | Effect | The effect to execute when triggered. Can be nested. |
| `trigger_after_rounds` | integer | Number of rounds to wait. |
| `is_hidden` | boolean | If true, target doesn't see this scheduled event. |
| `label` | string | Description shown in the active consequences panel (if not hidden). |

#### 3. conditional_effect

Executes an effect only when a condition is met. The condition is checked every round.

```jsonc
{
  "type": "conditional_trigger",
  "target": "self",
  "params": {
    "condition": {
      "type": "stat_threshold",
      "stat": "tt",
      "operator": "<=",
      "value": 3
    },
    "event": {
      "type": "modify_stat",
      "target": "self",
      "params": { "stat": "reputation", "delta": -2 }
    },
    "is_hidden": true,
    "label": "Trust collapse damages reputation"
  },
  "description": "Jika TT turun terlalu rendah, reputasi menurun"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `condition` | Condition | The condition to evaluate. |
| `event` | Effect | The effect to execute when condition is met. |
| `is_hidden` | boolean | Whether this conditional is visible to the target. |
| `label` | string | Description if visible. |

#### 4. reveal_information

Reveals hidden information to the target.

```jsonc
{
  "type": "reveal_information",
  "target": "self",
  "params": {
    "reveal_type": "full",
    "content": "Ternyata atasan sedang memantau siapa yang proaktif.",
    "scope": "chooser"
  },
  "timing": "immediate",
  "description": "Informasi tersembunyi terungkap"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `reveal_type` | enum | `full` \| `partial` \| `range` \| `direction` \| `category` |
| `content` | string | The text to reveal. |
| `scope` | enum | `chooser` \| `all_players` \| `specific_player` |

#### 5. roll_dice

Rolls a die and dispatches different effects based on result.

```jsonc
{
  "type": "roll_dice",
  "target": "self",
  "params": {
    "sides": 6,
    "outcomes": [
      {
        "range": [1, 2],
        "effects": [
          {
            "type": "modify_stat",
            "target": "self",
            "params": { "stat": "tt", "delta": -2 }
          },
          {
            "type": "trigger_dysfunction",
            "target": "self",
            "params": { "dysfunction": "random" }
          }
        ]
      },
      {
        "range": [5, 6],
        "effects": [
          {
            "type": "modify_stat",
            "target": "self",
            "params": { "stat": "tt", "delta": 1 }
          }
        ]
      }
    ],
    "default_effects": []
  },
  "description": "Risk Die — hasil krisis"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `sides` | integer | Number of die sides. |
| `outcomes` | Outcome[] | Mapped ranges to effects. |
| `default_effects` | Effect[] | Effects if no range matches (neutral result). |

#### 6. trigger_dysfunction

Triggers a Lencioni dysfunction.

```jsonc
{
  "type": "trigger_dysfunction",
  "target": "self",
  "params": {
    "dysfunction": "random",
    "share_penalty": true,
    "share_fraction": 0.5
  },
  "description": "Dysfunction mempengaruhi seluruh tim"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `dysfunction` | enum | `random` \| `absence_of_trust` \| `fear_of_conflict` \| `lack_of_commitment` \| `avoidance_of_accountability` \| `inattention_to_results` |
| `share_penalty` | boolean | Whether to apply fraction of effect to other players. |
| `share_fraction` | number | Fraction of penalty shared (0.0-1.0). |

#### 7. create_promise

Registers a social promise between players. Promises are tracked by the system but NOT enforced — players choose to fulfill or break them.

```jsonc
{
  "type": "create_promise",
  "target": "self",
  "params": {
    "promise_type": "help_rescue",
    "description": "Berjanji akan membantu pemain lain di krisis berikutnya",
    "auto_suggested": true
  },
  "description": "Sistem menyarankan janji"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `promise_type` | enum | `vote_for` \| `help_rescue` \| `share_resource` \| `support_bridge` \| `protect_trust` |
| `description` | string | Description of the promise. |
| `auto_suggested` | boolean | If true, this is a UI suggestion, not a forced promise. |

#### 8. affect_team

Apply an effect to all players in the room.

```jsonc
{
  "type": "affect_team",
  "target": "all_players",
  "params": {
    "effect": {
      "type": "modify_stat",
      "target": "self",
      "params": { "stat": "tt", "delta": -1 }
    },
    "exclude_source": true
  },
  "description": "Seluruh tim kehilangan 1 TT"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `effect` | Effect | The effect to apply to each player. |
| `exclude_source` | boolean | If true, the choosing player is excluded. |

#### 9. create_vote

Initiates a vote event that all players must participate in.

```jsonc
{
  "type": "create_vote",
  "target": "all_players",
  "params": {
    "topic": "Siapa yang mendapat bonus TT?",
    "description": "Tim harus memutuskan siapa yang paling membutuhkan bantuan",
    "vote_type": "single_choice",
    "options": ["Player dengan TT terendah", "Player dengan MP terendah", "Tidak ada bonus"],
    "timeout_seconds": 120,
    "resolution": {
      "type": "apply_to_choice",
      "effect": {
        "type": "modify_stat",
        "params": { "stat": "tt", "delta": 2 }
      }
    }
  },
  "timing": "immediate",
  "description": "Vote dimulai"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `topic` | string | The question being voted on. |
| `vote_type` | enum | `single_choice` \| `approval` |
| `options` | string[] | Available choices. |
| `timeout_seconds` | integer | How long the vote is open. |
| `resolution` | object | How the vote result is applied. |

#### 10. create_debt

Creates a debt obligation that must be resolved.

```jsonc
{
  "type": "create_debt",
  "target": "self",
  "params": {
    "debt_type": "stat_owed",
    "amount": { "stat": "tt", "delta": -2 },
    "resolve_condition": {
      "type": "after_rounds",
      "rounds": 3
    },
    "penalty_on_fail": {
      "type": "modify_stat",
      "params": { "stat": "reputation", "delta": -3 }
    },
    "label": "Hutang bantuan tim"
  },
  "timing": "immediate",
  "description": "Kamu berhutang bantuan ke tim"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `debt_type` | enum | `stat_owed` \| `action_owed` \| `promise_owed` |
| `amount` | object | The debt obligation. |
| `resolve_condition` | Condition | When the debt must be resolved. |
| `penalty_on_fail` | Effect | What happens if the debt isn't resolved. |
| `label` | string | Human-readable label. |

#### 11. advance_level

Offers a level advancement check.

```jsonc
{
  "type": "advance_level",
  "target": "self",
  "params": {
    "check": true,
    "auto_advance": false
  },
  "timing": "after_effects",
  "description": "Cek apakah memenuhi syarat naik level"
}
```

#### 12. relationship_change

Modifies the relationship between two players.

```jsonc
{
  "type": "relationship_change",
  "target": "other_players",
  "params": {
    "change": "trust_gained",
    "delta": 1,
    "description": "Keputusanmu meningkatkan kepercayaan tim"
  },
  "description": "Hubungan tim berubah"
}
```

| Param | Type | Description |
|-------|------|-------------|
| `change` | enum | `trust_gained` \| `trust_lost` \| `alliance_formed` \| `alliance_broken` |
| `delta` | integer | Magnitude of change. |
| `description` | string | Human-readable description. |

---

### Behavior Tags

Explicit signals for the leadership analytics framework. These are declared by the card author.

```jsonc
{
  "behavior_tags": {
    "risk_taking": 1,
    "collaboration": 0,
    "empathy": -1
  }
}
```

| Field | Type | Description |
|-------|------|-------------|
| Key | enum | Dimension name from `docs/leadership-framework.md`. |
| Value | integer | Signal: positive (1-2), neutral (0), negative (-1 to -2). |

Multiple dimensions can be tagged per choice. Value magnitude corresponds to evidence magnitude.

---

### Condition System

Conditions are evaluated by the event engine. They can be used as `condition` on any effect or as standalone conditional triggers.

#### Condition Types

```jsonc
// Stat threshold condition
{
  "type": "stat_threshold",
  "stat": "tt",
  "operator": "<=",
  "value": 3
}

// Level condition
{
  "type": "level_check",
  "level": "summit"
}

// Turn count condition
{
  "type": "turn_count",
  "operator": ">=",
  "value": 5
}

// Has debt condition
{
  "type": "has_debt",
  "debt_type": "any"
}

// Promise status condition
{
  "type": "promise_status",
  "status": "unfulfilled"
}

// Compound condition (AND)
{
  "type": "and",
  "conditions": [
    { "type": "stat_threshold", "stat": "tt", "operator": "<=", "value": 3 },
    { "type": "level_check", "level": "camp" }
  ]
}

// Compound condition (OR)
{
  "type": "or",
  "conditions": [
    { "type": "stat_threshold", "stat": "mp", "operator": "<=", "value": 5 },
    { "type": "stat_threshold", "stat": "sp", "operator": "<=", "value": 5 }
  ]
}

// Negation
{
  "type": "not",
  "condition": { "type": "has_debt", "debt_type": "any" }
}
```

| Operator | Description |
|----------|-------------|
| `>=` | Greater than or equal |
| `<=` | Less than or equal |
| `>` | Greater than |
| `<` | Less than |
| `==` | Equal |
| `!=` | Not equal |

---

### Lock Conditions

Choices can be locked under certain conditions, preventing the player from selecting them.

```jsonc
{
  "locked_conditions": [
    {
      "condition": {
        "type": "stat_threshold",
        "stat": "tt",
        "operator": "<=",
        "value": 2
      },
      "lock_reason": "Trust terlalu rendah — tim tidak akan mengikuti"
    }
  ]
}
```

When a choice is locked, the UI shows the choice text but grays it out with the lock reason. The player must choose the other option.

---

### Conditional Effects

Effects at the card level (not choice level) that trigger based on game state during or after the choice.

```jsonc
{
  "conditional_effects": {
    "on_choice": {
      "condition": {
        "type": "stat_threshold",
        "stat": "mp",
        "operator": ">=",
        "value": 10
      },
      "effects": [
        {
          "type": "modify_stat",
          "target": "self",
          "params": { "stat": "reputation", "delta": 1 },
          "description": "MP tinggi menunjukkan kompetensi — reputasi naik"
        }
      ]
    },
    "on_dysfunction": {
      "condition": {
        "type": "dysfunction_active",
        "dysfunction": "any"
      },
      "effects": [
        {
          "type": "modify_stat",
          "target": "self",
          "params": { "stat": "flexibility", "delta": -1 },
          "description": "Dysfunction mengurangi fleksibilitas"
        }
      ]
    }
  }
}
```

---

### Crisis Card Extension

Crisis cards include a `roll_dice` effect automatically. The `type: "crisis"` triggers the risk die mechanism.

```jsonc
{
  "type": "crisis",
  "choices": {
    "A": {
      "text": "...",
      "effects": [
        // Player's chosen effects first
        { "type": "modify_stat", "target": "self", "params": { "stat": "mp", "delta": 1 } },
        // Risk die is automatic for crisis type — no need to declare it
        // But you CAN override the default risk die config:
        {
          "type": "roll_dice",
          "target": "self",
          "params": {
            "sides": 6,
            "outcomes": [...]
          }
        }
      ]
    }
  }
}
```

When `type` is `"crisis"` and no `roll_dice` effect is declared in the choice's effects, the engine inserts the default risk die behavior from `config/summit.php`.

---

### Complete Card Examples

#### Example 1: Simple Dilemma

```json
{
  "id": "basecamp_mindset_001",
  "version": "1.0",
  "level": "basecamp",
  "category": "mindset",
  "type": "dilemma",
  "metadata": {
    "author": "summit-team",
    "created": "2026-07-28"
  },
  "narrative": {
    "situation": "Kamu mendapat tugas proyek baru yang menarik, tapi deadline-nya sangat ketat. Kamu antusias tapi khawatir bisa deliver tepat waktu.",
    "outcome_hint_a": "Waktu terasa terbuang di awal, tapi scope jadi jelas.",
    "outcome_hint_b": "Eksekusi cepat tapi ada risiko salah arah."
  },
  "choices": {
    "A": {
      "text": "Minta klarifikasi scope dulu sebelum mulai, meski terasa membuang waktu di awal.",
      "effects": [
        { "type": "modify_stat", "target": "self", "params": { "stat": "mp", "delta": 2 } },
        { "type": "modify_stat", "target": "self", "params": { "stat": "flexibility", "delta": -1 } },
        { "type": "schedule_event", "target": "self", "params": { "event": { "type": "modify_stat", "target": "self", "params": { "stat": "mp", "delta": 1 } }, "trigger_after_rounds": 3, "is_hidden": false, "label": "Scope benefit materializes" } }
      ],
      "behavior_tags": { "decisiveness": 0, "control": 1 }
    },
    "B": {
      "text": "Langsung eksekusi dan beradaptasi seiring jalan, percaya pada kemampuanmu.",
      "effects": [
        { "type": "modify_stat", "target": "self", "params": { "stat": "sp", "delta": 1 } },
        { "type": "modify_stat", "target": "self", "params": { "stat": "flexibility", "delta": 1 } }
      ],
      "behavior_tags": { "risk_taking": 1, "adaptability": 1 }
    }
  }
}
```

#### Example 2: Crisis with Hidden Info and Team Effects

```json
{
  "id": "basecamp_mindset_crisis_001",
  "version": "1.0",
  "level": "basecamp",
  "category": "mindset",
  "type": "crisis",
  "metadata": {
    "author": "summit-team",
    "created": "2026-07-28",
    "dysfunction_tag": "absence_of_trust"
  },
  "narrative": {
    "situation": "Kamu menemukan kesalahan besar dalam laporan yang sudah dikirim ke klien oleh rekanmu. Rekanmu itu teman dekatmu.",
    "outcome_hint_a": "Rekanmu dilindungi, tapi masalah mungkin tidak terselesaikan.",
    "outcome_hint_b": "Masalah terselesaikan, tapi hubungan dengan rekanmu rusak.",
    "hidden_reveal": "Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim."
  },
  "hidden_info": {
    "enabled": true,
    "reveal_timing": "after_choice",
    "reveal_scope": "chooser"
  },
  "choices": {
    "A": {
      "text": "Bicara privat dengan rekanmu dan minta dia yang melapor ke atasan.",
      "effects": [
        { "type": "modify_stat", "target": "self", "params": { "stat": "mp", "delta": 1 } },
        { "type": "modify_stat", "target": "self", "params": { "stat": "reputation", "delta": 1 } },
        { "type": "affect_team", "target": "other_players", "params": { "effect": { "type": "modify_stat", "params": { "stat": "tt", "delta": 1 } }, "exclude_source": true } },
        { "type": "reveal_information", "target": "self", "params": { "reveal_type": "full", "content": "Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim." } }
      ],
      "behavior_tags": { "empathy": 2, "control": -1 }
    },
    "B": {
      "text": "Langsung lapor ke atasan dengan bukti karena menyangkut integritas perusahaan.",
      "effects": [
        { "type": "modify_stat", "target": "self", "params": { "stat": "sp", "delta": 1 } },
        { "type": "modify_stat", "target": "self", "params": { "stat": "tt", "delta": -2 } },
        { "type": "modify_stat", "target": "self", "params": { "stat": "reputation", "delta": -2 } },
        { "type": "reveal_information", "target": "self", "params": { "reveal_type": "full", "content": "Ternyata klien sudah menemukan kesalahan itu sendiri dan sedang mengevaluasi respons tim." } }
      ],
      "behavior_tags": { "decisiveness": 2, "empathy": -2 }
    }
  }
}
```

---

### Validation Rules

Every card must pass these validation rules before being accepted:

| Rule | Description |
|------|-------------|
| Both choices must have at least one effect | Prevents empty options. |
| Stat deltas must be integers | No fractional values. |
| Scheduled events must have positive `trigger_after_rounds` | No zero or negative delays. |
| `reveal_information` content must not be empty | No empty reveals. |
| `roll_dice` outcomes must cover all sides | No unhandled die results. |
| `behavior_tags` values must be in [-2, 2] | Magnitude bounds. |
| `locked_conditions` must have a `lock_reason` | Always explain why a choice is locked. |
| Conditional effects must reference valid stat names | No typos in stat references. |
| `affect_team` with `exclude_source: false` on crisis cards must share_penalty | Prevents card from penalizing entire team including self. |

---

### Card File Storage Convention

```
database/cards/
  basecamp/
    mindset/
      001.json
      002.json
      ...
    skillset/
      001.json
      ...
  camp/
    mindset/
      ...
    skillset/
      ...
  summit/
    mindset/
      ...
    skillset/
      ...
```

The engine loads cards from this directory structure. File names are sequential. The `id` field in each JSON must be unique across all cards.
