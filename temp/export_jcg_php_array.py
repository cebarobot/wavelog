#!/usr/bin/env python3
"""Export PHP JCG array from jcg_with_coords.json.

Input:
  temp/jcg_with_coords.json

Output:
  temp/jcg_php_array.txt

Usage:
  python3 temp/export_jcg_php_array.py
  python3 temp/export_jcg_php_array.py --input temp/jcg_with_coords.json --output temp/jcg_php_array.txt
"""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Dict, List


def load_rows(path: Path) -> List[Dict]:
    with path.open("r", encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        raise ValueError(f"Expected top-level JSON array in {path}")
    return data


def to_php_bool(value: object) -> str:
    return "true" if bool(value) else "false"


def to_php_float_or_null(value: object) -> str:
    if value is None:
        return "null"
    if isinstance(value, (int, float)):
        return f"{float(value):.6f}"
    return "null"


def esc_php_single_quoted(text: str) -> str:
    return text.replace("\\", "\\\\").replace("'", "\\'")


def build_php_array(rows: List[Dict]) -> str:
    lines: List[str] = []
    lines.append("$jaGuns = array(")

    for row in rows:
        code = str(row.get("code", "")).strip()
        if not code:
            continue

        name = esc_php_single_quoted(str(row.get("name", "")).strip())
        lat_txt = to_php_float_or_null(row.get("lat"))
        lon_txt = to_php_float_or_null(row.get("lon"))
        deleted_txt = to_php_bool(row.get("deleted", False))

        lines.append(
            f"    '{code}' => array('name' => '{name}', 'lat' => {lat_txt}, 'lon' => {lon_txt}, 'deleted' => {deleted_txt}),"
        )

    lines.append(");")
    lines.append("")
    return "\n".join(lines)


def main() -> int:
    parser = argparse.ArgumentParser(description="Export temp/jcg_php_array.txt from temp/jcg_with_coords.json")
    parser.add_argument("--input", default="temp/jcg_with_coords.json", help="Input JSON path")
    parser.add_argument("--output", default="temp/jcg_php_array.txt", help="Output PHP array text path")
    args = parser.parse_args()

    in_path = Path(args.input)
    out_path = Path(args.output)

    rows = load_rows(in_path)
    content = build_php_array(rows)

    out_path.parent.mkdir(parents=True, exist_ok=True)
    with out_path.open("w", encoding="utf-8") as f:
        f.write(content)

    print(f"Loaded rows: {len(rows)}")
    print(f"Wrote PHP array: {out_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
