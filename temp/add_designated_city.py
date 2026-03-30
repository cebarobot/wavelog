#!/usr/bin/env python3
"""Add designated_city and designated_city_date fields to jcc_list.json."""

import json

# Designated cities mapping: JCC code -> (ja_name, designated_city_date in yyyy-mm-dd)
# Reference: task.md
DESIGNATED_CITIES = {
    "1001": ("東京23区", "1947-05-03"),     # 昭和22年5月3日
    "1101": ("横浜", "1956-09-01"),          # 昭和31年9月1日
    "2001": ("名古屋", "1956-09-01"),        # 昭和31年9月1日
    "2201": ("京都", "1956-09-01"),          # 昭和31年9月1日
    "2501": ("大阪", "1956-09-01"),          # 昭和31年9月1日
    "2701": ("神戸", "1956-09-01"),          # 昭和31年9月1日
    "4021": ("北九州", "1963-04-01"),        # 昭和38年4月1日
    "0101": ("札幌", "1972-04-01"),          # 昭和47年4月1日
    "1103": ("川崎", "1972-04-01"),          # 昭和47年4月1日
    "4001": ("福岡", "1972-04-01"),          # 昭和47年4月1日
    "3501": ("広島", "1980-04-01"),          # 昭和55年4月1日
    "0601": ("仙台", "1989-04-01"),          # 平成元年4月1日
    "1201": ("千葉", "1992-04-01"),          # 平成4年4月1日
    "1344": ("さいたま", "2003-04-01"),      # 平成15年4月1日
    "1801": ("静岡", "2005-04-01"),          # 平成17年4月1日
    "2502": ("堺", "2006-04-01"),            # 平成18年4月1日
    "0801": ("新潟", "2007-04-01"),          # 平成19年4月1日
    "1802": ("浜松", "2007-04-01"),          # 平成19年4月1日
    "3101": ("岡山", "2009-04-01"),          # 平成21年4月1日
    "1110": ("相模原", "2010-04-01"),        # 平成22年4月1日
    "4301": ("熊本", "2012-04-01"),          # 平成24年4月1日
}

JCC_FILE = "assets/json/japan_award/jcc_list.json"

with open(JCC_FILE, "r", encoding="utf-8") as f:
    data = json.load(f)

matched = 0
for code, entry in data.items():
    if code in DESIGNATED_CITIES:
        ref_name, date = DESIGNATED_CITIES[code]
        # Verify ja_name matches
        if ref_name not in entry.get("ja_name", ""):
            print(f"WARNING: Code {code} ja_name mismatch: expected '{ref_name}', got '{entry.get('ja_name', '')}'")
        entry["designated_city"] = True
        entry["designated_city_date"] = date
        matched += 1
    else:
        entry["designated_city"] = False
        entry["designated_city_date"] = ""

print(f"Matched {matched}/{len(DESIGNATED_CITIES)} designated cities")

# Check for any unmatched
for code in DESIGNATED_CITIES:
    if code not in data:
        print(f"WARNING: Code {code} not found in jcc_list.json")

with open(JCC_FILE, "w", encoding="utf-8") as f:
    json.dump(data, f, ensure_ascii=False, indent=2)
    f.write("\n")

print("Done: jcc_list.json updated with designated_city fields")
