# 任务

## 总体目标
改进 Wavelog 中由 JARL 颁发的日本业余无线电奖状，包括但不限于 AJD、WAJA、JCC (WACA)、JCG (WAGA)、WAKU、AJA 等。

## 当前任务：
将 JCC/JCG 数据从 PHP 的 models 移动到单独的 JSON

## 总体待办任务列表

* [ ] JCC/JCG 改进
  * [ ] 将 JCC/JCG 数据从 PHP 的 models 移动到单独的 JSON
    * [ ] 整理 JCC、JCG 相关数据
    * [ ] 处理现有的与 JCC/JCG 数据相关的 API
  * [ ] 将 JCC/JCG 的代码进行合并，避免一份代码在多个地方使用
    * [ ] 合并 PHP 中的代码
    * [ ] 合并 js 中的代码
  * [ ] 为 JCC/JCG 增加已删除 JCC/JCG 的处理逻辑
    * [ ] 评估可行性：检查与已删除 City/Gun 通联的 QSO 时间是否晚于 City/Gun 的删除时间
    * [ ] 为奖状页面增加筛选：是否包括已删除的 City/Gun
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

### JCC/JCG 改进
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
