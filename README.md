# Feiyun Auto Filter

[![Latest Stable Version](https://poser.pugx.org/feiyun/auto-filter/v/stable)](https://packagist.org/packages/feiyun/auto-filter)
[![Total Downloads](https://poser.pugx.org/feiyun/auto-filter/downloads)](https://packagist.org/packages/feiyun/auto-filter)
[![License](https://poser.pugx.org/feiyun/auto-filter/license)](https://packagist.org/packages/feiyun/auto-filter)

Laravel/Hyperf 模型自动筛选扩展包，根据请求参数和字段类型自动构建查询条件。

## ✨ 特性

- 🚀 **智能筛选**: 根据数据库字段类型自动选择合适的查询方式
- 🎯 **灵活控制**: 支持白名单、黑名单机制
- 🔗 **关联查询**: 支持关联表字段筛选
- 💾 **高性能**: 内置缓存机制，避免重复查询表结构
- 🛡️ **类型安全**: 严格的类型检测和参数验证
- 📦 **框架兼容**: 支持 Laravel 8.x - 11.x

## 📦 安装

```bash
composer require feiyun/auto-filter
```

Laravel 会自动发现并注册服务提供者。

## 🚀 快速开始

### 1. 在模型中使用

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Feiyun\AutoFilter\Traits\AutoFilterTrait;

class User extends Model
{
    use AutoFilterTrait;
    
    // 其他模型代码...
}
```

### 2. 在控制器中使用

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 自动根据请求参数筛选
        $users = User::query()->autoFilter()->paginate();
        
        return response()->json($users);
    }
}
```

### 3. 前端请求示例

```javascript
// GET /api/users?name=张三&age=25&created_at[start_time]=2024-01-01&created_at[end_time]=2024-12-31

// 自动生成的 SQL（示例）:
// SELECT * FROM users 
// WHERE name LIKE '%张三%' 
// AND age IN (25) 
// AND created_at BETWEEN '2024-01-01 00:00:00' AND '2024-12-31 23:59:59'
```

## 🎛️ 高级用法

### 黑名单和白名单

```php
// 使用黑名单（排除敏感字段）
$users = User::query()
    ->autoFilter(['password', 'remember_token'])
    ->get();

// 使用白名单（只允许指定字段）
$users = User::query()
    ->autoFilter([], ['name', 'email', 'age'])
    ->get();

// 同时使用黑名单和白名单
$users = User::query()
    ->autoFilter(['password'], ['name', 'email', 'age'])
    ->get();
```

### 外部参数注入

```php
// 注入额外的筛选条件
$users = User::query()
    ->autoFilter([], [], ['status' => 'active', 'role' => 'admin'])
    ->get();
```

### 关联表筛选

```php
// 筛选用户的角色名称
// GET /api/users?role.name=管理员

$users = User::query()
    ->with('role')
    ->autoFilter()
    ->get();

// 生成的 SQL 类似：
// SELECT * FROM users 
// WHERE EXISTS (
//     SELECT * FROM roles 
//     WHERE users.role_id = roles.id 
//     AND roles.name LIKE '%管理员%'
// )
```

## 🔧 字段类型支持

| 数据库类型 | 查询方式 | 示例 |
|------------|----------|------|
| `varchar`, `text` 等字符串类型 | `LIKE '%value%'` | `name=张三` → `name LIKE '%张三%'` |
| `int`, `bigint` 等整数类型 | `IN (values)` | `age=25` → `age IN (25)` |
| `decimal`, `float` 等数字类型 | `BETWEEN` | `price[start]=100&price[end]=200` |
| `date` 日期类型 | `BETWEEN` | `birthday[start_time]=2024-01-01` |
| `datetime`, `timestamp` | `BETWEEN` (自动处理时分秒) | `created_at[start_time]=2024-01-01` |

## ⚙️ 配置

发布配置文件：

```bash
php artisan vendor:publish --tag=auto-filter-config
```

配置文件 `config/auto-filter.php`：

```php
<?php

return [
    // 缓存配置
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'auto_filter_',
    ],

    // 默认黑名单字段
    'default_blacklist' => [
        'password',
        'password_hash',
        'remember_token',
        'api_token',
    ],
];
```

## 📚 使用场景

### 1. 用户管理

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

### 2. 订单查询

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

### 3. 商品筛选

```php
// GET /api/products?category.name=电子产品&price[start]=100&price[end]=5000&in_stock=1

class ProductController extends Controller
{
    public function index()
    {
        return Product::query()
            ->with('category')
            ->autoFilter([], ['name', 'price', 'category.name', 'in_stock'])
            ->paginate();
    }
}
```

## 🛡️ 安全考虑

1. **默认黑名单**: 自动排除敏感字段如 `password`、`remember_token` 等
2. **字段验证**: 只对数据库中存在的字段进行筛选
3. **类型安全**: 根据字段类型进行相应的查询构建
4. **SQL注入防护**: 使用 Laravel 的查询构建器，自动防止 SQL 注入

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

MIT License. 详见 [LICENSE](LICENSE) 文件。

## 🔗 相关链接

- [Packagist](https://packagist.org/packages/feiyun/auto-filter)
- [GitHub](https://github.com/your-username/feiyun-auto-filter)

---

如果这个包对您有帮助，请给个 ⭐️ Star 支持一下！
