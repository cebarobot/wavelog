#!/usr/bin/env python3
"""Format deleted_date fields in jcc_list.json and jcg_list.json to yyyy-mm-dd."""

import json
from datetime import datetime

FILES = [
    "assets/json/japan_award/jcc_list.json",
    "assets/json/japan_award/jcg_list.json",
]

# Current format: "Nov.30,1973" -> "1973-11-30"
def format_date(date_str):
    if not date_str:
        return ""
    try:
        # Parse "Mon.DD,YYYY" format
        dt = datetime.strptime(date_str, "%b.%d,%Y")
        return dt.strftime("%Y-%m-%d")
    except ValueError:
        print(f"  WARNING: Could not parse date: '{date_str}'")
        return date_str

for filepath in FILES:
    print(f"Processing {filepath}...")
    with open(filepath, "r", encoding="utf-8") as f:
        data = json.load(f)

    converted = 0
    for code, entry in data.items():
        old = entry.get("deleted_date", "")
        if old:
            new = format_date(old)
            if new != old:
                entry["deleted_date"] = new
                converted += 1

    with open(filepath, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write("\n")

    print(f"  Converted {converted} dates")

print("Done")
