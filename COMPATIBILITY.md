# 版本兼容性

## PHP 和 Laravel 版本支持矩阵

| Laravel 版本 | PHP 版本要求 | 支持状态 |
|-------------|-------------|---------|
| Laravel 8.x | PHP 8.0.2 - 8.3 | ✅ 支持 |
| Laravel 9.x | PHP 8.0.2 - 8.3 | ✅ 支持 |
| Laravel 10.x | PHP 8.1 - 8.3 | ✅ 支持 |
| Laravel 11.x | PHP 8.2 - 8.3 | ✅ 支持 |

## 安装要求

### 最低要求
- PHP 8.0.2 或更高版本
- Laravel 8.12 或更高版本

### 推荐配置
- PHP 8.2 或更高版本
- Laravel 10.x 或更高版本

## 常见问题

### PHP 版本问题

如果遇到 PHP 版本不兼容的错误，请检查：

1. **实际 PHP 版本**：
   ```bash
   php -v
   ```

2. **Composer 配置的 PHP 版本**：
   ```bash
   composer config platform.php --unset
   ```

3. **项目 composer.json 中的 PHP 版本限制**

### 依赖冲突

如果遇到依赖包冲突，尝试以下解决方案：

1. **清理 Composer 缓存**：
   ```bash
   composer clear-cache
   ```

2. **更新所有依赖**：
   ```bash
   composer update
   ```

3. **强制解析依赖**：
   ```bash
   composer require feiyun/tools --with-all-dependencies
   ```

### Laravel 版本特定问题

- **Laravel 8.x**: 需要使用 8.12 或更高版本以避免依赖冲突
- **Laravel 9.x**: 需要 PHP 8.0.2 或更高版本
- **Laravel 10.x**: 需要 PHP 8.1 或更高版本  
- **Laravel 11.x**: 需要 PHP 8.2 或更高版本
