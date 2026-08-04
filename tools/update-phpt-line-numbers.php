<?php

declare(strict_types=1);

/**
 * Apply line-number (and matching baseline hash) fixes from a PHPUnit failure log.
 *
 * Usage: php tools/update-phpt-line-numbers.php [path/to/phpunit.log]
 */

$logFile = $argv[1] ?? dirname(__DIR__) . '/../reports/phpunit/phpunit-20260804.log';

if (!is_file($logFile)) {
    fwrite(STDERR, "Log file not found: {$logFile}\n");

    exit(1);
}

$log          = file_get_contents($logFile);
$repoRoot     = dirname(__DIR__);
$replacements = 0;
$filesUpdated = [];

$blocks = preg_split('/\r?\n(?=\d+\) .+\.phpt\r?\nFailed asserting)/', $log);

foreach ($blocks as $block) {
    if (!preg_match('/^\d+\) (.+\.phpt)\r?\nFailed asserting/s', $block, $header)) {
        continue;
    }

    $phptPath = $header[1];

    if (!is_file($phptPath)) {
        continue;
    }

    if (!preg_match('/--- Expected\r?\n\+\+\+ Actual\r?\n(.*)/s', $block, $diffMatch)) {
        continue;
    }

    $diffSection = $diffMatch[1];
    $diffSection = preg_replace('/\r?\nC:\\\\Users\\\\Matt\\\\datasmith\\\\phpunit\\\\tests.*$/s', '', $diffSection) ?? $diffSection;

    $content  = file_get_contents($phptPath);
    $original = $content;

    preg_match_all('/^[-+]\s?(.*)$/m', $diffSection, $allLines, PREG_SET_ORDER);

    $minusLines = ['1' => []];
    $plusLines  = ['1' => []];

    foreach ($allLines as $lineMatch) {
        $line = $lineMatch[0];

        if ($line[0] === '-') {
            $minusLines[1][] = $lineMatch[1];
        } elseif ($line[0] === '+') {
            $plusLines[1][] = $lineMatch[1];
        }
    }

    $pairCount = min(count($minusLines[1]), count($plusLines[1]));

    for ($i = 0; $i < $pairCount; $i++) {
        $minus = rtrim($minusLines[1][$i]);
        $plus  = rtrim($plusLines[1][$i]);

        if (applyBaselineXmlReplacement($content, $minus, $plus)) {
            $replacements++;

            continue;
        }

        $updated = deriveUpdatedExpected($minus, $plus);

        if ($updated === null || $updated === $minus) {
            continue;
        }

        if (applyReplacement($content, $minus, $updated)) {
            $replacements++;
        }
    }

    if ($content !== $original) {
        file_put_contents($phptPath, $content);
        $filesUpdated[$phptPath] = true;
    }
}

syncBaselineFixtures($repoRoot);

echo sprintf(
    "Updated %d line references across %d phpt files.\n",
    $replacements,
    count($filesUpdated),
);

function applyBaselineXmlReplacement(string &$content, string $minus, string $plus): bool
{
    if (!preg_match('/^\s*<line number="(\d+)" hash="([^"]+)">$/', ltrim($minus), $m)
        || !preg_match('/^\s*<line number="(\d+)" hash="([^"]+)">$/', ltrim($plus), $p)) {
        return false;
    }

    $pattern = '/^\s*<line number="' . preg_quote($m[1], '/') . '" hash="' . preg_quote($m[2], '/') . '">/m';

    if (!preg_match($pattern, $content)) {
        return false;
    }

    $content = preg_replace(
        $pattern,
        '  <line number="' . $p[1] . '" hash="' . $p[2] . '">',
        $content,
        1,
    ) ?? $content;

    return true;
}

function applyReplacement(string &$content, string $minus, string $updated): bool
{
    if (str_contains($content, $minus)) {
        $content = str_replace($minus, $updated, $content);

        return true;
    }

    $trimmedMinus = ltrim($minus);

    if ($trimmedMinus !== $minus && str_contains($content, $trimmedMinus)) {
        $content = str_replace($trimmedMinus, ltrim($updated), $content);

        return true;
    }

    return false;
}

function deriveUpdatedExpected(string $minus, string $plus): ?string
{
    if ($minus === $plus) {
        return null;
    }

    if (preg_match('/line="(\d+)"/', $minus, $m)
        && preg_match('/line="(\d+)"/', $plus, $p)
        && $m[1] !== $p[1]) {
        return str_replace('line="' . $m[1] . '"', 'line="' . $p[1] . '"', $minus);
    }

    return updatePathLineNumbers($minus, $plus);
}

function updatePathLineNumbers(string $minus, string $plus): ?string
{
    preg_match_all('/([\w.-]+\.php):(\d+)/', $plus, $plusMatches, PREG_SET_ORDER);

    if ($plusMatches === []) {
        return null;
    }

    $result = $minus;

    foreach ($plusMatches as $pm) {
        $filename = $pm[1];
        $newLine  = $pm[2];
        $pattern  = '/' . preg_quote($filename, '/') . ':(\d+)/';

        if (!preg_match($pattern, $result, $oldMatch) || $oldMatch[1] === $newLine) {
            continue;
        }

        $newResult = preg_replace($pattern, $filename . ':' . $newLine, $result, 1);

        if (is_string($newResult)) {
            $result = $newResult;
        }
    }

    return $result !== $minus ? $result : null;
}

function syncBaselineFixtures(string $repoRoot): void
{
    $pairs = [
        'tests/end-to-end/baseline/generate-baseline.phpt' => 'tests/end-to-end/_files/baseline/generate-baseline/baseline.xml',
        'tests/end-to-end/baseline/generate-baseline-suppressed-with-ignored-suppression.phpt' => 'tests/end-to-end/_files/baseline/generate-baseline-suppressed-with-ignored-suppression/baseline.xml',
        'tests/end-to-end/baseline/generate-baseline-with-relative-directory.phpt' => 'tests/end-to-end/_files/baseline/generate-baseline-with-relative-directory/baseline.xml',
    ];

    foreach ($pairs as $phpt => $baseline) {
        $phptPath     = $repoRoot . '/' . $phpt;
        $baselinePath = $repoRoot . '/' . $baseline;

        if (!is_file($phptPath) || !is_file($baselinePath)) {
            continue;
        }

        $phptContent = file_get_contents($phptPath);

        if (!preg_match('/(<\?xml version="1\.0"\?>.*?<\/files>)/s', $phptContent, $match)) {
            continue;
        }

        file_put_contents($baselinePath, $match[1] . "\n");
    }

    $sharedBaseline = $repoRoot . '/tests/end-to-end/_files/baseline/use-baseline/baseline.xml';
    $generatePhpt     = $repoRoot . '/tests/end-to-end/baseline/generate-baseline.phpt';

    if (is_file($generatePhpt) && is_file($sharedBaseline)) {
        $phptContent = file_get_contents($generatePhpt);

        if (preg_match('/(<\?xml version="1\.0"\?>.*?<\/files>)/s', $phptContent, $match)) {
            file_put_contents($sharedBaseline, $match[1] . "\n");
        }
    }
}
