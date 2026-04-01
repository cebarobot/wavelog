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

## JCC demo 细化

### 完成内容

* 按反馈将 slot 做了中度压缩，降低了高度并缩小了字重视觉占比。
* 将县编号和县名调整为同一行显示，统计保留在第二行。
* 为 slot 增加了 Bootstrap tooltip，悬停时显示完整编号、英文名、日文名，以及 deleted 或 designated 标记。
* 收敛了 demo view 的自定义 CSS，更多改为 Bootstrap 5 的布局类、badge 和文本类。

### 验证情况

* 新的 tooltip 输出和 view 结构已经过 PHP lint 检查。

## JCC demo 第三轮细化

### 完成内容

* 去掉了每个都道府县行头的 confirmed/worked/total 统计。
* 将 demo 的 entity_status 查询切换为 slot 级别的 key_col = none。
* 新增整体 summary 数据结构，只保留 confirmed、worked、total，并加入百分比和 progress bar。
* 将整体 summary 移到 grid 顶部左侧，图例收拢到同一行右侧。
* 未勾选 include deleted 时，不再显示 deleted 图例。
* 勾选 include deleted 时，新增 alert-info 提示当前不会校验 QSO 是否发生在删除日期之前。
* tooltip 中不再展示政令指定都市信息，并临时注释掉日文名展示。
* 修复 jcc_demo 缺少 user_map_custom 导致 header 顶部出现的 PHP warning 和 deprecated 报错。

### 验证情况

* 计划在本轮修改后重新执行 PHP lint、重建容器并验证页面输出。

## JCC demo summary 扁平化

### 完成内容

* 将 summary 区改为更扁平的单行指标布局，压缩了整体高度。
* 调整 summary 与 legend 的容器结构，优先保证在桌面宽度下同排显示。
* 保留 progress bar，但将高度缩小为更薄的一条。
* 在勾选 include deleted 时，summary 中于 total 后新增 deleted 数量。

### 验证情况

* 已完成编辑器错误检查和 PHP lint，后续继续通过页面输出验证布局结构。

## JCC slot hover 收敛

### 完成内容

* 将 JCC demo 中 slot 的 hover 从上浮加阴影，调整为更接近 Bootstrap 5 按钮的颜色和边框过渡。
* 去掉了 hover 时的 translateY 和重阴影效果。

### 验证情况

* 已完成编辑器错误检查和 PHP lint。
* 已确认运行页输出包含新的 hover 选择器，且不再包含旧的上浮位移样式。

## JCC slot Bootstrap 变量收敛

### 完成内容

* 将 slot 的 hover 和 focus 样式进一步改为基于 Bootstrap 颜色变量与 rgb 变量的实现。
* 为 slot 增加了更接近 Bootstrap 按钮的 focus-visible ring。

### 验证情况

* 已完成编辑器错误检查和 PHP lint。
* 已确认运行页输出包含基于 color-mix 和 Bootstrap 变量的 hover/focus 样式。

## JCC 正式页切换到 grouped grid

### 完成内容

* 让 awards/jcc 正式页改为复用 grouped grid 实现，不再渲染原来的 band table。
* 将地图 tab、地图按钮和 confirmed QSO 导出按钮并回 grouped grid 视图。
* 抽出 controller 内的共享渲染逻辑，使 awards/jcc 和 awards/jcc_demo 共用同一套 grouped page 数据准备。
* 调整 jcc.js 和 jccmap.js，使其兼容 grouped grid 页面，不再依赖旧表格和旧 tab 结构。

### 验证情况

* 计划在 watch 环境下验证 awards/jcc 正式页的 grouped results、map tab 和 export 行为。

## JCC 遗留清理与入口合并

### 完成内容

* 移除了 awards/jcc_demo 入口，不再保留 demo alias。
* 去掉了 JCC controller 中仅为 demo 服务的包装函数和共享 postdata 函数。
* 将 grouped grid 视图全部并回 awards/jcc/index.php，并删除 awards/jcc/demo.php。
* 清理了 JCC table 专用的 model 逻辑和 jcc.js 中的 DataTable 初始化残留。
* 修复了地图页对 include deleted 不生效的问题：当前 map 会依据 deleted 复选框过滤 JSON 列表中的 deleted 城市。

### 验证情况

* 已确认 awards/jcc 在 watch 环境下输出 grouped grid、map tab 和 export 按钮。
* 已确认 awards/jcc_demo 返回 404。

## JCC 地图颜色图例恢复

### 完成内容

* 在保留 Leaflet 图层开关的同时，恢复了地图上的颜色 legend。
* legend 放置在地图右下角，避免与右上角的图层控制冲突。
* 顺手修复了 jccmap.js 中 mapColor 的隐式全局变量问题。

### 验证情况

* 已确认 watch 环境下运行中的 jccmap.js 输出包含新的颜色 legend 代码。

## Docker Compose watch 开发流

### 完成内容

* 在 docker-compose.yml 的 wavelog-main 服务下新增 develop.watch 配置。
* 将源码同步规则按 application、assets、src、system 等目录拆分，减少了单个根目录 sync 所需的 ignore 配置。
* 对 install、images 以及 index.php、manifest.json、robots.txt 等根层运行时文件保留单独 sync，避免缩小 watch 范围后漏同步入口文件。
* 在 application 的 sync 规则中补充排除 config/development 和 config/docker，避免触达挂载配置目录和容器构建期生成目录。
* 保留 .config、uploads、userdata 的现有卷策略，不使用源码 bind mount。
* 将 Dockerfile 和 htaccess.sample 单独配置为 rebuild，以覆盖镜像构建期变更。
* 更新 AGENTS.md，将本地开发入口改为 docker compose watch，并补充自动同步、自动重建和重启边界说明。

### 验证情况

* 确认本地 docker compose 版本为 v2.23.3-desktop.2，满足 Compose Watch 的版本要求。
* 已执行 docker compose config，对更新后的 compose 文件做语法和模型校验，develop.watch 配置被正确解析。
* 在当前环境中验证到 `docker compose up --watch` 不可用，但 `docker compose watch` 可用且会按需构建并启动服务。
* 实测 watch sync 会把被触达文件的 owner 改成宿主机 UID:GID（当前环境为 1000:1000）；未触达的镜像内文件仍保持 root:www-data。