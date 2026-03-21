# JCC/JCG 代码合并计划（聚焦 Model 抽象）

## 目标

在不改变现有功能与对外接口行为的前提下，合并 JCC/JCG 的重复实现，减少维护成本，并为后续 WAKU/AJA/AJD 的复用打基础。

本轮范围：
1. 仅合并 PHP model 代码。
2. 保持 Awards 控制器与现有页面调用兼容。
3. 不在本轮处理 JS 层合并。

## 已确认决策

1. JCC 中 get_worked_bands 使用 was 属于历史遗留 bug，应修正为 jcc。
2. JCC/JCG 后续统一使用同一个 get_worked_bands 参数（建议命名为 japan_award），并通过兼容映射平滑过渡。
3. COL_DXCC 过滤统一采用 JCG 规则（339/177/192）。
4. 兼容层的目标是只改 model，不改 controller/view。
5. 本任务中需要抽象层，但不强制使用严格抽象类。

## 现状评估

JCC 与 JCG 两个 model 的主流程高度一致，重复点包括：
1. 查询构造流程：worked、confirmed、summary、export、map 数据获取。
2. 通用过滤逻辑：band/mode/QSL/station_id 过滤与参数绑定。
3. 表格数据拼装流程：按 band 生成矩阵、填充 W/C 状态、按筛选裁剪。
4. 导出流程：先查实体，再逐个取 first QSO。

当前差异点应收敛为配置项：
1. 数据源：jcc_list.json vs jcg_list.json。
2. 实体文案与奖项标识：City/JCC vs Gun/JCG。
3. cnty 格式规则：JCC 与 JCG 的格式判定不同。
4. get_worked_bands 的 award 参数映射。

## DXCC 统一的性能评估

统一到 339/177/192 的性能风险较低，原因如下：
1. 查询仍受 station_id、band/mode/QSL、COL_CNTY IN(JSON keys) 等条件约束，DXCC 不是唯一过滤条件。
2. 339 本身覆盖主要数据，附加 177/192 通常只引入很小增量。
3. 在常见索引（station_id、col_cnty、col_dxcc）存在时，执行计划通常不会出现数量级变化。

落地时仍需做抽样验证：
1. 对 worked、confirmed、summary 三类核心查询做改造前后对比。
2. 用 explain 与页面响应时间确认无明显退化。

## 目标架构

采用“一个可实例化基础模型 + 两个轻量兼容模型”的方式：

1. 新增基础模型（建议名 JapanAwardEntity_model）
   - 承载全部公共逻辑：
     - getEntityArray
     - getWorked / getConfirmed
     - getSummary / getSummaryConfirmed
     - exportEntities / getFirstQso
     - fetchWorked / fetchConfirmed
   - 通过配置驱动差异：
     - awardType
     - jsonPath
     - entityLabel
     - exportKey
     - stateRule（DXCC、cnty pattern、合法 key 集）
     - workedBandsKey（支持兼容映射）

2. 保留 Jcc_model / Jcg_model 作为兼容层
   - 仅负责注入配置并保留旧方法名（例如 get_jcc_array、get_jcg_array）。
   - 返回结构保持不变，控制器与视图无感知。

## 分阶段实施

## Phase 1：抽象骨架与参数修复

1. 建立统一配置结构。
2. 修复 JCC 的历史参数：get_worked_bands('was') -> get_worked_bands('jcc')。
3. 在 Bands 模型增加参数兼容映射，为后续统一参数预留入口。
4. 创建基础模型，先落地数据加载与公共查询骨架。
5. Jcc_model/Jcg_model 接入基础模型，但不改 controller/view。

交付物：
1. 基础模型文件与配置结构。
2. JCC 历史参数 bug 修复。
3. 页面功能保持一致。

## Phase 2：迁移重复方法

1. 迁移 worked/confirmed 查询逻辑。
2. 迁移 summary 相关逻辑。
3. 迁移 export 与 first QSO 获取逻辑。
4. 迁移 map 数据查询逻辑。
5. wrapper 仅保留别名方法与最薄胶水代码。

交付物：
1. JCC/JCG model 重复代码显著减少。
2. Awards 现有调用保持兼容。

## Phase 3：收尾与清理（仅 PHP）

1. 统一 model 命名风格与加载大小写。
2. 明确兼容层保留周期并写注释。
3. 评估并处理未合入主线迁移文件 application/migrations/273_add_jcg_bandxuser.php：
   - 若主线已具备同等字段能力，则在本任务分支去掉该迁移。

交付物：
1. 迁移路径清晰，历史包袱减少。
2. 后续 WAKU/AJA/AJD 可直接复用基础模型。

## 验证与回归清单

1. JCC 页面：worked/confirmed/notworked/band/mode 组合筛选。
2. JCG 页面：同上组合筛选。
3. JCC/JCG 导出：记录数量、字段完整性、首通联时间排序。
4. JCC/JCG 地图：worked/confirmed/notworked 显示一致。
5. summary：各 band、Total、SAT 与改造前一致。
6. SQL 抽样：DXCC 统一前后 explain 与响应时间无明显退化。

## 风险与应对

1. 风险：抽象后 SQL 条件细节变化导致统计偏差。
   - 应对：每迁移一类方法就做改造前后结果对比。

2. 风险：统一参数牵涉 bandxuser 字段差异。
   - 应对：先做模型层兼容映射，避免强依赖数据库变更。

3. 风险：重构范围扩大导致排障困难。
   - 应对：分 phase 小步提交，每步只处理一类逻辑。

## 抽象类必要性结论

本任务有必要做抽象层，但不建议一开始就使用严格 abstract class：
1. CodeIgniter 3 的 model 加载方式更适合“可实例化基础模型 + 配置驱动”。
2. 当前目标是去重与稳定迁移，配置方案已足够约束差异点。
3. 待后续实体类型增多、规则分叉更明显时，再升级为严格抽象类更稳妥。
