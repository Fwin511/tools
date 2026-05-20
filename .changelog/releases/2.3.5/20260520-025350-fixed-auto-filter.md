id: 20260520-025350-fixed-auto-filter
date: 2026-05-20 02:53:50
type: Fixed
scope: Auto Filter
summary: 修复自定义日期 range 对纯日期字符串无法命中的问题
compatibility: backward-compatible
files: tools/auto-filter/src/Traits/AutoFilterTrait.php, tests/AutoFilterAliasTest.php
validation: ./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh auto-filter
