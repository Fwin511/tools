# Auto Filter 自动筛选工具

Laravel/Hyperf 模型自动筛选扩展，根据请求参数和字段类型自动构建查询条件。

## 功能特性

- 🚀 自动根据字段类型构建查询条件
- 🔍 支持字符串模糊查询、数字精确查询、日期范围查询等
- 🛡️ 支持黑白名单字段过滤
- 🔗 支持关联表字段查询
- ⚡ 内置缓存机制，提升性能
- 📦 零配置开箱即用

## 安装

```bash
composer require feiyun/tools
```

## 配置发布

```bash
php artisan vendor:publish --tag=feiyun-auto-filter-config
```

## 使用方法

### 1. 在模型中使用 Trait

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;

class User extends Model
{
    use AutoFilterTrait;
    
    // 模型定义...
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
        
        // 使用黑名单排除敏感字段
        $users = User::query()->autoFilter(['password', 'email'])->get();
        
        // 使用白名单只允许特定字段
        $users = User::query()->autoFilter([], ['name', 'status'])->get();
        
        // 传递额外参数
        $users = User::query()->autoFilter([], [], ['status' => 'active'])->get();
        
        return response()->json($users);
    }
}
```

## 支持的查询类型

- **字符串字段**: 使用 `LIKE` 模糊查询
- **整数字段**: 使用 `IN` 精确查询（支持数组）
- **浮点数字段**: 支持范围查询 `['start' => 10, 'end' => 100]`
- **日期字段**: 支持日期范围查询 `['start_time' => '2023-01-01', 'end_time' => '2023-12-31']`
- **时间戳字段**: 支持时间范围查询（自动处理开始和结束时间）

## 配置选项

```php
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
        // ...
    ],
    
    // 字段类型映射
    'field_type_mapping' => [
        // 自定义字段类型处理方式
    ],
];
```
