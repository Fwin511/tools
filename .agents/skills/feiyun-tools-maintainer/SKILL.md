---
name: feiyun-tools-maintainer
description: Maintain and extend the feiyun/tools Hyperf package with module-aware checks and safe change workflow. Use when working in this repository on bug fixes, feature changes, refactors, release prep, or docs updates that touch src/, tools/, tests/, composer.json, Makefile, or README files.
---

# Feiyun Tools Maintainer

## Overview

Use this skill to maintain `feiyun/tools` with consistent structure, backward-compatible behavior, and repeatable validation. Follow it for any code or documentation change in the package.

## Quick Start

1. Identify the change scope by touched paths.
2. Read [references/agent-standard-workflow.md](references/agent-standard-workflow.md) and [references/project-map.md](references/project-map.md) before editing.
3. Apply module-specific rules in this file.
4. Record each completed task via `php scripts/changelog-manager.php add ...`.
5. Run `scripts/run-maintenance-checks.sh` in the correct mode.
6. Run final checks from [references/change-checklist.md](references/change-checklist.md).

## Scope Routing

- If paths include `tools/auto-filter/**`, prioritize Auto Filter rules and alias behavior checks.
- If paths include `tools/sql-migration/**`, prioritize SQL migration idempotency and notify flow checks.
- If paths include `src/Providers/**` or `src/ToolsManager.php`, validate tool registration and publish metadata.
- If paths include `composer.json`, validate autoload namespaces and script command usability.
- If paths include `README.md` or `README_CN.md`, keep bilingual docs aligned and verify language switch commands.

## Core Maintenance Rules

1. Preserve package API contracts:
   - Keep namespaces under `Feiyun\\Tools\\...`.
   - Keep service provider wiring compatible with Hyperf 2.x/3.x expectations in code.
   - Keep tool keys and metadata stable unless an explicit breaking change is requested.
2. Preserve module boundaries:
   - Shared package wiring stays in `src/`.
   - Tool internals stay under `tools/<tool-name>/`.
3. Prefer additive changes over breaking behavior, especially for:
   - `AutoFilterTrait::scopeAutoFilter(...)` signature and alias parsing.
   - SQL migration config keys under `sql-migration.*`.
4. Keep docs and code consistent in the same change set.

## Auto Filter Rules

- Preserve `_as_` alias behavior for both direct fields and relation fields.
- Keep exclusion of system params that start with `_`, while allowing `_as_` fields.
- Validate relation path handling (`whereHas` and `whereHasMorph`) after changes.
- Keep field type behavior aligned with `QueryBuilder::buildWhere` rules.
- Avoid introducing ambiguous column names; keep qualified field behavior.

## SQL Migration Rules

- Keep idempotency based on `serial_number`.
- Keep listener guard behavior (single sync cycle and worker filtering).
- Keep notifier behavior tolerant to non-JSON responses and strict for `code` when present.
- Keep default store/notifier bindings overridable via interfaces.

## Validation Workflow

Run one of:

```bash
# Full validation
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh full

# Fast validation (metadata + syntax)
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh fast

# Auto Filter focused
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter
```

If tests cannot run due to environment limitations, state what was skipped and why.

## Required References

- Read [references/project-map.md](references/project-map.md) for architecture and ownership map.
- Read [references/agent-standard-workflow.md](references/agent-standard-workflow.md) for cross-session standard execution protocol.
- Read [references/change-checklist.md](references/change-checklist.md) before finalizing any change.
- Use [references/task-templates.md](references/task-templates.md) to bootstrap common maintenance tasks with minimal context.
- Follow [references/changelog-workflow.md](references/changelog-workflow.md) to keep release notes auditable and up to date.

## Output Expectations

- Return a concise change summary grouped by module.
- Include exact commands run for validation and the observed status.
- Call out potential compatibility impact for Hyperf and package consumers.
