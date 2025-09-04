<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 缓存配置
    |--------------------------------------------------------------------------
    |
    | 表字段类型缓存配置
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 缓存时间（秒）
        'prefix' => 'auto_filter_',
    ],

    /*
    |--------------------------------------------------------------------------
    | 默认黑名单字段
    |--------------------------------------------------------------------------
    |
    | 这些字段将被全局排除在自动筛选之外
    |
    */
    'default_blacklist' => [
        'password',
        'password_hash',
        'remember_token',
        'api_token',
        'access_token',
        'refresh_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | 字段类型映射
    |--------------------------------------------------------------------------
    |
    | 自定义字段类型的查询方式
    |
    */
    'field_type_mapping' => [
        'string_types' => [
            'char',
            'varchar',
            'varbinary',
            'binary',
            'tinytext',
            'text',
            'mediumtext',
            'longtext',
            'json'
        ],
        'integer_types' => [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint'
        ],
        'decimal_types' => [
            'decimal',
            'float',
            'double'
        ],
        'date_types' => [
            'date'
        ],
        'datetime_types' => [
            'datetime',
            'timestamp'
        ],
    ],
];
