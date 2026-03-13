# Changelog

All notable changes to `feiyun/tools` will be documented in this file.

## [Unreleased]

## [2.3.1] - 2026-03-13

### Fixed
- Auto Filter: `string_types` 字段在传入数组值时改为 `whereIn` 查询，标量值仍保持 `like` 查询
- Auto Filter: 关联字段查询前增加关联路径有效性校验，不存在关系时自动跳过避免异常
- Auto Filter: 自动为未限定字段补全表名前缀，修复 `belongsToMany` 场景下 `id` 字段歧义报错

### Added
- 字段别名功能：支持使用 `_as_` 前缀定义字段别名
  - 普通字段: `_as_field_name` → `field_name`
  - 关联字段: `relation._as_field_name` → `relation.field_name`
  - 多层关联: `relation.sub._as_field_name` → `relation.sub.field_name`
- 系统参数自动过滤：以单个下划线 `_` 开头的参数（除 `_as_`）会被自动排除
  - 例如: `_sort`, `_filter`, `_source` 等参数不会被当作数据库字段处理
- 新增 `parseFieldAlias()` 方法用于解析字段别名
- 新增 `excludeSystemParams()` 方法用于过滤系统参数
- 新增字段别名使用示例 (`tools/auto-filter/examples/FieldAliasExample.php`)
- 新增字段别名单元测试 (`tests/AutoFilterAliasTest.php`)

### Updated
- 更新 Auto Filter 文档，添加字段别名功能说明
- 更新 README 文档，提及字段别名特性

### Fixed
- 修复系统参数（如 `_sort`）被错误当作数据库字段处理的问题

## [1.0.0] - 2024-01-01

### Added
- 初始版本发布
- 自动筛选功能
- 支持字符串、整数、浮点数、日期等类型的智能筛选
- 支持关联表字段筛选
- 黑名单和白名单机制
- 缓存机制提升性能
- Laravel 8.x - 11.x 兼容性
- 完整的文档和使用示例
