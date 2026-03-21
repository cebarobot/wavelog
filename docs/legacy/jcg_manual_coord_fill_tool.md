# JCG 坐标人工补全工具说明

## 1. 目的

为 `temp/jcg_with_coords.json` 中尚未有坐标（`lat/lon` 为空）的郡，提供一个可中断、可持续的交互式补全工具。

工具脚本：`temp/manual_fill_jcg_coords.py`

## 2. 功能

1. 自动定位 JSON 中缺坐标的条目。
2. 逐条显示关键信息：`code/name/pref/deleted/error`。
3. 接收你输入的一个或多个 Wikidata QID（如 `Q123` 或 `Q123 Q456`）。
4. 抓取每个 QID 的 `P625` 坐标，并打印抓取过程。
5. 若得到多个坐标点，自动计算平均值作为该郡坐标。
6. 你确认后立即写回 `temp/jcg_with_coords.json`（原子写入，避免中断损坏）。

## 3. 运行方式

按仓库约定，访问 Wikidata 需要代理：

```bash
proxychains -q python3 temp/manual_fill_jcg_coords.py
```

如果你希望使用特定 Python（例如虚拟环境），替换 `python3` 为对应解释器即可。

## 4. 交互输入

在每条记录提示符 `>` 后可输入：

1. QID 列表：`Q123`、`Q123 Q456`、`Q123,Q456`（空格/逗号/分号都支持）。
2. `skip`：先跳过当前条目，稍后再处理。
3. `quit`：退出程序（已确认并保存的条目不会丢失）。

## 5. 输出说明

抓取阶段会显示类似日志：

1. `[FETCH] GET ...`：正在请求的 Wikidata API 地址。
2. `[FETCH] Qxxx P625#n: lat=..., lon=...`：该 QID 的第 n 个坐标点。
3. `[RESULT] aggregated_points=...`：本次汇总坐标点总数。
4. `[RESULT] avg_lat=..., avg_lon=...`：平均后的坐标。
5. `[SAVED] code=...`：已写回 JSON。

## 6. 写回字段规则

确认保存后，脚本会更新当前条目：

1. `lat` / `lon`：写入平均坐标（保留 9 位小数）。
2. `wikidata_id`：写入本次使用 QID，多个 QID 用 `|` 连接。
3. `query_used`：
   - 单坐标点：`manual_wikidata_qid`
   - 多坐标点：`manual_wikidata_qid_avg`
4. `error`：清空为 `""`。

## 7. 注意事项

1. 脚本不会自动过滤 `deleted` 条目；是否补全由你在交互中决定。
2. 若某个 QID 没有 `P625`，脚本会提示并等待你重新输入。
3. 可随时 `Ctrl+C` 或 `quit` 中断，已保存内容会保留。
