<?php

namespace Feiyun\Tools\AutoFilter\Contracts;

interface AutoFilterInterface
{
    /**
     * 自动筛选
     *
     * @param array $blacklist 禁止字段
     * @param array $whitelist 允许字段
     * @param array $asParams 外部传递的搜索参数
     * @return mixed
     */
    public function scopeAutoFilter($query, array $blacklist = [], array $whitelist = [], array $asParams = []);
}
