<?php

namespace Feiyun\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Feiyun\Tools\AutoFilter\Traits\AutoFilterTrait;

class AutoFilterAliasTest extends TestCase
{
    use AutoFilterTrait;

    private function invokeProtected(string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($this);
        $refMethod = $reflection->getMethod($method);
        $refMethod->setAccessible(true);

        return $refMethod->invokeArgs($this, $arguments);
    }

    /**
     * 测试解析普通字段别名
     */
    public function testParseSimpleFieldAlias()
    {
        // 测试普通字段别名
        $result = $this->invokeProtected('parseFieldAlias', ['_as_status']);
        $this->assertEquals('status', $result);

        // 测试没有别名前缀的字段
        $result = $this->invokeProtected('parseFieldAlias', ['status']);
        $this->assertEquals('status', $result);

        // 测试精确匹配前缀
        $result = $this->invokeProtected('parseFieldAlias', ['_only_goods_code']);
        $this->assertEquals('goods_code', $result);
    }

    /**
     * 测试解析关联表字段别名
     */
    public function testParseRelationFieldAlias()
    {
        // 测试关联表字段别名
        $result = $this->invokeProtected('parseFieldAlias', ['taskResult._as_submit_staff_id']);
        $this->assertEquals('taskResult.submit_staff_id', $result);

        // 测试没有别名前缀的关联字段
        $result = $this->invokeProtected('parseFieldAlias', ['taskResult.submit_staff_id']);
        $this->assertEquals('taskResult.submit_staff_id', $result);

        // 测试关联表精确匹配前缀
        $result = $this->invokeProtected('parseFieldAlias', ['taskResult._only_submit_staff_id']);
        $this->assertEquals('taskResult.submit_staff_id', $result);
    }

    /**
     * 测试多层关联字段别名
     */
    public function testParseNestedRelationFieldAlias()
    {
        // 测试多层关联
        $result = $this->invokeProtected('parseFieldAlias', ['user.profile._as_avatar']);
        $this->assertEquals('user.profile.avatar', $result);

        // 测试混合情况
        $result = $this->invokeProtected('parseFieldAlias', ['order.items._as_quantity']);
        $this->assertEquals('order.items.quantity', $result);
    }

    /**
     * 测试边界情况
     */
    public function testEdgeCases()
    {
        // 空字符串
        $result = $this->invokeProtected('parseFieldAlias', ['']);
        $this->assertEquals('', $result);

        // 只有 _as_
        $result = $this->invokeProtected('parseFieldAlias', ['_as_']);
        $this->assertEquals('', $result);

        // 关联表中只有 _as_
        $result = $this->invokeProtected('parseFieldAlias', ['relation._as_']);
        $this->assertEquals('relation.', $result);

        // 只有 _only_
        $result = $this->invokeProtected('parseFieldAlias', ['_only_']);
        $this->assertEquals('', $result);
    }

    /**
     * 测试系统参数过滤
     */
    public function testExcludeSystemParams()
    {
        $params = [
            '_sort' => [],
            '_filter' => 'test',
            '_source' => 'list',
            '_as_serial_number' => 'R01594',
            '_only_goods_code' => '0421-1811',
            'relation._as_field' => 'value',
            'relation._only_field' => 'value',
            'normal_field' => 'value',
        ];

        $result = $this->invokeProtected('excludeSystemParams', [$params]);

        // 系统参数应该被排除
        $this->assertArrayNotHasKey('_sort', $result);
        $this->assertArrayNotHasKey('_filter', $result);
        $this->assertArrayNotHasKey('_source', $result);

        // 别名字段应该保留
        $this->assertArrayHasKey('_as_serial_number', $result);
        $this->assertArrayHasKey('relation._as_field', $result);
        $this->assertArrayHasKey('_only_goods_code', $result);
        $this->assertArrayHasKey('relation._only_field', $result);

        // 普通字段应该保留
        $this->assertArrayHasKey('normal_field', $result);
    }

    /**
     * 测试 _only_ 精确匹配字段识别
     */
    public function testIsExactMatchField()
    {
        $this->assertTrue($this->invokeProtected('isExactMatchField', ['_only_goods_code']));
        $this->assertTrue($this->invokeProtected('isExactMatchField', ['taskResult._only_goods_code']));
        $this->assertFalse($this->invokeProtected('isExactMatchField', ['goods_code']));
        $this->assertFalse($this->invokeProtected('isExactMatchField', ['_as_goods_code']));
    }

    /**
     * 测试解析自定义字段键
     */
    public function testParseCustomFieldKey()
    {
        $this->assertSame(
            ['type' => 'string', 'code' => '_OOOOOQLZ'],
            $this->invokeProtected('parseCustomFieldKey', ['_customer_field_string__OOOOOQLZ'])
        );

        $this->assertSame(
            ['type' => 'array', 'code' => '_OOOOOQLC'],
            $this->invokeProtected('parseCustomFieldKey', ['_customer_field_array__OOOOOQLC'])
        );

        $this->assertNull(
            $this->invokeProtected('parseCustomFieldKey', ['customer_name'])
        );
    }

    /**
     * 测试自定义文本字段过滤
     */
    public function testApplyCustomStringFieldWhere()
    {
        $query = new FakeQueryBuilder();

        $result = $this->invokeProtected('applyFieldWhere', [
            $query,
            '_customer_field_string__OOOOOQLZ',
            '文本值',
            false,
        ]);

        $this->assertTrue($result);
        $this->assertSame([
            ['type' => 'basic', 'boolean' => 'and', 'column' => 'fake_related.code', 'operator' => '=', 'value' => '_OOOOOQLZ'],
            ['type' => 'basic', 'boolean' => 'and', 'column' => 'fake_related.input_value', 'operator' => 'like', 'value' => '%文本值%'],
        ], $query->conditions);
    }

    /**
     * 测试自定义数组字段过滤
     */
    public function testApplyCustomArrayFieldWhere()
    {
        $query = new FakeQueryBuilder();

        $result = $this->invokeProtected('applyFieldWhere', [
            $query,
            '_customer_field_array__OOOOOQLC',
            ['1', '3'],
            false,
        ]);

        $this->assertTrue($result);
        $this->assertSame('basic', $query->conditions[0]['type']);
        $this->assertSame('fake_related.code', $query->conditions[0]['column']);
        $this->assertSame('_OOOOOQLC', $query->conditions[0]['value']);
        $this->assertSame('group', $query->conditions[1]['type']);
        $this->assertCount(2, $query->conditions[1]['conditions']);
    }

    /**
     * 测试自定义区间字段过滤
     */
    public function testApplyCustomRangeFieldWhere()
    {
        $query = new FakeQueryBuilder();

        $result = $this->invokeProtected('applyFieldWhere', [
            $query,
            '_customer_field_range__OOOOOQYH',
            ['start_time' => '2026-05-19', 'end_time' => '2026-05-20'],
            false,
        ]);

        $this->assertTrue($result);
        $this->assertSame('basic', $query->conditions[0]['type']);
        $this->assertSame('_OOOOOQYH', $query->conditions[0]['value']);
        $this->assertSame('raw', $query->conditions[1]['type']);
        $this->assertSame("DATE(fake_related.input_value) BETWEEN ? AND ?", $query->conditions[1]['sql']);
        $this->assertSame(['2026-05-19', '2026-05-20'], $query->conditions[1]['bindings']);
    }
}

class FakeQueryBuilder
{
    public array $conditions = [];

    public function getModel(): FakeRelatedModel
    {
        return new FakeRelatedModel();
    }

    public function where($column, $operator = null, $value = null): self
    {
        if ($column instanceof \Closure) {
            $nested = new self();
            $column($nested);
            $this->conditions[] = [
                'type' => 'group',
                'boolean' => 'and',
                'conditions' => $nested->conditions,
            ];
            return $this;
        }

        $this->conditions[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null): self
    {
        if ($column instanceof \Closure) {
            $nested = new self();
            $column($nested);
            $this->conditions[] = [
                'type' => 'group',
                'boolean' => 'or',
                'conditions' => $nested->conditions,
            ];
            return $this;
        }

        $this->conditions[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->conditions[] = [
            'type' => 'raw',
            'sql' => $sql,
            'bindings' => $bindings,
        ];

        return $this;
    }
}

class FakeRelatedModel
{
    public function getTable(): string
    {
        return 'fake_related';
    }

    public function qualifyColumn(string $column): string
    {
        return $this->getTable() . '.' . $column;
    }
}
