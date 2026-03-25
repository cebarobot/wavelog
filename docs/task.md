# 任务

## 总体目标
改进 Wavelog 中由 JARL 颁发的日本业余无线电奖状，包括但不限于 AJD、WAJA、JCC (WACA)、JCG (WAGA)、WAKU、AJA 等。

## 当前任务：
将 JCC/JCG 数据从 PHP 的 models 移动到单独的 JSON

## 总体待办任务列表

* [x] 获取 JCC/JCG/Ku 数据
* [ ] 重构 JCC
  * [ ] 重构 Jcc_model
    * [ ] 设计 entity_status 查询
    * [ ] 设计 export_qsos 查询
    * [ ] 设计基于 entity_status 查询结果，输出表格、统计、Map 数据的功能
  * [ ] 调整适配 Controller 和 View
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




#### 设计 entity_status 查询
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
