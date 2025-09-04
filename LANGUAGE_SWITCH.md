# README Language Switching Guide

This project supports both English and Chinese README files with easy switching capabilities.

## 📋 Available Languages

- **English** (default): `README.md`
- **中文**: `README_CN.md`

## 🔄 How to Switch Languages

### Method 1: Using Make Commands (Recommended)

```bash
# Switch to English (default)
make readme-en

# Switch to Chinese
make readme-cn

# Restore from backup
make readme-restore

# Clean backup files
make clean
```

### Method 2: Using Composer Scripts

```bash
# Switch to English
composer readme:en

# Switch to Chinese  
composer readme:cn

# Restore from backup
composer readme:restore
```

### Method 3: Using Node.js Script

```bash
# Switch to English
node scripts/switch-readme.js en

# Switch to Chinese
node scripts/switch-readme.js cn

# Show help
node scripts/switch-readme.js --help
```

### Method 4: Manual Copy

```bash
# Switch to Chinese
cp README_CN.md README.md

# Switch to English (restore from backup)
cp README.backup.md README.md
```

## 🔒 Backup System

- When switching languages, the current `README.md` is automatically backed up
- Backup files are named `README.backup.md` or `README.backup.{timestamp}.md`
- Use `make readme-restore` to restore from backup
- Use `make clean` to remove all backup files

## 📝 File Structure

```
feiyun-tools/
├── README.md              # Main README (current language)
├── README_CN.md           # Chinese version
├── README.backup.md       # Backup of previous version
├── scripts/
│   └── switch-readme.js   # Language switching script
├── Makefile              # Make commands for easy switching
└── LANGUAGE_SWITCH.md    # This guide
```

## 🎯 Best Practices

1. **Default Language**: Always keep English as the default (`README.md`)
2. **Consistent Updates**: When updating content, update both language versions
3. **Backup Safety**: Don't delete backup files until you're sure the switch worked
4. **Git Commits**: Consider the language when committing README changes

## 🤝 Contributing Translations

When contributing:

1. Update both `README.md` and `README_CN.md`
2. Ensure content consistency between languages
3. Test language switching before submitting PR
4. Follow the same structure and formatting in both versions

## 🔍 Verification

After switching languages, verify:

```bash
# Check current language
head -1 README.md

# English version should start with: # Feiyun Tools
# Chinese version should start with: # 翡云工具包 (Feiyun Tools)
```

## 📞 Support

If you encounter issues with language switching:

1. Check if all required files exist
2. Verify file permissions
3. Try manual copy method as fallback
4. Report issues on GitHub with error details
