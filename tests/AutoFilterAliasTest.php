<?php

namespace Feiyun\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;

class AutoFilterAliasTest extends TestCase
{
    use AutoFilterTrait;

    /**
     * 测试解析普通字段别名
     */
    public function testParseSimpleFieldAlias()
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('parseFieldAlias');
        $method->setAccessible(true);

        // 测试普通字段别名
        $result = $method->invokeArgs($this, ['_as_status']);
        $this->assertEquals('status', $result);

        // 测试没有别名前缀的字段
        $result = $method->invokeArgs($this, ['status']);
        $this->assertEquals('status', $result);
    }

    /**
     * 测试解析关联表字段别名
     */
    public function testParseRelationFieldAlias()
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('parseFieldAlias');
        $method->setAccessible(true);

        // 测试关联表字段别名
        $result = $method->invokeArgs($this, ['taskResult._as_submit_staff_id']);
        $this->assertEquals('taskResult.submit_staff_id', $result);

        // 测试没有别名前缀的关联字段
        $result = $method->invokeArgs($this, ['taskResult.submit_staff_id']);
        $this->assertEquals('taskResult.submit_staff_id', $result);
    }

    /**
     * 测试多层关联字段别名
     */
    public function testParseNestedRelationFieldAlias()
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('parseFieldAlias');
        $method->setAccessible(true);

        // 测试多层关联
        $result = $method->invokeArgs($this, ['user.profile._as_avatar']);
        $this->assertEquals('user.profile.avatar', $result);

        // 测试混合情况
        $result = $method->invokeArgs($this, ['order.items._as_quantity']);
        $this->assertEquals('order.items.quantity', $result);
    }

    /**
     * 测试边界情况
     */
    public function testEdgeCases()
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('parseFieldAlias');
        $method->setAccessible(true);

        // 空字符串
        $result = $method->invokeArgs($this, ['']);
        $this->assertEquals('', $result);

        // 只有 _as_
        $result = $method->invokeArgs($this, ['_as_']);
        $this->assertEquals('', $result);

        // 关联表中只有 _as_
        $result = $method->invokeArgs($this, ['relation._as_']);
        $this->assertEquals('relation.', $result);
    }
}
