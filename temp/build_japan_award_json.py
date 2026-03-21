#!/usr/bin/env python3
from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TEMP = ROOT / "temp"
OUT_DIR = ROOT / "assets" / "json" / "japan_award"

JCC_MODEL_PATH = ROOT / "application" / "models" / "Jcc_model.php"
JCC_LIST_PATH = TEMP / "jcc-list-utf8.txt"
JCG_LIST_PATH = TEMP / "jcg-list-ja.txt"
JCG_COORDS_PATH = TEMP / "jcg_with_coords.json"
KU_LIST_PATH = TEMP / "ku-list-utf8.txt"

DATE_RE = re.compile(r"([A-Za-z]{3}\.\s*\d{1,2},\d{4}|\d{4}[^\s\"]*年\d+月\d+日以前)")


def parse_jcc_coords() -> dict[str, tuple[float | None, float | None]]:
    content = JCC_MODEL_PATH.read_text(encoding="utf-8")
    pattern = re.compile(
        r"'(?P<code>\d{4}|\d{6})'\s*=>\s*array\(\s*'name'\s*=>\s*'(?P<name>(?:\\'|[^'])*)'\s*,\s*'lat'\s*=>\s*(?P<lat>-?\d+(?:\.\d+)?)\s*,\s*'lon'\s*=>\s*(?P<lon>-?\d+(?:\.\d+)?)\s*\)",
        re.MULTILINE,
    )
    coords: dict[str, tuple[float | None, float | None]] = {}
    for m in pattern.finditer(content):
        code = m.group("code")
        lat = float(m.group("lat"))
        lon = float(m.group("lon"))
        coords[code] = (lat, lon)
    return coords


def parse_pref_headers(lines: list[str]) -> dict[str, str]:
    pref_ja: dict[str, str] = {}
    for line in lines:
        m = re.match(r"^\s*([^\s].*?)\s+(\d{2})\s*$", line)
        if not m:
            continue
        left = m.group(1).strip()
        if "No." in left or "リスト" in left or "現在" in left or "JARL" in left:
            continue
        if re.search(r"\d", left):
            continue
        pref_ja[m.group(2)] = left.replace(" ", "")
    return pref_ja


def parse_jcc_list() -> tuple[dict[str, dict], dict[str, str]]:
    lines = JCC_LIST_PATH.read_text(encoding="utf-8").splitlines()
    pref_ja = parse_pref_headers(lines)

    out: dict[str, dict] = {}
    code_re = re.compile(r"^\s*(?P<mark>\*)?\s*(?P<code>\d{4}|\d{6})\s+(?P<rest>.*)$")

    for line in lines:
        m = code_re.match(line)
        if not m:
            continue

        code = m.group("code")
        deleted = bool(m.group("mark"))
        rest = m.group("rest").rstrip()

        date_match = DATE_RE.search(rest)
        deleted_date = date_match.group(1).replace(" ", "") if date_match else ""
        if deleted and not deleted_date:
            deleted_date = ""

        if date_match:
            core = rest[: date_match.start()].rstrip(' "')
        else:
            core = rest

        parts = re.split(r"\s{2,}", core.strip())
        if len(parts) < 2:
            continue

        name = parts[0].strip()
        ja_name = parts[1].strip()

        out[code] = {
            "name": name,
            "ja_name": ja_name,
            "deleted": deleted,
            "deleted_date": deleted_date,
            "lat": None,
            "lon": None,
        }

    return out, pref_ja


def parse_jcg_list() -> tuple[dict[str, dict], dict[str, str]]:
    lines = JCG_LIST_PATH.read_text(encoding="utf-8").splitlines()
    pref_ja = parse_pref_headers(lines)

    out: dict[str, dict] = {}
    code_re = re.compile(r"^\s*(?P<mark>\*)?\s*(?P<code>\d{5})\s+(?P<rest>.*)$")

    for line in lines:
        m = code_re.match(line)
        if not m:
            continue

        code = m.group("code")
        deleted = bool(m.group("mark"))
        rest = m.group("rest").rstrip()

        date_match = DATE_RE.search(rest)
        deleted_date = date_match.group(1).replace(" ", "") if date_match else ""

        if date_match:
            core = rest[: date_match.start()].rstrip(' "')
        else:
            core = rest

        parts = re.split(r"\s{2,}", core.strip())
        if len(parts) < 2:
            continue

        name = parts[0].strip()
        ja_name = parts[1].strip()

        out[code] = {
            "name": name,
            "ja_name": ja_name,
            "deleted": deleted,
            "deleted_date": deleted_date,
            "lat": None,
            "lon": None,
        }

    return out, pref_ja


def parse_jcg_coords() -> tuple[dict[str, tuple[float | None, float | None]], dict[str, str]]:
    rows = json.loads(JCG_COORDS_PATH.read_text(encoding="utf-8"))
    coords: dict[str, tuple[float | None, float | None]] = {}
    pref_en: dict[str, str] = {}
    for row in rows:
        code = str(row.get("code", ""))
        if not code:
            continue
        lat = row.get("lat")
        lon = row.get("lon")
        coords[code] = (lat, lon)
        pref_code = str(row.get("pref_code", ""))
        pref_name = str(row.get("pref_name", ""))
        if pref_code and pref_name and pref_code not in pref_en:
            pref_en[pref_code] = pref_name
    return coords, pref_en


def parse_ku_list() -> dict[str, dict]:
    lines = KU_LIST_PATH.read_text(encoding="utf-8").splitlines()
    out: dict[str, dict] = {}

    code_re = re.compile(r"^\s*(?P<code>\d{6})\s*(?P<mark>※)?\s*(?P<rest>.*)$")

    for line in lines:
        m = code_re.match(line)
        if not m:
            continue

        code = m.group("code")
        deleted = bool(m.group("mark"))
        rest = m.group("rest").rstrip()

        date_match = DATE_RE.search(rest)
        deleted_date = date_match.group(1).replace(" ", "") if date_match else ""

        if date_match:
            core = rest[: date_match.start()].rstrip(' "')
        else:
            core = rest

        parts = re.split(r"\s{2,}", core.strip())
        if len(parts) < 2:
            continue

        name = parts[0].strip().replace("\u3000", "")
        ja_name = parts[1].strip().replace("\u3000", "")

        out[code] = {
            "name": name,
            "ja_name": ja_name,
            "deleted": deleted,
            "deleted_date": deleted_date,
        }

    return out


def sort_obj(d: dict[str, dict]) -> dict[str, dict]:
    return {k: d[k] for k in sorted(d.keys(), key=lambda x: (len(x), x))}


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)

    jcc, pref_ja_from_jcc = parse_jcc_list()
    jcg, pref_ja_from_jcg = parse_jcg_list()
    ku = parse_ku_list()

    jcc_coords = parse_jcc_coords()
    for code, data in jcc.items():
        lat, lon = jcc_coords.get(code, (None, None))
        data["lat"] = lat
        data["lon"] = lon

    jcg_coords, pref_en_from_jcg = parse_jcg_coords()
    for code, data in jcg.items():
        lat, lon = jcg_coords.get(code, (None, None))
        data["lat"] = lat
        data["lon"] = lon

    pref_ja: dict[str, str] = {}
    pref_ja.update(pref_ja_from_jcc)
    pref_ja.update(pref_ja_from_jcg)

    pref_list: dict[str, dict] = {}
    for code in sorted({*pref_ja.keys(), *pref_en_from_jcg.keys()}):
        pref_list[code] = {
            "name": pref_en_from_jcg.get(code, ""),
            "ja_name": pref_ja.get(code, ""),
        }

    (OUT_DIR / "pref_list.json").write_text(
        json.dumps(sort_obj(pref_list), ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (OUT_DIR / "jcc_list.json").write_text(
        json.dumps(sort_obj(jcc), ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (OUT_DIR / "jcg_list.json").write_text(
        json.dumps(sort_obj(jcg), ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (OUT_DIR / "ku_list.json").write_text(
        json.dumps(sort_obj(ku), ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    missing_jcc_coords = sorted([c for c, v in jcc.items() if v["lat"] is None or v["lon"] is None])
    missing_jcg_coords = sorted([c for c, v in jcg.items() if v["lat"] is None or v["lon"] is None])

    report = {
        "pref_count": len(pref_list),
        "jcc_count": len(jcc),
        "jcg_count": len(jcg),
        "ku_count": len(ku),
        "missing_jcc_coords_count": len(missing_jcc_coords),
        "missing_jcg_coords_count": len(missing_jcg_coords),
        "missing_jcc_coords": missing_jcc_coords,
        "missing_jcg_coords": missing_jcg_coords,
    }

    (TEMP / "japan_award_build_report.json").write_text(
        json.dumps(report, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )


if __name__ == "__main__":
    main()
