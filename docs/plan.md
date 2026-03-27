# 计划

## 当前阶段：完成 JCC 剩余收尾并为 JCG 重构做准备

1. 继续以 `entity_status` 为 JCC 页面表格、统计、地图的唯一数据基线，不再回退到旧的重复查库路径。
2. 为导出新增 `export_qsos` 单查询，替换旧的 `export_jcc + get_first_qso` 1+N 查询，并统一使用 `entity_status` 同源的 QSL 条件构造。
3. 将 JCC 表格中的 `displayContacts` 链接生成从 model 挪到 view/helper，保持 `Jcc_model` 只返回业务状态，不混入 HTML。
4. 修复 JCC 的 `reset`、`export`、`includedeleted` 三条链路，让页面、导出、地图对筛选条件的解释保持一致。
5. 修复 JCC 明细查询，支持 4 位 JCC 查询同时覆盖对应 6 位 Ku 编号。
6. 完成后更新任务文档与变更记录，并做语法校验。

## export_qsos SQL 伪代码

目标：一次查询得到每个 JCC entity 的首条已确认 QSO，避免旧实现的主查询 + N 次 `get_first_qso`。

```sql
-- 1A: 直接命中 JCC 的记录
select
	col_cnty as entity,
	col_primary_key,
	col_cnty,
	col_call,
	col_time_on,
	col_band,
	col_mode,
	col_prop_mode
from thcv
where
	col_dxcc in ('339') and
	col_cnty in (jcc_list) and
	station_id in (location_list) and
	query_band_condition and
	query_mode_condition and
	query_prop_mode_condition and
	query_confirmed_condition

union all

-- 1B: 命中 Ku，但按所属 4 位 JCC 聚合到 entity
select
	left(col_cnty, 4) as entity,
	col_primary_key,
	col_cnty,
	col_call,
	col_time_on,
	col_band,
	col_mode,
	col_prop_mode
from thcv
where
	col_dxcc in ('339') and
	col_cnty in (ku_list) and
	station_id in (location_list) and
	query_band_condition and
	query_mode_condition and
	query_prop_mode_condition and
	query_confirmed_condition
```

```sql
-- 2: 先取每个 entity 的最早通联时间
select entity, min(col_time_on) as first_time
from (1A union all 1B) source
group by entity
```

```sql
-- 3: 若同一 entity 在 first_time 上仍有多条记录，则以最小 primary key 作为稳定 tie-breaker
select
	source.entity,
	min(source.col_primary_key) as first_key
from (1A union all 1B) source
join (步骤2结果) first_time
	on first_time.entity = source.entity
 and first_time.first_time = source.col_time_on
group by source.entity
```

```sql
-- 4: 回取最终导出所需字段
select
	source.entity,
	source.col_cnty,
	source.col_call,
	source.col_time_on,
	source.col_band,
	source.col_mode,
	source.col_prop_mode
from (1A union all 1B) source
join (步骤3结果) first_key
	on first_key.entity = source.entity
 and first_key.first_key = source.col_primary_key
order by source.entity asc
```

## 设计说明

1. `Jcc_model` 内部继续保留 `entity_status` 与 `export_qsos` 两条查询路径，但共享同一套 band / mode / prop_mode / QSL 条件构造。
2. JCC 表格单元格的 HTML 改由 view/helper 生成，这比在 model 中拼接 `javascript:displayContacts(...)` 更符合职责分离。
3. JCC 明细查询对 Ku 的覆盖放在 `Logbook_model` 的 JCC 分支处理，而不是在前端拼接复杂查询参数；这样后续 JCG/WAKU/AJA 复用时更稳。