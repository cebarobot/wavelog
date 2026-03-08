#!/usr/bin/env python3
"""Rebuild temp/jcg_parsed.json from temp/jcg-list-ja.txt.

Enhancements over previous parsed data:
  - add Japanese district name (`ja_name`)
  - add deleted date (`deleted_date`) for deleted entries

Usage:
  python3 temp/rebuild_jcg_parsed_from_ja.py
"""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Dict, List

PREF_MAP = {
    "01": "Hokkaido",
    "02": "Aomori",
    "03": "Iwate",
    "04": "Akita",
    "05": "Yamagata",
    "06": "Miyagi",
    "07": "Fukushima",
    "08": "Niigata",
    "09": "Nagano",
    "10": "Tokyo",
    "11": "Kanagawa",
    "12": "Chiba",
    "13": "Saitama",
    "14": "Ibaraki",
    "15": "Tochigi",
    "16": "Gunma",
    "17": "Yamanashi",
    "18": "Shizuoka",
    "19": "Gifu",
    "20": "Aichi",
    "21": "Mie",
    "22": "Kyoto",
    "23": "Shiga",
    "24": "Nara",
    "25": "Osaka",
    "26": "Wakayama",
    "27": "Hyogo",
    "28": "Toyama",
    "29": "Fukui",
    "30": "Ishikawa",
    "31": "Okayama",
    "32": "Shimane",
    "33": "Yamaguchi",
    "34": "Tottori",
    "35": "Hiroshima",
    "36": "Kagawa",
    "37": "Tokushima",
    "38": "Ehime",
    "39": "Kochi",
    "40": "Fukuoka",
    "41": "Saga",
    "42": "Nagasaki",
    "43": "Kumamoto",
    "44": "Oita",
    "45": "Miyazaki",
    "46": "Kagoshima",
    "47": "Okinawa",
}

DATE_RE = re.compile(r"([A-Za-z]{3,4}\.\s*\d{1,2},\d{4})")
ENTRY_RE = re.compile(r"^\s*(\*)?\s*(\d{5})\s+(.*)$")


def normalize_line(line: str) -> str:
    # Convert full-width spaces often present in source text.
    return line.replace("\u3000", " ").rstrip("\n")


def normalize_deleted_date(raw_date: str) -> str:
    d = raw_date.strip()
    d = re.sub(r"\s+", "", d)
    if not d:
        return ""
    m = re.match(r"^([A-Za-z]{3,4})(\.\d{1,2},\d{4})$", d)
    if not m:
        return d
    month = m.group(1)
    month = month[0].upper() + month[1:].lower()
    return month + m.group(2)


def parse_entry_line(line: str) -> Dict | None:
    m = ENTRY_RE.match(line)
    if not m:
        return None

    deleted = bool(m.group(1))
    code = m.group(2)
    rest = m.group(3).strip()
    if not rest:
        return None

    date_match = DATE_RE.search(rest)
    deleted_date = normalize_deleted_date(date_match.group(1)) if date_match else ""

    # Remove any quoted date fragment so column split remains stable.
    rest_no_date = DATE_RE.sub("", rest)
    rest_no_date = rest_no_date.replace('"', " ")
    rest_no_date = re.sub(r"\s+", " ", rest_no_date).strip()

    # Split by >=2 spaces first, then fallback to single-space split.
    cols = [c.strip() for c in re.split(r"\s{2,}", m.group(3).strip()) if c.strip()]
    if len(cols) >= 2:
        romaji = cols[0]
        ja_name = cols[1]
    else:
        parts = rest_no_date.split(" ", 1)
        romaji = parts[0].strip() if parts else ""
        ja_name = parts[1].strip() if len(parts) > 1 else ""

    if not romaji:
        return None

    pref_code = code[:2]
    pref_name = PREF_MAP.get(pref_code, pref_code)

    return {
        "code": code,
        "name": romaji,
        "ja_name": ja_name,
        "deleted": deleted,
        "deleted_date": deleted_date if deleted else "",
        "pref_code": pref_code,
        "pref_name": pref_name,
    }


def parse_file(input_path: Path) -> List[Dict]:
    items: Dict[str, Dict] = {}
    with input_path.open("r", encoding="utf-8", errors="replace") as f:
        for raw in f:
            line = normalize_line(raw)
            entry = parse_entry_line(line)
            if not entry:
                continue
            # Last occurrence wins in case source has duplicates.
            items[entry["code"]] = entry

    return [items[k] for k in sorted(items.keys())]


def main() -> int:
    parser = argparse.ArgumentParser(description="Rebuild jcg_parsed.json from jcg-list-ja.txt")
    parser.add_argument("--input", default="temp/jcg-list-ja.txt", help="Input JCG JA text file")
    parser.add_argument("--output", default="temp/jcg_parsed.json", help="Output JSON path")
    args = parser.parse_args()

    input_path = Path(args.input)
    output_path = Path(args.output)

    rows = parse_file(input_path)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    with output_path.open("w", encoding="utf-8") as f:
        json.dump(rows, f, ensure_ascii=False, indent=2)
        f.write("\n")

    deleted_count = sum(1 for x in rows if x.get("deleted"))
    print(f"Wrote {len(rows)} rows to {output_path}")
    print(f"Deleted rows: {deleted_count}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
