# JCG 实施历史

## 2026-03-07 - Step 3: 获取 JCG 数据与坐标（首轮）

### 已完成

1. 下载 JARL 官方源文件到 `temp/`
- `temp/jcg-list.txt`

2. 新增临时构建脚本
- `temp/build_jcg_dataset.py`
- 功能：
  - 解析 `jcg-list.txt` 为结构化 JCG 列表
  - 通过 Wikidata API 检索并提取坐标（P625）
  - 生成后续导入所需产物

3. 当前输出产物（在 `temp/`）
- `jcg_parsed.json`：解析后的 JCG 清单
- `jcg_with_coords.json`：带坐标结果（含成功/失败条目）
- `jcg_missing_coords.json`：缺失坐标条目
- `jcg_php_array.txt`：可用于 `Jcg_model.php` 的 PHP 数组草稿
- `jcg_build.log`：构建日志

### 本次脚本增强

根据需求，脚本已增强为：

1. 遇到无法获取坐标时，**立即打印具体错误**，并继续处理下一条（不中断）。
2. 每个条目附带 `error` 字段，记录查询失败原因。
3. 每 25 条输出进度，包含 `found/missing` 计数。
4. 增强名称清洗，去掉源文件里尾部日期注记（含不规范引号场景）。

## 2026-03-07 - Step 3: 查询策略调整（按用户建议）

### 调整内容

1. 改为“先拉全量，再本地匹配”
- 不再逐条在 Wikidata 搜索。
- 先通过 SPARQL 一次性拉取日本郡目录（`P31` 属于 `Q1122846` 或 `Q46426234`，并结合 `P131` 归属都道府县）。
- 输出缓存文件：`temp/wikidata_japan_districts.json`。

2. 本地匹配规则
- 使用 JCG 名称 + 都道府县做本地匹配。
- 命中优先级：同名且同都道府县 > 同名。
- 无坐标或多候选歧义时，标记错误并留待人工处理。

3. 北海道括号郡处理
- 名称包含括号（如 `Abuta(Shiribeshi)`）的条目直接跳过自动坐标匹配。
- 这类条目会输出：`skip_parenthetical_hokkaido_special`，并进入人工处理清单。

4. 失败即时输出（不中断）
- 每个缺失坐标条目会立即输出：
  - `[MISSING_COORD] code=... name=... pref=... reason=...`
- 同时写入 JSON 的 `error` 字段。

### 新运行命令

```bash
proxychains -q /home/ceba/.local/share/virtualenvs/mkdocs-static-i18n-cpaQXGD-/bin/python -u temp/build_jcg_dataset.py > temp/jcg_build.log 2>&1
```

## 2026-03-07 - 去重修复（wikidata_japan_districts.json）

### 现象

- `temp/wikidata_japan_districts.json` 出现大量重复项。
- 根因是 `P131*` 递归路径会为同一个 `district + prefecture` 产生多条等价记录。

### 修复

1. SPARQL 层增加 `SELECT DISTINCT`。
2. Python 层增加强制去重：按 `(qid, pref_qid)` 保留唯一记录。
3. 去重冲突时优先保留有坐标（`lat/lon` 非空）的记录。

### 输出增强

- 终端统计新增：`wikidata_catalog_rows`，用于观察去重后目录大小。

## 2026-03-07 - 原因统计增强

### 调整

1. 将 `name_matched_but_no_coord` 拆分为：
- `name_matched_but_no_coord_deleted`
- `name_matched_but_no_coord_active`

2. 终端末尾新增原因统计分布：
- `reason_counts:`
- 列出每种 `reason` 及其数量（按数量倒序）。

### 目的

- 便于区分“已废除郡缺坐标”与“现存郡缺坐标”，优先安排人工补全顺序。

## 2026-03-07 - 匹配精度与离线缓存增强

### 调整

1. 名称归一化新增“去帽子/去重音”处理（Unicode NFKD + 去组合符）。
- 用于降低 `no_name_match_in_catalog`（例如 `Sōma` 与 `Soma`）。

2. 新增本地缓存优先模式。
- 当 `temp/wikidata_japan_prefectures.json` 与 `temp/wikidata_japan_districts.json` 同时存在时：
  - 脚本直接读取本地缓存；
  - 不访问 Wikidata。
- 终端会输出：`using_local_wikidata_cache: true/false`。

### 运行要求

- 访问 Wikidata 必须走代理：`proxychains`
- 建议运行命令（项目根目录执行）：

```bash
proxychains -q /home/ceba/.local/share/virtualenvs/mkdocs-static-i18n-cpaQXGD-/bin/python -u temp/build_jcg_dataset.py > temp/jcg_build.log 2>&1
```

- 运行中查看实时错误：

```bash
tail -f temp/jcg_build.log
```

- 运行后快速统计：

```bash
python3 - <<'PY'
import json
arr = json.load(open('temp/jcg_with_coords.json', 'r', encoding='utf-8'))
miss = [x for x in arr if x.get('lat') is None or x.get('lon') is None]
print('total=', len(arr), 'with_coords=', len(arr)-len(miss), 'missing=', len(miss))
print('missing sample=', [(x['code'], x['name']) for x in miss[:10]])
PY
```

### 备注

- `temp/` 下脚本与结果均为临时构建资产，后续将用于生成最终 `Jcg_model.php` 数据。