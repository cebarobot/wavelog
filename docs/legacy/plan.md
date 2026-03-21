# Wavelog JCG 功能实现方案

## 1. 背景与目标

计划在 Wavelog 中新增 **JCG (Japan Century Guns Award)** 奖状能力。

目标是复用现有 JCC 奖状的实现模式，提供：

- JCG 奖状统计页面（Worked / Confirmed / Not worked）
- 按 Band/Mode/QSL 类型过滤
- JCG 地图展示
- 导出（CSV）
- 点击表格/地图后查看匹配 QSO 详情
- 与 Band 设置联动（可控制哪些波段参与 JCG 统计）

本次范围：

- 仅实现 JCG（不包含 WAGA）
- JARL 列表中的 deleted Gun（带 `*`）默认纳入统计
- JCG 数据首版放在 `Jcg_model.php`（与 JCC 一致）

## 2. 现有 JCC 实现参考

JCC 的关键实现链路如下：

1. 控制器入口
- `application/controllers/Awards.php`
- 关键方法：`jcc()`、`jcc_export()`、`jcc_cities()`、`jcc_map()`

2. 业务模型
- `application/models/Jcc_model.php`
- 内置 JCC 列表（编号、名称、坐标）
- 基于 `COL_DXCC=339` + `COL_CNTY` 进行 Worked/Confirmed 统计

3. 页面与交互
- `application/views/awards/jcc/index.php`
- `assets/js/sections/jcc.js`
- `assets/js/sections/jccmap.js`

4. QSO 详情弹窗联动
- `application/models/Logbook_model.php`
- `displayContacts` 中 `case 'JCC'`

5. 菜单与 Band 联动
- 菜单：`application/views/interface_assets/header.php`
- Band 开关：`bandxuser.jcc`（迁移 + Band 页面 + 保存逻辑 + JS）

## 3. JCG 数据设计

### 3.1 数据来源

- 规则说明：
  - `https://www.jarl.org/English/4_Library/A-4-2_Awards/award_list.htm#JCG`
- 官方 JCG 列表：
  - `https://www.jarl.org/English/4_Library/A-4-5_jcc-jcg/jcg-list.txt`

### 3.2 数据结构

在 `Jcg_model.php` 中维护数组，结构与 JCC 对齐：

```php
public $jaGuns = array(
  '01001' => array('name' => 'Akan', 'lat' => ..., 'lon' => ...),
  ...
);
```

建议在内部保留（或可扩展）以下元信息用于后续维护：

- `deleted`（是否 deleted Gun）
- `pref_code` / `pref_name`
- `source`（坐标来源）

首版统计逻辑按既定决策：**全部纳入**（不区分 deleted 与 active）。

### 3.3 坐标补全策略

- 基于 JARL `jcg-list.txt` 先解析出完整 Gun 编号与名称
- 再用 Wikidata/Wikipedia 自动抓取坐标
- 自动抓取失败的条目人工补齐
- 最终人工复核后写入 `Jcg_model.php`

## 4. 代码接入设计

### 4.1 模型层

新增：`application/models/Jcg_model.php`

实现方法（命名可与 JCC 对应）：

- `get_jcg_array($bands, $postdata)`
- `get_jcg_summary($bands, $postdata)`
- `getJcgWorked(...)`
- `getJcgConfirmed(...)`
- `exportJcg($postdata)`
- `fetch_jcg_wkd($postdata)`
- `fetch_jcg_cnfm($postdata)`
- `jcgGuns()`（或 `jcgCities()`，建议命名为 `jcgGuns`）

过滤原则：

- `COL_DXCC = 339`（日本）
- `COL_CNTY` 必须是合法 JCG 编号（存在于 `jaGuns`）

### 4.2 控制器层

修改：`application/controllers/Awards.php`

新增方法：

- `jcg()`
- `jcg_export()`
- `jcg_map()`
- `jcg_guns()`（或保持 `jcg_cities()` 风格）

行为要求与 JCC 一致：

- 同样支持 band/mode/qsl/lotw/eqsl/qrz/clublog/worked/confirmed/notworked 参数
- 返回格式尽量与 JCC 对齐，复用前端交互模式

### 4.3 视图与前端 JS

新增文件：

- `application/views/awards/jcg/index.php`
- `assets/js/sections/jcg.js`
- `assets/js/sections/jcgmap.js`

实现方式：

- 以 JCC 文件为模板复制改造
- 更新页面文案为 JCG
- 更新 API 路径为 `awards/jcg_*`
- 地图点击 drill-down 的奖状类型参数改为 `JCG`

### 4.4 QSO 详情联动

修改：`application/models/Logbook_model.php`

在 `displayContacts` 的 switch 中新增：

- `case 'JCG'`：
  - `COL_CNTY = searchphrase`
  - `COL_DXCC = 339`

确保表格与地图点击后能打开正确的 QSO 详情。

### 4.5 菜单与 Band 配置联动

1. 奖状菜单
- 修改 `application/views/interface_assets/header.php`
- Japan 子菜单下新增 JCG 入口

2. `bandxuser` 增加 `jcg` 字段
- 新建迁移（参考 `183_jcc_bandxuser.php` / `251_add_wapc_bandxuser.php`）

3. Band 设置链路同步更新
- `application/controllers/Band.php`（接收/保存 `jcg`）
- `application/models/Bands.php`（`saveBand` 映射中加入 `jcg`）
- `application/views/bands/index.php`（新增 JCG 列和总开关）
- `assets/js/sections/bands.js`（保存 payload 增加 `jcg`）

这样可直接复用：

- `get_worked_bands('jcg')`

## 5. 国际化与文档

1. 文案
- 在 `assets/lang_src/messages.pot` 增加 JCG 相关文本（标题、Award Info、按钮文案等）

2. 语言更新
- 按项目既有流程更新/编译翻译资源

3. 维护文档
- 在 `docs/` 追加 JCG 数据维护说明：
  - 数据来源
  - 坐标更新方法
  - 变更校验步骤

## 6. 实施步骤（建议顺序）

1. 梳理并准备 JCG 基础数据（编号、名称、状态）
2. 补齐坐标并人工复核
3. 新建 `Jcg_model.php` 并完成后端查询/汇总/导出
4. 在 `Awards.php` 接入 JCG 控制器方法
5. 新增 JCG 视图与 JS
6. 在 `Logbook_model` 增加 `case 'JCG'`
7. 增加 `bandxuser.jcg` 迁移与 Band 设置链路
8. 增加菜单入口与 i18n 文案
9. 回归测试 JCC 与其他奖状

## 7. 验证清单

1. 数据完整性
- JCG 编号唯一
- 数量与官方列表一致
- 坐标字段有效（可绘制）

2. 页面功能
- 过滤器（Band/Mode/QSL 类型）工作正常
- Worked/Confirmed/Not worked 显示正确
- Summary 数字与 SQL 抽样一致

3. 导出功能
- `jcg_export` CSV 内容正确（编号、名称、首个匹配 QSO 信息）

4. 地图功能
- `jcg_map` 与 `jcg_guns` 接口返回正常
- marker 数量与状态统计一致
- 点击 marker 可打开正确的 QSO 列表

5. Band 开关
- 在 Band 设置中关闭 JCG 后，对应波段不参与 JCG 统计

6. 回归
- JCC 功能（页面、导出、地图、详情弹窗）不受影响

## 8. 风险与后续优化

1. 风险
- JCG 坐标抓取自动化存在缺失，需人工补全
- JCC/JCG 逻辑重复较多，后续维护成本偏高

2. 可选优化（后续）
- 抽取 JP 奖状共用查询逻辑（JCC/JCG）
- 将静态数组改为可生成的数据文件（例如 JSON + 生成脚本）
- 视需求增加“仅 active / 包含 deleted”的过滤开关
