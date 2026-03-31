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
* [ ] 扩展现有 Jcc_model 中的公共函数
  * [ ] 对于生成 sql + bindings 对的函数，统一返回值格式
  * [ ] 改进 build_entity_status_union_all_sql
  * [ ] 调整 build_entity_query_where_sql 的参数
  * [ ] 让 query_export_qsos 支持 key_col
  * [ ] 让 query_entity_status 和 query_export_qsos 支持数组表达的多组查询
  * [ ] 包装 query_entity_status 和 query_export_qsos
  * [ ] 移动函数、完成分 model
* [ ] 实现 JCG
* [ ] 实现 WAKU
* [ ] 实现 AJA

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

### 基于类的逻辑复用
对于 AJA、JCC、JCG、WAKU 奖状，查询请求一般可以分为 2 类：
* entity_status：查询一个市郡区（+ band/mode）是否 worked/confirmed
  * 对于表格，市郡区 + band
  * 对于地图，只需要 市郡区
* export_qso：查询一个市郡区（+ band/mode）最早的 QSO
  * 对于 JCC、JCG、WAKU 只需要市郡区
  * 对于 AJA，需要市郡区 + band

我们注意到，对这两类请求，总是需要拼接多组查询结果的：

* 对于 JCC，包含 2 种情况：
  1. 一般的市：COL_DXCC = '339' and COL_CNTY in jcc_list，entity 编号为 COL_CNTY
  2. 政令市：COL_DXCC = '339' and COL_CNTY in ku_list，entity 编号为 LEFT(COL_CNTY, 4)

* 对于 JCG，包含 2 种情况：
  1. 一般的郡：COL_DXCC = '339' and COL_CNTY in jcg_list，entity 编号为 COL_CNTY
  2. 小笠原支庁：COL_DXCC in ('177', '192') ，entity 编号为 '10007'

* 对于 WAKU，包含 1 种情况：
  1. 一般的区：COL_DXCC = '339' and COL_CNTY in ku_list，entity 编号为 COL_CNTY

* 对于 AJA，包含 4 种情况：
  1. 市：COL_DXCC = '339' and COL_CNTY in jcc_list，entity 编号为 COL_CNTY
  2. 区：COL_DXCC = '339' and COL_CNTY in ku_list，entity 编号为 COL_CNTY
  3. 郡：COL_DXCC = '339' and COL_CNTY in jcg_list，entity 编号为 COL_CNTY
  4. 小笠原支庁：COL_DXCC in ('177', '192') ，entity 编号为 '10007'

#### 现状和修改要求
1. 对于生成 sql + bindings 对的函数
   * 返回值统一格式为：
     ```php
     return [
         'sql' => $sql,
         'bindings' => $bindings,
     ];
     ```

2. build_entity_status_union_all_sql
   * 现有函数只支持两个 sql 的 union all
   * 需要修改支持 n 个的 union all，即支持一个数组的 sql + bindings 对进行 union all
   * 函数名称要变改，现在的名称不够通用

3. build_entity_query_where_sql
   * 现有函数中 DXCC 被写死在函数里，只允许传入 entity_in_list_sql
   * 应当允许传入完整的条件 sql：
     * 类似于：`COL_DXCC = '339' and COL_CNTY in (...)`

4. query_export_qsos
   * 现有函数不支持 key_col，这会影响将来的 AJA 导出
   * 应当支持 key_col 的能力，即
     * 对于 sql 里 row_number() 窗口函数，partition by 应当为 entity, key_col
     * 对于 build_export_entity_source_query 中 select 的列，应当新增 band as key_col
     * 允许指定 key_col 的类型
     * 参考 entity_status 的 key_col

5. 对于 query_entity_status 和 query_export_qsos 两个完整 sql 的构造函数
   * 应当根据“拼接多组查询结果”的要求，支持抽象的多组查询的输入
     * 即，接受一个数组，每一行为一个包含 entity_expr、entity_cond 的数组
       ```php
       array(
        array(
          'entity_expr' => "COL_CNTY",
          'entity_cond' => "COL_DXCC = '339' and COL_CNTY in jcg_list",
        ),
        array(
          'entity_expr' => "'10007'",
          'entity_cond' => "COL_DXCC in ('177', '192')",
        ),
       )

6. 应在 query_entity_status 和 query_export_qsos 外，为 jcc/jcg/waku/aja 包装一个可以在 controller 调用的函数

7. 模型拆分安排：
   * 公共函数和 AJA 相关的放在 Aja_model 中
   * JCC/JCG/WAKU 可以有自己的 model，继承自 Aja_model
   * 请评估这一安排的合理性
