# 变更记录

## 2026-03-22：JCC/JCG 数据外置到 JSON（第一轮落地）

### 已完成

1. 新增并生成以下 JSON 数据文件：
   - `assets/json/japan_award/pref_list.json`
   - `assets/json/japan_award/jcc_list.json`
   - `assets/json/japan_award/jcg_list.json`
   - `assets/json/japan_award/ku_list.json`

2. 数据来源与处理：
   - 从 JARL 下载 `jcc-list.txt`、`ku-list.txt`（CP932），并转换为 UTF-8（存放于 `temp/`）。
   - 使用现有 `temp/jcg-list-ja.txt` 与 `temp/jcg_with_coords.json` 生成 JCG 数据。
   - JCC 坐标从 `application/models/Jcc_model.php` 抽取。
   - JCG 坐标从 `temp/jcg_with_coords.json` 继承。
   - Pref key 使用两位 pref code。

3. 新增数据构建脚本：
   - `temp/build_japan_award_json.py`
   - 构建报告：`temp/japan_award_build_report.json`

4. 后端读取接入（运行时优先 JSON）：
   - `application/models/Jcc_model.php`
   - `application/models/Jcg_model.php`
   - 在模型构造函数中增加 JSON 加载逻辑，运行时由 JSON 数据覆盖原数组。

### 校验结果

- 生成统计：
  - pref: 47
  - jcc: 914
  - jcg: 623
  - ku: 183
- 坐标缺失：
  - jcc: 0
  - jcg: 1（`09013`，保留空值）
- 四个 JSON 均可被 Python `json.load` 正常解析。

### 备注

- 已按确认要求保留 `ku_list.json` 的 `deleted` / `deleted_date`。
- 罗马字均以 JARL 列表原文为准。

## 2026-03-22：JCC/JCG 现有 API/前端链路收尾

### 已完成

1. JCC/JCG 地图数据加载链路改造完成：
   - `assets/js/sections/jccmap.js` 直接读取 `assets/json/japan_award/jcc_list.json`
   - `assets/js/sections/jcgmap.js` 直接读取 `assets/json/japan_award/jcg_list.json`

2. 移除不必要的 JSON -> PHP 数组 -> JSON 中转链路：
   - `application/models/Jcc_model.php` 移除中转方法（仅保留 JSON 加载与业务查询逻辑）
   - `application/models/Jcg_model.php` 移除中转方法（仅保留 JSON 加载与业务查询逻辑）
   - `application/controllers/Awards.php` 中对应接口改为直接输出静态 JSON 文件

3. 模型中的 JSON 加载逻辑进一步简化：
   - `loadJccDataFromJson()` 与 `loadJcgDataFromJson()` 改为直接解码赋值，避免冗余分支。

### 结果

- 当前“将 JCC/JCG 数据从 PHP 的 models 移动到单独的 JSON”子任务的两项子要求已完成：
  - 整理 JCC/JCG 数据
  - 处理现有相关 API

## 2026-03-25：Jcc_model 审阅意见收敛

### 已完成

1. 修复 `Jcc_model` 中 `entity_status` 查询构造代码的语法错误。
2. 将 `entity_status` 内部聚合路径统一为始终带 `key_col` 聚合，再在 `key_col = none` 时对外移除该字段。
3. 简化 `union all` 与最终聚合 SQL 的构造，去除 `null key_expr` 分支。
4. 将 band/mode/prop_mode 过滤辅助函数实际接入到 `entity_status` 基础查询中。

### 备注

- 本次只收敛审阅意见对应的 `Jcc_model` 内部实现，没有扩大到 `summary` 与 `export_jcc` 的进一步重构。

## 2026-03-25：JCC 增加 Propagation Mode 过滤链路

### 已完成

1. 在 JCC 页面增加 `Propagation Mode` 下拉框，并保留当前选择状态。
2. 在 `Awards::jcc`、`Awards::jcc_export`、`Awards::jcc_map` 中补齐 `prop_mode` 的读取与默认值。
3. 在 JCC 的导出与地图 AJAX 请求中补齐 `prop_mode` 参数传递。
4. 修复 `Jcc_model` 中对 `prop_mode` 的直接数组访问 warning，并让 summary/export/first_qso 查询支持该过滤条件。

## 2026-03-26：JCC 基于 entity_status 输出表格/统计/地图

### 已完成

1. `Awards::jcc` 改为先执行一次 `query_entity_status(..., 'band')`，再把原始结果分别交给表格和统计整形函数，避免重复 SQL 查询。
2. `Jcc_model` 新增基于 `entity_status` 原始结果的表格与统计整形函数：
   - 表格输出复用同一份 band 维度状态数据。
   - 统计输出不再依赖 `get_summary_by_band` / `get_summary_by_band_confirmed` 的重复查库路径。
3. `Awards::jcc_map` 改为先执行一次 `query_entity_status(..., 'none')`，再由 `Jcc_model` 直接格式化为地图前端所需的 `{ entity: [worked, confirmed] }` 结构。
4. `docs/task.md` 中“基于 entity_status 查询结果，输出表格/统计/地图”三项子任务已标记完成。

### 备注

- 本次未处理 `export_qsos` 查询重构。
- 本次未处理“将创建 logbook 查询链接的放到 view 去”子任务。
