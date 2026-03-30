# 任务

## 总体目标
改进 Wavelog 中由 JARL 颁发的日本业余无线电奖状，包括：
* JCC、WACA
* JCG、WAGA
* WAKU
* AJA

## 总体待办任务列表

* [x] 获取 JCC/JCG/Ku 数据
* [x] 重构 JCC
  * [x] 重构 Jcc_model
    * [x] 设计 entity_status 查询
    * [x] 设计 export_qsos 查询
    * [x] 基于 entity_status 查询结果，输出表格
    * [x] 基于 entity_status 查询结果，输出统计
    * [x] 基于 entity_status 查询结果，输出地图
    * [x] 调整表格输出，将创建 logbook 查询链接的放到 view 去
  * [x] 修复“重置筛选条件”的问题
  * [x] 修复“导出”的问题
  * [x] 补齐 includedeleted 链路
* [x] 为 jcc_list.json 补全政令指定都市信息
* [x] 为 ku_list.json 补全坐标信息
* [x] 格式化 jcc_list.json 和 jcg_list.json 中的日期
* [ ] 将 Jcc_model 里面的公共函数迁移到 Aja_model
* [ ] 实现 JCG
* [ ] 实现 WAKU
* [ ] 依托 JCC/JCG/WAKU 的实现，新增 AJA 功能

## 任务提示

### 通用提示
* 这些奖项的描述可以在这里找到：https://www.jarl.org/English/4_Library/A-4-2_Awards/award_list.htm
* 可以把 Cities（市）、Kus（区）、Guns（郡）统称为 entity 或 county
  * 疑问：哪个比较好？
* 市、区、郡数据保存在以下三个 list：
  * assets/json/japan_award/jcc_list.json
  * assets/json/japan_award/ku_list.json
  * assets/json/japan_award/jcg_list.json
* 虽然市、区、郡数据中包含了删除日期（成为政令指定都市的日期），但暂时不考虑验证 QSO 的日期
  * 验证 QSO 日期需要把上述 list 写入数据库，目前不具备这一条件。

### 为 jcc_list.json 补全政令指定都市信息
* 增加 2 个字段，表示该市是否为政令指定都市、何时成为政令指定都市
* 参考命名：`designated_city`，`designated_city_date`

解释：这在将来 AJA 奖状统计中将会很有用。
* QSO 在城市指定为政令指定都市前的计入“市”的 slot，之后的计入“区”的 slot。
* 目前不考虑验证 QSO 日期，只参照 deleted 的实现，直接在 AJA 统计中不包括政令指定都市。

可使用 wikidata 数据，考虑的数据抓取方式：
* 首先查询“属于”“政令指定都市”的数据项：P31 Q1749269
* 查找 P31 Q1749269 的 P580 始于

结论与以下信息进行比对：
```
横浜市	1101	昭和31年9月1日	
名古屋市	2001	昭和31年9月1日
京都市	2201	昭和31年9月1日	
大阪市	2501	昭和31年9月1日	
神戸市	2701	昭和31年9月1日	
北九州市	4021	昭和38年4月1日
札幌市	0101	昭和47年4月1日	
川崎市	1103	昭和47年4月1日	
福岡市	4001	昭和47年4月1日	
広島市	3501	昭和55年4月1日	
仙台市	0601	平成元年4月1日
千葉市	1201	平成4年4月1日
さいたま市	1344	平成15年4月1日
静岡市	1801	平成17年4月1日
堺市	2502	平成18年4月1日
新潟市	0801	平成19年4月1日
浜松市	1802	平成19年4月1日
岡山市	3101	平成21年4月1日
相模原市	1110	平成22年４月１日
熊本市	4301	平成24年４月１日
```

### 为 ku_list.json 补全坐标信息
* 参考 jcc_list.json 和 jcg_list.json 的格式
* 使用 wikidata 的数据，用 python 抓取
* 访问 wikidata 时，请使用 proxychains
* 脚本和中间结果存储到 temp 文件夹

考虑的数据抓取方式：
* 首先查询“属于”“政令指定都市”的数据项：P31 Q1749269
* 然后查找对每个政令指定都市，查找同时满足以下条件的区
  * “属于”“行政区”：P31 Q137773
  * “所在行政区”“某某市”：P131 Qxx
* 然后对于每个区，匹配其“转写”：P2440
  * 注意去掉字母帽子
* 获取“地理坐标”：P625

wikidata 可能查不到以下的区，请使用下面的 wikidata 项目的“地理坐标”P625
* 402108 八幡区：Q3276115
* 402109 小倉市：Q516373
* 270110 葺合区：Q11621211

### 格式化 jcc_list.json 和 jcg_list.json 中的日期
将 deleted_date 字段的日期，格式化为 yyyy-mm-dd 的形式
