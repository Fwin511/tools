#!/usr/bin/env bash
set -euo pipefail

MODE="${1:-full}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../.." && pwd)"

cd "$ROOT_DIR"

if [[ ! -f "composer.json" ]]; then
  echo "[error] composer.json not found. Run this script inside feiyun-tools."
  exit 1
fi

if [[ ! -x "vendor/bin/phpunit" ]]; then
  echo "[error] vendor/bin/phpunit is missing. Run 'composer install' first."
  exit 1
fi

run_fast() {
  echo "[check] composer metadata"
  composer validate --no-check-publish

  echo "[check] changelog consistency"
  php scripts/changelog-manager.php validate

  echo "[check] php syntax under src tools tests"
  find src tools tests -type f -name "*.php" -print0 | xargs -0 -n1 php -l >/dev/null
}

case "$MODE" in
  fast)
    run_fast
    ;;
  auto-filter)
    run_fast
    echo "[check] auto-filter focused test"
    vendor/bin/phpunit tests/AutoFilterAliasTest.php
    ;;
  full)
    run_fast
    echo "[check] full test suite"
    composer test
    ;;
  *)
    echo "Usage: $0 [fast|auto-filter|full]"
    exit 1
    ;;
esac

echo "[ok] maintenance checks passed in mode: $MODE"
