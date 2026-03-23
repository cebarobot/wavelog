#!/usr/bin/env python3
"""Build JCG dataset from JARL text and enrich coordinates from Wikidata.

Input:
  temp/jcg-list.txt

Outputs:
  temp/jcg_parsed.json
  temp/jcg_with_coords.json
  temp/jcg_missing_coords.json
  temp/jcg_php_array.txt
"""

from __future__ import annotations

import json
import os
import re
import sys
import time
import urllib.parse
import urllib.request
import unicodedata
from dataclasses import dataclass
from typing import Dict, List, Optional, Tuple

INPUT_PATH = "temp/jcg-list.txt"
PARSED_JSON = "temp/jcg_parsed.json"
WITH_COORDS_JSON = "temp/jcg_with_coords.json"
MISSING_JSON = "temp/jcg_missing_coords.json"
PHP_ARRAY_TXT = "temp/jcg_php_array.txt"
WIKIDATA_DISTRICTS_JSON = "temp/wikidata_japan_districts.json"
WIKIDATA_PREFECTURES_JSON = "temp/wikidata_japan_prefectures.json"

UA = "Wavelog-JCG-Builder/1.0 (https://github.com/wavelog/wavelog)"

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


@dataclass
class JcgItem:
    code: str
    name: str
    deleted: bool
    pref_code: str
    pref_name: str


@dataclass
class WdDistrict:
    qid: str
    name: str
    pref_name: str
    pref_qid: str
    lat: Optional[float]
    lon: Optional[float]


@dataclass
class WdPrefecture:
    qid: str
    name: str


def _http_get_json(url: str) -> dict:
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=20) as resp:
        return json.loads(resp.read().decode("utf-8"))


def _http_post_json(url: str, body: str, headers: Optional[Dict[str, str]] = None) -> dict:
    req_headers = {"User-Agent": UA, "Content-Type": "application/x-www-form-urlencoded"}
    if headers:
        req_headers.update(headers)
    data = body.encode("utf-8")
    req = urllib.request.Request(url, data=data, headers=req_headers, method="POST")
    with urllib.request.urlopen(req, timeout=40) as resp:
        return json.loads(resp.read().decode("utf-8"))


def parse_jcg_text(path: str) -> List[JcgItem]:
    with open(path, "r", encoding="utf-8", errors="replace") as f:
        lines = [ln.rstrip("\n") for ln in f]

    items: List[JcgItem] = []
    current_pref_code = ""
    current_pref_name = ""

    pref_re = re.compile(r"^([A-Z][A-Z ]*[A-Z])\s+(\d{2})\s*$")
    item_re = re.compile(r'^\s*(\*)?\s*(\d{5})\s+(.+?)(?:\s+"[^"]*")?\s*$')

    for raw in lines:
        line = raw.strip()
        if not line:
            continue

        pm = pref_re.match(line)
        if pm:
            pref_code = pm.group(2)
            current_pref_code = pref_code
            current_pref_name = PREF_MAP.get(pref_code, pm.group(1).title())
            continue

        if line.startswith("JCG Number List") or line.startswith("Note"):
            continue

        m = item_re.match(raw)
        if not m:
            continue

        deleted = bool(m.group(1))
        code = m.group(2)
        name = m.group(3).strip()
        # Some lines in the source contain trailing date notes with malformed quotes.
        # Remove any trailing quoted note fragment, e.g. "Dec.31,2005 or "Sep.30,2005".
        name = re.sub(r'\s+"[^\"]*"?\s*$', '', name)
        # Normalize spacing inside names like "Naka  "
        name = re.sub(r"\s+", " ", name)

        # Safety fallback: derive prefecture from code if heading was not seen.
        pref_code = current_pref_code or code[:2]
        pref_name = current_pref_name or PREF_MAP.get(pref_code, pref_code)

        items.append(
            JcgItem(
                code=code,
                name=name,
                deleted=deleted,
                pref_code=pref_code,
                pref_name=pref_name,
            )
        )

    # De-dup if source has repeated records.
    unique: Dict[str, JcgItem] = {}
    for it in items:
        unique[it.code] = it

    return [unique[k] for k in sorted(unique.keys())]


def normalize_name(text: str) -> str:
    s = text.lower().strip()
    s = unicodedata.normalize("NFKD", s)
    s = "".join(ch for ch in s if not unicodedata.combining(ch))
    s = re.sub(r"\bdistrict\b", "", s)
    s = re.sub(r"\bgun\b", "", s)
    s = re.sub(r"\(.*?\)", "", s)
    s = s.replace("'", "")
    s = re.sub(r"[^a-z0-9]+", "", s)
    return s


def normalize_pref(text: str) -> str:
    s = text.lower().strip()
    s = unicodedata.normalize("NFKD", s)
    s = "".join(ch for ch in s if not unicodedata.combining(ch))
    s = s.replace(" prefecture", "")
    s = s.replace("metropolis", "")
    s = re.sub(r"[^a-z0-9]+", "", s)
    return s


def parse_point_wkt(point: str) -> Tuple[Optional[float], Optional[float]]:
    # WKT format from SPARQL: "Point(lon lat)"
    m = re.match(r"^Point\((-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\)$", point)
    if not m:
        return None, None
    lon = float(m.group(1))
    lat = float(m.group(2))
    return lat, lon


def fetch_wikidata_district_catalog() -> List[WdDistrict]:
    raise NotImplementedError("Use fetch_wikidata_district_catalog(pref_qids) with prefecture mapping")


def fetch_wikidata_prefecture_catalog() -> List[WdPrefecture]:
    sparql = """
SELECT ?item ?itemLabel WHERE {
  ?item wdt:P31 wd:Q50337 .
  ?item wdt:P17 wd:Q17 .
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en,ja". }
}
""".strip()

    data = _http_post_json(
        "https://query.wikidata.org/sparql",
        urllib.parse.urlencode({"query": sparql, "format": "json"}),
        headers={"Accept": "application/sparql-results+json"},
    )

    rows = data.get("results", {}).get("bindings", [])
    out: List[WdPrefecture] = []
    for b in rows:
        item_uri = b.get("item", {}).get("value", "")
        if not item_uri:
            continue
        out.append(
            WdPrefecture(
                qid=item_uri.rsplit("/", 1)[-1],
                name=b.get("itemLabel", {}).get("value", "").strip(),
            )
        )
    return out


def fetch_wikidata_district_catalog_with_prefs(pref_qids: List[str]) -> List[WdDistrict]:
    pref_values = " ".join(f"wd:{qid}" for qid in sorted(set(pref_qids)))
    sparql = """
SELECT DISTINCT ?item ?itemLabel ?coord ?pref ?prefLabel WHERE {
  VALUES ?inst { wd:Q1122846 wd:Q46426234 }
  VALUES ?pref { __PREF_VALUES__ }
  ?item wdt:P31 ?inst .
  ?item wdt:P17 wd:Q17 .
  ?item wdt:P131* ?pref .
  OPTIONAL { ?item wdt:P625 ?coord . }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en,ja". }
}
""".strip().replace("__PREF_VALUES__", pref_values)

    data = _http_post_json(
        "https://query.wikidata.org/sparql",
        urllib.parse.urlencode({"query": sparql, "format": "json"}),
        headers={"Accept": "application/sparql-results+json"},
    )

    rows = data.get("results", {}).get("bindings", [])
    out_raw: List[WdDistrict] = []

    for b in rows:
        item_uri = b.get("item", {}).get("value", "")
        if not item_uri:
            continue
        qid = item_uri.rsplit("/", 1)[-1]
        name = b.get("itemLabel", {}).get("value", "").strip()
        pref = b.get("prefLabel", {}).get("value", "").strip()
        pref_uri = b.get("pref", {}).get("value", "")
        pref_qid = pref_uri.rsplit("/", 1)[-1] if pref_uri else ""

        lat: Optional[float] = None
        lon: Optional[float] = None
        coord = b.get("coord", {}).get("value")
        if coord:
            lat, lon = parse_point_wkt(coord)

        out_raw.append(WdDistrict(qid=qid, name=name, pref_name=pref, pref_qid=pref_qid, lat=lat, lon=lon))

    # P131* may yield duplicate rows for the same district/prefecture pair via multiple paths.
    # Keep one row per (qid, pref_qid), preferring the row that has coordinates.
    dedup: Dict[Tuple[str, str], WdDistrict] = {}
    for row in out_raw:
        key = (row.qid, row.pref_qid)
        if key not in dedup:
            dedup[key] = row
            continue

        current = dedup[key]
        current_has_coord = current.lat is not None and current.lon is not None
        row_has_coord = row.lat is not None and row.lon is not None

        if (not current_has_coord) and row_has_coord:
            dedup[key] = row

    return list(dedup.values())


def match_coord_from_catalog(item: JcgItem, index: Dict[str, List[WdDistrict]], pref_code_to_qid: Dict[str, str]) -> Tuple[Optional[float], Optional[float], Optional[str], str, str]:
    """Return lat, lon, qid, query_used, error_detail."""
    if "(" in item.name and ")" in item.name:
        return None, None, None, "local_catalog", "skip_parenthetical_hokkaido_special"

    key = normalize_name(item.name)
    target_pref_qid = pref_code_to_qid.get(item.pref_code, "")
    pref_key = normalize_pref(item.pref_name)
    candidates = index.get(key, [])

    if not candidates:
        return None, None, None, "local_catalog", "no_name_match_in_catalog"

    by_pref_qid = [c for c in candidates if target_pref_qid and c.pref_qid == target_pref_qid]
    by_pref_name = [c for c in candidates if normalize_pref(c.pref_name) == pref_key]
    by_pref = by_pref_qid if by_pref_qid else by_pref_name
    chosen_pool = by_pref if by_pref else candidates

    with_coord = [c for c in chosen_pool if c.lat is not None and c.lon is not None]
    if len(with_coord) == 1:
        c = with_coord[0]
        return c.lat, c.lon, c.qid, "local_catalog", ""

    if len(with_coord) > 1:
        pref_hit = "pref_matched" if by_pref else "pref_not_matched"
        return None, None, None, "local_catalog", f"ambiguous_multiple_candidates:{len(with_coord)}:{pref_hit}"

    # name matched but no coordinate in chosen candidates
    return None, None, None, "local_catalog", "name_matched_but_no_coord"


def to_dict(item: JcgItem) -> dict:
    return {
        "code": item.code,
        "name": item.name,
        "deleted": item.deleted,
        "pref_code": item.pref_code,
        "pref_name": item.pref_name,
    }


def load_cached_pref_mapping(path: str) -> Dict[str, str]:
    data = json.load(open(path, "r", encoding="utf-8"))
    mapping_rows = data.get("jcg_pref_mapping", [])
    mapping: Dict[str, str] = {}
    for row in mapping_rows:
        code = str(row.get("pref_code", "")).zfill(2)
        qid = row.get("wikidata_qid", "") or ""
        if code:
            mapping[code] = qid
    return mapping


def load_cached_district_catalog(path: str) -> List[WdDistrict]:
    data = json.load(open(path, "r", encoding="utf-8"))
    out: List[WdDistrict] = []
    for d in data:
        out.append(
            WdDistrict(
                qid=d.get("qid", ""),
                name=d.get("name", ""),
                pref_name=d.get("pref_name", ""),
                pref_qid=d.get("pref_qid", ""),
                lat=d.get("lat"),
                lon=d.get("lon"),
            )
        )
    return out


def main() -> None:
    items = parse_jcg_text(INPUT_PATH)

    # Offline-first mode: if both local cache files exist, do not access Wikidata.
    use_local_cache = os.path.exists(WIKIDATA_PREFECTURES_JSON) and os.path.exists(WIKIDATA_DISTRICTS_JSON)

    if use_local_cache:
        pref_code_to_qid = load_cached_pref_mapping(WIKIDATA_PREFECTURES_JSON)
        catalog = load_cached_district_catalog(WIKIDATA_DISTRICTS_JSON)
        print("using_local_wikidata_cache: true")
    else:
        prefectures = fetch_wikidata_prefecture_catalog()
        pref_name_to_qid: Dict[str, str] = {}
        for p in prefectures:
            pref_name_to_qid[normalize_pref(p.name)] = p.qid

        pref_code_to_qid: Dict[str, str] = {}
        pref_mapping_rows: List[dict] = []
        for code, jcg_name in PREF_MAP.items():
            qid = pref_name_to_qid.get(normalize_pref(jcg_name), "")
            pref_code_to_qid[code] = qid
            pref_mapping_rows.append(
                {
                    "pref_code": code,
                    "jcg_pref_name": jcg_name,
                    "wikidata_qid": qid,
                    "matched": bool(qid),
                }
            )

        with open(WIKIDATA_PREFECTURES_JSON, "w", encoding="utf-8") as f:
            json.dump(
                {
                    "prefectures_from_wikidata": [
                        {"qid": p.qid, "name": p.name} for p in prefectures
                    ],
                    "jcg_pref_mapping": pref_mapping_rows,
                },
                f,
                ensure_ascii=False,
                indent=2,
            )

        # Fetch all candidate Japanese districts once, then match locally.
        catalog = fetch_wikidata_district_catalog_with_prefs([q for q in pref_code_to_qid.values() if q])
        with open(WIKIDATA_DISTRICTS_JSON, "w", encoding="utf-8") as f:
            json.dump(
                [
                    {
                        "qid": d.qid,
                        "name": d.name,
                        "pref_name": d.pref_name,
                        "pref_qid": d.pref_qid,
                        "lat": d.lat,
                        "lon": d.lon,
                    }
                    for d in catalog
                ],
                f,
                ensure_ascii=False,
                indent=2,
            )
        print("using_local_wikidata_cache: false")

    catalog_index: Dict[str, List[WdDistrict]] = {}
    for row in catalog:
        key = normalize_name(row.name)
        if not key:
            continue
        catalog_index.setdefault(key, []).append(row)

    with open(PARSED_JSON, "w", encoding="utf-8") as f:
        json.dump([to_dict(x) for x in items], f, ensure_ascii=False, indent=2)

    with_coords: List[dict] = []
    missing: List[dict] = []
    found_count = 0
    missing_count = 0
    name_no_coord_deleted = 0
    name_no_coord_active = 0
    reason_counts: Dict[str, int] = {}

    for idx, item in enumerate(items, start=1):
        lat, lon, qid, query_used, error_detail = match_coord_from_catalog(item, catalog_index, pref_code_to_qid)

        # Split generic no-coordinate reason into deleted/active-specific reasons.
        if error_detail == "name_matched_but_no_coord":
            if item.deleted:
                error_detail = "name_matched_but_no_coord_deleted"
            else:
                error_detail = "name_matched_but_no_coord_active"

        row = to_dict(item)
        row["lat"] = lat
        row["lon"] = lon
        row["wikidata_id"] = qid
        row["query_used"] = query_used
        row["error"] = error_detail

        if lat is None or lon is None:
            missing.append(row)
            missing_count += 1
            reason_counts[error_detail] = reason_counts.get(error_detail, 0) + 1
            if error_detail == "name_matched_but_no_coord_deleted":
                name_no_coord_deleted += 1
            elif error_detail == "name_matched_but_no_coord_active":
                name_no_coord_active += 1
            # Report each failure immediately but continue processing the full list.
            print(
                f"[MISSING_COORD] code={item.code} name={item.name} pref={item.pref_name} reason={error_detail}",
                file=sys.stderr,
                flush=True,
            )
        else:
            found_count += 1

        with_coords.append(row)

        if idx % 25 == 0:
            print(f"processed {idx}/{len(items)} found={found_count} missing={missing_count}")

    with open(WITH_COORDS_JSON, "w", encoding="utf-8") as f:
        json.dump(with_coords, f, ensure_ascii=False, indent=2)

    with open(MISSING_JSON, "w", encoding="utf-8") as f:
        json.dump(missing, f, ensure_ascii=False, indent=2)

    # Build a PHP array draft, preserving all items.
    lines: List[str] = []
    lines.append("$jaGuns = array(")
    for row in with_coords:
        code = row["code"]
        name = row["name"].replace("'", "\\'")
        lat = row["lat"]
        lon = row["lon"]
        deleted = "true" if row["deleted"] else "false"

        lat_txt = "null" if lat is None else f"{lat:.6f}"
        lon_txt = "null" if lon is None else f"{lon:.6f}"

        lines.append(
            f"    '{code}' => array('name' => '{name}', 'lat' => {lat_txt}, 'lon' => {lon_txt}, 'deleted' => {deleted}),"
        )
    lines.append(");")

    with open(PHP_ARRAY_TXT, "w", encoding="utf-8") as f:
        f.write("\n".join(lines) + "\n")

    print("done")
    print(f"total: {len(items)}")
    print(f"wikidata_catalog_rows: {len(catalog)}")
    print(f"with coords: {found_count}")
    print(f"missing coords: {missing_count}")
    print(f"name_matched_but_no_coord_deleted: {name_no_coord_deleted}")
    print(f"name_matched_but_no_coord_active: {name_no_coord_active}")
    print("reason_counts:")
    for reason, count in sorted(reason_counts.items(), key=lambda x: (-x[1], x[0])):
        print(f"  {reason}: {count}")


if __name__ == "__main__":
    main()
