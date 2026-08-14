#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="$(command -v php || true)"

if [ -z "$PHP_BIN" ]; then
  echo "PHP tidak ditemukan di PATH. Pastikan PHP sudah terpasang sebelum menyiapkan cron." >&2
  exit 1
fi

if ! command -v crontab >/dev/null 2>&1; then
  echo "crontab tidak tersedia. Pastikan daemon cron sudah terpasang di server." >&2
  exit 1
fi

CRON_LINE="* * * * * cd \"$APP_DIR\" && \"$PHP_BIN\" artisan schedule:run >> /dev/null 2>&1"
TMP_FILE="$(mktemp)"
trap 'rm -f "$TMP_FILE"' EXIT

crontab -l 2>/dev/null > "$TMP_FILE" || true

if grep -Fqx "$CRON_LINE" "$TMP_FILE"; then
  echo "Cron Laravel sudah terpasang."
  exit 0
fi

printf '\n# Laravel scheduler auto-run\n%s\n' "$CRON_LINE" >> "$TMP_FILE"
crontab "$TMP_FILE"

echo "Cron Laravel berhasil ditambahkan."
echo "Entry cron: $CRON_LINE"
