#!/usr/bin/env bash
# Cloud agent: PHP 8.4+ and extensions for PHPUnit dogfood (unit + end-to-end).
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"

ensure_ondrej_if_needed() {
  if apt-cache show php8.4-cli &>/dev/null 2>&1; then
    return 0
  fi
  sudo apt-get update -qq
  sudo apt-get install -y --no-install-recommends software-properties-common ca-certificates gnupg
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -qq
}

if ! php -r 'exit(version_compare(PHP_VERSION, "8.4.1", ">=") ? 0 : 1);' 2>/dev/null; then
  export DEBIAN_FRONTEND=noninteractive
  ensure_ondrej_if_needed
  sudo apt-get install -y --no-install-recommends \
    php8.4-cli \
    php8.4-common \
    php8.4-curl \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-pdo
  if command -v update-alternatives &>/dev/null; then
    sudo update-alternatives --set php /usr/bin/php8.4 2>/dev/null || true
  fi
fi

php -r 'exit(version_compare(PHP_VERSION, "8.4.1", ">=") ? 0 : 1);'

for ext in ctype curl dom json libxml mbstring openssl phar tokenizer xml xmlwriter pcntl pdo; do
  if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
    echo "ERROR: PHP extension missing: ${ext}" >&2
    php -m >&2 || true
    exit 1
  fi
done

export PHP_INI_SCAN_DIR="${repo_root}/.cursor/php-ini:${PHP_INI_SCAN_DIR:-/etc/php/8.4/cli/conf.d}"

php -r 'echo "phpunit cloud PHP ".PHP_VERSION." memory_limit=".ini_get("memory_limit").PHP_EOL;'

cd "$repo_root"
php ./tools/composer install --no-ansi --no-interaction --no-progress
