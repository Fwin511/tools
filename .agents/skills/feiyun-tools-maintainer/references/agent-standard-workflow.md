# Agent Standard Workflow

Use this as the mandatory SOP for any new agent session in this repository.

## 1) Session Bootstrap (Mandatory)

At session start, do exactly this:

1. Activate `$feiyun-tools-maintainer`.
2. Read only:
   - `AGENTS.md`
   - `.agents/skills/feiyun-tools-maintainer/SKILL.md`
   - `.agents/skills/feiyun-tools-maintainer/references/project-map.md`
   - this file (`references/agent-standard-workflow.md`)
3. Confirm target scope paths with the user.
4. Avoid full-repo scan unless user explicitly asks.
5. State planned validation mode (`fast`, `auto-filter`, or `full`).

## 2) Context Budget Rules

- Load files by path scope, not by directory-wide browsing.
- Prefer the minimum set of files needed to complete the current task.
- Do not preload unrelated docs.
- Keep responses focused on changed modules and verification status.

## 3) Standard Execution By Scenario

### Bug Fix

- Reproduce or infer root cause from local code and tests.
- Patch minimal scope.
- Add/adjust tests if behavior changes.
- Record changelog entry as `--type fixed`.

### New Feature

- Keep backward compatibility unless user asks for breaking changes.
- Update module docs and examples.
- Record changelog entry as `--type added` or `--type changed`.

### Refactor

- Preserve external behavior by default.
- Explain risk areas and test coverage gaps.
- Record changelog entry as `--type refactored`.

### Code Review

- Focus findings first: regression, compatibility, missing tests.
- If no blockers, state that explicitly.
- Record review outcome as `--type review`.

### Release Prep

- Run changelog validation.
- Run full maintenance checks.
- Archive unreleased entries into a version block.

## 4) Changelog Discipline (Mandatory)

After each completed task, run:

```bash
php scripts/changelog-manager.php add \
  --type <fixed|added|changed|refactored|docs|review|security|removed> \
  --scope "<module>" \
  --summary "<one-line summary>" \
  --files "<path1,path2>" \
  --validation "<command>"
```

Before release:

```bash
php scripts/changelog-manager.php validate
php scripts/changelog-manager.php release --version x.y.z --date YYYY-MM-DD
```

## 5) Final Response Contract

Final response must include:

- Changed files grouped by module.
- Compatibility impact.
- Commands run and pass/fail status.
- Skipped checks and reasons (if any).

## 6) New Session Prompt Template

```text
使用 $feiyun-tools-maintainer。
按标准启动流程：先读取 AGENTS.md、SKILL.md、references/project-map.md、references/agent-standard-workflow.md。
本次只处理：{目标路径}。
禁止全仓扫描；只在必要时读取相关文件。
完成后执行：{fast|auto-filter|full} 检查，并写入 changelog entry。
```

