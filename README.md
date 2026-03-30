# Feiyun Tools

[![Latest Stable Version](https://poser.pugx.org/feiyun/tools/v/stable)](https://packagist.org/packages/feiyun/tools)
[![Total Downloads](https://poser.pugx.org/feiyun/tools/downloads)](https://packagist.org/packages/feiyun/tools)
[![License](https://poser.pugx.org/feiyun/tools/license)](https://packagist.org/packages/feiyun/tools)

**Language**: [English](README.md) | [中文](README_CN.md)

Enterprise-grade Hyperf utility toolkit that provides various practical development tools to improve Hyperf project development efficiency.

## 🚀 Features

- **Modular Design**: Each tool is managed independently and used on demand
- **Zero Configuration**: Ready to use out of the box, no complex configuration required
- **High Performance**: Built-in caching mechanism for optimized performance
- **Enterprise Grade**: Stable toolkit suitable for production environments
- **Easy to Extend**: Easy to add new tools and features

## 📦 Installation

```bash
composer require feiyun/tools
```

Hyperf will automatically discover and load the configuration.

## 🛠️ Available Tools

### Auto Filter Tool
Intelligent database query filtering tool that automatically builds query conditions based on request parameters and field types.

Supports `_as_` alias fields and `_only_` exact-match fields for string columns.

[View Detailed Documentation →](./tools/auto-filter/README.md)

### More Tools (Planned)
We plan to add more practical enterprise-grade tools, such as universal export tools, cache management tools, etc.

## 📁 Project Structure

```
feiyun-tools/
├── src/                          # Core code
│   ├── Providers/               # Service providers
│   │   └── FeiyunToolsServiceProvider.php
│   └── ToolsManager.php         # Tools manager
├── tools/                       # Tools directory
│   └── auto-filter/            # Auto filter tool
│       ├── config/             # Configuration files
│       ├── src/                # Source code
│       │   ├── Contracts/      # Interface definitions
│       │   ├── Support/        # Support classes
│       │   ├── Traits/         # Trait files
│       │   └── Providers/      # Service providers
│       └── README.md           # Tool documentation
├── tests/                       # Test files
└── composer.json               # Composer configuration
```

## 🔧 Configuration

### Hyperf Configuration

The package will automatically load the configuration without manual setup. Configuration files for each tool can be published separately:

```bash
# Publish Auto Filter configuration
php bin/hyperf.php vendor:publish feiyun/tools
```

### Tools Management

Use the `ToolsManager` class to manage and view available tools:

```php
use Feiyun\Tools\ToolsManager;

// Get all available tools
$tools = ToolsManager::getAvailableTools();

// Check if tool is available
$isAvailable = ToolsManager::isToolAvailable('auto-filter');
```

## 🤝 Contributing

Welcome to contribute code! Please follow these steps:

1. Fork this repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add some amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Adding New Tools

If you want to add new tools, please follow this structure:

```
tools/your-tool/
├── config/              # Configuration files
├── src/                 # Source code
│   ├── Contracts/      # Interfaces
│   ├── Providers/      # Service providers
│   └── ...             # Other code
├── tests/              # Test files
└── README.md           # Tool documentation
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙋‍♀️ Support

If you have questions or suggestions, please contact us through:

- Submit [Issue](https://github.com/Fwin511/tools/issues)
- Email: support@feiwin.cn

## 🔗 Related Links

- [Packagist](https://packagist.org/packages/feiyun/tools)
- [GitHub](https://github.com/Fwin511/tools)

---

**Feiyun Tools** - Make development easier! 🚀

If this package helps you, please give a ⭐️ Star to support us!
