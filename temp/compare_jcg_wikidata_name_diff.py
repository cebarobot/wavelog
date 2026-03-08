#!/usr/bin/env python3
"""Compare district name differences by QID and export CSV.

Inputs:
  - temp/wikidata_japan_districts-jcg.json
  - temp/wikidata_japan_districts-wikidata.json
  - temp/jcg_parsed.json

Output CSV columns:
  - code (JCG code)
  - jcg_name (romaji from JCG-side Wikidata snapshot)
  - wikidata_name (romaji from Wikidata snapshot)
"""

from __future__ import annotations

import argparse
import csv
import json
import re
import unicodedata
from pathlib import Path
from typing import Dict, List, Optional


def load_json(path: Path):
    with path.open("r", encoding="utf-8") as f:
        return json.load(f)


def normalize_name(text: str) -> str:
    s = text.strip().lower()
    s = unicodedata.normalize("NFKD", s)
    s = "".join(ch for ch in s if not unicodedata.combining(ch))
    s = re.sub(r"\bdistrict\b", "", s)
    s = re.sub(r"\bgun\b", "", s)
    s = re.sub(r"\(.*?\)", "", s)
    s = s.replace("'", "")
    s = re.sub(r"[^a-z0-9]+", "", s)
    return s


def build_code_lookup(jcg_parsed: List[dict]) -> Dict[str, List[str]]:
    lookup: Dict[str, List[str]] = {}
    for row in jcg_parsed:
        name = str(row.get("name", "")).strip()
        code = str(row.get("code", "")).strip()
        if not name or not code:
            continue
        key = normalize_name(name)
        lookup.setdefault(key, []).append(code)

    for key, codes in lookup.items():
        deduped = sorted(set(codes))
        lookup[key] = deduped

    return lookup


def resolve_code(jcg_name: str, code_lookup: Dict[str, List[str]]) -> Optional[str]:
    key = normalize_name(jcg_name)
    codes = code_lookup.get(key)
    if not codes:
        return None
    if len(codes) == 1:
        return codes[0]
    return "|".join(codes)


def main() -> int:
    parser = argparse.ArgumentParser(description="Compare JCG and Wikidata district names by QID.")
    parser.add_argument(
        "--jcg-json",
        default="temp/wikidata_japan_districts-jcg.json",
        help="Path to JCG-side district JSON.",
    )
    parser.add_argument(
        "--wikidata-json",
        default="temp/wikidata_japan_districts-wikidata.json",
        help="Path to Wikidata-side district JSON.",
    )
    parser.add_argument(
        "--jcg-parsed-json",
        default="temp/jcg_parsed.json",
        help="Path to parsed JCG list JSON containing code/name.",
    )
    parser.add_argument(
        "--output-csv",
        default="temp/jcg_wikidata_name_diff.csv",
        help="Output CSV path.",
    )
    args = parser.parse_args()

    jcg_items = load_json(Path(args.jcg_json))
    wd_items = load_json(Path(args.wikidata_json))
    jcg_parsed = load_json(Path(args.jcg_parsed_json))

    jcg_by_qid = {
        str(row.get("qid", "")).strip(): str(row.get("name", "")).strip()
        for row in jcg_items
        if str(row.get("qid", "")).strip()
    }
    wd_by_qid = {
        str(row.get("qid", "")).strip(): str(row.get("name", "")).strip()
        for row in wd_items
        if str(row.get("qid", "")).strip()
    }

    code_lookup = build_code_lookup(jcg_parsed)

    rows = []
    for qid in sorted(set(jcg_by_qid) & set(wd_by_qid)):
        jcg_name = jcg_by_qid[qid]
        wd_name = wd_by_qid[qid]
        if jcg_name == wd_name:
            continue

        code = resolve_code(jcg_name, code_lookup)
        rows.append(
            {
                "code": code or "",
                "jcg_name": jcg_name,
                "wikidata_name": wd_name,
            }
        )

    rows.sort(key=lambda r: (r["code"] == "", r["code"], r["jcg_name"], r["wikidata_name"]))

    out_path = Path(args.output_csv)
    out_path.parent.mkdir(parents=True, exist_ok=True)
    with out_path.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["code", "jcg_name", "wikidata_name"])
        writer.writeheader()
        writer.writerows(rows)

    print(f"Wrote {len(rows)} rows to {out_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
