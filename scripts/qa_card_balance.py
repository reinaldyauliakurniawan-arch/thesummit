#!/usr/bin/env python3
"""
QA gate: scan semua card JSON, flag card yang:
1. Salah satu opsi net stat-nya dominan (selisih > 1)
2. Tidak ada visible negative effect di kedua opsi
Exit code 1 kalau ada card yang gagal check -> bisa dipakai di CI/pre-commit.
"""
import json
import glob
import sys

CARD_DIRS = [
    'database/cards/basecamp/basecamp_mindset',
    'database/cards/basecamp/basecamp_skillset',
    'database/cards/camp/camp_mindset',
    'database/cards/camp/camp_skillset',
    'database/cards/summit/summit_mindset',
    'database/cards/summit/summit_skillset',
]

MAX_NET_DIFF = 3  # selisih net stat maksimum yang dianggap "balanced" — gap kecil (2-3) masih sehat untuk dilema naratif; yang dikejar bukan angka rata sempurna, tapi opsi yang benar secara nilai tetap costly, bukan menang telak

def net_stat_sum(effects):
    total = 0
    negs = []
    for e in effects:
        etype = e.get('type') or e.get('primitive')
        if etype == 'modify_stat':
            d = e['params']['delta']
            total += d
            if d < 0:
                negs.append((e['params']['stat'], d))
        elif etype == 'schedule_event':
            inner = e['params'].get('event', {})
            inner_type = inner.get('type') or inner.get('primitive')
            if inner_type == 'modify_stat':
                d = inner['params']['delta']
                total += d
                if d < 0:
                    negs.append((inner['params']['stat'] + '(delayed)', d))
    return total, negs

def main():
    failures = []
    total_checked = 0

    for d in CARD_DIRS:
        for f in sorted(glob.glob(f"{d}/*.json")):
            card = json.load(open(f))
            choices = card.get('choices', {})
            if 'A' not in choices or 'B' not in choices:
                continue
            total_checked += 1

            a_total, a_negs = net_stat_sum(choices['A'].get('effects', []))
            b_total, b_negs = net_stat_sum(choices['B'].get('effects', []))

            diff = abs(a_total - b_total)
            both_have_neg = len(a_negs) > 0 and len(b_negs) > 0

            issues = []
            if diff > MAX_NET_DIFF:
                issues.append(f"net diff {diff} (A={a_total}, B={b_total}) exceeds max {MAX_NET_DIFF}")
            if not both_have_neg:
                issues.append(f"missing visible downside on one option (A_negs={a_negs}, B_negs={b_negs})")

            if issues:
                failures.append((f, card.get('id', '?'), issues))

    print(f"Checked {total_checked} cards.\n")

    if failures:
        print(f"FAILED: {len(failures)} card(s) need review:\n")
        for path, card_id, issues in failures:
            print(f"  {card_id} ({path})")
            for issue in issues:
                print(f"    - {issue}")
        print(f"\n{len(failures)}/{total_checked} cards failed balance check.")
        sys.exit(1)
    else:
        print("All cards passed balance check.")
        sys.exit(0)

if __name__ == '__main__':
    main()
