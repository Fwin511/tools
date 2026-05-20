id: 20260520-024248-fixed-auto-filter
date: 2026-05-20 02:42:48
type: Fixed
scope: Auto Filter
summary: 修复自定义 range 字段按 JSON 解析导致的时间过滤报错
compatibility: backward-compatible
files: tools/auto-filter/src/Traits/AutoFilterTrait.php, tests/AutoFilterAliasTest.php
validation: ./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter
