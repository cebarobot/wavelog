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

## 2026-03-22：JCC/JCG Model 合并（PHP）第一轮落地

### 已完成

1. 新增通用基础模型：
   - `application/models/JapanAwardEntity_model.php`
   - 抽离并统一了 JCC/JCG 的公共逻辑：
     - 表格数据构建（worked/confirmed/notworked）
     - worked/confirmed 查询
     - summary 统计
     - export 与首条 QSO 获取
     - 地图 worked/confirmed 数据查询

2. JCC/JCG 模型改为兼容层：
   - `application/models/Jcc_model.php`
   - `application/models/Jcg_model.php`
   - 保留原有对外方法名与调用方式，控制器/视图无需改动。

3. 修复历史参数问题并统一参数入口：
   - JCC 不再使用 `get_worked_bands('was')`。
   - JCC/JCG 均改为使用 `get_worked_bands('japan_award')`。

4. 在 `application/models/Bands.php` 增加 `japan_award` 映射逻辑：
   - 优先兼容 `jcc`/`jcg` 字段并支持缺字段回退。
   - 避免强依赖额外数据库迁移。

5. DXCC 过滤统一：
   - JCC/JCG 统一采用 `339/177/192`。

6. 清理迁移：
   - 移除未合入主线且本方案不再依赖的 `application/migrations/273_add_jcg_bandxuser.php`。

### 说明

- 本轮仅涉及 PHP model 层，不包含 JS 合并。
- 兼容层策略已生效：重构集中在 model 内部，对上层接口保持稳定。

## 2026-03-22：JCC/JCG Model 合并（PHP）第二轮收敛

### 已完成

1. 去除 `initializeEntityConfig` 显式注入调用：
   - `application/models/JapanAwardEntity_model.php`
   - 父类构造中自动合并默认配置与子类配置，并自动加载 JSON。

2. JCC/JCG 子类改为“属性声明配置”风格：
   - `application/models/Jcc_model.php`
   - `application/models/Jcg_model.php`
   - 子类仅保留配置属性与数据别名赋值，不再传入配置数组。

3. `workedBandsKey` 暂时保持原样：
   - JCC: `jcc`
   - JCG: `jcg`

### 说明

- 当前模型结构更接近“基类 + 子类声明配置”的目标形态。
- 本轮未引入 JS 层改动。
