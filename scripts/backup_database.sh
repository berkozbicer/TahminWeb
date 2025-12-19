#!/bin/bash

# Database Backup Script
# Bu script otomatik database backup'ı alır
# Cron job ile günlük çalıştırılabilir: 0 2 * * * /path/to/backup_database.sh

set -e

# Configuration
PROJECT_PATH="/var/www/at-yarislari-tahmin"
BACKUP_DIR="${PROJECT_PATH}/storage/backups"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME=$(grep DB_DATABASE "${PROJECT_PATH}/.env" | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME "${PROJECT_PATH}/.env" | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD "${PROJECT_PATH}/.env" | cut -d '=' -f2 | sed 's/^"\|"$//g')
DB_HOST=$(grep DB_HOST "${PROJECT_PATH}/.env" | cut -d '=' -f2)

# Backup directory oluştur
mkdir -p "${BACKUP_DIR}"

# Backup dosya adı
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${DATE}.sql.gz"

# MySQL/MariaDB backup
if command -v mysqldump &> /dev/null; then
    mysqldump -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${BACKUP_FILE}"
    echo "Backup completed: ${BACKUP_FILE}"
else
    echo "Error: mysqldump not found"
    exit 1
fi

# Eski backup'ları sil (retention policy)
find "${BACKUP_DIR}" -name "backup_*.sql.gz" -type f -mtime +${RETENTION_DAYS} -delete
echo "Old backups older than ${RETENTION_DAYS} days deleted"

# Backup başarılı mı kontrol et
if [ -f "${BACKUP_FILE}" ] && [ -s "${BACKUP_FILE}" ]; then
    echo "Backup successful: ${BACKUP_FILE}"
    # İsteğe bağlı: Email gönder veya notification
    # mail -s "Database Backup Success" admin@example.com <<< "Backup completed: ${BACKUP_FILE}"
else
    echo "Error: Backup file is empty or missing"
    exit 1
fi


