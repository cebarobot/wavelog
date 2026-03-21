# JCC/JCG 数据外置到 JSON 的任务计划

## 1. 目标与范围

### 1.1 当前迭代目标
将 JCC/JCG 数据从 PHP model 中迁移到独立 JSON 文件，并打通现有读取与 API 使用链路。

### 1.2 本迭代交付物
1. `assets/json/japan_award/pref_list.json`
2. `assets/json/japan_award/jcc_list.json`
3. `assets/json/japan_award/jcg_list.json`
4. `assets/json/japan_award/ku_list.json`
5. 代码层完成对新 JSON 数据源的读取（至少覆盖当前 JCC/JCG 已在用的入口）
6. 基础校验文档（数据完整性与字段一致性）

### 1.3 暂不包含
- JCC/JCG PHP 与 JS 逻辑合并
- 已删除 JCC/JCG 的奖状筛选逻辑实现
- WAKU / WAJA / AJD / AJA 功能开发

---

## 2. 数据规范（拟定）

### 2.1 JSON 路径
统一放置于：`assets/json/japan_award/`

### 2.2 字段规范

#### 2.2.1 `pref_list.json`
- key: prefecture code（字符串）
- value:
  - `name`（英文/罗马字）
  - `ja_name`（日文名）

> 注：按需求，不包含 `lat` / `lon` / `deleted` / `deleted_date`。

#### 2.2.2 `jcc_list.json` / `jcg_list.json`
- key: JCC/JCG 编码（字符串）
- value:
  - `name`
  - `ja_name`
  - `deleted`（布尔）
  - `deleted_date`（字符串，未删除为空串）
  - `lat`（数字）
  - `lon`（数字）

#### 2.2.3 `ku_list.json`
- key: Ku 编码（字符串）
- value:
  - `name`
  - `ja_name`
  - （若上游数据有状态信息，可预留 `deleted` / `deleted_date`，否则仅基础字段）

---

## 3. 执行步骤

### 阶段 A：现状梳理（半天）
1. 梳理当前 JCC/JCG 数据来源与读取入口：
   - `application/models/Jcc_model.php`
   - 相关 controller/model/library/helper/API 使用点
2. 梳理前端是否直接依赖某些字段名（避免迁移后断裂）。
3. 明确 JCG 当前使用来源：`temp/jcg_with_coords.json`。

产出：
- 使用点清单
- 目标字段对照表（旧结构 -> 新 JSON 结构）

### 阶段 B：数据生成与清洗（1 天）
1. 基于 JARL 最新列表整理 JCC/JCG/Ku 原始数据。
2. 从 `Jcc_model.php` 提取 JCC 坐标并对齐编码。
3. 基于 `temp/jcg_with_coords.json` 继承 JCG 坐标，并按最新 JCG list 修正英文名（罗马字）。
4. 生成 `pref_list.json`（仅名称字段）。
5. 生成 4 个 JSON 初稿并执行结构校验。

产出：
- 4 个 JSON 文件（初版）
- 数据清洗脚本（可放 `temp/`，便于复现）

### 阶段 C：后端接入改造（1 天）
1. 为 JCC/JCG 数据读取增加统一入口（建议新增 service/helper，避免散落读取逻辑）。
2. 将原先依赖 PHP 内嵌数组的逻辑替换为 JSON 读取。
3. 处理与 JCC/JCG 相关 API 的兼容，确保返回结构不破坏现有调用方。
4. 增加异常兜底（JSON 不存在、格式错误、字段缺失）。

产出：
- 后端改造代码
- 兼容性说明

### 阶段 D：验证与回归（半天）
1. 校验 4 个 JSON：
   - JSON 合法性
   - 编码唯一性
   - 必填字段完整
   - 坐标类型正确
2. 回归关键页面/API：
   - JCC/JCG 相关统计/查询/奖状页面
3. 对比迁移前后关键输出是否一致（数量、编码、名称）。

产出：
- 验证记录
- 差异说明（如有）

### 阶段 E：文档与交付（半天）
1. 在 `docs/history.md` 记录本轮变更。
2. 在文档中说明 JSON 维护来源与更新流程。
3. 给出后续任务衔接点（代码合并、删除逻辑、WAKU）。

---

## 4. 验收标准

1. `assets/json/japan_award/` 下存在并可读取 4 个目标 JSON。
2. JCC/JCG 现有功能可正常运行，无明显功能回退。
3. 与迁移前相比，核心查询/API 输出在可接受范围内一致。
4. JCG 英文名已按最新 JCG list 完成修正。
5. 文档可指导后续维护者独立更新数据。

---

## 5. 风险与应对

1. **上游列表格式变化**（JARL 文本结构调整）
   - 应对：解析脚本采用容错策略，并保留人工校验步骤。
2. **编码对齐问题**（JCC/JCG 与坐标源无法一一匹配）
   - 应对：输出未匹配清单，人工补齐后再生成。
3. **历史逻辑隐式依赖旧字段**
   - 应对：先做使用点扫描，再做兼容映射层。
4. **deleted 字段语义不一致**
   - 应对：先保持现状语义，仅做数据迁移，不在本迭代引入新判定规则。

---

## 6. 后续阶段预留（不在本次实现）

1. JCC/JCG 代码去重（PHP + JS）
2. 已删除 City/Gun 的时间判定与奖状筛选
3. WAKU 数据与实现
4. WAJA 清理与导出
5. AJD / AJA 新功能

---

## 7. 已确认约束

1. `ku_list.json` 必须包含 `deleted` 与 `deleted_date`。
2. `name` 的罗马字以 JARL 列表原文为准。
3. `pref_list.json` 的 key 使用 JCC/JCG 前缀中的两位 pref 编码。
4. 坐标缺失项允许留空，不额外处理。
5. 当前阶段不做 API 兼容层，按新数据结构推进组件适配。
