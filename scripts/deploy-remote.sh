#!/bin/bash

# Deploy script untuk remote server
# Jalankan setelah git pull dari main/production branch

set -e  # Exit on error

APP_DIR="/var/www/ABSENSI_DIGITAL"
LOG_FILE="$APP_DIR/storage/logs/deployment.log"

echo "========== DEPLOYMENT START ==========" >> "$LOG_FILE"
echo "Time: $(date)" >> "$LOG_FILE"

cd "$APP_DIR" || exit 1

log_step() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

backup_db_with_progress() {
    log_step "[0/7] Backup database: preparing backup..."
    log_step "[0/7] Backup database: running backup process..."

    if ! BACKUP_OUTPUT=$(php artisan db:backup --format=sql 2>&1); then
        log_step "[0/7] Backup database: FAILED"
        printf '%s\n' "$BACKUP_OUTPUT" | tee -a "$LOG_FILE"
        echo "Database backup failed. Deployment stopped to protect data."
        exit 1
    fi

    log_step "[0/7] Backup database: backup command finished. Validating result..."
    printf '%s\n' "$BACKUP_OUTPUT" | tee -a "$LOG_FILE"

    BACKUP_NAME=$(printf '%s\n' "$BACKUP_OUTPUT" | tail -n 1 | sed -E 's/^.*Backup created: //')
    if [ -n "$BACKUP_NAME" ] && [ "$BACKUP_NAME" != "$BACKUP_OUTPUT" ]; then
        log_step "[0/7] Backup database: success - $BACKUP_NAME"
    else
        log_step "[0/7] Backup database: success, but backup name could not be parsed. Please check the log."
    fi
}

# 0. Backup database before pulling code or running migrations
# Hal ini penting agar data aman jika deploy/update dari GitHub gagal.
backup_db_with_progress

# Optional: keep only recent backups (7 days)
log_step "[0/7] Cleaning old backups older than 7 days..."
find "$APP_DIR/storage/app/backups" -mindepth 1 -maxdepth 1 -type d -name 'SIMADIS_BACKUP_*' -mtime +7 -exec rm -rf {} + 2>/dev/null || true
log_step "[0/7] Old backup cleanup completed."

# 1. Pull latest changes from production/main branch
echo "[1/7] Pulling latest changes from GitHub..." | tee -a "$LOG_FILE"
git pull origin production 2>&1 | tee -a "$LOG_FILE" || git pull origin main 2>&1 | tee -a "$LOG_FILE"

# 2. Install/update composer dependencies
echo "[2/7] Installing composer dependencies..." | tee -a "$LOG_FILE"
composer install --no-dev --optimize-autoloader 2>&1 | tee -a "$LOG_FILE"

# 3. Run pending migrations
echo "[3/7] Running database migrations..." | tee -a "$LOG_FILE"
php artisan migrate --force 2>&1 | tee -a "$LOG_FILE"

# 4. Seed essential data if needed (Kelas, Semester, etc)
echo "[4/7] Checking and seeding essential data..." | tee -a "$LOG_FILE"
php artisan db:seed --class=TahunAjaranSeeder --force 2>&1 | tee -a "$LOG_FILE" || true
php artisan db:seed --class=SemesterSeeder --force 2>&1 | tee -a "$LOG_FILE" || true
php artisan db:seed --class=JamBelajarSeeder --force 2>&1 | tee -a "$LOG_FILE" || true
php artisan db:seed --class=RoleSeeder --force 2>&1 | tee -a "$LOG_FILE" || true

# 5. Clear application cache
echo "[5/7] Clearing application cache..." | tee -a "$LOG_FILE"
php artisan cache:clear 2>&1 | tee -a "$LOG_FILE"
php artisan config:cache 2>&1 | tee -a "$LOG_FILE"
php artisan route:cache 2>&1 | tee -a "$LOG_FILE"
php artisan view:cache 2>&1 | tee -a "$LOG_FILE"

# 6. Set proper permissions
echo "[6/7] Setting file permissions..." | tee -a "$LOG_FILE"
chmod -R 755 "$APP_DIR/storage" 2>&1 | tee -a "$LOG_FILE"
chmod -R 755 "$APP_DIR/bootstrap/cache" 2>&1 | tee -a "$LOG_FILE"
chown -R www-data:www-data "$APP_DIR/storage" 2>&1 | tee -a "$LOG_FILE" || true

# 7. Verify deployment
echo "[7/7] Verifying deployment..." | tee -a "$LOG_FILE"
php artisan tinker --execute="
\$kelas = \App\Models\Kelas::count();
echo 'Total Kelas: ' . \$kelas . PHP_EOL;
" 2>&1 | tee -a "$LOG_FILE"

echo "========== DEPLOYMENT SUCCESS ==========" >> "$LOG_FILE"
echo "Deployment completed successfully!"
echo "Check log: $LOG_FILE"
