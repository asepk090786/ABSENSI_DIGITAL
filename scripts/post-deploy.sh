#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SETUP_CRON="$APP_DIR/scripts/setup-laravel-cron.sh"

if [ -f "$SETUP_CRON" ]; then
  bash "$SETUP_CRON"
else
  echo "Script setup cron tidak ditemukan: $SETUP_CRON" >&2
  exit 1
fi

echo "Deploy hook selesai. Cron Laravel siap dijalankan."
