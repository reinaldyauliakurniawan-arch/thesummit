#!/usr/bin/env python3
"""Validate all card JSONs per card-schema.md rules."""
import json, os, sys

CARDS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "database", "cards")
VALID_STATS = ["mp","sp","tt","reputation","resources","flexibility"]
VALID_DIMS = ["risk_taking","collaboration","empathy","decisiveness","coaching","control","adaptability"]

def load_all_cards():
    cards = []
    for root, dirs, files in os.walk(CARDS_DIR):
        for f in sorted(files):
            if f.endswith(".json"):
                with open(os.path.join(root, f)) as fh:
                    cards.append(json.load(fh))
    return cards

def extract_stats(effects):
    stats = {}
    for e in effects:
        if e.get("type") == "modify_stat":
            s = e["params"]["stat"]
            stats[s] = stats.get(s, 0) + e["params"]["delta"]
    return stats

def option_positive_total(stats):
    return sum(v for v in stats.values() if v > 0)

def validate(cards):
    errors = []
    dominant = []
    identical = []
    hidden_count = 0
    delayed_count = 0
    conditional_count = 0
    behavior_tagged = 0

    for c in cards:
        cid = c.get("id", "?")
        for req in ["id","version","level","category","type","narrative","choices"]:
            if req not in c:
                errors.append(f"{cid}: missing '{req}'")
        for ch in ["A","B"]:
            choice = c.get("choices",{}).get(ch,{})
            if "text" not in choice:
                errors.append(f"{cid}: choice {ch} missing text")
            if not choice.get("effects"):
                errors.append(f"{cid}: choice {ch} has no effects")
            for e in choice.get("effects",[]):
                if "type" not in e:
                    errors.append(f"{cid}: choice {ch} effect without type")
                if e.get("type") == "modify_stat":
                    st = e.get("params",{}).get("stat","")
                    if st not in VALID_STATS:
                        errors.append(f"{cid}: choice {ch} invalid stat '{st}'")
            tags = choice.get("behavior_tags",{})
            for dim, val in tags.items():
                if dim not in VALID_DIMS:
                    errors.append(f"{cid}: choice {ch} unknown dim '{dim}'")
                if abs(val) > 2:
                    errors.append(f"{cid}: choice {ch} tag '{dim}'={val} outside [-2,2]")

        if c.get("hidden_info",{}).get("enabled"):
            hidden_count += 1
        for ch in ["A","B"]:
            for e in c.get("choices",{}).get(ch,{}).get("effects",[]):
                if e.get("type") == "schedule_event": delayed_count += 1
                if e.get("type") == "conditional_trigger": conditional_count += 1
        a_tags = c.get("choices",{}).get("A",{}).get("behavior_tags",{})
        b_tags = c.get("choices",{}).get("B",{}).get("behavior_tags",{})
        if a_tags or b_tags:
            behavior_tagged += 1

        a_s = extract_stats(c.get("choices",{}).get("A",{}).get("effects",[]))
        b_s = extract_stats(c.get("choices",{}).get("B",{}).get("effects",[]))
        all_s = set(list(a_s.keys()) + list(b_s.keys()))
        a_better = sum(1 for s in all_s if a_s.get(s,0) > b_s.get(s,0))
        b_better = sum(1 for s in all_s if b_s.get(s,0) > a_s.get(s,0))
        a_tot = option_positive_total(a_s)
        b_tot = option_positive_total(b_s)
        if a_better > 0 and b_better == 0 and a_tot - b_tot > 2:
            dominant.append(f"{cid}: A dominates B (A+={a_tot}, B+={b_tot})")
        if b_better > 0 and a_better == 0 and b_tot - a_tot > 2:
            dominant.append(f"{cid}: B dominates A (A+={a_tot}, B+={b_tot})")
        if a_s == b_s and a_tot > 0:
            a_types = sorted([e["type"] for e in c.get("choices",{}).get("A",{}).get("effects",[])])
            b_types = sorted([e["type"] for e in c.get("choices",{}).get("B",{}).get("effects",[])])
            if a_types == b_types and len(set(a_types)) <= 2:
                identical.append(f"{cid}: options functionally identical")

    total = len(cards)
    print(f"=== Card Validation Report ===")
    print(f"Total cards: {total}")
    print(f"Hidden info: {hidden_count}/{total} ({hidden_count/total*100:.1f}%)" if total else "")
    print(f"Delayed effects: {delayed_count}")
    print(f"Conditional effects: {conditional_count}")
    print(f"Behavior-tagged: {behavior_tagged}/{total} ({behavior_tagged/total*100:.1f}%)" if total else "")
    print()
    if errors:
        print(f"ERRORS ({len(errors)}):")
        for e in errors: print(f"  [ERR] {e}")
    else:
        print("No schema errors.")
    print()
    if dominant:
        print(f"DOMINANT OPTIONS ({len(dominant)}):")
        for d in dominant: print(f"  [DOM] {d}")
    else:
        print("No dominant options. PASS")
    print()
    if identical:
        print(f"IDENTICAL OPTIONS ({len(identical)}):")
        for i in identical: print(f"  [ID] {i}")
    else:
        print("No identical options. PASS")
    print()
    sys.exit(1 if (errors or dominant or identical) else 0)

if __name__ == "__main__":
    cards = load_all_cards()
    validate(cards)
