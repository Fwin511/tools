# Feiyun Tools Task Templates

Use these templates to start tasks quickly with `$feiyun-tools-maintainer` and reduce unnecessary context loading.

## How To Use

1. Copy one template block.
2. Replace placeholders in `{...}`.
3. Keep the scope path-limited (for example only `tools/auto-filter/**`).
4. Require explicit validation command in the prompt.

## Template: Session Bootstrap (New Agent)

```text
使用 $feiyun-tools-maintainer。
按标准启动流程：先读取 AGENTS.md、SKILL.md、references/project-map.md、references/agent-standard-workflow.md。
本次只处理：{目标路径列表}。
禁止全仓扫描；仅在必要时读取相关文件。
完成后执行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh {fast|auto-filter|full}
并记录：
php scripts/changelog-manager.php add --type {fixed|added|changed|refactored|review|docs} --scope "{模块}" --summary "{摘要}" --files "{文件1,文件2}" --validation "{验证命令}"
```

## Template: Bug Fix

```text
使用 $feiyun-tools-maintainer 修复 bug。
问题描述：{现象/报错/期望行为}
影响范围：{src|tools/auto-filter|tools/sql-migration|tests}
只允许修改：{具体路径，尽量小范围}
先读：AGENTS.md、SKILL.md、references/project-map.md。
完成代码后先记录：
php scripts/changelog-manager.php add --type fixed --scope "{模块}" --summary "{修复摘要}" --files "{文件1,文件2}" --validation "{验证命令}"
完成后运行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh {fast|auto-filter|full}
输出：根因、修改点、兼容性影响、验证结果。
```

## Template: New Feature

```text
使用 $feiyun-tools-maintainer 实现新功能。
需求：{功能描述}
约束：保持向后兼容；不改动未授权模块。
仅处理模块：{auto-filter|sql-migration|shared src}
先给出最小实现路径，然后直接改代码与文档。
完成代码后先记录：
php scripts/changelog-manager.php add --type added --scope "{模块}" --summary "{功能摘要}" --files "{文件1,文件2}" --validation "{验证命令}"
完成后运行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh {fast|full}
输出：变更文件、行为变化、风险点、验证命令结果。
```

## Template: Refactor

```text
使用 $feiyun-tools-maintainer 做重构。
目标：{可读性|复用性|解耦|性能}
不改变外部行为：{是/否，默认是}
限定路径：{具体目录}
需要补齐：{测试|文档|注释}
完成代码后先记录：
php scripts/changelog-manager.php add --type refactored --scope "{模块}" --summary "{重构摘要}" --files "{文件1,文件2}" --validation "{验证命令}"
完成后运行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh {fast|full}
输出：重构前后差异、是否影响 API、验证结果。
```

## Template: Release Prep

```text
使用 $feiyun-tools-maintainer 做发版前检查。
目标版本：{version}
检查范围：composer.json、src/ToolsManager.php、providers publish、tools/* 文档、README 双语一致性。
按 references/change-checklist.md 全量检查。
发布前先执行：
php scripts/changelog-manager.php validate
执行发版归档：
php scripts/changelog-manager.php release --version {version} --date {YYYY-MM-DD}
执行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh full
输出：可发版/不可发版结论，阻塞项清单，建议修复顺序。
```

## Template: Code Review

```text
使用 $feiyun-tools-maintainer 评审以下改动：{PR/commit/文件列表}
优先找：行为回归、兼容性破坏、配置键变更风险、测试缺口。
按严重级别输出发现，并附文件定位。
若无问题，明确写“未发现阻塞问题”，再给残余风险。
评审完成后记录：
php scripts/changelog-manager.php add --type review --scope "{模块}" --summary "{评审结论摘要}" --files "{评审文件}" --validation "{已执行检查}"
```

## Template: Ultra Lean Context

```text
使用 $feiyun-tools-maintainer。
只读：AGENTS.md、SKILL.md、references/project-map.md、{目标文件列表}。
禁止全仓扫描；禁止读取无关文档。
完成改动后仅运行：./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh fast
```
