# 飞云工具包 (Feiyun Tools)

[![Latest Stable Version](https://poser.pugx.org/feiyun/tools/v/stable)](https://packagist.org/packages/feiyun/tools)
[![Total Downloads](https://poser.pugx.org/feiyun/tools/downloads)](https://packagist.org/packages/feiyun/tools)
[![License](https://poser.pugx.org/feiyun/tools/license)](https://packagist.org/packages/feiyun/tools)

企业级 Laravel/Hyperf 辅助工具集合，提供各种实用的开发工具来提升开发效率。

## 🚀 特性

- **模块化设计**: 每个工具独立管理，按需使用
- **零配置**: 开箱即用，无需复杂配置
- **高性能**: 内置缓存机制，优化性能
- **企业级**: 适用于生产环境的稳定工具集
- **易扩展**: 便于添加新工具和功能

## 📦 安装

```bash
composer require feiyun/tools
```

Laravel 会自动发现并注册服务提供者。

## 🛠️ 可用工具

### 1. Auto Filter 自动筛选工具

根据请求参数和字段类型自动构建查询条件的强大工具。

**特性:**
- 🚀 智能根据字段类型构建查询条件
- 🎯 支持黑白名单字段过滤
- 🔗 支持关联表字段查询
- 💾 内置缓存机制，提升性能
- 🛡️ 严格的类型检测和参数验证

**快速使用:**
```php
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;

class User extends Model
{
    use AutoFilterTrait;
}

// 控制器中使用
$users = User::query()->autoFilter()->paginate();
```

**支持的查询类型:**
- **字符串字段**: `LIKE '%value%'` 模糊查询
- **整数字段**: `IN (values)` 精确查询
- **浮点数字段**: `BETWEEN` 范围查询
- **日期时间字段**: `BETWEEN` 日期范围查询

[查看 Auto Filter 详细文档](./tools/auto-filter/README.md)

### 2. 更多工具 (规划中)

我们计划添加更多实用的企业级工具，如：
- 通用导出工具
- 缓存管理工具  
- 日志分析工具
- API 限流工具

## 📁 项目结构

```
feiyun-tools/
├── src/                          # 核心代码
│   ├── Providers/               # 服务提供者
│   │   └── FeiyunToolsServiceProvider.php
│   └── ToolsManager.php         # 工具管理器
├── tools/                       # 工具目录
│   └── auto-filter/            # 自动筛选工具
│       ├── config/             # 配置文件
│       ├── src/                # 源代码
│       │   ├── Contracts/      # 接口定义
│       │   ├── Support/        # 支持类
│       │   ├── Traits/         # Trait 文件
│       │   └── Providers/      # 服务提供者
│       └── README.md           # 工具文档
├── tests/                       # 测试文件
└── composer.json               # Composer 配置
```

## 🔧 配置

### Laravel 配置

包会自动注册服务提供者，无需手动配置。各工具的配置文件可以单独发布：

```bash
# 发布 Auto Filter 配置
php artisan vendor:publish --tag=feiyun-auto-filter-config
```

### 工具管理

使用 `ToolsManager` 类来管理和查看可用工具：

```php
use Feiyun\Tools\ToolsManager;

// 获取所有可用工具
$tools = ToolsManager::getAvailableTools();

// 检查工具是否可用
$isAvailable = ToolsManager::isToolAvailable('auto-filter');

// 获取工具信息
$info = ToolsManager::getToolInfo('auto-filter');
```

## 📚 使用示例

### 用户管理系统

```php
// GET /api/users?name=张&email=@gmail.com&age=25&created_at[start_time]=2024-01-01

class UserController extends Controller
{
    public function index()
    {
        return User::query()
            ->autoFilter(['password', 'remember_token'])
            ->paginate();
    }
}
```

### 订单查询系统

```php
// GET /api/orders?status=completed&amount[start]=100&amount[end]=1000&user.name=张三

class OrderController extends Controller  
{
    public function index()
    {
        return Order::query()
            ->with('user')
            ->autoFilter()
            ->paginate();
    }
}
```

## 🛡️ 安全考虑

1. **默认黑名单**: 自动排除敏感字段如 `password`、`remember_token` 等
2. **字段验证**: 只对数据库中存在的字段进行筛选
3. **类型安全**: 根据字段类型进行相应的查询构建
4. **SQL注入防护**: 使用 Laravel 的查询构建器，自动防止 SQL 注入

## 🔄 版本升级指南

### 从 feiyun/auto-filter 升级到 feiyun/tools

如果您之前使用的是 `feiyun/auto-filter` 包，升级到 `feiyun/tools` 需要进行以下更改：

1. **更新 composer.json**:
```bash
composer remove feiyun/auto-filter
composer require feiyun/tools
```

2. **更新命名空间**:
```php
// 旧的命名空间
use Feiyun\AutoFilter\Traits\AutoFilterTrait;

// 新的命名空间
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;
```

3. **重新发布配置** (如果需要):
```bash
php artisan vendor:publish --tag=feiyun-auto-filter-config
```

## 🤝 贡献

欢迎贡献代码！请遵循以下步骤：

1. Fork 本仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 打开 Pull Request

### 添加新工具

如果您想添加新的工具，请按照以下结构：

```
tools/your-tool/
├── config/              # 配置文件
├── src/                 # 源代码
│   ├── Contracts/      # 接口
│   ├── Providers/      # 服务提供者
│   └── ...             # 其他代码
├── tests/              # 测试文件
└── README.md           # 工具文档
```

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🙋‍♀️ 支持

如有问题或建议，请通过以下方式联系：

- 提交 [Issue](https://github.com/feiyun/tools/issues)
- 发送邮件至: your-email@example.com

## 🔗 相关链接

- [Packagist](https://packagist.org/packages/feiyun/tools)
- [GitHub](https://github.com/feiyun/tools)

---

**飞云工具包** - 让开发更简单！ 🚀

如果这个包对您有帮助，请给个 ⭐️ Star 支持一下！