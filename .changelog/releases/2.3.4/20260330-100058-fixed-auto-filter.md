id: 20260330-100058-fixed-auto-filter
date: 2026-03-30 10:00:58
type: Fixed
scope: Auto Filter
summary: 新增 _only_ 前缀支持字符串字段精确匹配并保持原有筛选兼容
compatibility: backward-compatible
files: tools/auto-filter/src/Traits/AutoFilterTrait.php, tools/auto-filter/src/Support/QueryBuilder.php, tests/AutoFilterAliasTest.php, tools/auto-filter/README.md
validation: ./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter
