#!/bin/bash

# Конфигурация для миграции на S3
# Отредактируйте эти переменные под ваши настройки

# AWS S3 настройки
S3_BUCKET="your-ptp-bucket"  # Замените на имя вашего S3 bucket
S3_REGION="us-east-1"        # Замените на ваш регион
CDN_URL="https://d1234567890.cloudfront.net"  # Замените на URL вашего CloudFront CDN (опционально)

# Если CDN не настроен, используйте прямой S3 URL
# S3_BASE_URL="https://${S3_BUCKET}.s3.${S3_REGION}.amazonaws.com"
S3_BASE_URL="${CDN_URL}"

# Локальные пути
LOCAL_IMAGES_DIR="./static/images"
LOCAL_PUBLIC_DIR="./public/images"
CONTENT_DIR="./content"
CONFIG_FILE="./config.toml"
CONFIG_PROD_FILE="./config-prod.toml"

# Временные файлы
TEMP_DIR="./migration-temp"
BACKUP_DIR="./migration-backup"

# Лог файлы
LOG_FILE="./migration-s3/migration.log"
ERROR_LOG="./migration-s3/errors.log"

# Функции для логирования
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error_log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" | tee -a "$ERROR_LOG"
}

# Проверка зависимостей
check_dependencies() {
    log "Проверка зависимостей..."
    
    if ! command -v aws &> /dev/null; then
        error_log "AWS CLI не установлен. Установите: https://aws.amazon.com/cli/"
        exit 1
    fi
    
    if ! command -v jq &> /dev/null; then
        error_log "jq не установлен. Установите: brew install jq"
        exit 1
    fi
    
    # Проверка AWS credentials
    if ! aws sts get-caller-identity &> /dev/null; then
        error_log "AWS credentials не настроены. Запустите: aws configure"
        exit 1
    fi
    
    log "Все зависимости установлены"
}

# Создание директорий
create_dirs() {
    mkdir -p "$TEMP_DIR"
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$(dirname "$LOG_FILE")"
    mkdir -p "$(dirname "$ERROR_LOG")"
}

# Инициализация
init_migration() {
    log "Инициализация миграции..."
    create_dirs
    check_dependencies
    
    # Создание backup
    log "Создание backup..."
    cp -r "$CONTENT_DIR" "$BACKUP_DIR/content_backup_$(date +%Y%m%d_%H%M%S)"
    cp "$CONFIG_FILE" "$BACKUP_DIR/config_backup_$(date +%Y%m%d_%H%M%S).toml"
    
    log "Backup создан в $BACKUP_DIR"
}