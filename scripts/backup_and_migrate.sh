#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

TIMESTAMP=$(date -u +%Y%m%dT%H%M%SZ)
BACKUP_DIR="$ROOT/storage/backups/db-backups"
mkdir -p "$BACKUP_DIR"

# Load DB_* from .env if present (best-effort)
if [ -f "$ROOT/.env" ]; then
  export $(grep -v '^#' "$ROOT/.env" | grep '^DB_' | sed 's/ *= */=/g' | xargs 2>/dev/null) || true
fi

DB_CONNECTION=${DB_CONNECTION:-sqlite}
echo "Detected DB_CONNECTION=$DB_CONNECTION"

case "$DB_CONNECTION" in
  sqlite)
    DB_DATABASE=${DB_DATABASE:-database/database.sqlite}
    if [ "$DB_DATABASE" = ":memory:" ]; then
      echo "DB is in-memory; skipping file backup"
    else
      if [ ! -f "$DB_DATABASE" ]; then
        echo "Warning: sqlite DB file not found: $DB_DATABASE"
      else
        cp "$DB_DATABASE" "$BACKUP_DIR/db-$TIMESTAMP.sqlite"
        echo "Backed up sqlite DB to $BACKUP_DIR/db-$TIMESTAMP.sqlite"
      fi
    fi
    ;;

  mysql|mariadb)
    DB_HOST=${DB_HOST:-127.0.0.1}
    DB_PORT=${DB_PORT:-3306}
    DB_DATABASE=${DB_DATABASE:-}
    DB_USERNAME=${DB_USERNAME:-}
    DB_PASSWORD=${DB_PASSWORD:-}
    if ! command -v mysqldump >/dev/null 2>&1; then
      echo "mysqldump not found. Install it or run the backup manually. Aborting." >&2
      exit 1
    fi
    echo "Running mysqldump for $DB_DATABASE@$DB_HOST:$DB_PORT"
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_DIR/db-$TIMESTAMP.sql"
    echo "Backed up MySQL DB to $BACKUP_DIR/db-$TIMESTAMP.sql"
    ;;

  *)
    echo "Unsupported DB_CONNECTION: $DB_CONNECTION" >&2
    exit 1
    ;;
esac

echo "Running migrations..."
php artisan migrate --force

if [ "${NO_TESTS:-0}" -ne 1 ]; then
  echo "Running test suite..."
  if [ -x "./vendor/bin/phpunit" ]; then
    ./vendor/bin/phpunit --colors=never
  else
    echo "phpunit not found in vendor/bin; skipping tests"
  fi
fi

echo "Backup + migrate finished successfully. Backups: $BACKUP_DIR"
