# 翡云工具包 (Feiyun Tools)

[![Latest Stable Version](https://poser.pugx.org/feiyun/tools/v/stable)](https://packagist.org/packages/feiyun/tools)
[![Total Downloads](https://poser.pugx.org/feiyun/tools/downloads)](https://packagist.org/packages/feiyun/tools)
[![License](https://poser.pugx.org/feiyun/tools/license)](https://packagist.org/packages/feiyun/tools)

**语言**: [English](README.md) | [中文](README_CN.md)

企业级 Hyperf 辅助工具集合，提供各种实用的开发工具来提升 Hyperf 项目的开发效率。

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

Hyperf 会自动发现并加载配置。

## 🛠️ 可用工具

### Auto Filter 自动筛选工具
智能数据库查询筛选工具，根据请求参数和字段类型自动构建查询条件。

**核心功能**:
- 自动根据字段类型构建查询条件
- 支持关联表字段查询
- 支持字段别名（`_as_` 前缀）避免字段名冲突
- 黑白名单字段过滤
- 内置缓存机制

[查看详细文档 →](./tools/auto-filter/README.md)

### 更多工具 (规划中)
我们计划添加更多实用的企业级工具，如通用导出工具、缓存管理工具等。

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

### Hyperf 配置

包会自动加载配置，无需手动配置。各工具的配置文件可以单独发布：

```bash
# 发布 Auto Filter 配置
php bin/hyperf.php vendor:publish feiyun/tools
```

### 工具管理

使用 `ToolsManager` 类来管理和查看可用工具：

```php
use Feiyun\Tools\ToolsManager;

// 获取所有可用工具
$tools = ToolsManager::getAvailableTools();

// 检查工具是否可用
$isAvailable = ToolsManager::isToolAvailable('auto-filter');
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

- 提交 [Issue](https://github.com/Fwin511/tools/issues)
- 发送邮件至: support@feiwin.cn

## 🔗 相关链接

- [Packagist](https://packagist.org/packages/feiyun/tools)
- [GitHub](https://github.com/Fwin511/tools)

---

**翡云工具包** - 让开发更简单！ 🚀

如果这个包对您有帮助，请给个 ⭐️ Star 支持一下！
