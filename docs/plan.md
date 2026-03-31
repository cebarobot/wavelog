# 实施计划：改进 JARL 日本业余无线电奖状

## 概述

本计划分为 4 个阶段，依次完成：
1. 重构 Jcc_model 公共函数，使其通用化
2. 实现 JCG 奖状
3. 实现 WAKU 奖状
4. 实现 AJA 奖状

---

## 阶段 1：扩展/重构 Jcc_model 中的公共函数

### 1.1 统一 SQL+bindings 对函数的返回值格式

**现状：** 函数通过 `&$bindings` 引用参数传递 bindings，只返回 SQL 字符串。

**涉及函数：**
- `build_entity_status_base_query()` — 第 196 行
- `build_export_entity_source_query()` — 第 432 行
- `build_entity_query_where_sql()` — 第 150 行

**修改方案：**
- 这些函数不再接收 `&$bindings` 参数
- 改为返回关联数组 `['sql' => $sql, 'bindings' => $bindings]`
- 调用这些函数的上层函数（如 `query_entity_status`、`query_export_qsos`）需要相应调整

### 1.2 改进 build_entity_status_union_all_sql

**现状：** 函数只支持两个 SQL 的 union all（第 239 行）。

**修改方案：**
- 重命名为 `build_union_all_sql`（更通用）
- 接收一个数组参数，每个元素是 `['sql' => ..., 'bindings' => ...]` 对
- 返回合并后的 `['sql' => ..., 'bindings' => ...]`
- 支持 1 到 N 个 SQL 的 union all

### 1.3 调整 build_entity_query_where_sql 的参数

**现状：** 函数第一个参数是 `$entity_in_list_sql`（仅 IN 列表的内容），DXCC='339' 在函数内部写死（第 155-158 行）。

**修改方案：**
- 第一个参数改为接收完整的 entity 条件 SQL，例如：
  - `"COL_DXCC = '339' and COL_CNTY in ('0101','0102',...)"` — 一般情况
  - `"COL_DXCC in ('177', '192')"` — 小笠原支庁特殊情况
- 函数内部删除写死的 `col_dxcc in ('339')` 和 `col_cnty in (...)` 两行
- 替换为直接使用传入的完整条件 SQL
- 参数名改为 `$entity_cond_sql`

### 1.4 让 query_export_qsos 支持 key_col

**现状：** `query_export_qsos()` 不支持 `key_col`，窗口函数 `partition by` 只用 `entity`。

**修改方案：**
- 给 `query_export_qsos()` 添加 `$key_col` 参数（默认 `'none'`）
- 给 `build_export_entity_source_query()` 添加 `$key_col` 参数：
  - 当 `key_col === 'band'`：SELECT 中增加 `build_band_key_expr() . ' as key_col'`
  - 当 `key_col === 'mode'`：SELECT 中增加 `build_mode_key_expr() . ' as key_col'`
  - 当 `key_col === 'none'`：不添加 key_col
- 调整 `row_number()` 窗口函数：
  - `key_col !== 'none'` 时，`partition by entity, key_col`
  - `key_col === 'none'` 时，`partition by entity`（保持现有行为）
- 最终 SELECT 中，`key_col !== 'none'` 时增加 `key_col` 列

### 1.5 让 query_entity_status 和 query_export_qsos 支持数组表达的多组查询

**现状：** 两个函数内部硬编码了 JCC + KU 两组查询的逻辑。

**修改方案：**
- 将这两个函数改造为接受一个 `$entity_queries` 数组参数
- 数组中每个元素包含：
  ```php
  [
    'entity_expr' => "COL_CNTY",              // 或 "LEFT(COL_CNTY, 4)" 或 "'10007'"
    'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (...)",  // 完整的条件
  ]
  ```
- 函数内部遍历数组，为每组构建 base query，然后 union all 合并
- entity_cond 中如果包含 entity IN list，则需要包含 bindings；但由于我们使用 `$this->db->escape()` 生成列表，所以不需要额外 bindings

**对于 query_entity_status：**
- 遍历每组 → `build_entity_status_base_query` → `build_entity_status_max_confirmed_group_by_sql`
- 对所有组的结果 → `build_union_all_sql`
- 外层再包一个 `build_entity_status_max_confirmed_group_by_sql`

**对于 query_export_qsos：**
- 遍历每组 → `build_export_entity_source_query`
- 对所有组的结果 → `build_union_all_sql`
- 外层做 `row_number()` 窗口排序 → 取 rn=1

### 1.6 包装 query_entity_status 和 query_export_qsos

在各自的 Model 中为 JCC/JCG/WAKU/AJA 创建包装函数，构造对应的 `$entity_queries` 数组，然后调用通用的 `query_entity_status` / `query_export_qsos`。

**JCC 的包装函数：**
```php
function build_jcc_entity_queries($postdata) {
    $jcc_data = $this->filter_entity_data($this->ja_cities, $postdata);
    $ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
    return [
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($jcc_data) . ")"],
        ['entity_expr' => 'LEFT(COL_CNTY, 4)', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($ku_data) . ")"],
    ];
}
```

**JCG 的包装函数：**
```php
function build_jcg_entity_queries($postdata) {
    $jcg_data = $this->filter_entity_data($this->ja_guns, $postdata);
    return [
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($jcg_data) . ")"],
        ['entity_expr' => "'10007'", 'entity_cond' => "COL_DXCC IN ('177', '192')"],
    ];
}
```

**WAKU 的包装函数：**
```php
function build_waku_entity_queries($postdata) {
    $ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
    return [
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($ku_data) . ")"],
    ];
}
```

**AJA 的包装函数：**
```php
function build_aja_entity_queries($postdata) {
    $jcc_data = $this->filter_entity_data($this->ja_cities, $postdata);
    $ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
    $jcg_data = $this->filter_entity_data($this->ja_guns, $postdata);
    return [
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($jcc_data) . ")"],
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($ku_data) . ")"],
        ['entity_expr' => 'COL_CNTY', 'entity_cond' => "COL_DXCC = '339' AND COL_CNTY IN (" . $this->build_entity_in_list_sql($jcg_data) . ")"],
        ['entity_expr' => "'10007'", 'entity_cond' => "COL_DXCC IN ('177', '192')"],
    ];
}
```

### 1.7 模型拆分安排

**评估：**

将公共函数和 AJA 放在 Aja_model 中，JCC/JCG/WAKU 继承自 Aja_model 的安排是合理的，原因：
- AJA 是最全面的奖状（覆盖所有实体类型：市、区、郡、小笠原），其查询是其他奖状的超集
- JCC、JCG、WAKU 各自只是 AJA 的子集
- 公共 SQL 构建、查询执行、数据过滤等逻辑放在 Aja_model 中，避免代码重复
- 各子 Model 只需定义自己的 entity_queries 构建方法和展示层方法

唯一需要注意的是：Aja_model 既是基类又对应具体的 AJA 奖状，在命名上可能会造成些许混淆，但由于 CodeIgniter 3 不支持抽象模型类（需要可以直接实例化），这是一个可接受的折中。

**拆分方案：**

**Aja_model（基类+AJA 奖状）：**
- 加载所有 JSON 数据（jcc_list, ku_list, jcg_list）
- 公共函数：
  - `filter_entity_data()`
  - `build_band_key_expr()` / `build_mode_key_expr()`
  - `get_qsl_condition_sql()` / `get_qsl_confirmed_expr()`
  - `build_entity_in_list_sql()`
  - `build_entity_query_where_sql()`
  - `build_entity_status_base_query()`
  - `build_entity_status_max_confirmed_group_by_sql()`
  - `build_union_all_sql()`
  - `build_export_entity_source_query()`
  - `query_entity_status()`
  - `query_export_qsos()`
- AJA 特有函数：
  - `build_aja_entity_queries()`
  - `get_aja_array()` / `get_aja_summary()` / `get_aja_map_array()` / `get_aja_export()`

**Jcc_model（继承 Aja_model）：**
- JCC 特有函数：
  - `build_jcc_entity_queries()`
  - `get_jcc_array()` / `get_jcc_summary()` / `get_jcc_map_array()` / `get_jcc_export()`

**Jcg_model（继承 Aja_model）：**
- JCG 特有函数：
  - `build_jcg_entity_queries()`
  - `get_jcg_array()` / `get_jcg_summary()` / `get_jcg_map_array()` / `get_jcg_export()`

**Waku_model（继承 Aja_model）：**
- WAKU 特有函数：
  - `build_waku_entity_queries()`
  - `get_waku_array()` / `get_waku_summary()` / `get_waku_map_array()` / `get_waku_export()`

---

## 阶段 2：实现 JCG 奖状

### 2.1 创建 Jcg_model

文件：`application/models/Jcg_model.php`

继承 Aja_model，实现：
- `build_jcg_entity_queries($postdata)`：构建 JCG 的两组查询（一般郡 + 小笠原支庁）
- `get_jcg_array($bands, $postdata, $entity_status = null)`：表格数据
- `get_jcg_summary($bands, $postdata, $entity_status = null)`：汇总数据
- `get_jcg_map_array($postdata, $entity_status = null)`：地图数据
- `get_jcg_export($postdata)`：导出数据

JCG 查询条件（来自 task.md）：
1. 一般郡：`COL_DXCC = '339' AND COL_CNTY IN (jcg_list)`，entity = `COL_CNTY`
2. 小笠原支庁：`COL_DXCC IN ('177', '192')`，entity = `'10007'`

### 2.2 Controller 方法

在 `application/controllers/Awards.php` 中添加：
- `jcg()` — 主页面，参照 `jcc()` 实现
- `jcg_export()` — CSV 导出，参照 `jcc_export()` 实现
- `jcg_map()` — 地图 AJAX 端点，参照 `jcc_map()` 实现

### 2.3 View 文件

创建 `application/views/awards/jcg/index.php`：
- 参照 JCC 的 view，修改：
  - 奖状信息改为 JCG 相关内容
  - 表单提交地址改为 `awards/jcg`
  - 表格列头改为 "Number" / "Gun"（郡）
  - "Deleted cities" 改为 "Deleted guns"
  - 地图按钮改为 "Show JCG Map"
  - 导出按钮文案不变

### 2.4 JavaScript 文件

创建：
- `assets/js/sections/jcg.js` — 参照 `jcc.js`，修改 DataTable ID 和导出 URL
- `assets/js/sections/jcgmap.js` — 参照 `jccmap.js`，修改：
  - AJAX URL 改为 `awards/jcg_map`
  - JSON 数据源改为 `jcg_list.json`
  - 地图 div ID 改为 `jcgmap`
  - click 事件中类型改为 `'JCG'`

### 2.5 数据库迁移

创建迁移文件添加 `jcg` 列到 `bandxuser` 表：
- 参照 `183_jcc_bandxuser.php`

### 2.6 集成修改

- **header.php**：在 Japan 下拉菜单中添加 JCG 链接
- **Logbook_model.php**：在 `qso_details()` 的 switch 中添加 `case 'JCG'`
- **awards_helper.php**：添加 `awards_render_jcg_cell()` 函数
- **Bands.php**：`saveBand()` 方法中添加 `jcg` 字段处理

### 2.7 注意小笠原特殊处理

小笠原支庁 (entity '10007') 在 jcg_list.json 中有对应条目（需验证）。如果没有：
- 需要在 jcg_list.json 中补充 '10007' 的条目
- 或者在 Jcg_model 中单独处理

地图显示时需要为 '10007' 提供坐标。查看 qso_details_ajax 处理 JCG 类型时，小笠原的 DXCC 是 177/192（而非 339），所以 QSO 详情查询可能需要特殊处理。

---

## 阶段 3：实现 WAKU 奖状

### 3.1 创建 Waku_model

文件：`application/models/Waku_model.php`

继承 Aja_model，实现：
- `build_waku_entity_queries($postdata)`：构建 WAKU 的查询（仅 KU 区）
- `get_waku_array($bands, $postdata, $entity_status = null)`：表格数据
- `get_waku_summary($bands, $postdata, $entity_status = null)`：汇总数据
- `get_waku_map_array($postdata, $entity_status = null)`：地图数据
- `get_waku_export($postdata)`：导出数据

WAKU 查询条件：
1. 一般的区：`COL_DXCC = '339' AND COL_CNTY IN (ku_list)`，entity = `COL_CNTY`

### 3.2 Controller 方法

- `waku()` — 主页面
- `waku_export()` — CSV 导出
- `waku_map()` — 地图 AJAX 端点

### 3.3 View 和 JS 文件

- `application/views/awards/waku/index.php`
- `assets/js/sections/waku.js`
- `assets/js/sections/wakumap.js`

修改要点类似 JCG，但实体名称改为 "Ku"（区）。

### 3.4 数据库迁移、集成修改

与 JCG 类似：
- 添加 `waku` 列到 `bandxuser`
- header.php 导航
- Logbook_model 添加 `case 'WAKU'`
- awards_helper.php 添加 `awards_render_waku_cell()`
- Bands.php saveBand 处理

---

## 阶段 4：实现 AJA 奖状

### 4.1 Aja_model 中的 AJA 相关函数

AJA 有 4 组查询（最复杂的奖状）：
1. 市：`COL_DXCC = '339' AND COL_CNTY IN (jcc_list)`，entity = `COL_CNTY`
2. 区：`COL_DXCC = '339' AND COL_CNTY IN (ku_list)`，entity = `COL_CNTY`
3. 郡：`COL_DXCC = '339' AND COL_CNTY IN (jcg_list)`，entity = `COL_CNTY`
4. 小笠原：`COL_DXCC IN ('177', '192')`，entity = `'10007'`

实现：
- `build_aja_entity_queries($postdata)`
- `get_aja_array($bands, $postdata, $entity_status = null)`
- `get_aja_summary($bands, $postdata, $entity_status = null)`
- `get_aja_map_array($postdata, $entity_status = null)`
- `get_aja_export($postdata)` — 需要使用 `key_col = 'band'`

### 4.2 AJA 导出的特殊性

AJA 的导出需要按 entity + band 去重（每个实体在每个频段取最早的 QSO），而 JCC/JCG/WAKU 的导出只按 entity 去重。这就是 1.4 中 key_col 支持的原因。

### 4.3 Controller 方法

- `aja()` — 主页面
- `aja_export()` — CSV 导出
- `aja_map()` — 地图 AJAX 端点

### 4.4 View 和 JS 文件

- `application/views/awards/aja/index.php`
- `assets/js/sections/aja.js`
- `assets/js/sections/ajamap.js`

AJA 视图需要显示所有类型（市、区、郡），表格中可能需要区分实体类型。

### 4.5 AJA 地图特殊考虑

AJA 地图需要同时显示市、区、郡的标记，需要从多个 JSON 文件加载坐标数据。

### 4.6 数据库迁移、集成修改

与其他奖状类似：
- 添加 `aja` 列到 `bandxuser`
- header.php 导航
- Logbook_model 添加 `case 'AJA'`
- awards_helper.php 添加 `awards_render_aja_cell()`
- Bands.php saveBand 处理

---

## 跨阶段共通修改

### 导航菜单（header.php）

在 Japan 下拉菜单中，最终结构：
```
🇯🇵 Japan
├── WAJA
├── ───────
├── JCC
├── JCG
├── WAKU
├── AJA
├── ───────
└── JA Gridmaster
```

### Logbook_model.php qso_details()

添加 case：
- `'JCG'`：`COL_CNTY` + `COL_DXCC = '339'`（与 JCC 类似）
  - 注意：小笠原的 DXCC 不是 339，可能需要特殊处理
- `'WAKU'`：`COL_CNTY` + `COL_DXCC = '339'`
- `'AJA'`：`COL_CNTY` + `COL_DXCC = '339'`（小笠原同上）

### Bands.php

- `saveBand()` 添加 jcg、waku、aja 字段
- 确保 `get_user_bands()` 支持这些新的 award 类型（现有实现已通过参数化方式支持）

### 迁移文件

需要 3 个新迁移文件（JCG、WAKU、AJA），为 bandxuser 表添加列。

---

## 实施顺序建议

1. **阶段 1**（重构）→ 先完成，确保 JCC 功能不受影响（测试！）
2. **阶段 3**（WAKU）→ 最简单，只有一组查询，先实现可以验证框架
3. **阶段 2**（JCG）→ 有两组查询，包含小笠原特殊情况
4. **阶段 4**（AJA）→ 最复杂，4 组查询 + key_col 导出

---

## 风险与注意事项

1. **小笠原支庁的 DXCC 问题**：DXCC 177（Minami Torishima）和 192（Ogasawara）不是 339（Japan），在 qso_details_ajax 中点击单元格查看详情时需要正确处理
2. **jcg_list.json 中的 10007**：需要确认此条目是否已存在，是否有正确的坐标
3. **阶段 1 的向后兼容**：重构公共函数后必须确保 JCC 的所有功能正常运作
4. **AJA 的实体重复**：同一个 COL_CNTY 可能同时出现在 jcc_list 和 ku_list 中（例如政令市），在 AJA 的 4 组查询中要注意不重复计数
5. **性能**：AJA 有 4 组 UNION ALL，在 QSO 较多时可能有性能问题
