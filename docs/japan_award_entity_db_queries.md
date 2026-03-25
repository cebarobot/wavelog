# JapanAwardEntity_model 数据库查询说明

本文针对 `application/models/JapanAwardEntity_model.php` 中所有直接执行 SQL 的方法进行整理，覆盖：

1. SQL 伪代码
2. 查询输出数据结构
3. 合理性与冗余分析
4. 哪些逻辑更适合下推到 SQL

## 1. 查询清单总览

当前模型中直接访问数据库的方法如下：

- `getWorked($location_list, $band, $postdata)`
- `getConfirmed($location_list, $band, $postdata)`
- `getSummaryByBand($band, $postdata, $location_list)`
- `getSummaryByBandConfirmed($band, $postdata, $location_list)`
- `exportEntities($postdata)`（第 1 段查询）
- `getFirstQso($location_list, $entityCode, $postdata)`
- `fetch_entity_wkd($postdata)`
- `fetch_entity_cnfm($postdata)`

其中公共过滤片段来自：

- `addStateToQuery()`
- `Genfunctions::addBandToQuery()`
- `Genfunctions::addQslToQuery()`

---

## 2. 各查询 SQL 伪代码与输出结构

注：以下为“伪代码”，用来表达逻辑结构，非逐字 SQL。

## 2.1 getWorked

用途：查询“已通联但未满足确认条件”的实体（按 band/mode 等筛选）。

```sql
SELECT DISTINCT thcv.col_cnty
FROM table thcv
WHERE thcv.station_id IN (:location_list)
  AND (:mode = 'All' OR thcv.col_mode = :mode OR thcv.col_submode = :mode)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND NOT EXISTS (
    SELECT 1
    FROM table t2
    WHERE t2.station_id IN (:location_list)
      AND t2.col_cnty = thcv.col_cnty
      AND (:mode = 'All' OR t2.col_mode = :mode OR t2.col_submode = :mode)
      AND <band_filter_from_addBandToQuery>
      AND <qsl_filter_from_addQslToQuery>
      AND <state_filter_from_addStateToQuery>
  )
```

输出结构（`$query->result()`，对象数组）：

```json
[
  {
    "col_cnty": "01101"
  }
]
```

## 2.2 getConfirmed

用途：查询“满足确认条件”的实体。

```sql
SELECT DISTINCT thcv.col_cnty
FROM table thcv
WHERE thcv.station_id IN (:location_list)
  AND (:mode = 'All' OR thcv.col_mode = :mode OR thcv.col_submode = :mode)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND <qsl_filter_from_addQslToQuery>
```

输出结构：

```json
[
  {
    "col_cnty": "01101"
  }
]
```

## 2.3 getSummaryByBand

用途：统计某 band（或 All/SAT）下已通联实体数。

```sql
SELECT COUNT(DISTINCT thcv.col_cnty) AS count
FROM table thcv
WHERE thcv.station_id IN (:location_list)
  AND <band_logic>
  AND (:mode = 'All' OR thcv.col_mode = :mode OR thcv.col_submode = :mode)
  AND <state_filter_from_addStateToQuery>
```

`<band_logic>` 规则：

- band = `SAT`：`thcv.col_prop_mode = 'SAT'`
- band = `All`：`thcv.col_band IN (:worked_bands) AND thcv.col_prop_mode != 'SAT'`
- 其他 band：`thcv.col_prop_mode != 'SAT' AND thcv.col_band = :band`

输出结构：

```json
[
  {
    "count": 123
  }
]
```

## 2.4 getSummaryByBandConfirmed

用途：统计某 band（或 All/SAT）下已确认实体数。

```sql
SELECT COUNT(DISTINCT thcv.col_cnty) AS count
FROM table thcv
WHERE thcv.station_id IN (:location_list)
  AND <band_logic>
  AND (:mode = 'All' OR thcv.col_mode = :mode OR thcv.col_submode = :mode)
  AND <qsl_filter_from_addQslToQuery>
  AND <state_filter_from_addStateToQuery>
```

输出结构：

```json
[
  {
    "count": 99
  }
]
```

## 2.5 exportEntities（第一段）

用途：按筛选条件导出实体代码列表（后续再逐个取首 QSO）。

```sql
SELECT DISTINCT thcv.col_cnty
FROM table thcv
WHERE thcv.station_id IN (:location_list)
  AND (:mode = 'All' OR thcv.col_mode = :mode OR thcv.col_submode = :mode)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND <qsl_filter_from_addQslToQuery>
ORDER BY thcv.col_cnty ASC
```

输出结构：

```json
[
  {
    "col_cnty": "01101"
  },
  {
    "col_cnty": "01102"
  }
]
```

## 2.6 getFirstQso

用途：针对某一实体代码，取满足筛选条件的最早一条 QSO。

```sql
SELECT
  t1.col_cnty,
  t1.col_call,
  t1.col_time_on,
  t1.col_band,
  t1.col_mode,
  t1.col_prop_mode
FROM table t1
WHERE t1.station_id IN (:location_list)
  AND (:mode = 'All' OR t1.col_mode = :mode OR t1.col_submode = :mode)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND <qsl_filter_from_addQslToQuery>
  AND t1.col_cnty = :entity_code
ORDER BY t1.col_time_on ASC
LIMIT 1
```

输出结构：

```json
[
  {
    "COL_CNTY": "01101",
    "COL_CALL": "JA1XXX",
    "COL_TIME_ON": "2024-01-01 00:00:00",
    "COL_BAND": "20m",
    "COL_MODE": "SSB",
    "COL_PROP_MODE": ""
  }
]
```

注：当前代码访问返回对象属性时使用大写字段名（如 `COL_CALL`）。

## 2.7 fetch_entity_wkd

用途：为地图或列表取“已通联实体代码集合”。

```sql
SELECT DISTINCT col_cnty
FROM table
WHERE station_id IN (:location_list)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND (:mode = 'All' OR col_mode = :mode OR col_submode = :mode)
ORDER BY col_cnty ASC
```

输出结构：

```json
[
  {
    "COL_CNTY": "01101"
  }
]
```

## 2.8 fetch_entity_cnfm

用途：为地图或列表取“已确认实体代码集合”。

```sql
SELECT DISTINCT col_cnty
FROM table
WHERE station_id IN (:location_list)
  AND <state_filter_from_addStateToQuery>
  AND <band_filter_from_addBandToQuery>
  AND (:mode = 'All' OR col_mode = :mode OR col_submode = :mode)
  AND <qsl_filter_from_addQslToQuery>
ORDER BY col_cnty ASC
```

输出结构：

```json
[
  {
    "COL_CNTY": "01101"
  }
]
```

---

## 3. 合理性与冗余评估

## 3.1 合理部分

- 查询过滤维度完整：`station_id`、`band`、`mode/submode`、`QSL`、`DXCC/CNTY` 都覆盖到了。
- `getWorked` 使用 `NOT EXISTS` 定义“未确认”语义，逻辑上正确且常见。
- Summary 与明细查询的过滤口径基本一致，统计口径较稳定。

## 3.2 主要冗余

1. `getSummaryByBand` 与 `getSummaryByBandConfirmed` 高度重复。
- 仅差一个 `QSL` 过滤片段，可合并成一个私有方法（通过布尔参数决定是否追加 QSL 条件）。

2. `fetch_entity_wkd` 与 `fetch_entity_cnfm` 高度重复。
- 仅差一个 `QSL` 过滤片段，也可合并。

3. `getWorked` 与 `getConfirmed` 有大量公共 SQL 片段。
- 可抽取“基础实体查询构造器”，降低后续维护成本。

4. `addStateToQuery()` 在 `getWorked` 的外层与 `NOT EXISTS` 内层都执行一次。
- 从语义上不是错误，但有重复执行同一状态过滤片段的开销。

5. `get_entity_array()` 在按 band 循环后，针对 `worked == NULL` / `confirmed == NULL` 又追加查询。
- 会产生额外 SQL 调用，存在一定冗余。

6. `exportEntities()` + `getFirstQso()` 存在典型 N+1 查询。
- 先取实体列表 1 次，再按实体逐个查首 QSO N 次，总计 N+1 次。

---

## 4. 哪些逻辑应下推到 SQL（而不是留在 PHP）

## 4.1 强烈建议下推

1. 导出首 QSO 查询（当前 N+1）
- 现状：先查实体列表，再逐实体 `ORDER BY ... LIMIT 1`。
- 建议：单 SQL 完成“每个实体最早 QSO”选取。

可选伪代码（兼容传统 MySQL 的写法）：

```sql
SELECT q.col_cnty, q.col_call, q.col_time_on, q.col_band, q.col_mode, q.col_prop_mode
FROM table q
JOIN (
  SELECT col_cnty, MIN(col_time_on) AS first_time
  FROM table
  WHERE station_id IN (:location_list)
    AND <mode_filter>
    AND <state_filter>
    AND <band_filter>
    AND <qsl_filter>
  GROUP BY col_cnty
) f
  ON q.col_cnty = f.col_cnty
 AND q.col_time_on = f.first_time
WHERE q.station_id IN (:location_list)
  AND <same_filters_if_needed>
```

收益：显著减少数据库往返次数，尤其在实体数较多时。

## 4.2 可考虑下推

1. `get_entity_array()` 的“W/C/未通联”判定
- 当前：按 band 分别查 worked/confirmed，再在 PHP 中拼矩阵和裁剪。
- 可选：在 SQL 中做条件聚合（例如按 `col_cnty, col_band` 聚合 worked_flag/confirmed_flag），PHP 只负责渲染。
- 代价：SQL 复杂度上升，需要谨慎验证与当前页面行为完全一致。

2. Summary 统计
- 当前每个 band 分别调用两次（worked + confirmed）。
- 可选：使用条件聚合一次返回多个统计列，减少查询次数。

## 4.3 适合保留在 PHP

1. 实体名称映射（`entity_name`）
- 名称来自 JSON，不在数据库表中；在 PHP 中映射是合理的。

2. 前端展示字段（W/C HTML 片段）
- 这是视图层拼装，放在 PHP 比较自然（进一步可迁移到 View/模板）。

---

## 5. 结论

- 当前查询整体“功能上合理”，能正确表达 worked/confirmed/summary/export 的业务语义。
- 主要问题不是“查询错误”，而是“重复构造 + N+1 + 部分可合并统计”。
- 最优先优化项：`exportEntities` 的 N+1 改为单 SQL；其次是将成对重复查询方法收敛成可配置的公共查询构造。
- 若后续需要继续降本增效，再考虑把部分矩阵判定与汇总统计进一步下推到 SQL。

---

## 6. 针对本轮问题的结论

本节回答两个具体问题：

1) `fetch_entity_wkd` vs `getWorked`
2) `fetch_entity_cnfm` vs `getConfirmed`
3) summary 是否可由 PHP 手工统计以避免二次查库

### 6.1 `fetch_entity_wkd` 与 `getWorked` 是否冗余

结论：语义不等价，不能直接互相替代；但可以通过上层统一结果模型来减少重复查询入口。

- `fetch_entity_wkd` 语义是“worked 集合”（不要求确认）。
- `getWorked` 在当前实现中语义是“worked but not confirmed”（通过 `NOT EXISTS` 排除满足 QSL 条件的实体）。
- 因此两者不是同一个集合，前者通常包含后者与 confirmed 子集。

可行的上层整合方式：

- 页面或接口若只需要二值状态（是否 worked、是否 confirmed），建议统一成一套状态查询输出（例如每个实体返回 `is_worked`、`is_confirmed`）。
- 在这种模型下：
  - 未确认 = `is_worked = 1 AND is_confirmed = 0`
  - 已确认 = `is_confirmed = 1`
- 这样可以减少“按语义拆成多方法再各自查库”的重复。

注意：即使上层可统一，底层仍建议提供单一聚合查询，而不是先查 worked 再查 confirmed 再在 PHP 做多轮集合运算。

### 6.2 `fetch_entity_cnfm` 与 `getConfirmed` 是否冗余

结论：这对在 SQL 条件上高度接近，属于“接口层面冗余”，可合并。

- 两者都在做 confirmed 实体集合查询。
- 当前主要差异是调用场景（表格流程 vs 地图流程）和返回字段大小写风格（受查询写法影响）。

建议：

- 复用同一个底层查询构造器，例如 `queryEntities($band, $postdata, $requireConfirmed)`。
- 对外保留旧方法名作为兼容壳，内部转调统一实现，避免行为变更风险。

### 6.3 summary 能否改为 PHP 手工统计

结论：可以，但不建议用“当前 getWorked/getConfirmed 的返回直接统计”替代 SQL summary；推荐做法是“单次状态聚合查询 + PHP 轻量汇总”。

原因：

- 当前奖状页面确实同一请求里同时调用实体表格与 summary，会存在重复访问数据库。
- 但若直接用 `getWorked/getConfirmed` 结果统计 summary，会引入口径偏差风险：
  - `getWorked` 是“未确认 worked”，不是“全部 worked”。
  - summary 的 worked 口径是“全部 worked”。
- 另外页面是按 band 维度展示 summary，直接在 PHP 从现有结果回推并不总是便宜，尤其当 band=All 或 band 列表较多时。

推荐路径（优先级从高到低）：

1. 单 SQL 返回实体状态（含 band 维度），PHP 负责渲染与汇总
  - 每个实体/每个 band 返回 `is_worked`、`is_confirmed`
  - summary 与表格共用同一批原始结果
2. 若暂不改大结构，可先把 summary 两个 SQL 合并为一个条件聚合 SQL
  - 同时返回 worked_count、confirmed_count
3. 最后才考虑“完全 PHP 手工统计且不额外查库”
  - 仅在你已经拿到完整且口径一致的明细数据时才适用

实际页面现状说明：

- JCC/JCG 页面在同一请求中同时调用实体数组与 summary（存在重复查库空间）。
- 地图接口是独立请求（`jcc_map`/`jcg_map`），其查询不与页面主请求自动复用。