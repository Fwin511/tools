#!/usr/bin/env php
<?php

declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';
const CHANGELOG_FILE = ROOT_DIR . '/CHANGELOG.md';
const UNRELEASED_DIR = ROOT_DIR . '/.changelog/unreleased';
const RELEASES_DIR = ROOT_DIR . '/.changelog/releases';

const TYPE_ORDER = [
    'Added',
    'Changed',
    'Fixed',
    'Refactored',
    'Docs',
    'Security',
    'Removed',
    'Review',
];

const TYPE_ALIASES = [
    'added' => 'Added',
    'add' => 'Added',
    'new' => 'Added',
    'changed' => 'Changed',
    'change' => 'Changed',
    'updated' => 'Changed',
    'update' => 'Changed',
    'fixed' => 'Fixed',
    'fix' => 'Fixed',
    'bugfix' => 'Fixed',
    'bug' => 'Fixed',
    'refactored' => 'Refactored',
    'refactor' => 'Refactored',
    'docs' => 'Docs',
    'doc' => 'Docs',
    'documentation' => 'Docs',
    'security' => 'Security',
    'removed' => 'Removed',
    'remove' => 'Removed',
    'review' => 'Review',
];

main($argv);

function main(array $argv): void
{
    if (count($argv) < 2) {
        printUsage();
        exit(1);
    }

    $command = $argv[1];
    $options = parseOptions(array_slice($argv, 2));

    try {
        ensureBaseFiles();

        switch ($command) {
            case 'add':
                commandAdd($options);
                break;
            case 'rebuild-unreleased':
                commandRebuildUnreleased();
                break;
            case 'release':
                commandRelease($options);
                break;
            case 'validate':
                commandValidate();
                break;
            case 'help':
            case '--help':
            case '-h':
                printUsage();
                break;
            default:
                throw new RuntimeException(sprintf('Unknown command: %s', $command));
        }
    } catch (Throwable $throwable) {
        fwrite(STDERR, "[error] {$throwable->getMessage()}\n");
        exit(1);
    }
}

function printUsage(): void
{
    echo <<<TEXT
Usage:
  php scripts/changelog-manager.php add --type <type> --scope <scope> --summary <summary> [--compatibility <label>] [--files <comma-separated-paths>] [--validation <text>] [--author <name>] [--notes <text>]
  php scripts/changelog-manager.php rebuild-unreleased
  php scripts/changelog-manager.php validate
  php scripts/changelog-manager.php release --version <x.y.z> [--date <YYYY-MM-DD>] [--allow-empty]

Examples:
  php scripts/changelog-manager.php add --type fixed --scope "Auto Filter" --summary "修复 MorphTo 多级关联过滤" --files "tools/auto-filter/src/Traits/AutoFilterTrait.php,tests/AutoFilterAliasTest.php"
  php scripts/changelog-manager.php rebuild-unreleased
  php scripts/changelog-manager.php release --version 2.3.4 --date 2026-03-30

TEXT;
}

function parseOptions(array $args): array
{
    $options = [];
    $length = count($args);

    for ($i = 0; $i < $length; $i++) {
        $arg = $args[$i];
        if (! str_starts_with($arg, '--')) {
            continue;
        }

        $chunk = substr($arg, 2);
        if ($chunk === '') {
            continue;
        }

        $key = $chunk;
        $value = true;

        if (strpos($chunk, '=') !== false) {
            [$key, $value] = explode('=', $chunk, 2);
        } elseif (isset($args[$i + 1]) && ! str_starts_with($args[$i + 1], '--')) {
            $value = $args[$i + 1];
            $i++;
        }

        $options[$key] = $value;
    }

    return $options;
}

function commandAdd(array $options): void
{
    $type = normalizeType(requiredOption($options, 'type'));
    $scope = trim((string) requiredOption($options, 'scope'));
    $summary = trim((string) requiredOption($options, 'summary'));

    if ($scope === '' || $summary === '') {
        throw new RuntimeException('--scope and --summary must not be empty.');
    }

    $id = trim((string) ($options['id'] ?? ''));
    if ($id === '') {
        $id = date('Ymd-His') . '-' . slugify($type . '-' . $scope);
    }

    $entry = [
        'id' => $id,
        'date' => date('Y-m-d H:i:s'),
        'type' => $type,
        'scope' => $scope,
        'summary' => $summary,
        'compatibility' => trim((string) ($options['compatibility'] ?? 'backward-compatible')),
        'files' => normalizeCsv((string) ($options['files'] ?? '')),
        'validation' => trim((string) ($options['validation'] ?? '')),
        'author' => trim((string) ($options['author'] ?? '')),
        'notes' => trim((string) ($options['notes'] ?? '')),
    ];

    $target = UNRELEASED_DIR . '/' . $id . '.md';
    if (is_file($target)) {
        throw new RuntimeException(sprintf('Entry file already exists: %s', $target));
    }

    $lines = [];
    foreach (['id', 'date', 'type', 'scope', 'summary', 'compatibility'] as $requiredKey) {
        $lines[] = $requiredKey . ': ' . stringifyScalar($entry[$requiredKey]);
    }
    if ($entry['files'] !== []) {
        $lines[] = 'files: ' . implode(', ', $entry['files']);
    }
    if ($entry['validation'] !== '') {
        $lines[] = 'validation: ' . stringifyScalar($entry['validation']);
    }
    if ($entry['author'] !== '') {
        $lines[] = 'author: ' . stringifyScalar($entry['author']);
    }
    if ($entry['notes'] !== '') {
        $lines[] = 'notes: ' . stringifyScalar($entry['notes']);
    }

    file_put_contents($target, implode(PHP_EOL, $lines) . PHP_EOL);

    commandRebuildUnreleased();
    echo sprintf("[ok] added changelog entry: %s\n", relativePath($target));
}

function commandRebuildUnreleased(): void
{
    $entries = loadEntries();
    $body = buildUnreleasedBody($entries);
    writeUnreleasedBody($body);

    echo sprintf("[ok] rebuilt Unreleased section from %d entries\n", count($entries));
}

function commandRelease(array $options): void
{
    $version = trim((string) requiredOption($options, 'version'));
    $date = trim((string) ($options['date'] ?? date('Y-m-d')));
    $allowEmpty = isset($options['allow-empty']);

    if (! preg_match('/^\d+\.\d+\.\d+$/', $version)) {
        throw new RuntimeException('Version must be in x.y.z format (example: 2.3.4).');
    }

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new RuntimeException('Date must be in YYYY-MM-DD format.');
    }

    $entries = loadEntries();
    if (! $allowEmpty && $entries === []) {
        throw new RuntimeException('No unreleased entries found. Use --allow-empty to force.');
    }

    $body = buildUnreleasedBody($entries);
    if (! $allowEmpty && trim($body) === '') {
        throw new RuntimeException('Unreleased body is empty. Add entries first.');
    }

    $content = file_get_contents(CHANGELOG_FILE);
    if ($content === false) {
        throw new RuntimeException('Failed to read CHANGELOG.md');
    }

    if (strpos($content, "## [{$version}] - {$date}") !== false || strpos($content, "## [{$version}]") !== false) {
        throw new RuntimeException(sprintf('Version %s already exists in CHANGELOG.md', $version));
    }

    $content = replaceUnreleasedBodyInContent($content, '');
    $content = insertReleasedSectionInContent($content, $version, $date, $body);
    file_put_contents(CHANGELOG_FILE, $content);

    archiveEntries($entries, $version);
    commandRebuildUnreleased();

    echo sprintf("[ok] released %s on %s\n", $version, $date);
}

function commandValidate(): void
{
    $entries = loadEntries();
    $expected = normalizeBlock(buildUnreleasedBody($entries));
    $current = normalizeBlock(readUnreleasedBody());

    if ($expected !== $current) {
        throw new RuntimeException('CHANGELOG.md [Unreleased] is out of sync. Run: php scripts/changelog-manager.php rebuild-unreleased');
    }

    echo sprintf("[ok] changelog validation passed (%d unreleased entries)\n", count($entries));
}

function ensureBaseFiles(): void
{
    if (! is_file(CHANGELOG_FILE)) {
        throw new RuntimeException('CHANGELOG.md not found.');
    }

    if (! is_dir(UNRELEASED_DIR) && ! mkdir(UNRELEASED_DIR, 0777, true) && ! is_dir(UNRELEASED_DIR)) {
        throw new RuntimeException('Failed to create .changelog/unreleased directory.');
    }

    if (! is_dir(RELEASES_DIR) && ! mkdir(RELEASES_DIR, 0777, true) && ! is_dir(RELEASES_DIR)) {
        throw new RuntimeException('Failed to create .changelog/releases directory.');
    }
}

/**
 * @return array<int, array<string, mixed>>
 */
function loadEntries(): array
{
    $files = glob(UNRELEASED_DIR . '/*.md');
    if ($files === false || $files === []) {
        return [];
    }

    sort($files, SORT_STRING);
    $entries = [];
    foreach ($files as $file) {
        $entries[] = parseEntryFile($file);
    }

    usort($entries, function (array $a, array $b): int {
        $cmp = strcmp((string) $a['date'], (string) $b['date']);
        if ($cmp !== 0) {
            return $cmp;
        }
        return strcmp((string) $a['id'], (string) $b['id']);
    });

    return $entries;
}

/**
 * @return array<string, mixed>
 */
function parseEntryFile(string $file): array
{
    $content = file_get_contents($file);
    if ($content === false) {
        throw new RuntimeException(sprintf('Failed to read entry file: %s', relativePath($file)));
    }

    $entry = [];
    $normalized = str_replace("\r\n", "\n", $content);
    $normalized = str_replace("\r", "\n", $normalized);
    $lines = explode("\n", $normalized);
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }

        if (! preg_match('/^([a-z_]+):\s*(.*)$/', $line, $matches)) {
            throw new RuntimeException(sprintf('Invalid line in %s: %s', relativePath($file), $line));
        }

        $key = $matches[1];
        $value = trim($matches[2]);
        $entry[$key] = $value;
    }

    foreach (['id', 'date', 'type', 'scope', 'summary'] as $required) {
        if (! isset($entry[$required]) || trim((string) $entry[$required]) === '') {
            throw new RuntimeException(sprintf('Missing required key "%s" in %s', $required, relativePath($file)));
        }
    }

    $entry['type'] = normalizeType((string) $entry['type']);
    $entry['compatibility'] = trim((string) ($entry['compatibility'] ?? 'backward-compatible'));
    $entry['files'] = normalizeCsv((string) ($entry['files'] ?? ''));
    $entry['validation'] = trim((string) ($entry['validation'] ?? ''));
    $entry['author'] = trim((string) ($entry['author'] ?? ''));
    $entry['notes'] = trim((string) ($entry['notes'] ?? ''));

    return $entry;
}

/**
 * @param array<int, array<string, mixed>> $entries
 */
function buildUnreleasedBody(array $entries): string
{
    if ($entries === []) {
        return '';
    }

    $grouped = [];
    foreach ($entries as $entry) {
        $type = (string) $entry['type'];
        $grouped[$type][] = $entry;
    }

    $output = [];
    foreach (TYPE_ORDER as $section) {
        if (empty($grouped[$section])) {
            continue;
        }

        $output[] = "### {$section}";
        foreach ($grouped[$section] as $entry) {
            $line = sprintf('- [%s] %s', (string) $entry['scope'], (string) $entry['summary']);

            $meta = [];
            $meta[] = 'compat: ' . (string) $entry['compatibility'];
            if ($entry['files'] !== []) {
                $files = array_map(static fn(string $file): string => '`' . $file . '`', $entry['files']);
                $meta[] = 'files: ' . implode(', ', $files);
            }
            if ($entry['validation'] !== '') {
                $meta[] = 'verify: `' . (string) $entry['validation'] . '`';
            }
            $meta[] = 'entry: ' . (string) $entry['id'];

            $line .= ' (' . implode('; ', $meta) . ')';
            $output[] = $line;
        }
        $output[] = '';
    }

    return trim(implode(PHP_EOL, $output));
}

function writeUnreleasedBody(string $body): void
{
    $content = file_get_contents(CHANGELOG_FILE);
    if ($content === false) {
        throw new RuntimeException('Failed to read CHANGELOG.md');
    }

    $updated = replaceUnreleasedBodyInContent($content, $body);
    file_put_contents(CHANGELOG_FILE, $updated);
}

function readUnreleasedBody(): string
{
    $content = file_get_contents(CHANGELOG_FILE);
    if ($content === false) {
        throw new RuntimeException('Failed to read CHANGELOG.md');
    }

    $bounds = locateUnreleasedBounds($content);
    return trim((string) substr($content, $bounds['body_start'], $bounds['body_end'] - $bounds['body_start']));
}

/**
 * @return array{body_start:int,body_end:int}
 */
function locateUnreleasedBounds(string $content): array
{
    $marker = '## [Unreleased]';
    $start = strpos($content, $marker);
    if ($start === false) {
        throw new RuntimeException('CHANGELOG.md is missing "## [Unreleased]" section.');
    }

    $headingEnd = strpos($content, PHP_EOL, $start);
    if ($headingEnd === false) {
        return [
            'body_start' => strlen($content),
            'body_end' => strlen($content),
        ];
    }

    $bodyStart = $headingEnd + strlen(PHP_EOL);
    $nextSection = strpos($content, PHP_EOL . '## [', $bodyStart);
    if ($nextSection === false) {
        $nextSection = strlen($content);
    }

    return [
        'body_start' => $bodyStart,
        'body_end' => $nextSection,
    ];
}

function replaceUnreleasedBodyInContent(string $content, string $body): string
{
    $bounds = locateUnreleasedBounds($content);
    $replacement = PHP_EOL . PHP_EOL;
    if (trim($body) !== '') {
        $replacement .= trim($body) . PHP_EOL . PHP_EOL;
    }

    return substr($content, 0, $bounds['body_start']) . $replacement . substr($content, $bounds['body_end']);
}

function insertReleasedSectionInContent(string $content, string $version, string $date, string $body): string
{
    $bounds = locateUnreleasedBounds($content);
    $insertPos = $bounds['body_end'];

    $section = "## [{$version}] - {$date}" . PHP_EOL . PHP_EOL;
    if (trim($body) !== '') {
        $section .= trim($body) . PHP_EOL . PHP_EOL;
    }

    return substr($content, 0, $insertPos) . PHP_EOL . $section . substr($content, $insertPos);
}

/**
 * @param array<int, array<string, mixed>> $entries
 */
function archiveEntries(array $entries, string $version): void
{
    $targetDir = RELEASES_DIR . '/' . $version;
    if (! is_dir($targetDir) && ! mkdir($targetDir, 0777, true) && ! is_dir($targetDir)) {
        throw new RuntimeException(sprintf('Failed to create release archive directory: %s', relativePath($targetDir)));
    }

    foreach ($entries as $entry) {
        $id = (string) $entry['id'];
        $source = UNRELEASED_DIR . '/' . $id . '.md';
        if (! is_file($source)) {
            continue;
        }

        $destination = $targetDir . '/' . basename($source);
        if (! rename($source, $destination)) {
            throw new RuntimeException(sprintf('Failed to archive entry %s', relativePath($source)));
        }
    }
}

function requiredOption(array $options, string $key): mixed
{
    if (! array_key_exists($key, $options) || $options[$key] === true || trim((string) $options[$key]) === '') {
        throw new RuntimeException(sprintf('Missing required option --%s', $key));
    }

    return $options[$key];
}

function normalizeType(string $raw): string
{
    $key = strtolower(trim($raw));
    if ($key === '') {
        throw new RuntimeException('Type must not be empty.');
    }

    if (isset(TYPE_ALIASES[$key])) {
        return TYPE_ALIASES[$key];
    }

    foreach (TYPE_ORDER as $type) {
        if (strtolower($type) === $key) {
            return $type;
        }
    }

    throw new RuntimeException(sprintf('Unsupported type "%s". Allowed: %s', $raw, implode(', ', TYPE_ORDER)));
}

/**
 * @return array<int, string>
 */
function normalizeCsv(string $value): array
{
    if (trim($value) === '') {
        return [];
    }

    $items = array_filter(array_map('trim', explode(',', $value)), static fn(string $item): bool => $item !== '');
    return array_values(array_unique($items));
}

function normalizeBlock(string $text): string
{
    $text = str_replace("\r\n", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
    return trim($text);
}

function stringifyScalar(string $value): string
{
    return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
}

function slugify(string $text): string
{
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    return $slug === '' ? 'entry' : $slug;
}

function relativePath(string $path): string
{
    $root = rtrim((string) realpath(ROOT_DIR), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $full = (string) realpath($path);
    if ($full !== '' && str_starts_with($full, $root)) {
        return substr($full, strlen($root));
    }

    return $path;
}
