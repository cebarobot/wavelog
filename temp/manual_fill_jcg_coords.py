#!/usr/bin/env python3
"""Interactive helper to fill missing JCG coordinates using Wikidata QIDs.

Usage:
  proxychains -q python3 temp/manual_fill_jcg_coords.py

Notes:
  - The script updates temp/jcg_with_coords.json after each confirmed entry.
  - You can interrupt anytime; saved entries remain persisted.
"""

from __future__ import annotations

import json
import re
import sys
import urllib.request
from pathlib import Path
from typing import Dict, List, Set, Tuple

DATA_PATH = Path("temp/jcg_with_coords.json")
USER_AGENT = "Wavelog-JCG-ManualFill/1.0 (https://github.com/wavelog/wavelog)"
QID_RE = re.compile(r"^Q\d+$", re.IGNORECASE)


def load_items(path: Path) -> List[Dict]:
    if not path.exists():
        raise FileNotFoundError(f"JSON file not found: {path}")
    with path.open("r", encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        raise ValueError(f"Expected top-level list in {path}")
    return data


def save_items(path: Path, items: List[Dict]) -> None:
    tmp_path = path.with_suffix(path.suffix + ".tmp")
    with tmp_path.open("w", encoding="utf-8") as f:
        json.dump(items, f, ensure_ascii=False, indent=2)
        f.write("\n")
    tmp_path.replace(path)


def has_coord(item: Dict) -> bool:
    return item.get("lat") is not None and item.get("lon") is not None


def extract_qids(raw: str) -> List[str]:
    tokens = re.split(r"[\s,;|]+", raw.strip())
    qids: List[str] = []
    for token in tokens:
        if not token:
            continue
        token_up = token.upper()
        if QID_RE.match(token_up):
            qids.append(token_up)
    seen = set()
    deduped: List[str] = []
    for qid in qids:
        if qid in seen:
            continue
        seen.add(qid)
        deduped.append(qid)
    return deduped


def fetch_entity_json(qid: str) -> Dict:
    url = f"https://www.wikidata.org/wiki/Special:EntityData/{qid}.json"
    print(f"[FETCH] GET {url}")
    req = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    with urllib.request.urlopen(req, timeout=30) as resp:
        payload = resp.read().decode("utf-8")
    return json.loads(payload)


def parse_coords_from_entity(doc: Dict, qid: str) -> List[Tuple[float, float]]:
    entities = doc.get("entities", {})
    ent = entities.get(qid)
    if not isinstance(ent, dict):
        return []

    claims = ent.get("claims", {})
    p625 = claims.get("P625", [])
    if not isinstance(p625, list):
        return []

    coords: List[Tuple[float, float]] = []
    for idx, claim in enumerate(p625, start=1):
        mainsnak = claim.get("mainsnak", {}) if isinstance(claim, dict) else {}
        datavalue = mainsnak.get("datavalue", {}) if isinstance(mainsnak, dict) else {}
        value = datavalue.get("value", {}) if isinstance(datavalue, dict) else {}

        lat = value.get("latitude")
        lon = value.get("longitude")
        if isinstance(lat, (int, float)) and isinstance(lon, (int, float)):
            coords.append((float(lat), float(lon)))
            print(f"[FETCH] {qid} P625#{idx}: lat={lat:.9f}, lon={lon:.9f}")

    print(f"[FETCH] {qid} coordinates_found={len(coords)}")
    return coords


def avg_coords(coords: List[Tuple[float, float]]) -> Tuple[float, float]:
    lat = sum(c[0] for c in coords) / len(coords)
    lon = sum(c[1] for c in coords) / len(coords)
    return lat, lon


def print_item(item: Dict, idx: int, total_missing: int) -> None:
    print("\n" + "=" * 72)
    print(f"Missing #{idx}/{total_missing}")
    print(f"code      : {item.get('code', '')}")
    print(f"name      : {item.get('name', '')}")
    print(f"pref      : {item.get('pref_code', '')} {item.get('pref_name', '')}")
    print(f"deleted   : {item.get('deleted', False)}")
    print(f"last_error: {item.get('error', '')}")
    print("Input one or more Wikidata QIDs (example: Q123 Q456).")
    print("Commands: skip | quit")


def ask_yes_no_default_yes(prompt: str) -> bool:
    while True:
        ans = input(prompt).strip().lower()
        if ans == "":
            return True
        if ans in {"y", "yes"}:
            return True
        if ans in {"n", "no"}:
            return False
        print("Please answer y or n (Enter for default y).")


def main() -> int:
    try:
        items = load_items(DATA_PATH)
    except Exception as exc:  # noqa: BLE001
        print(f"[ERROR] failed to load {DATA_PATH}: {exc}")
        return 1

    total = len(items)
    print(f"Loaded {total} records from {DATA_PATH}")
    deferred_codes: Set[str] = set()

    while True:
        missing_indexes = [i for i, item in enumerate(items) if not has_coord(item)]
        if not missing_indexes:
            print("All records have coordinates. Nothing left to fill.")
            return 0

        target_index = -1
        for idx in missing_indexes:
            code = str(items[idx].get("code", ""))
            if code and code in deferred_codes:
                continue
            target_index = idx
            break

        if target_index < 0:
            deferred_codes.clear()
            target_index = missing_indexes[0]

        current_pos = missing_indexes.index(target_index) + 1
        item = items[target_index]
        print_item(item, current_pos, len(missing_indexes))

        raw = input("> ").strip()
        if not raw:
            print("No input received. Please enter QIDs or a command.")
            continue

        cmd = raw.lower()
        if cmd == "quit":
            print("Quit requested. Progress has been saved incrementally.")
            return 0
        if cmd == "skip":
            code = str(item.get("code", ""))
            if code:
                deferred_codes.add(code)
            print("Skipped current item for now.")
            continue

        qids = extract_qids(raw)
        if not qids:
            print("No valid QID found. Example input: Q123 or Q123 Q456")
            continue

        print(f"[FETCH] qids={qids}")
        all_coords: List[Tuple[float, float]] = []
        for qid in qids:
            try:
                doc = fetch_entity_json(qid)
                coords = parse_coords_from_entity(doc, qid)
            except Exception as exc:  # noqa: BLE001
                print(f"[ERROR] fetch failed for {qid}: {exc}")
                coords = []
            all_coords.extend(coords)

        if not all_coords:
            print("[RESULT] No coordinates found from provided QIDs. Try another QID set.")
            continue

        mean_lat, mean_lon = avg_coords(all_coords)
        print(f"[RESULT] aggregated_points={len(all_coords)}")
        print(f"[RESULT] avg_lat={mean_lat:.9f}, avg_lon={mean_lon:.9f}")

        if not ask_yes_no_default_yes("Apply these coordinates to current JCG record? [Y/n]: "):
            print("Not applied.")
            continue

        item["lat"] = round(mean_lat, 9)
        item["lon"] = round(mean_lon, 9)
        item["wikidata_id"] = "|".join(qids)
        item["query_used"] = "manual_wikidata_qid_avg" if len(all_coords) > 1 else "manual_wikidata_qid"
        item["error"] = ""

        code = str(item.get("code", ""))
        if code and code in deferred_codes:
            deferred_codes.remove(code)

        try:
            save_items(DATA_PATH, items)
        except Exception as exc:  # noqa: BLE001
            print(f"[ERROR] failed to save {DATA_PATH}: {exc}")
            return 1

        code = item.get("code", "")
        name = item.get("name", "")
        print(f"[SAVED] code={code} name={name} lat={item['lat']} lon={item['lon']}")


if __name__ == "__main__":
    sys.exit(main())
