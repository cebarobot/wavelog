# 任务

## 总体目标
改进 Wavelog 中由 JARL 颁发的日本业余无线电奖状，包括但不限于 AJD、WAJA、JCC (WACA)、JCG (WAGA)、WAKU、AJA 等。

## 当前任务：
将 JCC/JCG 数据从 PHP 的 models 移动到单独的 JSON

## 总体待办任务列表

* [x] 获取 JCC/JCG/Ku 数据
* [ ] 重构 JCC
  * [ ] 重构 Jcc_model
    * [x] 设计 entity_status 查询
    * [x] 设计 export_qsos 查询
    * [x] 基于 entity_status 查询结果，输出表格
    * [x] 基于 entity_status 查询结果，输出统计
    * [x] 基于 entity_status 查询结果，输出地图
    * [x] 调整表格输出，将创建 logbook 查询链接的放到 view 去
  * [x] 修复“重置筛选条件”的问题
  * [x] 修复“导出”的问题
  * [x] 补齐 includedeleted 链路
* [ ] 仿照 JCC 的新实现，重构 JCG
* [ ] 仿照 JCC/JCG 增加 WAKU 功能
  * [ ] 整理 Ku-list 数据
  * [ ] 实现 WAKU 的代码
* [ ] 改进完善 WAJA
  * [ ] 清除冗余代码
  * [ ] 增加导出功能（参考 JCC/JCG）
* [ ] 新增 AJD 功能
* [ ] 依托 JCC/JCG/WAKU 的实现，新增 AJA 功能

## 任务提示

* 这些奖项的描述可以在这里找到：https://www.jarl.org/English/4_Library/A-4-2_Awards/award_list.htm

### 重构 Jcc_model
我们姑且称一个 city 为一个 entity。

#### 设计 export_qsos 查询
* 设计此查询时，请参考现有的 entity_status 查询
  * 但这个显然不需要 key_col
* export_qsos 的关键是解决 1+N 查询问题
  * 即，在一次查询中完成 export_jcc 和 N 个 get_first_qso 查询。
* 做计划时，请给出 SQL 伪代码。
* 另外，export_qsos 的查询，请放在到 Jcc_model 的最后面。
* 对于老的 export_qsos 使用到的 和 entity_status 不同的逻辑（如 QSL 条件构造），总是使用 entity_status 的那一套。

#### 把 logbook 查询链接生成逻辑移动到 view
* 这个链接是指类似于：
```
<div class="bg-danger awardsBgWarning"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","JCG", "")\'>W</a></div>
<div class="bg-success awardsBgSuccess"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","JCG", "' . $qsl . '")\'>C</a></div>
```
* 你可以参考 Jcg_model.php 中的旧逻辑。
* 你不能在 Jcc_model.php 中直接混入 html，生成 HTML 应该在 view 等地方。
  * 请评估这一思路是否是最佳实践
* 可能需要一个专门的 php 函数，来生成 js 的 displayContacts 函数调用
  * 也可能需要专门的 php 函数来生成完整的 div
* 你需要检查 javascript:displayContacts、相关 api、相关 logbook 的查询是否正常
  * 一个可能的问题是，现在的 logbook 查询无法覆盖 COL_CNTY 为政令指定都市的区（Ku）的 6 位编号的情况
    * 比如：对于 JCC 0101 札幌，010101 是札幌的一个区，COL_CNTY = 010101 的 QSO 也应列在 JCC 0101 的查询结果中。
      * 得想想怎么优雅地解决这个问题


#### 修复“重置筛选条件”的问题
* bug：重置筛选条件只会重置到本次查询的初始条件，而非全局初始条件。

#### 修复“导出”的问题
* 现在导出似乎完全挂了，addStateToQuery 被我在之前清理掉了。
  * 如果你关心，addStateToQuery 可以参照 Jcg_model 中的同名函数，但没什么用。
* bug：导出时，筛选条件（如频段、模式）并不会影响导出结果
* 总是只导出 confirmed 的 QSO。修改 view 中按钮的提示词以匹配功能。
  * 考量：这个是生成奖状申请表，导出未确认的可能造成迷惑。

#### 补齐 includedeleted 链路
* 补齐 includedeleted 链路，加上相关的 UI。
* 默认不 include deleted entity.

#### [done] 基于 entity_status 查询结果，输出表格/统计/地图

提示：
* 先在 controller 中调用 query_entity_status，然后把获得的原始数据交给 jcc_model 的数据处理函数
  * 这样可以避免重复进行相同的 sql 查询。
* 用于输出表格的 get_jcc_array 已经基本满足需求，除了上面有关 query_entity_status 调用的要求；
* 用于输出统计的 get_jcc_summary 需要大改：
  * get_summary_by_band、get_summary_by_band_confirmed 应该是不需要了。
* 用于输出地图的 fetch_jcc_wkd 和 fetch_jcc_cnfm 需要大改：
  * 这两个函数可以合并成一个，直接输出 Awards 控制器里 jcc_map() 所要求的格式即可。


#### [done] 设计 entity_status 查询
总体要求，对于 (entity, key_col) 的组合，输出 confirmed：0 表示 worked_not_confirmed，1 表示 confirmed。

具体步骤（伪 SQL）如下：

1A:
```
select
  col_cnty as entity,
  col_band as band,
  case
    when col_submode = 'DSTAR' then 'DSTAR'
    when col_mode in ('AM', 'FM', 'CW', 'SSB', 'ATV', 'FAX', 'SSTV', 'DIGITALVOICE') then col_mode
    else 'DIGITAL'
  end as mode,
  ($addQslToQuery) as confirmed
from
  thcv
where
  col_dxcc in $japan_dxcc_list and
  col_cnty in $jcc_list and           // !!!!
  station_id in $location_list and
  query_band_condition and            // addBandToQuery
  query_mode_condition and            // addModeToQuery
  query_prop_mode_condition           // addPropModeToQuery
```

1B:
```
select
  left(col_cnty, 4) as entity,
  col_band as band,
  mode(略)
from
  thcv
where
  col_dxcc in $japan_dxcc_list and
  col_cnty in $ku_list and            // !!!! 
  station_id in $location_list and
  query_band_condition and            // addBandToQuery
  query_mode_condition and            // addModeToQuery
  query_prop_mode_condition           // addPropModeToQuery
```

2A & 2B:
```
select
  entity,
  key_col,      // band or mode or nothing
  max($addQslToQuery) as confirmed
from
  1A / 1B 的结果
group by (entity, key_col)
```

3:
```
2A 结果
union all
2B 结果
```

4:
对 3 结果再进行一次 2A/2B 一样的 group by

额外的解释说明：
* key_col 可以是 band、mode，或者没有 key_col。这应该是一个参数。
* addQslToQuery 似乎可以用 genfunctions 中的，给出的应该是一个 or 起来的列表。
* addBandToQuery 和 addModeToQuery 和 addPropModeToQuery 得自行实现
  * col_band = $input_data
  * col_mode = $input_data
  * col_prop_mode = $input_data

### [DONE] 获取 JCC/JCG/Ku 数据
* JCC/JCG/Ku 的列表：
  * JCC list: https://www.jarl.org/Japanese/A_Shiryo/A-2_jcc-jcg/jcc-list.txt
  * JCG list: https://www.jarl.org/Japanese/A_Shiryo/A-2_jcc-jcg/jcg-list.txt
  * ku list: https://www.jarl.org/Japanese/A_Shiryo/A-2_jcc-jcg/ku-list.txt
* wavelog 中 JCC 的 list 保存于：application/models/Jcc_model.php
  * JCC 的坐标由此获取。
* 我有一个此前整理好的 JCG 的 json：temp/jcg_with_coords.json
  * 但其中的英文名（罗马字）可能不正确，需要根据最新的 JCG list 进行修正
  * JCG 的坐标由此获取。
* 我希望获得以下 4 个 JSON：
  * assets/json/japan_award/pref_list.json
  * assets/json/japan_award/jcc_list.json
  * assets/json/japan_award/jcg_list.json
  * assets/json/japan_award/ku_list.json

JSON 的格式类似于：
```json
{
    "01001": {
        "name": "Akan",
        "ja_name": "阿寒",
        "deleted": false,
        "deleted_date": "",
        "lat": 43.23015,
        "lon": 144.321125,
    },
}
```
* 都道府县（Prefecture）不要坐标，不要是否删除/删除时间（因为不存在此类情形）。
