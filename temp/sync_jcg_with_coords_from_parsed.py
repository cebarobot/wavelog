#!/usr/bin/env python3
"""Sync temp/jcg_with_coords.json from temp/jcg_parsed.json.

Rules:
  - use jcg_parsed.json as authoritative base fields
  - preserve existing coordinate-related fields by `code`
  - write output ordered by jcg_parsed.json order

Usage:
  python3 temp/sync_jcg_with_coords_from_parsed.py
"""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Dict, List

BASE_FIELDS = ["code", "name", "ja_name", "deleted", "deleted_date", "pref_code", "pref_name"]
COORD_FIELDS_DEFAULTS = {
    "lat": None,
    "lon": None,
    "wikidata_id": None,
    "query_used": "",
    "error": "",
}


def load_json_array(path: Path) -> List[Dict]:
    with path.open("r", encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        raise ValueError(f"Expected list in {path}")
    return data


def main() -> int:
    parser = argparse.ArgumentParser(description="Sync jcg_with_coords.json using jcg_parsed.json")
    parser.add_argument("--parsed", default="temp/jcg_parsed.json", help="Path to jcg_parsed.json")
    parser.add_argument("--with-coords", default="temp/jcg_with_coords.json", help="Path to jcg_with_coords.json")
    args = parser.parse_args()

    parsed_path = Path(args.parsed)
    with_coords_path = Path(args.with_coords)

    parsed = load_json_array(parsed_path)
    current = load_json_array(with_coords_path)
    current_by_code = {str(r.get("code", "")).strip(): r for r in current if str(r.get("code", "")).strip()}

    merged: List[Dict] = []
    seen_codes = set()

    for item in parsed:
        code = str(item.get("code", "")).strip()
        if not code:
            continue
        seen_codes.add(code)

        old = current_by_code.get(code, {})

        # Start from old row to retain unknown/extra fields, then override base fields.
        row = dict(old)
        for key in BASE_FIELDS:
            if key in item:
                row[key] = item.get(key)
            elif key not in row:
                row[key] = "" if key in {"name", "ja_name", "deleted_date", "pref_code", "pref_name"} else None

        for key, default_value in COORD_FIELDS_DEFAULTS.items():
            if key not in row:
                row[key] = default_value

        merged.append(row)

    dropped_codes = sorted(c for c in current_by_code.keys() if c not in seen_codes)

    with with_coords_path.open("w", encoding="utf-8") as f:
        json.dump(merged, f, ensure_ascii=False, indent=2)
        f.write("\n")

    print(f"Parsed rows: {len(parsed)}")
    print(f"Merged rows written: {len(merged)} -> {with_coords_path}")
    print(f"Dropped rows not present in parsed: {len(dropped_codes)}")
    if dropped_codes:
        print("Dropped codes sample:", ",".join(dropped_codes[:20]))

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
