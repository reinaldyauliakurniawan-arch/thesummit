"""
The Summit — behavior tracker (Python port).

Faithful port of app/Services/BehaviorTracker.php for offline simulation.
Implements:
  - Opportunity tracking (TASK 1) — every LRA item tagged on ANY option
  - Missed opportunity tracking (TASK 3) — unchosen option's LRA tags
  - LRA assessment with fairness gate (TASK 4) — "Not enough evidence"
    instead of low score when opportunities are insufficient
  - Confidence scoring with context weighting

Evidence records are kept as dicts in player.evidence list:
  {
    'turn': 3, 'card_id': 'basecamp_mindset_007',
    'option': 'A', 'lra_item': 'PtP_M1', 'source': 'lra_tag',
    'lra_signal': 'proving', 'context_type': 'neutral_basecamp',
    'context_weight': 0.8, 'description': '...',
  }

Sources:
  - 'opportunity'          : counter (card presented this item)
  - 'lra_tag'              : direct evidence (chosen option tagged)
  - 'missed_opportunity'   : indirect evidence (unchosen option tagged)
"""
from __future__ import annotations
from dataclasses import dataclass, field
from typing import Any

from . import config


@dataclass
class EvidenceRecord:
    turn: int
    card_id: str
    option: str
    lra_item: str
    source: str           # 'opportunity' | 'lra_tag' | 'missed_opportunity'
    lra_signal: str       # 'proving' | 'disproving' | 'opportunity' | 'missed_proving' | 'missed_disproving'
    context_type: str
    context_weight: float
    description: str


def get_lra_context_type(level: str, is_krisis: bool) -> str:
    """Match BehaviorTracker::getLRAContextType."""
    crisis = "crisis" if is_krisis else "neutral"
    return f"{crisis}_{level}"


def get_context_weight(context_type: str) -> float:
    return config.LRA_CONTEXT_WEIGHTS.get(context_type, 1.0)


class BehaviorTracker:
    """Ports BehaviorTracker.php logic for offline simulation."""

    def track_turn(self, player, turn_number: int, card, chosen_option: str) -> None:
        """Record opportunity + chosen + missed-opportunity evidence for this turn.

        Mirrors BehaviorTracker::trackBehaviors (the LRA portions only —
        dimension-level pattern detection is not needed for LRA validation).
        """
        chosen_option = chosen_option.upper()
        if chosen_option not in ("A", "B"):
            raise ValueError(f"Invalid option: {chosen_option}")

        is_krisis = card.is_krisis()
        ctx_type = get_lra_context_type(player.current_level, is_krisis)
        ctx_weight = get_context_weight(ctx_type)

        chosen_tags = card.option_lra_tags(chosen_option)
        other_option = "B" if chosen_option == "A" else "A"
        other_tags = card.option_lra_tags(other_option)

        # ── Record opportunity for every LRA item tagged on ANY option ──
        # (TASK 1: opportunity tracking)
        seen_items: set[str] = set()
        for opt in ("A", "B"):
            for lra_item, signal in card.option_lra_tags(opt).items():
                if lra_item in seen_items:
                    continue
                seen_items.add(lra_item)
                if signal not in ("proving", "disproving"):
                    continue
                player.evidence.append(EvidenceRecord(
                    turn=turn_number,
                    card_id=card.id,
                    option=opt,
                    lra_item=lra_item,
                    source="opportunity",
                    lra_signal="opportunity",
                    context_type=ctx_type,
                    context_weight=0.0,  # counter only — no weight
                    description=f"Opportunity: kartu menampilkan kompetensi {lra_item} pada Option {opt} ({signal})",
                ))

        # ── Record direct evidence for the CHOSEN option's LRA tags ──
        for lra_item, signal in chosen_tags.items():
            if signal not in ("proving", "disproving"):
                continue
            item_cfg = config.LRA_ITEMS.get(lra_item, {})
            label = item_cfg.get("label", lra_item)
            desc = (f"Option {chosen_option}: Mendukung indikator '{label}'"
                    if signal == "proving"
                    else f"Option {chosen_option}: Bertentangan dengan indikator '{label}'")
            player.evidence.append(EvidenceRecord(
                turn=turn_number,
                card_id=card.id,
                option=chosen_option,
                lra_item=lra_item,
                source="lra_tag",
                lra_signal=signal,
                context_type=ctx_type,
                context_weight=ctx_weight,
                description=desc,
            ))

        # ── Record missed opportunities for the UNCHOSEN option's tags ──
        # (TASK 3: missed opportunity tracking)
        for lra_item, signal in other_tags.items():
            if signal not in ("proving", "disproving"):
                continue
            # Don't double-count if item also on chosen option
            if lra_item in chosen_tags:
                continue
            item_cfg = config.LRA_ITEMS.get(lra_item, {})
            label = item_cfg.get("label", lra_item)
            missed_signal = f"missed_{signal}"
            desc = (f"Peluang terlewat: Option {other_option} mendukung '{label}' "
                    f"({signal}) tapi pemain memilih Option {chosen_option}")
            # Score: missed_proving → -1, missed_disproving → +1
            # (matches PHP: $signal === 'proving' ? -1 : 1)
            player.evidence.append(EvidenceRecord(
                turn=turn_number,
                card_id=card.id,
                option=other_option,
                lra_item=lra_item,
                source="missed_opportunity",
                lra_signal=missed_signal,
                context_type=ctx_type,
                context_weight=ctx_weight,
                description=desc,
            ))

    # ── LRA Assessment ─────────────────────────────────────────

    def assess(self, player) -> dict[str, dict]:
        """Build LRA assessment for all 31 items.

        Mirrors BehaviorTracker::getLRAAssessment + assessLRAItem.
        Returns: {item_code: assessment_dict, '_coverage': ..., '_summary': ...}
        """
        results: dict[str, dict] = {}
        for item_code in config.LRA_ITEMS:
            opp_count = sum(1 for e in player.evidence
                            if e.lra_item == item_code and e.source == "opportunity")
            evidence = [e for e in player.evidence
                        if e.lra_item == item_code
                        and e.source in ("lra_tag", "missed_opportunity")]
            results[item_code] = self._assess_item(item_code, evidence, opp_count)

        results["_coverage"] = self._build_coverage(player)
        results["_summary"] = self._build_summary(player)
        return results

    def _assess_item(self, item_code: str, evidence: list[EvidenceRecord],
                     opp_count: int) -> dict:
        """Assess a single LRA item. Mirrors assessLRAItem in PHP."""
        item_cfg = config.LRA_ITEMS.get(item_code, {})
        opp_model = config.OPPORTUNITY_MODEL.get(item_code, {})
        min_opp = opp_model.get("min_opportunities", 2)
        limited = opp_model.get("limited_coverage", False)
        label = item_cfg.get("label", item_code)

        # ── TASK 4: Opportunity fairness gate ──
        if opp_count < min_opp:
            reason = ("No card drawn tested this competency."
                      if opp_count == 0
                      else f"Only {opp_count} opportunity(ies) encountered — need at least {min_opp}.")
            return {
                "label": label,
                "tier": item_cfg.get("tier", "PtP"),
                "category": item_cfg.get("category", "MINDSET"),
                "description": item_cfg.get("description", ""),
                "evidence_count": len(evidence),
                "opportunities_presented": opp_count,
                "min_opportunities": min_opp,
                "proving_count": 0,
                "disproving_count": 0,
                "missed_proving_count": 0,
                "missed_disproving_count": 0,
                "context_types": [],
                "positive_pct": 0.0,
                "confidence": 0.0,
                "quality_level": "insufficient",
                "suggested_score": None,
                "defensible": False,
                "fairness_status": ("no_opportunity" if opp_count == 0
                                    else "insufficient_opportunity"),
                "limited_coverage": limited,
                "facilitator_explanation":
                    f"{config.INSUFFICIENT_LABEL} for '{label}'. {reason} "
                    f"Cannot assign a score — insufficient opportunity to "
                    f"demonstrate this competency.",
            }

        count = len(evidence)

        # Count proving/disproving/missed
        proving = sum(1 for e in evidence if e.lra_signal == "proving")
        disproving = sum(1 for e in evidence if e.lra_signal == "disproving")
        missed_proving = sum(1 for e in evidence
                             if e.lra_signal == "missed_proving")
        missed_disproving = sum(1 for e in evidence
                                if e.lra_signal == "missed_disproving")

        # Insufficient evidence (opportunities existed but <2 observations)
        if count < 2:
            return {
                "label": label,
                "tier": item_cfg.get("tier", "PtP"),
                "category": item_cfg.get("category", "MINDSET"),
                "description": item_cfg.get("description", ""),
                "evidence_count": count,
                "opportunities_presented": opp_count,
                "min_opportunities": min_opp,
                "proving_count": 0,
                "disproving_count": 0,
                "missed_proving_count": missed_proving,
                "missed_disproving_count": missed_disproving,
                "context_types": [],
                "positive_pct": 0.0,
                "confidence": 0.0,
                "quality_level": "insufficient",
                "suggested_score": None,
                "defensible": False,
                "fairness_status": "insufficient_evidence",
                "limited_coverage": limited,
                "facilitator_explanation":
                    f"{config.INSUFFICIENT_LABEL} for '{label}'. "
                    f"Opportunities existed ({opp_count} card(s) tested this) "
                    f"but only {count} observation(s) recorded. Need at least 2.",
            }

        positive_pct = proving / count if count > 0 else 0

        # Context types
        context_types: dict[str, bool] = {}
        total_weight = 0.0
        proving_weight = 0.0
        direction_changes = 0
        last_signal = None
        for e in evidence:
            ctx = e.context_weight if e.context_weight > 0 else 1.0
            total_weight += ctx
            if e.lra_signal == "proving":
                proving_weight += ctx
            context_types[e.context_type] = True
            if last_signal is not None and e.lra_signal != last_signal:
                direction_changes += 1
            last_signal = e.lra_signal

        context_count = len(context_types)

        # Confidence (matches PHP formula)
        if count >= config.LRA_MIN_OBS_STRONG:
            raw_conf = 0.85
        elif count >= config.LRA_MIN_OBS_MEDIUM:
            raw_conf = 0.65
        else:
            raw_conf = 0.40
        context_bonus = 0.10 if context_count >= 3 else (0.05 if context_count >= 2 else 0)
        stability = 1.0 - (direction_changes / (count - 1)) if count > 1 else 1.0
        final_conf = min(1.0, raw_conf + context_bonus) * (0.6 + 0.4 * stability)

        # Quality level
        quality = "insufficient"
        if final_conf >= 0.90 and count >= 7 and context_count >= 3:
            quality = "repeated"
        elif final_conf >= 0.75 and count >= 5 and context_count >= 3:
            quality = "strong"
        elif final_conf >= 0.50 and count >= 3 and context_count >= 2:
            quality = "medium"
        elif final_conf >= 0.25 and count >= 2:
            quality = "weak"

        # Contradictory evidence
        is_contradictory = (proving >= 3 and disproving >= 3)

        suggested_score = None
        if is_contradictory:
            suggested_score = "mixed"
        elif quality != "insufficient":
            suggested_score = self._map_score(positive_pct, quality)

        return {
            "label": label,
            "tier": item_cfg.get("tier", "PtP"),
            "category": item_cfg.get("category", "MINDSET"),
            "description": item_cfg.get("description", ""),
            "evidence_count": count,
            "opportunities_presented": opp_count,
            "min_opportunities": min_opp,
            "proving_count": proving,
            "disproving_count": disproving,
            "missed_proving_count": missed_proving,
            "missed_disproving_count": missed_disproving,
            "context_types": list(context_types.keys()),
            "positive_pct": round(positive_pct, 2),
            "confidence": round(final_conf, 2),
            "quality_level": ("contradictory" if is_contradictory else quality),
            "suggested_score": suggested_score,
            "defensible": (final_conf >= config.LRA_MIN_CONFIDENCE
                           and not is_contradictory),
            "fairness_status": "fair",
            "limited_coverage": limited,
        }

    def _map_score(self, positive_pct: float, quality: str) -> int | None:
        if positive_pct >= 0.80 and quality == "strong":
            return 5
        if positive_pct >= 0.70 and quality in ("strong", "repeated"):
            return 4
        if positive_pct >= 0.60 and quality != "insufficient":
            return 3
        if positive_pct >= 0.40 and quality != "insufficient":
            return 2
        return 1

    def _build_coverage(self, player) -> dict:
        report = {}
        for item_code, item_cfg in config.LRA_ITEMS.items():
            opp = sum(1 for e in player.evidence
                      if e.lra_item == item_code and e.source == "opportunity")
            opp_model = config.OPPORTUNITY_MODEL.get(item_code, {})
            min_opp = opp_model.get("min_opportunities", 2)
            limited = opp_model.get("limited_coverage", False)
            report[item_code] = {
                "opportunities_presented": opp,
                "min_required": min_opp,
                "fairness_status": ("fair" if opp >= min_opp
                                    else ("no_opportunity" if opp == 0
                                          else "insufficient_opportunity")),
                "limited_coverage": limited,
            }
        return report

    def _build_summary(self, player) -> dict:
        summary = {
            "total_items": len(config.LRA_ITEMS),
            "items_assessable": 0,
            "items_no_opportunity": 0,
            "items_insufficient_opportunity": 0,
            "items_limited_coverage": 0,
            "items_insufficient_evidence": 0,
            "items_assessed": 0,
            "per_item": {},
        }
        assessment = self.assess(player) if False else None  # not used; we use caller's results
        for item_code, item_cfg in config.LRA_ITEMS.items():
            opp = sum(1 for e in player.evidence
                      if e.lra_item == item_code and e.source == "opportunity")
            ev_count = sum(1 for e in player.evidence
                           if e.lra_item == item_code
                           and e.source in ("lra_tag", "missed_opportunity"))
            opp_model = config.OPPORTUNITY_MODEL.get(item_code, {})
            min_opp = opp_model.get("min_opportunities", 2)
            limited = opp_model.get("limited_coverage", False)
            status = ("fair" if opp >= min_opp
                      else ("no_opportunity" if opp == 0
                            else "insufficient_opportunity"))

            summary["per_item"][item_code] = {
                "label": item_cfg.get("label", item_code),
                "tier": item_cfg.get("tier", "PtP"),
                "opportunities_presented": opp,
                "evidence_collected": ev_count,
                "min_required": min_opp,
                "fairness_status": status,
            }

            if opp == 0:
                summary["items_no_opportunity"] += 1
            elif opp < min_opp:
                summary["items_insufficient_opportunity"] += 1
            else:
                summary["items_assessable"] += 1
            if limited:
                summary["items_limited_coverage"] += 1
        return summary
