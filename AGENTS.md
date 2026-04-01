# wavelog

## Basic Info
Wavelog is a self-hosted PHP application that allows you to log your amateur radio contacts anywhere.

* Using PHP Codeigniter version 3
* Using Bootstrap 5 for the user CSS framework

## Style
* 如无特殊说明，函数和变量通常使用下划线 snake_case 风格。
* 严格限制兜底代码的使用，无必要不加入兜底代码。

# Task
Refer to docs/task.md

## Documentation
* Please put all documentations into docs/ folder.
  * Ignore docs/legacy/ folder.
* docs/plan.md is plan of the whole task
* docs/history.md is what the agent did

## 调试
### python 脚本
* 访问 wikidata 时，请在命令前加 proxychains
* python 用全局的 python 即可。
* 各类 python 临时脚本放在 temp 文件夹下。

### PHP cli
* 本地没有安装 php，请使用 docker 来调用 php-cli（无需 sudo）。

### 网站部署测试
在工作目录下：
<!-- 1. 设置代理： `export https_proxy="http://172.31.80.1:7897"`、`export http_proxy="http://172.31.80.1:7897"` -->
2. 开发模式启动：`docker compose watch`

* 开发时请保持该命令在单独终端中运行。
* 用户可能已经运行了该命令，开始前，请先检查 watch 是否已经在工作
* `docker compose watch` 会按需构建并启动服务，然后进入 watch 模式。
* 修改普通代码后，`wavelog-main` 会自动同步，不需要手动执行 `docker compose build` 和 `docker compose up -d`。
* 修改 `Dockerfile` 或 `htaccess.sample` 后，watch 会自动重建 `wavelog-main`。
* 普通代码同步后不需要重新登录；只有容器被自动重建后才可能需要重新登录。
* 如果修改了 `docker-compose.yml`，需要重新执行 `docker compose watch`。
* 访问网址：127.0.0.1:8086
* 用户名：asdf
* 密码：asdfasdf
* 你可能需要用一些工具来模拟浏览器访问

## language

* 总是使用中文和我交流。
* 请用中文书写所有的 docs 中的文档。
* 代码、注释、提交信息中总是使用英文。

# Note
* Use #tool:vscode/askQuestions to clarify intent with the user.
* 时不时地回顾 task.md，可能有针对你的行为做出的新的指示
* 我有时也会编辑文件来帮助你解决一些问题。