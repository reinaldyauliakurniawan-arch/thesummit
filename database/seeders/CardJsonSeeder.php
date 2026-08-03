<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\ExpeditionCard;

class CardJsonSeeder extends Seeder
{
    public function run(): void
    {
        ExpeditionCard::query()->delete();

        $cardsDir = database_path("cards");
        $flatFiles = glob("$cardsDir/*/*.json");
        $nestedFiles = glob("$cardsDir/*/*/*.json");
        sort($flatFiles);
        sort($nestedFiles);
        // Flat files diprioritaskan: skemanya sudah lengkap & kompatibel dengan seeder ini.
        // Nested files (skema draft lain) hanya dipakai untuk id yang belum ada di flat.
        $allFiles = array_merge($flatFiles, $nestedFiles);

        // Deduplicate by card_id to avoid UNIQUE constraint violations
        $seen = [];
        $files = [];
        foreach ($allFiles as $file) {
            $json = json_decode(file_get_contents($file), true);
            $id = $json['id'] ?? null;
            if ($id && !isset($seen[$id])) {
                $seen[$id] = true;
                $files[] = $file;
            }
        }

        $count = 0;
        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (!$json) continue;

            $aEff = $json["choices"]["A"]["effects"] ?? [];
            $bEff = $json["choices"]["B"]["effects"] ?? [];

            ExpeditionCard::create([
                "card_id"                    => $json["id"],
                "level"                      => $json["level"],
                "kategori"                   => $json["category"],
                "tipe"                       => $json["type"] === "crisis" ? "krisis" : "netral",
                "teks_situasi"              => $json["narrative"]["situation"] ?? "",
                "opsi_a_teks"               => $json["choices"]["A"]["text"] ?? "",
                "opsi_a_mp"                 => $this->stat($aEff, "mp"),
                "opsi_a_sp"                 => $this->stat($aEff, "sp"),
                "opsi_a_tt"                 => $this->stat($aEff, "tt"),
                "opsi_a_reputation"         => $this->stat($aEff, "reputation"),
                "opsi_a_resources"          => $this->stat($aEff, "resources"),
                "opsi_a_flexibility"        => $this->stat($aEff, "flexibility"),
                "opsi_a_behavior_tags"      => $json["choices"]["A"]["behavior_tags"] ?? ($json["behavior_tags"]["A"] ?? []),
                "opsi_a_delayed_effects"    => $this->delayed($aEff),
                "opsi_a_conditional_effects"=> $this->conditional($aEff),
                "opsi_a_cross_player"       => $this->team($aEff),
                "opsi_a_relationship"       => $this->relationship($aEff),
                "opsi_b_teks"               => $json["choices"]["B"]["text"] ?? "",
                "opsi_b_mp"                 => $this->stat($bEff, "mp"),
                "opsi_b_sp"                 => $this->stat($bEff, "sp"),
                "opsi_b_tt"                 => $this->stat($bEff, "tt"),
                "opsi_b_reputation"         => $this->stat($bEff, "reputation"),
                "opsi_b_resources"          => $this->stat($bEff, "resources"),
                "opsi_b_flexibility"        => $this->stat($bEff, "flexibility"),
                "opsi_b_behavior_tags"      => $json["choices"]["B"]["behavior_tags"] ?? ($json["behavior_tags"]["B"] ?? []),
                "opsi_b_delayed_effects"    => $this->delayed($bEff),
                "opsi_b_conditional_effects"=> $this->conditional($bEff),
                "opsi_b_cross_player"       => $this->team($bEff),
                "opsi_b_relationship"       => $this->relationship($bEff),
                "dysfunction_tag"            => $json["metadata"]["dysfunction_tag"] ?? null,
                "has_hidden_info"           => ($json["hidden_info"]["enabled"] ?? false) ? true : false,
                "hidden_info_reveal"        => $json["narrative"]["hidden_reveal"] ?? null,
                "card_json"                 => json_encode($json),
            ]);
            $count++;
        }
        $this->command->info("$count cards seeded from JSON files.");
    }

    private function stat(array $effects, string $s): int
    {
        foreach ($effects as $e) {
            $etype = $e["type"] ?? $e["primitive"] ?? "";
            if ($etype === "modify_stat" && ($e["params"]["stat"] ?? "") === $s)
                return $e["params"]["delta"] ?? 0;
        }
        return 0;
    }

    private function delayed(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            $etype = $e["type"] ?? $e["primitive"] ?? "";
            if ($etype === "schedule_event") {
                $inner = $e["params"]["event"] ?? [];
                $r[] = [
                    "stat" => $inner["params"]["stat"] ?? "",
                    "delta" => $inner["params"]["delta"] ?? 0,
                    "trigger_after_rounds" => $e["params"]["trigger_after_rounds"] ?? 0,
                    "label" => $e["params"]["label"] ?? ($inner["reason"] ?? ""),
                    "is_hidden" => $e["params"]["is_hidden"] ?? false,
                ];
            }
        }
        return $r;
    }

    private function conditional(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            $etype = $e["type"] ?? $e["primitive"] ?? "";
            if ($etype === "conditional_trigger") {
                $inner = $e["params"]["event"] ?? [];
                $r[] = [
                    "stat" => $inner["params"]["stat"] ?? "",
                    "delta" => $inner["params"]["delta"] ?? 0,
                    "condition" => $e["params"]["condition"] ?? [],
                    "label" => $e["params"]["label"] ?? "",
                    "is_hidden" => $e["params"]["is_hidden"] ?? true,
                ];
            }
        }
        return $r;
    }

    private function team(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            $etype = $e["type"] ?? $e["primitive"] ?? "";
            if ($etype === "affect_team") {
                $inner = $e["params"]["effect"]["params"] ?? $e["params"] ?? [];
                $stat = $inner["stat"] ?? "";
                if ($stat === "") {
                    continue;
                }
                $r[] = [
                    "stat" => $stat,
                    "delta" => $inner["delta"] ?? 0,
                    "exclude_source" => $e["params"]["exclude_source"] ?? true,
                ];
            } elseif ($etype === "affect_player") {
                $r[] = [
                    "stat" => $e["params"]["stat"] ?? "",
                    "delta" => $e["params"]["delta"] ?? 0,
                    "exclude_source" => false,
                ];
            }
        }
        return $r;
    }

    private function relationship(array $effects): array
    {
        $r = [];
        foreach ($effects as $e) {
            $etype = $e["type"] ?? $e["primitive"] ?? "";
            if ($etype === "relationship_change") {
                $r[] = [
                    "dimension" => $e["params"]["dimension"] ?? "trust",
                    "delta" => $e["params"]["amount"] ?? $e["params"]["delta"] ?? 0,
                    "reason" => $e["params"]["reason"] ?? "",
                ];
            }
        }
        return $r;
    }
}
