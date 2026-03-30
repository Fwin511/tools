# Feiyun Tools Change Checklist

## Before Editing

- Confirm target module from changed paths (`src/`, `tools/auto-filter/`, `tools/sql-migration/`, docs).
- Check whether the change affects public behavior, config keys, or autoload namespaces.
- Prefer backward-compatible changes unless explicitly asked for breaking updates.

## During Editing

- Keep module boundaries clean (shared package wiring vs module internals).
- Update tests when behavior changes.
- Keep docs aligned with behavior:
  - `README.md`, `README_CN.md`
  - module README files under `tools/*/README.md`
  - config examples when config shape changes
- After each completed task, append a changelog entry with `php scripts/changelog-manager.php add ...`.

## Validation Commands

Run from repository root:

```bash
# Full package checks
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh full

# Fast checks while iterating
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh fast

# Auto Filter focused checks
./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter

# Changelog sync check
php scripts/changelog-manager.php validate
```

Manual fallback commands:

```bash
composer validate --no-check-publish
vendor/bin/phpunit tests/AutoFilterAliasTest.php
composer test
```

## Release Readiness

- Verify `composer.json` version constraints still match intended support matrix.
- Verify provider publish entries still point to valid source files.
- Verify tool registry in `src/ToolsManager.php` reflects actual tools.
- Ensure no placeholder template values remain in shipped config examples.
- Ensure `CHANGELOG.md` `[Unreleased]` is auto-synced from `.changelog/unreleased/*.md`.
- Run `php scripts/changelog-manager.php release --version x.y.z --date YYYY-MM-DD` to archive release notes.

## Reporting Template

When finishing maintenance work, report:

- Scope: changed files grouped by module
- Compatibility: potential impact on package consumers
- Verification: commands run and pass/fail status
- Follow-up: remaining risks or skipped checks
