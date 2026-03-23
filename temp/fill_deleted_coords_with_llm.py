#!/usr/bin/env python3
"""Fill deleted JCG district coordinates with LLM + Japanese Wikipedia workflow.

Workflow per target district:
  1) Fetch Japanese Wikipedia district article (e.g. 北会津郡)
  2) Send article text to LLM to extract municipalities at dissolution time
  3) Find those municipality links in district article
  4) Visit municipality pages and extract Wikidata QIDs
  5) Fetch QID coordinates (P625), average them, and update JSON

Targets:
  - lat/lon missing
  - error == "name_matched_but_no_coord_deleted"

Environment variables (OpenAI-compatible API):
  - LLM_API_KEY (required)
  - LLM_MODEL (default: gpt-4.1-mini)
  - LLM_API_URL (default: https://api.openai.com/v1/chat/completions)

Usage examples:
  proxychains -q python3 temp/fill_deleted_coords_with_llm.py --code 07008
  proxychains -q python3 temp/fill_deleted_coords_with_llm.py --limit 20
  proxychains -q python3 temp/fill_deleted_coords_with_llm.py --dry-run --code 07008
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
import urllib.parse
import urllib.request
from html import unescape
from html.parser import HTMLParser
from pathlib import Path
from typing import Dict, List, Optional, Tuple

DATA_PATH = Path("temp/jcg_with_coords.json")
TARGET_ERROR = "name_matched_but_no_coord_deleted"
UA = "Wavelog-JCG-LLMFill/1.0 (https://github.com/wavelog/wavelog)"


def http_get(url: str) -> str:
    print(f"[HTTP] GET {url}")
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=40) as resp:
        return resp.read().decode("utf-8", errors="replace")


def http_get_json(url: str) -> Dict:
    return json.loads(http_get(url))


def http_post_json(url: str, body: Dict, headers: Optional[Dict[str, str]] = None) -> Dict:
    req_headers = {"User-Agent": UA, "Content-Type": "application/json"}
    if headers:
        req_headers.update(headers)
    data = json.dumps(body).encode("utf-8")
    req = urllib.request.Request(url, data=data, headers=req_headers, method="POST")
    with urllib.request.urlopen(req, timeout=80) as resp:
        payload = resp.read().decode("utf-8", errors="replace")
    return json.loads(payload)


def load_items(path: Path) -> List[Dict]:
    with path.open("r", encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        raise ValueError(f"Expected list in {path}")
    return data


def save_items(path: Path, items: List[Dict]) -> None:
    tmp = path.with_suffix(path.suffix + ".tmp")
    with tmp.open("w", encoding="utf-8") as f:
        json.dump(items, f, ensure_ascii=False, indent=2)
        f.write("\n")
    tmp.replace(path)


def parse_point_from_wikidata_entity(qid: str) -> List[Tuple[float, float]]:
    url = f"https://www.wikidata.org/wiki/Special:EntityData/{qid}.json"
    doc = http_get_json(url)
    ent = doc.get("entities", {}).get(qid, {})
    claims = ent.get("claims", {})
    p625 = claims.get("P625", [])
    out: List[Tuple[float, float]] = []
    for idx, claim in enumerate(p625, start=1):
        main = claim.get("mainsnak", {}) if isinstance(claim, dict) else {}
        value = main.get("datavalue", {}).get("value", {}) if isinstance(main, dict) else {}
        lat = value.get("latitude")
        lon = value.get("longitude")
        if isinstance(lat, (int, float)) and isinstance(lon, (int, float)):
            out.append((float(lat), float(lon)))
            print(f"[WIKIDATA] {qid} P625#{idx}: lat={lat:.9f}, lon={lon:.9f}")
    print(f"[WIKIDATA] {qid} coordinates_found={len(out)}")
    return out


class WikiHtmlParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.text_parts: List[str] = []
        self.links: List[Tuple[str, str]] = []
        self._current_href: Optional[str] = None
        self._current_link_text: List[str] = []

    def handle_starttag(self, tag: str, attrs: List[Tuple[str, Optional[str]]]) -> None:
        if tag == "a":
            attrs_map = dict(attrs)
            self._current_href = attrs_map.get("href")
            self._current_link_text = []

    def handle_endtag(self, tag: str) -> None:
        if tag == "a":
            if self._current_href:
                txt = "".join(self._current_link_text).strip()
                if txt:
                    self.links.append((txt, self._current_href))
            self._current_href = None
            self._current_link_text = []

    def handle_data(self, data: str) -> None:
        if data:
            self.text_parts.append(data)
            if self._current_href is not None:
                self._current_link_text.append(data)

    def combined_text(self) -> str:
        raw = "\n".join(self.text_parts)
        raw = unescape(raw)
        raw = re.sub(r"\n+", "\n", raw)
        raw = re.sub(r"[ \t]+", " ", raw)
        return raw.strip()


def fetch_wikipedia_parse(page_title: str) -> Tuple[str, str, List[Tuple[str, str]]]:
    q = urllib.parse.urlencode(
        {
            "action": "parse",
            "format": "json",
            "formatversion": "2",
            "prop": "text",
            "page": page_title,
        }
    )
    url = f"https://ja.wikipedia.org/w/api.php?{q}"
    doc = http_get_json(url)
    parse = doc.get("parse", {})
    html = parse.get("text", "")
    if not html:
        raise RuntimeError(f"Wikipedia parse text empty for page: {page_title}")

    parser = WikiHtmlParser()
    parser.feed(html)
    article_text = parser.combined_text()
    return html, article_text, parser.links


def normalize_title_for_district(ja_name: str) -> str:
    n = (ja_name or "").strip()
    if not n:
        return n
    if n.endswith("郡"):
        return n
    return n + "郡"


def parse_llm_json(text: str) -> Dict:
    text = text.strip()
    if not text:
        raise ValueError("LLM empty response")
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        m = re.search(r"\{.*\}", text, flags=re.S)
        if not m:
            raise
        return json.loads(m.group(0))


def extract_municipalities_with_llm(article_text: str, district_title: str, deleted_date: str) -> List[str]:
    api_key = os.getenv("LLM_API_KEY", "").strip() or os.getenv("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise RuntimeError("LLM_API_KEY (or OPENAI_API_KEY) is required")

    api_url = os.getenv("LLM_API_URL", "https://api.openai.com/v1/chat/completions").strip()
    model = os.getenv("LLM_MODEL", "gpt-4.1-mini").strip()

    text_for_llm = article_text
    if len(text_for_llm) > 20000:
        text_for_llm = text_for_llm[:20000]

    system = (
        "You extract Japanese municipalities from Japanese Wikipedia text. "
        "Return strict JSON only."
    )
    user = {
        "task": "Find municipalities (towns/villages/cities) that belonged to the district at the time of dissolution.",
        "district_page_title": district_title,
        "deleted_date_hint": deleted_date,
        "requirements": [
            "Use only evidence from the provided text.",
            "Prefer dissolution timeline statements near abolition event.",
            "Only return the last existing municipalities.",
            "Output JSON with key municipalities as an array of names.",
            "Example: {\"municipalities\":[\"北会津村\"]}",
        ],
        "article_text": text_for_llm,
    }

    payload = {
        "model": model,
        "temperature": 0,
        "messages": [
            {"role": "system", "content": system},
            {"role": "user", "content": json.dumps(user, ensure_ascii=False)},
        ],
    }

    print(f"[LLM] POST {api_url} model={model}")
    resp = http_post_json(api_url, payload, headers={"Authorization": f"Bearer {api_key}"})
    choices = resp.get("choices", [])
    if not choices:
        raise RuntimeError(f"LLM response has no choices: {resp}")
    content = choices[0].get("message", {}).get("content", "")
    print(f"[LLM] raw_response={content[:300].replace(chr(10), ' ')}")
    parsed = parse_llm_json(content)
    muni = parsed.get("municipalities", [])
    if not isinstance(muni, list):
        raise RuntimeError(f"LLM response municipalities is not list: {parsed}")

    out: List[str] = []
    seen = set()
    for name in muni:
        s = str(name).strip()
        if not s:
            continue
        if s in seen:
            continue
        seen.add(s)
        out.append(s)
    return out


def to_page_title_from_href(href: str) -> Optional[str]:
    if not href:
        return None
    if href.startswith("/wiki/"):
        frag = href[len("/wiki/") :]
        if ":" in frag:
            # Skip special namespaces like File:, Help:, etc.
            return None
        return urllib.parse.unquote(frag)
    return None


def find_links_for_municipalities(
    municipalities: List[str],
    links: List[Tuple[str, str]],
) -> Dict[str, str]:
    by_text: Dict[str, str] = {}
    for text, href in links:
        t = text.strip()
        if not t:
            continue
        if t not in by_text:
            by_text[t] = href

    matched: Dict[str, str] = {}
    for name in municipalities:
        if name in by_text:
            matched[name] = by_text[name]
            continue

        # fallback: remove whitespace difference
        slim = re.sub(r"\s+", "", name)
        for text, href in links:
            if re.sub(r"\s+", "", text) == slim:
                matched[name] = href
                break
    return matched


def fetch_municipality_wikidata_qid(page_title: str) -> Optional[str]:
    q = urllib.parse.urlencode(
        {
            "action": "query",
            "prop": "pageprops",
            "titles": page_title,
            "format": "json",
            "formatversion": "2",
            "redirects": "1",
        }
    )
    url = f"https://ja.wikipedia.org/w/api.php?{q}"
    doc = http_get_json(url)

    pages = doc.get("query", {}).get("pages", [])
    if not isinstance(pages, list) or not pages:
        print(f"[WARN] wikipedia pageprops API returned no pages: {page_title}")
        return None

    page = pages[0]
    pageprops = page.get("pageprops", {}) if isinstance(page, dict) else {}
    qid = pageprops.get("wikibase_item") if isinstance(pageprops, dict) else None
    if not qid:
        print(f"[WARN] no wikibase_item found via API for page: {page_title}")
        return None

    qid_str = str(qid).strip()
    if not re.match(r"^Q\d+$", qid_str):
        print(f"[WARN] invalid wikibase_item format for page {page_title}: {qid_str}")
        return None

    print(f"[WIKIPEDIA_API] page={page_title} wikibase_item={qid_str}")
    return qid_str


def has_coord(item: Dict) -> bool:
    return item.get("lat") is not None and item.get("lon") is not None


def avg_coords(coords: List[Tuple[float, float]]) -> Tuple[float, float]:
    lat = sum(x[0] for x in coords) / len(coords)
    lon = sum(x[1] for x in coords) / len(coords)
    return lat, lon


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


def process_one(item: Dict, dry_run: bool, confirm: bool) -> bool:
    code = str(item.get("code", ""))
    ja_name = str(item.get("ja_name", "")).strip()
    deleted_date = str(item.get("deleted_date", "")).strip()
    district_title = normalize_title_for_district(ja_name)

    print("\n" + "=" * 80)
    print(f"[TARGET] code={code} name={item.get('name','')} ja_name={ja_name} deleted_date={deleted_date}")
    print(f"[STEP1] district_page={district_title}")

    _html, article_text, links = fetch_wikipedia_parse(district_title)
    print(f"[STEP1] article_text_chars={len(article_text)} links_found={len(links)}")

    municipalities = extract_municipalities_with_llm(article_text, district_title, deleted_date)
    print(f"[STEP2] llm_municipalities={municipalities}")
    if not municipalities:
        item["query_used"] = "llm_wikipedia_deleted_subunits"
        item["error"] = "llm_no_municipalities"
        print("[RESULT] failed: llm_no_municipalities")
        return False

    matched_links = find_links_for_municipalities(municipalities, links)
    print(f"[STEP3] matched_links={matched_links}")
    if not matched_links:
        item["query_used"] = "llm_wikipedia_deleted_subunits"
        item["error"] = "llm_links_not_found"
        print("[RESULT] failed: llm_links_not_found")
        return False

    qids: List[str] = []
    for muni in municipalities:
        href = matched_links.get(muni)
        if not href:
            print(f"[WARN] municipality link not found: {muni}")
            continue
        page_title = to_page_title_from_href(href)
        if not page_title:
            print(f"[WARN] unsupported municipality href: {href}")
            continue

        print(f"[STEP4] municipality={muni} page={page_title}")
        qid = fetch_municipality_wikidata_qid(page_title)
        if not qid:
            continue
        if qid not in qids:
            qids.append(qid)
            print(f"[STEP4] municipality={muni} qid={qid}")

    if not qids:
        item["query_used"] = "llm_wikipedia_deleted_subunits"
        item["error"] = "municipality_qid_not_found"
        print("[RESULT] failed: municipality_qid_not_found")
        return False

    all_coords: List[Tuple[float, float]] = []
    for qid in qids:
        coords = parse_point_from_wikidata_entity(qid)
        all_coords.extend(coords)

    if not all_coords:
        item["query_used"] = "llm_wikipedia_deleted_subunits"
        item["error"] = "municipality_qid_no_coord"
        print("[RESULT] failed: municipality_qid_no_coord")
        return False

    lat, lon = avg_coords(all_coords)
    print(f"[STEP5] coordinates_points={len(all_coords)} avg_lat={lat:.9f} avg_lon={lon:.9f}")

    if confirm:
        print("[CONFIRM] Proposed update:")
        print(f"[CONFIRM] code={code} district={district_title}")
        print(f"[CONFIRM] municipalities={municipalities}")
        print(f"[CONFIRM] qids={qids}")
        print(f"[CONFIRM] lat={lat:.9f} lon={lon:.9f}")
        if not ask_yes_no_default_yes("Apply this update? [Y/n]: "):
            item["query_used"] = "llm_wikipedia_deleted_subunits"
            item["error"] = "manual_rejected"
            print("[RESULT] skipped by manual confirmation")
            return False

    if dry_run:
        print("[DRYRUN] skip writing current item")
        return True

    item["lat"] = round(lat, 9)
    item["lon"] = round(lon, 9)
    item["wikidata_id"] = "|".join(qids)
    item["query_used"] = "llm_wikipedia_deleted_subunits_avg" if len(all_coords) > 1 else "llm_wikipedia_deleted_subunits"
    item["error"] = ""
    print(f"[UPDATED] code={code} lat={item['lat']} lon={item['lon']} qids={item['wikidata_id']}")
    return True


def main() -> int:
    parser = argparse.ArgumentParser(description="Fill deleted JCG missing coordinates with LLM + JA Wikipedia")
    parser.add_argument("--input", default=str(DATA_PATH), help="Path to jcg_with_coords.json")
    parser.add_argument("--code", default="", help="Process one specific JCG code")
    parser.add_argument("--limit", type=int, default=0, help="Process at most N targets")
    parser.add_argument("--dry-run", action="store_true", help="Run workflow without writing JSON")
    parser.add_argument("--confirm", action="store_true", help="Ask manual confirmation before each write")
    args = parser.parse_args()

    path = Path(args.input)
    items = load_items(path)

    targets: List[int] = []
    for i, item in enumerate(items):
        if args.code and str(item.get("code", "")) != args.code:
            continue
        if has_coord(item):
            continue
        # if str(item.get("error", "")) != TARGET_ERROR:
        #     continue
        targets.append(i)

    if args.limit > 0:
        targets = targets[: args.limit]

    print(f"Loaded {len(items)} rows from {path}")
    print(f"Targets: {len(targets)} (error={TARGET_ERROR}, missing lat/lon)")
    if not targets:
        return 0

    ok = 0
    fail = 0
    for pos, idx in enumerate(targets, start=1):
        print(f"\n[PROGRESS] {pos}/{len(targets)}")
        try:
            done = process_one(items[idx], dry_run=args.dry_run, confirm=args.confirm)
            if done:
                ok += 1
            else:
                fail += 1
        except Exception as exc:  # noqa: BLE001
            fail += 1
            code = str(items[idx].get("code", ""))
            items[idx]["query_used"] = "llm_wikipedia_deleted_subunits"
            items[idx]["error"] = f"llm_pipeline_exception:{exc}"
            print(f"[ERROR] code={code} exception={exc}")

        # Write after each processed record so user can interrupt anytime.
        if not args.dry_run:
            save_items(path, items)
            print(f"[SAVE] wrote {path}")

    print("\n" + "-" * 80)
    print(f"Done. success={ok} failed={fail} dry_run={args.dry_run}")
    return 0 if fail == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
