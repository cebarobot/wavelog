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
