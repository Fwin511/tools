# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is **Feiyun Tools**, a modular enterprise-grade Hyperf utility toolkit library. It's designed as a PHP Composer package that provides reusable tools for Hyperf 3.0+ applications. The package is distributed via Packagist as `feiyun/tools`.

**Key Architecture Principle**: This is a modular toolkit where each tool lives independently in the `tools/` directory with its own namespace, configuration, and documentation. Tools are managed centrally through `ToolsManager`.

## Core Commands

### Development & Testing
```bash
# Install dependencies
composer install

# Run tests (when tests are created)
composer test

# Run tests with coverage
composer test-coverage

# Validate composer.json
composer validate
```

### Makefile Commands
```bash
# View all available commands
make help

# Switch README language
make readme-en    # English (default)
make readme-cn    # Chinese

# Run tests (aliases to composer)
make test

# Code validation
make lint
```

### Hyperf Integration
```bash
# Publish configuration files to a Hyperf project
php bin/hyperf.php vendor:publish feiyun/tools
```

## Architecture

### Modular Tool Structure
Each tool follows this standardized layout:
```
tools/[tool-name]/
├── config/              # Tool-specific config files
├── src/                 # Tool source code
│   ├── Contracts/      # Interfaces
│   ├── Support/        # Helper/utility classes
│   ├── Traits/         # Reusable traits
│   └── Providers/      # Hyperf service providers
└── README.md           # Tool documentation
```

### Current Tools

#### Auto Filter (tools/auto-filter/)
Automatically builds database query conditions based on HTTP request parameters and database field types.

**Core Components**:
- `AutoFilterTrait`: Scope method `autoFilter()` added to Eloquent models
- `QueryBuilder`: Builds WHERE clauses based on field type (string→LIKE, int→IN, date→BETWEEN, etc.)
- `FieldTypeDetector`: Detects and caches database column types

**Query Type Mapping**:
- String fields (varchar, text, etc.) → `LIKE '%value%'`
- Integer fields (int, bigint, etc.) → `IN (values)`
- Decimal fields (float, double, etc.) → `BETWEEN start AND end`
- Date fields → `BETWEEN start_time AND end_time`
- DateTime/Timestamp → `BETWEEN` with automatic time parsing (start of day/end of day)

**Key Features**:
- Supports blacklist/whitelist field filtering
- Handles relationship queries via dot notation (e.g., `user.name`)
- Caches table schema for performance
- Auto-excludes pagination parameters (page, page_size, per_page)
- Coroutine-safe for Hyperf/Swoole environments

### Central Management
- `src/ToolsManager.php`: Central registry of all available tools with metadata
- `src/Providers/FeiyunToolsServiceProvider.php`: Main Hyperf service provider that registers configurations via the `publish` array

### Package Registration
The package auto-registers with Hyperf via the `extra.hyperf.config` key in composer.json, pointing to `FeiyunToolsServiceProvider`.

## Development Guidelines

### Adding a New Tool
1. Create tool directory: `tools/[tool-name]/`
2. Follow the standard structure (config/, src/, README.md)
3. Create a PSR-4 autoload entry in composer.json: `"Feiyun\\Tools\\[ToolName]\\": "tools/[tool-name]/src/"`
4. Register the tool in `ToolsManager::getAvailableTools()`
5. Add publish configuration in `FeiyunToolsServiceProvider` if the tool has config files
6. Write tool-specific README.md with usage examples

### Code Patterns

#### Request Parameter Access (Hyperf)
```php
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;

$container = ApplicationContext::getContainer();
$request = $container->get(RequestInterface::class);
$params = $request->all();
```

#### Model Scope Methods
Use Laravel-style scope methods that accept the query builder as the first parameter:
```php
public function scopeAutoFilter($query, array $blacklist = [], array $whitelist = [], array $asParams = [])
{
    // Implementation
    return $query;
}
```

#### Hyperf Service Provider Format
```php
class ServiceProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
            'listeners' => [],
            'publish' => [
                [
                    'id' => 'unique-id',
                    'description' => 'Description',
                    'source' => __DIR__ . '/path/to/config.php',
                    'destination' => BASE_PATH . '/config/autoload/config.php',
                ],
            ],
        ];
    }
}
```

### Namespace Conventions
- Main package: `Feiyun\Tools\`
- Each tool: `Feiyun\Tools\[ToolName]\`
- Example: Auto Filter uses `Feiyun\Tools\AutoFilter\`

### Configuration
- Config files use PHP arrays (Laravel/Hyperf convention)
- Support caching configuration where applicable (see auto-filter config for example)
- Always include default blacklist for sensitive fields (password, tokens, etc.)

## Important Notes

### Framework Compatibility
- **Primary Target**: Hyperf 3.0+ (PHP 8.0+)
- The README mentions Laravel/Illuminate compatibility in examples, but the actual codebase is Hyperf-specific
- Uses Hyperf's dependency injection container, not Laravel's
- Uses `Hyperf\Database\Model\Builder`, not Eloquent's builder

### Coroutine Safety
All code must be coroutine-safe for Swoole/Hyperf environments:
- Use Hyperf's Context for request-scoped data
- Avoid static properties for mutable state
- Be careful with singleton instances

### Multi-language Support
- Maintain both English (README.md) and Chinese (README_CN.md) documentation
- Use Makefile or composer scripts to switch between language versions
- The codebase itself uses Chinese comments in some places

### Testing
The tests/ directory currently exists but is empty. When writing tests:
- Use PHPUnit (already in require-dev)
- Use Hyperf's testing utilities (`hyperf/testing`)
- Place tests in `tests/` with namespace `Feiyun\Tools\Tests\`
