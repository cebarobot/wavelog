# 计划

## 当前阶段：JCC 基于 entity_status 输出表格/统计/地图

1. 梳理 JCC 页面、统计汇总和地图接口当前的数据流，确认重复查询位置。
2. 在 Controller 中统一先调用 `query_entity_status`，按 band / entity 两种维度分别复用结果。
3. 在 `Jcc_model` 中补充基于 `entity_status` 原始结果的表格、统计、地图整形函数。
4. 保持现有页面结构与前端接口格式不变，降低联动改动范围。
5. 完成后更新任务文档与变更记录，并做基础语法校验。