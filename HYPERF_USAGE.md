# Hyperf 使用指南

## 快速开始

### 1. 安装

```bash
composer require feiyun/tools
```

### 2. 在模型中使用

```php
<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;

class User extends Model
{
    use AutoFilterTrait;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'age', 'status'];
}
```

### 3. 在控制器中使用

```php
<?php

namespace App\Controller;

use App\Model\User;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[AutoController]
class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        // 自动根据请求参数筛选
        $users = User::query()->autoFilter()->paginate(15);
        
        return $response->json($users);
    }
    
    public function search(RequestInterface $request, ResponseInterface $response)
    {
        // 使用黑名单排除敏感字段
        $users = User::query()
            ->autoFilter(['password', 'remember_token'])
            ->get();
        
        return $response->json($users);
    }
    
    public function filter(RequestInterface $request, ResponseInterface $response)
    {
        // 使用白名单只允许特定字段
        $users = User::query()
            ->autoFilter([], ['name', 'email', 'status'])
            ->get();
        
        return $response->json($users);
    }
    
    public function customFilter(RequestInterface $request, ResponseInterface $response)
    {
        // 传递额外参数
        $users = User::query()
            ->autoFilter([], [], ['status' => 'active'])
            ->get();
        
        return $response->json($users);
    }
}
```

### 4. 前端请求示例

```javascript
// GET /user?name=张三&age=25&created_at[start_time]=2024-01-01&created_at[end_time]=2024-12-31

// 自动生成的查询相当于：
// SELECT * FROM users 
// WHERE name LIKE '%张三%' 
// AND age IN (25) 
// AND created_at BETWEEN '2024-01-01 00:00:00' AND '2024-12-31 23:59:59'
```

## 配置发布

```bash
php bin/hyperf.php vendor:publish feiyun/tools
```

配置文件将发布到 `config/autoload/auto-filter.php`

## 支持的查询类型

| 数据库类型 | 查询方式 | 示例 |
|------------|----------|------|
| `varchar`, `text` 等字符串类型 | `LIKE '%value%'` | `name=张三` → `name LIKE '%张三%'` |
| `int`, `bigint` 等整数类型 | `IN (values)` | `age=25` → `age IN (25)` |
| `decimal`, `float` 等数字类型 | `BETWEEN` | `price[start]=100&price[end]=200` |
| `date` 日期类型 | `BETWEEN` | `birthday[start_time]=2024-01-01` |
| `datetime`, `timestamp` | `BETWEEN` (自动处理时分秒) | `created_at[start_time]=2024-01-01` |

## 关联查询

```php
// 查询用户的角色名称
// GET /user?role.name=管理员

class User extends Model
{
    use AutoFilterTrait;
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

// 在控制器中
$users = User::query()
    ->with('role')
    ->autoFilter()
    ->get();
```

## 注意事项

1. **协程安全**: 本工具包专门为 Hyperf 的协程环境设计，完全协程安全
2. **性能优化**: 使用 Hyperf 的缓存系统缓存表结构信息
3. **依赖注入**: 完全集成 Hyperf 的依赖注入容器
4. **请求处理**: 使用 Hyperf 的 RequestInterface 处理请求参数

## 故障排除

如果遇到问题，请检查：

1. Hyperf 版本是否为 3.0+
2. 是否已正确配置数据库连接
3. 模型是否继承自 `Hyperf\DbConnection\Model\Model`
4. 是否已在模型中使用 `AutoFilterTrait`
