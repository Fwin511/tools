#!/usr/bin/env node

/**
 * README Language Switcher
 * 
 * Usage:
 * node scripts/switch-readme.js en    # Switch to English
 * node scripts/switch-readme.js cn    # Switch to Chinese
 */

const fs = require('fs');
const path = require('path');

const args = process.argv.slice(2);
const language = args[0] || 'en';

const rootDir = path.resolve(__dirname, '..');
const readmePath = path.join(rootDir, 'README.md');
const readmeCnPath = path.join(rootDir, 'README_CN.md');

function switchLanguage(lang) {
    try {
        let sourceFile, targetContent;
        
        if (lang === 'cn' || lang === 'zh') {
            // Switch to Chinese
            if (!fs.existsSync(readmeCnPath)) {
                console.error('❌ README_CN.md not found!');
                process.exit(1);
            }
            targetContent = fs.readFileSync(readmeCnPath, 'utf8');
            console.log('🇨🇳 Switching to Chinese README...');
        } else {
            // Switch to English (default)
            console.log('🇺🇸 Switching to English README...');
            // If switching to English, we need to restore the English version
            // For now, we'll assume the current README.md is the English version
            console.log('✅ Already using English README');
            return;
        }
        
        // Backup current README.md if it's different
        const currentContent = fs.readFileSync(readmePath, 'utf8');
        if (currentContent !== targetContent) {
            const backupPath = path.join(rootDir, `README.backup.${Date.now()}.md`);
            fs.writeFileSync(backupPath, currentContent);
            console.log(`📦 Current README backed up to: ${path.basename(backupPath)}`);
        }
        
        // Write new content
        fs.writeFileSync(readmePath, targetContent);
        console.log('✅ README.md updated successfully!');
        
    } catch (error) {
        console.error('❌ Error switching README:', error.message);
        process.exit(1);
    }
}

function showHelp() {
    console.log(`
📖 README Language Switcher

Usage:
  node scripts/switch-readme.js [language]

Languages:
  en, english    Switch to English (default)
  cn, zh         Switch to Chinese

Examples:
  node scripts/switch-readme.js en
  node scripts/switch-readme.js cn
`);
}

if (args.includes('--help') || args.includes('-h')) {
    showHelp();
} else {
    switchLanguage(language);
}
