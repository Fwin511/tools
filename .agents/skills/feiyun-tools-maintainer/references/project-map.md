# Feiyun Tools Project Map

## Stack and Runtime

- Language: PHP `>=8.0`
- Framework target: Hyperf `^2.0|^3.0` components
- Package type: Composer library (`feiyun/tools`)
- Tests: PHPUnit (`composer test`)

## Top-Level Ownership

- `src/`: package-level shared wiring
- `tools/auto-filter/`: query auto-filter module
- `tools/sql-migration/`: sql migration sync/notify module
- `tests/`: package tests (currently alias/system-param behavior coverage)
- `scripts/`: utility scripts (README switching)
- `.changelog/`: unreleased entry fragments and release archives

## Shared Core Files

- `src/ToolsManager.php`: tool registry metadata (`auto-filter`, `sql-migration`)
- `src/Providers/FeiyunToolsServiceProvider.php`: global dependency bindings, listeners, publish rules
- `composer.json`: package metadata, dependencies, autoload namespaces, scripts
- `Makefile`: commonly used command wrappers
- `scripts/changelog-manager.php`: changelog entry/rebuild/release automation

## Auto Filter Module Map

- `tools/auto-filter/src/Traits/AutoFilterTrait.php`: request param filtering and relation query assembly
- `tools/auto-filter/src/Support/QueryBuilder.php`: type-driven where clause builder
- `tools/auto-filter/src/Support/FieldTypeDetector.php`: information_schema type detection with cache
- `tools/auto-filter/config/auto-filter.php`: module config defaults
- `tools/auto-filter/README.md`: usage and behavior docs

Critical behaviors to preserve:

- `_as_` alias parsing for normal and relation fields
- Exclusion of system params beginning with `_` except alias fields
- `MorphTo` relation handling compatibility
- Field qualification to reduce ambiguous columns

## SQL Migration Module Map

- `tools/sql-migration/src/Services/SqlHandleRecordSyncService.php`: sync rows and optional notify
- `tools/sql-migration/src/Listeners/WorkerStartSqlSyncListener.php`: guarded startup trigger
- `tools/sql-migration/src/Stores/ModelSqlHandleRecordStore.php`: default record persistence adapter
- `tools/sql-migration/src/Notifiers/HttpSqlMigrationNotifier.php`: default HTTP notify adapter
- `tools/sql-migration/config/sql-migration.php`: runtime config
- `tools/sql-migration/config/sql.php`: sql row template

Critical behaviors to preserve:

- Idempotent insert based on `serial_number`
- Safe listener guard on startup events and worker conditions
- Notify only when pending databases exist
- Swappable store/notifier implementations via interfaces
