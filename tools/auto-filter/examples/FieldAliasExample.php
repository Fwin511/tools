<?php

/**
 * 字段别名功能使用示例
 * 
 * 该示例演示如何使用 _as_ 前缀定义字段别名，避免与系统保留字段冲突
 */

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Order;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller()
 */
class ExampleController
{
    /**
     * 示例 1: 普通字段别名
     * 
     * URL: GET /api/users?_as_status=active&name=张三
     * 
     * 说明：
     * - _as_status 会被解析为 status 字段
     * - name 保持不变
     * 
     * @GetMapping(path="/api/users")
     */
    public function getUsersWithAlias()
    {
        // _as_status 会被自动解析为 status
        // 实际执行: WHERE status = 'active' AND name LIKE '%张三%'
        return User::query()
            ->autoFilter()
            ->get();
    }

    /**
     * 示例 2: 关联表字段别名
     * 
     * URL: GET /api/tasks?taskResult._as_submit_staff_id=100&status=pending
     * 
     * 说明：
     * - taskResult._as_submit_staff_id 会被解析为 taskResult.submit_staff_id
     * - 自动构建关联查询条件
     * 
     * @GetMapping(path="/api/tasks")
     */
    public function getTasksWithRelationAlias()
    {
        return Task::query()
            ->with('taskResult')
            ->autoFilter()
            ->paginate();
    }

    /**
     * 示例 3: 多层关联字段别名
     * 
     * URL: GET /api/orders?user.profile._as_avatar=default.png&status=completed
     * 
     * @GetMapping(path="/api/orders")
     */
    public function getOrdersWithNestedAlias()
    {
        return Order::query()
            ->with(['user.profile'])
            ->autoFilter()
            ->get();
    }

    /**
     * 示例 4: 混合使用别名和普通字段
     * 
     * URL: GET /api/tasks?
     *      status=pending&
     *      _as_priority=high&
     *      taskResult._as_submit_staff_id=100&
     *      created_at[start_time]=2024-01-01
     * 
     * 说明：展示如何同时使用别名字段和普通字段
     */
    public function getMixedFieldsQuery()
    {
        return Task::query()
            ->with('taskResult')
            ->autoFilter()
            ->get();
    }

    /**
     * 示例 5: 使用黑名单和白名单 + 别名
     * 
     * URL: GET /api/users?_as_email=test@example.com&_as_status=active
     * 
     * 注意：
     * - 黑白名单检查基于解析后的实际字段名
     * - _as_email 会先解析为 email，然后进行黑名单检查
     */
    public function getWithBlacklistAndAlias()
    {
        // email 字段会被黑名单过滤掉，即使使用了 _as_ 别名
        return User::query()
            ->autoFilter(['email'], [])
            ->get();
    }

    /**
     * 示例 6: 实际应用场景
     * 
     * 场景：任务管理系统中，需要根据提交人 ID 筛选任务
     * 但前端框架可能对 submit_staff_id 有特殊处理，使用别名可以避免冲突
     */
    public function getRealWorldExample()
    {
        /**
         * URL 参数示例:
         * taskResult._as_submit_staff_id=100
         * taskResult._as_submit_time[start_time]=2024-01-01
         * taskResult._as_submit_time[end_time]=2024-12-31
         * _as_status=pending
         */
        
        return Task::query()
            ->with('taskResult')
            ->autoFilter(
                // 黑名单：排除敏感字段
                ['deleted_at', 'internal_notes'],
                // 白名单：只允许特定字段
                ['status', 'priority', 'taskResult.submit_staff_id', 'taskResult.submit_time']
            )
            ->paginate();
    }
}

/**
 * 字段别名解析规则总结：
 * 
 * 1. 普通字段：
 *    _as_field_name → field_name
 * 
 * 2. 关联字段：
 *    relation._as_field_name → relation.field_name
 * 
 * 3. 多层关联：
 *    relation.subRelation._as_field_name → relation.subRelation.field_name
 * 
 * 4. 黑白名单验证：
 *    - 基于解析后的实际字段名进行验证
 *    - _as_email 会解析为 email 后再验证黑白名单
 * 
 * 5. 使用场景：
 *    - 避免与前端框架的保留字冲突
 *    - 避免与数据库关键字冲突
 *    - 保持 URL 参数的语义清晰
 *    - 实现字段名映射而不修改数据库结构
 */
