# 2026-04-01

## JCC grouped grid demo

### 完成内容

* 新增独立页面 awards/jcc_demo，用于展示 JCC grouped grid demo。
* 保持原有 awards/jcc 页面、地图、导出逻辑不变。
* 在 Aja_model 中补充日本都道府县编号到名称的共用映射，供新 UI 分组标题使用。
* 在 Jcc_model 中新增 grouped grid formatter，将 JCC 状态整理为“都道府县 -> slots”的层级结构。
* 在 awards helper 中新增 grouped grid slot 渲染函数，复用现有 QSO 详情弹窗链路。
* 新建 awards/jcc/demo view，采用按都道府县分组、slot 自动换行的布局。
* 新 demo 首版不包含地图 tab，不包含导出按钮。
* 新 demo 移除了 worked、confirmed、notworked 三个状态筛选，仅保留 QSL 类型、band、mode、include deleted。

### 验证情况

* VS Code 错误检查通过，相关 PHP 文件无静态错误。
* 使用 PHP CLI 容器对修改后的 model、helper、controller、view 全部执行了 php -l，语法通过。
* 重新构建并部署了 wavelog-main 容器。
* 使用测试账号登录后，请求 awards/jcc_demo，已确认返回新页面标题、Classic JCC View 按钮以及 grouped slot 标记。