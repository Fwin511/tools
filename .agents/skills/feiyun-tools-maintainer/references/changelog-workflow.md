# Changelog Workflow

Use this workflow to keep all maintenance tasks traceable before release.

## Goal

- Record each task immediately after completion.
- Keep `CHANGELOG.md` `[Unreleased]` synchronized automatically.
- Archive all unreleased entries into a version section at release time.

## Commands

```bash
# 1) Add one task record
php scripts/changelog-manager.php add \
  --type fixed \
  --scope "Auto Filter" \
  --summary "修复 xxx" \
  --files "tools/auto-filter/src/Traits/AutoFilterTrait.php,tests/AutoFilterAliasTest.php" \
  --validation "./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter"

# 2) Rebuild Unreleased section manually (normally add already does this)
php scripts/changelog-manager.php rebuild-unreleased

# 3) Validate changelog sync status
php scripts/changelog-manager.php validate

# 4) Release (archive unreleased entries and write version block)
php scripts/changelog-manager.php release --version 2.3.4 --date 2026-03-30
```

## Type Mapping

- `added`: new feature or capability
- `changed`: non-breaking behavior adjustment
- `fixed`: bug fix
- `refactored`: internal refactor with intended behavior preserved
- `docs`: documentation-only updates
- `security`: security fix or hardening
- `removed`: removed/deprecated behavior
- `review`: review-only maintenance record

## Required Practice

- After each completed task (`bug/feature/refactor/review`), run `add`.
- Before release, run `validate` and maintenance checks.
- During release, run `release --version x.y.z`.
