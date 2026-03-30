# Feiyun Tools Makefile

.PHONY: help readme-en readme-cn test lint changelog-rebuild changelog-validate changelog-release

# Default target
help:
	@echo "📖 Feiyun Tools - Available Commands:"
	@echo ""
	@echo "  readme-en    Switch README to English (default)"
	@echo "  readme-cn    Switch README to Chinese"
	@echo "  test         Run tests"
	@echo "  lint         Run code linting"
	@echo "  install      Install dependencies"
	@echo "  changelog-rebuild  Rebuild CHANGELOG Unreleased from entry files"
	@echo "  changelog-validate Validate changelog entry sync"
	@echo "  changelog-release  Archive unreleased entries into a version section (use VERSION=x.y.z)"
	@echo "  help         Show this help message"
	@echo ""

# Switch README to English
readme-en:
	@echo "🇺🇸 Switching to English README..."
	@cp README.md README.backup.md 2>/dev/null || true
	@echo "✅ Already using English README (default)"

# Switch README to Chinese  
readme-cn:
	@echo "🇨🇳 Switching to Chinese README..."
	@cp README.md README.backup.md 2>/dev/null || true
	@cp README_CN.md README.md
	@echo "✅ README switched to Chinese"

# Restore English README
readme-restore:
	@echo "🔄 Restoring English README..."
	@if [ -f README.backup.md ]; then \
		cp README.backup.md README.md; \
		echo "✅ English README restored"; \
	else \
		echo "❌ No backup found"; \
	fi

# Run tests
test:
	@echo "🧪 Running tests..."
	@composer test

# Run linting
lint:
	@echo "🔍 Running code linting..."
	@composer validate

# Install dependencies
install:
	@echo "📦 Installing dependencies..."
	@composer install

# Rebuild changelog unreleased section
changelog-rebuild:
	@echo "📝 Rebuilding CHANGELOG.md [Unreleased]..."
	@php scripts/changelog-manager.php rebuild-unreleased

# Validate changelog consistency
changelog-validate:
	@echo "🔎 Validating changelog consistency..."
	@php scripts/changelog-manager.php validate

# Create release changelog section
changelog-release:
	@if [ -z "$(VERSION)" ]; then \
		echo "❌ Missing VERSION. Usage: make changelog-release VERSION=2.3.4"; \
		exit 1; \
	fi
	@echo "🚀 Releasing changelog version $(VERSION)..."
	@php scripts/changelog-manager.php release --version $(VERSION)

# Clean backup files
clean:
	@echo "🧹 Cleaning backup files..."
	@rm -f README.backup.*.md README.backup.md
	@echo "✅ Cleanup complete"
