#!/usr/bin/env bash
# Full dogfood suite (unit + end-to-end). Done = JUnit failures=0 errors=0; skips OK.
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"
cd "$repo_root"

junit="${1:-phpunit-remote-20260921.xml}"

php ./phpunit --no-coverage --log-junit "$junit"
php -r '
$xml = simplexml_load_file($argv[1]);
if ($xml === false) { fwrite(STDERR, "Invalid JUnit\n"); exit(1); }
$f = (int) ($xml->testsuite["failures"] ?? 0);
$e = (int) ($xml->testsuite["errors"] ?? 0);
$t = (int) ($xml->testsuite["tests"] ?? 0);
$s = (int) ($xml->testsuite["skipped"] ?? 0);
echo "JUnit: tests=$t failures=$f errors=$e skipped=$s\n";
exit ($f === 0 && $e === 0) ? 0 : 1;
' "$junit"
