# JCC 重构实施计划（本轮，已确认版）

## 1. 任务目标与边界

### 本轮目标
1. 重构 `Jcc_model`，以“entity_status 查询”为核心统一数据查询入口。
2. 先完成可复用的状态查询能力，为后续 `export_qsos`、表格统计、地图输出打基础。

### 本轮不做
1. 不直接展开 JCG/WAKU/AJD/AJA 的功能开发。
2. 不改动前端样式与交互，仅做后端查询能力重构和兼容适配。
3. 不在本轮实现 `export_qsos` 查询重构。

## 2. 已确认约束（来自最新沟通）

1. `key_col` 行为：
   - `key_col = band|mode`：按当前逻辑输出对应字段。
   - `key_col = none`：结果中不返回 `key_col` 字段（直接移除该字段）。
2. mode 归一：严格按任务提示规则执行（`DSTAR` 特判 + `DIGITAL` 兜底）。
3. band 处理：本轮只考虑 `All` 场景，不考虑 `SAT` 特殊语义。
4. `includedeleted`：由运行时 `postdata` 控制。
   - 默认不包含 `deleted=true` 的 JCC。
   - 当 `includedeleted` 打开时，查询与展示包含 deleted 实体。
5. 调试可视化：需要提供可通过 Web 访问直接看到 SQL 与查询结果的临时入口（HTML 页面）。

## 3. 任务要求解读

根据 `docs/task.md` 的提示，`entity_status` 需要满足：

1. 数据来源为日志表（`thcv`），并同时覆盖：
   - JCC 本体（`col_cnty in jcc_list`）
   - KU 折算到 JCC（`col_cnty in ku_list`，实体使用 `left(col_cnty, 4)`）
2. 输出语义为 `(entity, key_col) -> confirmed`：
   - `confirmed = 0` 表示 worked but not confirmed
   - `confirmed = 1` 表示 confirmed
3. 支持 `key_col` 参数化：
   - 可选 `band`、`mode`、或无 key
4. 聚合路径要求：
   - 先分别聚合 JCC 与 KU
   - 再 `union all`
   - 最后再次聚合去重（同一 entity/key 取 max confirmed）
5. 过滤条件要求：
   - DXCC、entity 列表、station 列表
   - `band`、`mode`、`prop_mode` 过滤
   - QSL 确认逻辑（`addQslToQuery`）

## 4. 现状评估

### 正向结论
1. 现有 `Jcc_model` 已完成 JSON 外置（JCC 数据来自 `assets/json/japan_award/jcc_list.json`），具备重构基础。
2. 现有查询链路（表格、summary、地图、导出）完整，便于做兼容改造。

### 主要问题
1. 查询能力分散：`getJccWorked`、`getJccConfirmed`、`fetch_jcc_wkd`、`fetch_jcc_cnfm`、`summary` 各自拼 SQL，重复高。
2. 缺少统一状态模型：目前是“先 worked 再 confirmed”在 PHP 合并，导致逻辑重复。
3. `exportJcc` 存在 N+1 查询（先拿实体列表，再逐实体查首 QSO）。
4. KU 数据虽有加载方法，但构造函数中未加载，当前无法直接参与 JCC 聚合。

### 评价
本任务设计方向正确，且与后续 JCG/WAKU 复用高度一致。优先落地 `entity_status` 是正确顺序，可以显著降低重复 SQL 和后续扩展成本。

## 5. 本轮实施计划

## 阶段 A：重构查询基础设施（先打底）
1. 在 `Jcc_model` 增加统一过滤构造器：
   - `buildBandFilter(...)`
   - `buildModeFilter(...)`
   - `buildPropModeFilter(...)`
2. 增加 `buildModeKeyExpr()`：
   - 保持任务要求中的 mode 归一规则：
     - `col_submode = 'DSTAR'` => `DSTAR`
     - `col_mode in (AM, FM, CW, SSB, ATV, FAX, SSTV, DIGITALVOICE)` => `col_mode`
     - 其他 => `DIGITAL`
3. 在构造函数中加载 KU JSON（`loadKuDataFromJson()`），准备 1B 查询输入。
4. `buildBandFilter(...)` 本轮仅实现 `All`（不引入 `SAT` 分支）。

交付物：统一过滤片段与 mode 归一表达式，可被后续所有查询复用。

## 阶段 B：实现 entity_status 核心查询
1. 新增核心方法（示例命名）：
   - `queryEntityStatus($postdata, $keyType)`
2. SQL 分层实现（严格对应任务提示）：
   - 子查询 1A：JCC 实体
   - 子查询 1B：KU -> JCC 实体（`left(col_cnty, 4)`）
   - 子查询 2A/2B：各自 `group by entity, key_col`，`max(confirmed)`
   - 合并 3：`union all`
   - 终聚合 4：再做一次 `group by` + `max(confirmed)`
3. `confirmed` 计算：
   - 采用 `CASE WHEN (QSL 条件) THEN 1 ELSE 0 END`，确保可参与 `max()`。
4. `key_col` 策略：
   - `band` -> `col_band`
   - `mode` -> `buildModeKeyExpr()`
   - `none` -> SQL 与返回结构均不包含 `key_col`

交付物：统一输出结构（entity + key + confirmed），可直接供表格/统计/地图使用。

## 阶段 C：以 entity_status 替换旧接口（兼容输出）
1. 用 `queryEntityStatus(..., none)` 改造：
   - `fetch_jcc_wkd`
   - `fetch_jcc_cnfm`
2. 用 `queryEntityStatus(..., band)` 改造：
   - `get_jcc_summary`
3. 用 `queryEntityStatus(..., band/mode)` 改造：
   - `get_jcc_array`（保留原 HTML 输出结构，降低前端改动）
4. 旧方法名暂保留为兼容壳，内部转调新实现。

交付物：控制器和视图接口不变，内部查询统一。

## 阶段 D：临时调试入口（Web 可见 SQL + 结果）
1. 新增一个临时调试接口（仅开发期启用），返回 HTML 页面：
   - 入参：`band`、`mode`、`key_col`、QSL 相关参数。
   - 出参：
     - `sql_steps`（1A/1B/2A2B/3/4 各阶段 SQL 文本）
     - `bindings`（参数绑定）
     - `rows`（最终查询结果）
2. 临时接口路径：
   - `index.php/awards/jcc_entity_status_debug`
3. 约束：
   - 仅在开发环境开放（例如 `ENVIRONMENT !== 'production'`）。
   - 响应 `Content-Type: text/html`，便于你直接在浏览器查看。

交付物：你可直接访问 URL，看到 SQL 与结果，无需我代你访问。

## 阶段 E：验证与回归
1. 功能回归：
   - JCC 页面（All/单 band、All/单 mode）
   - 地图状态（W/C）
   - summary 数值一致性
2. SQL 语义回归：
   - JCC 与 KU 同实体冲突时，以 `max(confirmed)` 为准
   - 同一实体多记录仅保留正确状态
3. 性能基线：
   - 对比重构前后的查询次数与接口耗时（重点看地图和 summary）

交付物：回归结论记录到 `docs/history.md`。

## 6. Query Builder 可行性评估与策略

1. 可行点：
   - `where`/`where_in`/`select`/`group_by`/`get_compiled_select` 适合构建 1A、1B 子查询。
   - 可通过 `get_compiled_select()` 先编译子 SQL，再手工拼接 `union all` 与外层聚合。
2. 冲突点：
   - 现有 `Genfunctions::addBandToQuery()`、`addQslToQuery()` 返回的是 SQL 字符串片段，不是 Builder 条件对象。
   - 复杂 `union all` + 二次 `group by` 在 CI3 Builder 中原生支持有限，最终仍需部分手写 SQL。
3. 结论（本轮采用）：
   - 采用“混合方案”：
     - 简单过滤优先 Builder；
     - `union all`、最终聚合、`CASE WHEN` confirmed 计算使用手写 SQL；
     - 所有动态值继续使用绑定参数。

## 7. 验收标准

1. 所有 JCC 页面功能行为与重构前一致（除明确修复的问题外）。
2. 新增 `entity_status` 能按 `key_col` 输出稳定结果。
3. 地图、summary、表格都由统一状态查询驱动。
4. 不引入 SQL 注入风险（动态值全部走绑定或受控白名单）。
5. 为后续 `export_qsos` 单 SQL 化提供可复用基础。

## 8. 待确认问题

当前无阻塞性待确认问题，可直接继续进入下一步联调与回归。
