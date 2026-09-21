#!/usr/bin/env bash
# Cloud agent: PHP 8.5 + pcov + Xdebug (coverage mode) for full dogfood baseline (~12 skips).
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"

ensure_ondrej_if_needed() {
  if apt-cache show php8.5-cli &>/dev/null 2>&1; then
    return 0
  fi
  sudo apt-get update -qq
  sudo apt-get install -y --no-install-recommends software-properties-common ca-certificates gnupg
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -qq
}

if ! php -r 'exit(version_compare(PHP_VERSION, "8.5.0", ">=") ? 0 : 1);' 2>/dev/null; then
  export DEBIAN_FRONTEND=noninteractive
  ensure_ondrej_if_needed
  sudo apt-get install -y --no-install-recommends \
    php8.5-cli \
    php8.5-common \
    php8.5-curl \
    php8.5-mbstring \
    php8.5-xml \
    php8.5-pdo \
    php8.5-pcov \
    php8.5-xdebug
  if command -v update-alternatives &>/dev/null; then
    sudo update-alternatives --set php /usr/bin/php8.5 2>/dev/null || true
  fi
fi

php -r 'exit(version_compare(PHP_VERSION, "8.5.0", ">=") ? 0 : 1);'

for ext in ctype curl dom json libxml mbstring openssl phar tokenizer xml xmlwriter pcntl pdo pcov xdebug; do
  if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
    echo "ERROR: PHP extension missing: ${ext}" >&2
    php -m >&2 || true
    exit 1
  fi
done

export PHP_INI_SCAN_DIR="${repo_root}/.cursor/php-ini${PHP_INI_SCAN_DIR:+:${PHP_INI_SCAN_DIR}}"

php -r 'echo "phpunit cloud PHP ".PHP_VERSION
 echo " memory_limit=".ini_get("memory_limit");
 echo " xdebug.mode=".ini_get("xdebug.mode");
 echo " pcov.enabled=".ini_get("pcov.enabled").PHP_EOL;'

cd "$repo_root"
php ./tools/composer install --no-ansi --no-interaction --no-progress
