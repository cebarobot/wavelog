# JCG Deleted 郡坐标 LLM 自动补全工具

## 1. 目的

对 `temp/jcg_with_coords.json` 中满足以下条件的条目自动补全坐标：

1. `lat/lon` 为空
2. `error == name_matched_but_no_coord_deleted`

脚本：`temp/fill_deleted_coords_with_llm.py`

## 2. 处理流程

每个目标郡按如下步骤执行：

1. 访问日文维基条目（如 `北会津郡`）。
2. 将条目文本发送给 LLM，提取“消灭时下辖的町/村”。
3. 在郡条目中查找这些町/村对应链接。
4. 访问各町/村页面，提取其 Wikidata QID。
5. 拉取 QID 的 `P625` 坐标并求平均。
6. 回写当前郡坐标到 `jcg_with_coords.json`。

脚本会在每处理完一个条目后立即写盘，便于随时中断。

## 3. LLM 配置

脚本使用 OpenAI 兼容接口，需配置环境变量：

1. `LLM_API_KEY`（必填，或使用 `OPENAI_API_KEY`）
2. `LLM_MODEL`（可选，默认 `gpt-4.1-mini`）
3. `LLM_API_URL`（可选，默认 `https://api.openai.com/v1/chat/completions`）

## 4. 运行方式

访问维基和 Wikidata 时请走代理：

```bash
proxychains -q python3 temp/fill_deleted_coords_with_llm.py --code 07008
```

常用参数：

1. `--code 07008`：仅处理指定 JCG 编号。
2. `--limit 20`：最多处理 20 条。
3. `--dry-run`：只执行流程和日志，不写文件。
4. `--input temp/jcg_with_coords.json`：自定义输入文件路径。
5. `--confirm`：每条写入前人工确认（回车默认 Y）。

示例：

```bash
proxychains -q python3 temp/fill_deleted_coords_with_llm.py --limit 30
proxychains -q python3 temp/fill_deleted_coords_with_llm.py --dry-run --code 07008
proxychains -q python3 temp/fill_deleted_coords_with_llm.py --code 07008 --confirm
```

## 5. 写回字段

成功时更新：

1. `lat` / `lon`
2. `wikidata_id`（多个 QID 用 `|` 连接）
3. `query_used`：`llm_wikipedia_deleted_subunits` 或 `llm_wikipedia_deleted_subunits_avg`
4. `error` 清空为 `""`

失败时更新：

1. `query_used=llm_wikipedia_deleted_subunits`
2. `error` 写入失败原因（如 `llm_no_municipalities`）
