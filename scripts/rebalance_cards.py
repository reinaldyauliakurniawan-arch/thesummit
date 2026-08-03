#!/usr/bin/env python3
"""Rebalance all card JSON files per user instructions.
- Seeder: add affect_player + relationship method
- 7 extreme B-penalty cards: reduce penalties
- 9 other penalty imbalance cards: reduce gaps
- 19 no-trade-off cards: add -1 to one option
- 2 full rewrites: basecamp_skillset/007, 009
"""

import json, os

BASE = "/home/z/my-project/thesummit/database/cards"

def read_json(path):
    with open(path) as f:
        return json.load(f)

def write_json(path, data):
    with open(path, 'w') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
        f.write('\n')

def find_effect(effects, stat):
    for i, e in enumerate(effects):
        etype = e.get('type') or e.get('primitive', '')
        if etype == 'modify_stat' and e.get('params', {}).get('stat') == stat:
            return i
    return None

def set_stat(effects, stat, delta):
    idx = find_effect(effects, stat)
    if idx is not None:
        effects[idx]['params']['delta'] = delta
    else:
        effects.append({
            "type": "modify_stat",
            "target": "self",
            "params": {"stat": stat, "delta": delta}
        })

def remove_stat(effects, stat):
    return [e for e in effects
            if not ((e.get('type') or e.get('primitive','')) == 'modify_stat'
                    and e.get('params',{}).get('stat') == stat)]

def remove_conditionals(effects):
    return [e for e in effects
            if (e.get('type') or e.get('primitive','')) != 'conditional_trigger']

def remove_team_effects(effects):
    return [e for e in effects
            if (e.get('type') or e.get('primitive','')) != 'affect_team']

def remove_relationship_effects(effects):
    return [e for e in effects
            if (e.get('type') or e.get('primitive','')) != 'relationship_change']

def remove_delayed(effects):
    return [e for e in effects
            if (e.get('type') or e.get('primitive','')) != 'schedule_event']

# ═══════════════════════════════════════════════════════════════
# SECTION 1: Extreme B-penalty cards
# ═══════════════════════════════════════════════════════════════

def fix_summit_mindset_005():
    p = f"{BASE}/summit_mindset/005.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-3→tt-1, rep-2→rep-1, remove flex-1 and conditional, add sp+1
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', -1)
    b = remove_stat(b, 'flexibility')
    b = remove_conditionals(b)
    set_stat(b, 'sp', 1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_006():
    p = f"{BASE}/summit_mindset/006.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-2→tt-1, rep-1→rep+1, remove flex-1
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', 1)
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_007():
    p = f"{BASE}/summit_mindset/007.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-2→tt-1 only, remove rep-1, flex-1, conditional. Add sp+1
    b = remove_stat(b, 'reputation')
    b = remove_stat(b, 'flexibility')
    b = remove_conditionals(b)
    set_stat(b, 'tt', -1)
    set_stat(b, 'sp', 1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_009():
    p = f"{BASE}/summit_mindset/009.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-3→tt-1, rep-2→rep-1, remove flex-1, conditional
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', -1)
    b = remove_stat(b, 'flexibility')
    b = remove_conditionals(b)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_skillset_006():
    p = f"{BASE}/summit_skillset/006.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-3→tt-1, rep-2→rep-1, remove flex-1, team flex-1, add sp+1
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', -1)
    b = remove_stat(b, 'flexibility')
    b = remove_team_effects(b)
    set_stat(b, 'sp', 1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_skillset_010():
    p = f"{BASE}/summit_skillset/010.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-3→tt-1, rep-2→rep-1, remove flex-1, team tt-2, conditional
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', -1)
    b = remove_stat(b, 'flexibility')
    b = remove_team_effects(b)
    b = remove_conditionals(b)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_basecamp_mindset_007():
    p = f"{BASE}/basecamp_mindset/007.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-2→tt-1, rep-2→rep-1
    set_stat(b, 'tt', -1)
    set_stat(b, 'reputation', -1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

# ═══════════════════════════════════════════════════════════════
# SECTION 2: Other penalty imbalance cards
# ═══════════════════════════════════════════════════════════════

def fix_camp_mindset_003():
    p = f"{BASE}/camp_mindset/003.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, rep-1→0, add sp+1
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'reputation')
    set_stat(b, 'sp', 1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_camp_skillset_002():
    p = f"{BASE}/camp_skillset/002.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_002():
    p = f"{BASE}/summit_mindset/002.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_003():
    p = f"{BASE}/summit_mindset/003.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_mindset_004():
    p = f"{BASE}/summit_mindset/004.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_skillset_002():
    p = f"{BASE}/summit_skillset/002.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_skillset_004():
    p = f"{BASE}/summit_skillset/004.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # rep-1→0, tt-1→0, flex-1→0, add mp+1
    b = remove_stat(b, 'reputation')
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    b = remove_conditionals(b)
    set_stat(b, 'mp', 1)
    d['choices']['B']['effects'] = b
    write_json(p, d)

def fix_summit_skillset_009():
    p = f"{BASE}/summit_skillset/009.json"
    d = read_json(p)
    b = d['choices']['B']['effects']
    # tt-1→0, flex-1→0
    b = remove_stat(b, 'tt')
    b = remove_stat(b, 'flexibility')
    d['choices']['B']['effects'] = b
    write_json(p, d)

# ═══════════════════════════════════════════════════════════════
# SECTION 3: No-trade-off cards — add -1 to one option
# ═══════════════════════════════════════════════════════════════

def add_tradeoff(path, option, stat, reason):
    d = read_json(path)
    effs = d['choices'][option]['effects']
    set_stat(effs, stat, -1)
    d['choices'][option]['effects'] = effs
    write_json(path, d)

def fix_no_tradeoffs():
    # B has more total → add resources-1 to B (seeking mentor costs time/energy)
    add_tradeoff(f"{BASE}/basecamp_mindset/004.json", 'B', 'resources',
                 'Mentor dependency costs autonomy')
    # A is more subtle/safe → add mp-1 to A (holding back takes mental energy)
    add_tradeoff(f"{BASE}/basecamp_mindset/005.json", 'A', 'mp',
                 'Delayed credit-sharing requires ongoing effort')
    # A is more thorough → add resources-1 to A (full accountability = more work on you)
    add_tradeoff(f"{BASE}/basecamp_mindset/010.json", 'A', 'resources',
                 'Owning everything means more follow-up work')
    # A is more prepared → add tt-1 to A (data-heavy prep limits engagement with audience)
    add_tradeoff(f"{BASE}/basecamp_skillset/001.json", 'A', 'tt',
                 'Over-preparation leaves less room for audience questions')
    # A is more thorough → add resources-1 to A (rewriting from scratch is time-intensive)
    add_tradeoff(f"{BASE}/basecamp_skillset/004.json", 'A', 'resources',
                 'Full rewrite is time-intensive with uncertain ROI')
    # B has delayed tt+1 → add mp-1 to B (co-presentation requires coordination overhead)
    add_tradeoff(f"{BASE}/camp_mindset/004.json", 'B', 'mp',
                 'Co-presentation coordination takes mental bandwidth')
    # B has more total → add flex-1 to B (direct feedback may trigger defensiveness)
    add_tradeoff(f"{BASE}/camp_mindset/005.json", 'B', 'flexibility',
                 'Direct approach risks person becoming defensive, reducing options')
    # A has team benefit → add resources-1 to A (follow-up meeting costs time)
    add_tradeoff(f"{BASE}/camp_mindset/008.json", 'A', 'resources',
                 'Scheduling follow-up discussion takes time away from execution')
    # A has more total → add sp-1 to A (listening/involving slows decision speed)
    add_tradeoff(f"{BASE}/camp_mindset/010.json", 'A', 'sp',
                 'Inclusive process is slower than top-down directive')
    # A has team benefit → add resources-1 to A (buddy system costs mentor capacity)
    add_tradeoff(f"{BASE}/camp_skillset/006.json", 'A', 'resources',
                 'Buddy system requires senior time investment')
    # A has team benefit → add sp-1 to A (post-mortem takes time from fixing)
    add_tradeoff(f"{BASE}/camp_skillset/007.json", 'A', 'sp',
                 'Blameless post-mortem is thorough but time-consuming')
    # A has team benefit → add mp-1 to A (mediation is mentally draining)
    add_tradeoff(f"{BASE}/camp_skillset/008.json", 'A', 'mp',
                 'Facilitation of technical disagreement is mentally taxing')
    # A has team benefit → add resources-1 to A (transition period costs money)
    add_tradeoff(f"{BASE}/camp_skillset/009.json", 'A', 'resources',
                 '1-month transition period costs salary budget')
    # A has team benefit → add mp-1 to A (root cause investigation is slow)
    add_tradeoff(f"{BASE}/camp_skillset/010.json", 'A', 'mp',
                 'Investigation before action delays the fix')
    # B has conditional risk → add tt-1 to B (public forum risks trust if done poorly)
    add_tradeoff(f"{BASE}/summit_mindset/008.json", 'B', 'tt',
                 'Public forum risks humiliating the lead and damaging trust further')
    # A has team benefit → add mp-1 to A (custom framework is mentally intensive)
    add_tradeoff(f"{BASE}/summit_skillset/001.json", 'A', 'mp',
                 'Custom framework design is cognitively demanding')
    # A has team benefit → add mp-1 to A (pilot is slower, less immediately decisive)
    add_tradeoff(f"{BASE}/summit_skillset/003.json", 'A', 'mp',
                 'Pilot-first approach is slower than decisive rollout')
    # A has team benefit → add sp-1 to A (systemic fix is slower)
    add_tradeoff(f"{BASE}/summit_skillset/005.json", 'A', 'sp',
                 'Systemic approach takes longer than punitive action')
    # A has team benefit → add mp-1 to A (rapid response plan is mentally draining)
    add_tradeoff(f"{BASE}/summit_skillset/007.json", 'A', 'mp',
                 'Rapid response planning under pressure is exhausting')

# ═══════════════════════════════════════════════════════════════
# SECTION 4: Full rewrites
# ═══════════════════════════════════════════════════════════════

def rewrite_basecamp_skillset_007():
    p = f"{BASE}/basecamp_skillset/007.json"
    data = {
        "id": "basecamp_skillset_007",
        "version": "1.1",
        "level": "basecamp",
        "category": "skillset",
        "type": "crisis",
        "metadata": {
            "author": "summit-team",
            "created": "2026-07-28",
            "dysfunction_tag": "fear_of_conflict"
        },
        "narrative": {
            "situation": "Kamu sedang kerjakan tugas individu yang kompleks dan deadline besok. Kamu stuck pada bagian yang tidak kamu kuasai dan semakin panik.",
            "hidden_reveal": "Temen yang biasa bantu kamu sedang sibuk dengan deadline sendiri — tapi dia sebenarnya sudah menguasai area ini."
        },
        "hidden_info": {
            "enabled": True,
            "reveal_timing": "after_choice",
            "reveal_scope": "chooser"
        },
        "choices": {
            "A": {
                "text": "Minta bantuan teman yang lebih berpengalaman, meski dia juga sedang sibuk.",
                "effects": [
                    {"type": "modify_stat", "target": "self", "params": {"stat": "sp", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "tt", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "flexibility", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "resources", "delta": -1}},
                    {"type": "relationship_change", "target": "other_players", "params": {"change": "trust_gained", "delta": 1, "description": "Asking for help shows vulnerability"}}
                ],
                "behavior_tags": {"collaboration": 1, "empathy": 1, "adaptability": 1},
                "lra_tags": {"PtP_M5": "proving", "R2_S4": "proving"}
            },
            "B": {
                "text": "Coba selesaikan sendiri sampai larut malam, cari workaround dari dokumentasi.",
                "effects": [
                    {"type": "modify_stat", "target": "self", "params": {"stat": "sp", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "mp", "delta": -1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "flexibility", "delta": -1}}
                ],
                "behavior_tags": {"decisiveness": 1, "control": 1, "collaboration": -1},
                "lra_tags": {"R2_M1": "proving", "R1_M4": "disproving", "PtP_M5": "disproving"}
            }
        }
    }
    write_json(p, data)

def rewrite_basecamp_skillset_009():
    p = f"{BASE}/basecamp_skillset/009.json"
    data = {
        "id": "basecamp_skillset_009",
        "version": "1.1",
        "level": "basecamp",
        "category": "skillset",
        "type": "crisis",
        "metadata": {
            "author": "summit-team",
            "created": "2026-07-28",
            "dysfunction_tag": "fear_of_conflict"
        },
        "narrative": {
            "situation": "Kamu dan rekan setara punya pendekatan teknis berbeda untuk fitur yang kamu kerjakan bersama. Masing-masing yakin pendekatannya lebih baik.",
            "hidden_reveal": "Atasan sudah tahu tentang ketidaksepakatan ini dan menunggu langkahmu."
        },
        "hidden_info": {
            "enabled": True,
            "reveal_timing": "before_choice",
            "reveal_scope": "chooser"
        },
        "choices": {
            "A": {
                "text": "Ajak diskusi terbuka, dengarkan pendapatnya dulu sebelum menyampaikan argumenmu.",
                "effects": [
                    {"type": "modify_stat", "target": "self", "params": {"stat": "mp", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "tt", "delta": 2}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "reputation", "delta": 1}},
                    {"type": "relationship_change", "target": "other_players", "params": {"change": "trust_gained", "delta": 1, "description": "Listening first builds mutual respect"}}
                ],
                "behavior_tags": {"empathy": 2, "collaboration": 1, "adaptability": 1},
                "lra_tags": {"PtP_S2": "proving", "R2_S5": "proving", "R3_S5": "proving"}
            },
            "B": {
                "text": "Tunjukkan data kenapa pendekatanmu lebih efisien dan minta dia buktikan klaimnya.",
                "effects": [
                    {"type": "modify_stat", "target": "self", "params": {"stat": "sp", "delta": 1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "tt", "delta": -1}},
                    {"type": "modify_stat", "target": "self", "params": {"stat": "flexibility", "delta": -1}}
                ],
                "behavior_tags": {"decisiveness": 2, "control": 1, "collaboration": -1},
                "lra_tags": {"PtP_S2": "disproving", "R2_S5": "disproving", "R2_M1": "proving"}
            }
        }
    }
    write_json(p, data)

# ═══════════════════════════════════════════════════════════════
# MAIN
# ═══════════════════════════════════════════════════════════════

if __name__ == '__main__':
    print('=== Extreme B-penalty cards ===')
    fix_summit_mindset_005()
    fix_summit_mindset_006()
    fix_summit_mindset_007()
    fix_summit_mindset_009()
    fix_summit_skillset_006()
    fix_summit_skillset_010()
    fix_basecamp_mindset_007()

    print('=== Other penalty imbalance cards ===')
    fix_camp_mindset_003()
    fix_camp_skillset_002()
    fix_summit_mindset_002()
    fix_summit_mindset_003()
    fix_summit_mindset_004()
    fix_summit_skillset_002()
    fix_summit_skillset_004()
    fix_summit_skillset_009()
    # summit_skillset_012 doesn't exist, skip

    print('=== No-trade-off cards ===')
    fix_no_tradeoffs()

    print('=== Full rewrites ===')
    rewrite_basecamp_skillset_007()
    rewrite_basecamp_skillset_009()

    print('Done. All cards rebalanced.')
