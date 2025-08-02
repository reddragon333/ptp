#!/bin/bash

# Скрипт для загрузки всех изображений в S3
# Этот скрипт загружает все изображения из static/images в S3 bucket

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

# Инициализация
init_migration

log "Начало загрузки изображений в S3..."

# Проверка существования bucket
if ! aws s3 ls "s3://$S3_BUCKET" &> /dev/null; then
    error_log "S3 bucket '$S3_BUCKET' не найден или недоступен"
    exit 1
fi

# Подсчет файлов для загрузки
total_files=$(find "$LOCAL_IMAGES_DIR" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" \) | wc -l)
log "Найдено $total_files изображений для загрузки"

# Счетчик загруженных файлов
uploaded_count=0
error_count=0

# Функция для загрузки файла
upload_file() {
    local file_path="$1"
    local relative_path="${file_path#$LOCAL_IMAGES_DIR/}"
    local s3_key="images/$relative_path"
    
    # Определение MIME типа
    local mime_type=""
    case "${file_path##*.}" in
        jpg|jpeg) mime_type="image/jpeg" ;;
        png) mime_type="image/png" ;;
        gif) mime_type="image/gif" ;;
        webp) mime_type="image/webp" ;;
        *) mime_type="application/octet-stream" ;;
    esac
    
    # Загрузка файла
    if aws s3 cp "$file_path" "s3://$S3_BUCKET/$s3_key" \
        --content-type "$mime_type" \
        --cache-control "max-age=31536000" \
        --metadata-directive REPLACE; then
        
        uploaded_count=$((uploaded_count + 1))
        log "[$uploaded_count/$total_files] Загружено: $relative_path"
    else
        error_count=$((error_count + 1))
        error_log "Ошибка загрузки: $relative_path"
    fi
}

# Загрузка всех изображений
log "Загрузка изображений..."
export -f upload_file log error_log
export LOCAL_IMAGES_DIR S3_BUCKET uploaded_count error_count total_files LOG_FILE ERROR_LOG

find "$LOCAL_IMAGES_DIR" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" \) | while read -r file; do
    upload_file "$file"
done

# Загрузка фавикона и фоновых изображений
log "Загрузка специальных изображений..."

# Фавикон
if [ -f "./static/images/favicon.ico" ]; then
    aws s3 cp "./static/images/favicon.ico" "s3://$S3_BUCKET/images/favicon.ico" \
        --content-type "image/x-icon" \
        --cache-control "max-age=31536000"
    log "Загружен favicon.ico"
fi

# Фоновые изображения
for bg_file in "./static/images/bg-"*.jpg "./static/images/DESKTOP_NEW_1.jpg"; do
    if [ -f "$bg_file" ]; then
        basename_file=$(basename "$bg_file")
        aws s3 cp "$bg_file" "s3://$S3_BUCKET/images/$basename_file" \
            --content-type "image/jpeg" \
            --cache-control "max-age=31536000"
        log "Загружен фоновый файл: $basename_file"
    fi
done

# Настройка публичного доступа для чтения
log "Настройка публичного доступа..."
aws s3api put-object-acl --bucket "$S3_BUCKET" --key "images/" --acl public-read || true

# Применение политики bucket для публичного чтения
cat > "$TEMP_DIR/bucket-policy.json" << EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::$S3_BUCKET/images/*"
        }
    ]
}
EOF

aws s3api put-bucket-policy --bucket "$S3_BUCKET" --policy file://"$TEMP_DIR/bucket-policy.json"

# Статистика загрузки
log "Загрузка завершена!"
log "Загружено файлов: $uploaded_count"
log "Ошибок: $error_count"
log "Общий размер: $(du -sh "$LOCAL_IMAGES_DIR" | cut -f1)"

# Проверка нескольких случайных файлов
log "Проверка доступности загруженных файлов..."
sample_files=($(find "$LOCAL_IMAGES_DIR" -type f -name "*.jpg" | head -3))
for file in "${sample_files[@]}"; do
    relative_path="${file#$LOCAL_IMAGES_DIR/}"
    s3_url="$S3_BASE_URL/images/$relative_path"
    
    if curl -I "$s3_url" &> /dev/null; then
        log "✓ Файл доступен: $s3_url"
    else
        error_log "✗ Файл недоступен: $s3_url"
    fi
done

log "Скрипт 1 завершен. Теперь запустите: ./2-update-content.sh"