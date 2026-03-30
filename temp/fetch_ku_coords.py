#!/usr/bin/env python3
"""Fetch coordinates for ku_list.json entries from Wikidata."""

import json
import requests
import time
import unicodedata

WIKIDATA_SPARQL = "https://query.wikidata.org/sparql"

# SPARQL query: get all wards (Q137773) of designated cities (Q1749269)
# with their coordinates and Japanese/romanized names
SPARQL_QUERY = """
SELECT ?city ?cityLabel ?ward ?wardLabel ?wardRomanized ?coord WHERE {
  ?city wdt:P31 wd:Q1749269 .
  ?ward wdt:P31 wd:Q137773 .
  ?ward wdt:P131 ?city .
  OPTIONAL { ?ward wdt:P625 ?coord . }
  OPTIONAL { ?ward wdt:P2440 ?wardRomanized . }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "ja,en" . }
}
"""

# Special cases from task.md that may not appear in the SPARQL query
SPECIAL_CASES = {
    # ku code -> wikidata QID
    "402108": "Q3276115",  # 八幡区
    "402109": "Q516373",   # 小倉市
}

# Mapping: JCC city code (4 digits) -> Japanese city name
CITY_CODE_TO_JA = {
    "0101": "札幌",
    "0601": "仙台",
    "0801": "新潟",
    "1101": "横浜",
    "1103": "川崎",
    "1110": "相模原",
    "1201": "千葉",
    "1344": "さいたま",
    "1801": "静岡",
    "1802": "浜松",
    "2001": "名古屋",
    "2201": "京都",
    "2501": "大阪",
    "2502": "堺",
    "2701": "神戸",
    "3101": "岡山",
    "3501": "広島",
    "4001": "福岡",
    "4021": "北九州",
    "4301": "熊本",
}

# Reverse mapping
JA_TO_CITY_CODE = {v: k for k, v in CITY_CODE_TO_JA.items()}


def remove_diacritics(text):
    """Remove diacritical marks (macrons etc) from romanized text."""
    nfkd = unicodedata.normalize('NFKD', text)
    return ''.join(c for c in nfkd if not unicodedata.combining(c))


def parse_coord(coord_str):
    """Parse wikidata coordinate string like 'Point(141.35 43.06)' -> (lat, lon)."""
    # Format: Point(longitude latitude)
    coord_str = coord_str.replace("Point(", "").replace(")", "")
    parts = coord_str.split()
    lon = float(parts[0])
    lat = float(parts[1])
    return lat, lon


def fetch_sparql(query):
    """Execute SPARQL query against Wikidata."""
    headers = {
        "Accept": "application/sparql-results+json",
        "User-Agent": "WavelogBot/1.0 (amateur radio logging)"
    }
    resp = requests.get(WIKIDATA_SPARQL, params={"query": query}, headers=headers, timeout=60)
    resp.raise_for_status()
    return resp.json()


def fetch_entity_coords(qid):
    """Fetch coordinates for a specific Wikidata entity by QID."""
    query = f"""
    SELECT ?coord WHERE {{
      wd:{qid} wdt:P625 ?coord .
    }}
    """
    result = fetch_sparql(query)
    bindings = result["results"]["bindings"]
    if bindings and "coord" in bindings[0]:
        return parse_coord(bindings[0]["coord"]["value"])
    return None


def main():
    # Load ku_list.json
    with open("assets/json/japan_award/ku_list.json", "r", encoding="utf-8") as f:
        ku_data = json.load(f)

    print(f"Total ku entries: {len(ku_data)}")

    # Build lookup: (city_code, ja_name) -> ku_code
    ku_lookup = {}
    for code, entry in ku_data.items():
        city_code = code[:4]
        ja_name = entry["ja_name"]
        ku_lookup[(city_code, ja_name)] = code

    # Query wikidata for all wards
    print("Querying Wikidata for wards of designated cities...")
    result = fetch_sparql(SPARQL_QUERY)
    bindings = result["results"]["bindings"]
    print(f"Got {len(bindings)} results from Wikidata")

    # Save raw results for debugging
    with open("temp/wikidata_wards_raw.json", "w", encoding="utf-8") as f:
        json.dump(bindings, f, ensure_ascii=False, indent=2)

    # Process results
    matched = 0
    unmatched_wd = []

    for binding in bindings:
        city_label = binding["cityLabel"]["value"]
        ward_label = binding["wardLabel"]["value"]
        coord_str = binding.get("coord", {}).get("value", "")

        if not coord_str:
            continue

        lat, lon = parse_coord(coord_str)

        # Extract city name (remove 市 suffix)
        city_name = city_label.replace("市", "")

        # Extract ward name (remove 区 suffix)
        ward_name = ward_label.replace("区", "")

        # Try to find matching city code
        city_code = JA_TO_CITY_CODE.get(city_name)
        if not city_code:
            # Try with 市
            city_code = JA_TO_CITY_CODE.get(city_label)
        if not city_code:
            unmatched_wd.append(f"City not found: {city_label} ({ward_label})")
            continue

        # Try to match ward
        key = (city_code, ward_name)
        if key in ku_lookup:
            ku_code = ku_lookup[key]
            ku_data[ku_code]["lat"] = lat
            ku_data[ku_code]["lon"] = lon
            matched += 1
        else:
            unmatched_wd.append(f"Ward not found: {city_label}/{ward_label} (city_code={city_code}, ward_name={ward_name})")

    print(f"Matched {matched} wards from Wikidata SPARQL query")
    if unmatched_wd:
        print("Unmatched from Wikidata:")
        for msg in unmatched_wd:
            print(f"  {msg}")

    # Handle special cases
    print("\nHandling special cases...")
    for ku_code, qid in SPECIAL_CASES.items():
        if ku_code in ku_data:
            if "lat" not in ku_data[ku_code] or ku_data[ku_code].get("lat") is None:
                print(f"Fetching coords for {ku_code} ({ku_data[ku_code]['ja_name']}) from {qid}...")
                time.sleep(1)
                coords = fetch_entity_coords(qid)
                if coords:
                    ku_data[ku_code]["lat"] = coords[0]
                    ku_data[ku_code]["lon"] = coords[1]
                    matched += 1
                    print(f"  -> {coords}")
                else:
                    print(f"  -> No coordinates found!")

    # Check what's still missing
    missing = []
    for code, entry in ku_data.items():
        if "lat" not in entry or entry.get("lat") is None:
            missing.append(f"{code}: {entry['ja_name']} ({entry['name']})")

    if missing:
        print(f"\nStill missing coordinates for {len(missing)} entries:")
        for msg in missing:
            print(f"  {msg}")
    else:
        print(f"\nAll {len(ku_data)} entries have coordinates!")

    # Write back
    with open("assets/json/japan_award/ku_list.json", "w", encoding="utf-8") as f:
        json.dump(ku_data, f, ensure_ascii=False, indent=2)
        f.write("\n")

    print(f"\nDone: ku_list.json updated with {matched} coordinate entries")


if __name__ == "__main__":
    main()
