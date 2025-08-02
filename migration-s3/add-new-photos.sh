#!/bin/bash

# Скрипт для быстрого добавления новых фотографий в S3
# Использовать ПОСЛЕ миграции для добавления новых изображений

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

# Проверка параметров
if [ $# -eq 0 ]; then
    echo "Использование: $0 <путь_к_изображениям> [описание]"
    echo ""
    echo "Примеры:"
    echo "  $0 /path/to/new/photos"
    echo "  $0 /path/to/new/photos \"Фото из поездки в Сочи\""
    echo "  $0 ./new-photos"
    echo ""
    echo "Скрипт загружает изображения в S3 и создает готовый код для вставки в пост."
    exit 1
fi

photos_path="$1"
description="${2:-Новые фотографии}"

# Проверка существования директории
if [ ! -d "$photos_path" ]; then
    error_log "Директория не найдена: $photos_path"
    exit 1
fi

log "Начало загрузки новых фотографий..."
log "Путь к фото: $photos_path"
log "Описание: $description"

# Подсчет изображений
image_files=$(find "$photos_path" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" \) | sort)
total_images=$(echo "$image_files" | wc -l)

if [ $total_images -eq 0 ]; then
    error_log "Изображения не найдены в $photos_path"
    exit 1
fi

log "Найдено $total_images изображений для загрузки"

# Создание уникального префикса для этой партии фото
batch_prefix="batch_$(date +%Y%m%d_%H%M%S)"
uploaded_files=()
failed_files=()

# Функция для загрузки одного файла
upload_single_file() {
    local file_path="$1"
    local filename=$(basename "$file_path")
    local extension="${filename##*.}"
    
    # Создание уникального имени файла
    local base_name="${filename%.*}"
    local s3_filename="${base_name}_${batch_prefix}.${extension}"
    local s3_key="images/$s3_filename"
    
    # Определение MIME типа
    local mime_type=""
    case "$extension" in
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
        
        uploaded_files+=("$s3_filename")
        log "✓ Загружено: $filename → $s3_filename"
        return 0
    else
        failed_files+=("$filename")
        error_log "✗ Ошибка загрузки: $filename"
        return 1
    fi
}

# Загрузка всех файлов
log "Загрузка изображений в S3..."
uploaded_count=0
failed_count=0

echo "$image_files" | while read -r file; do
    if [ -n "$file" ]; then
        if upload_single_file "$file"; then
            uploaded_count=$((uploaded_count + 1))
        else
            failed_count=$((failed_count + 1))
        fi
    fi
done

# Пересчет после загрузки
uploaded_count=${#uploaded_files[@]}
failed_count=${#failed_files[@]}

log "Загрузка завершена: $uploaded_count успешно, $failed_count ошибок"

# Проверка загруженных файлов
log "Проверка доступности загруженных файлов..."
accessible_files=()
for filename in "${uploaded_files[@]}"; do
    file_url="$S3_BASE_URL/images/$filename"
    if curl -s -I "$file_url" | grep -q "200 OK"; then
        accessible_files+=("$filename")
        log "✓ Доступен: $file_url"
    else
        error_log "✗ Недоступен: $file_url"
    fi
done

# Создание кода для вставки в пост
if [ ${#accessible_files[@]} -gt 0 ]; then
    log "Создание кода для вставки в пост..."
    
    # Файл с готовым кодом
    code_file="./migration-s3/ready-code-$(date +%Y%m%d_%H%M%S).md"
    
    {
        echo "# Готовый код для вставки в пост"
        echo ""
        echo "**Описание:** $description"
        echo "**Дата:** $(date)"
        echo "**Загружено изображений:** ${#accessible_files[@]}"
        echo ""
        echo "## Код для галереи"
        echo ""
        echo "\`\`\`markdown"
        echo "{{< gallery >}}"
        for filename in "${accessible_files[@]}"; do
            echo "{{< figure src=\"$S3_BASE_URL/images/$filename\" >}}"
        done
        echo "{{< /gallery >}}"
        echo "\`\`\`"
        echo ""
        echo "## Код для отдельных изображений"
        echo ""
        echo "\`\`\`markdown"
        for filename in "${accessible_files[@]}"; do
            echo "![Описание]($S3_BASE_URL/images/$filename)"
        done
        echo "\`\`\`"
        echo ""
        echo "## Код с figure shortcode"
        echo ""
        echo "\`\`\`markdown"
        for filename in "${accessible_files[@]}"; do
            echo "{{< figure src=\"$S3_BASE_URL/images/$filename\" alt=\"Описание\" >}}"
        done
        echo "\`\`\`"
        echo ""
        echo "## Прямые ссылки"
        echo ""
        for filename in "${accessible_files[@]}"; do
            echo "- $S3_BASE_URL/images/$filename"
        done
        echo ""
        echo "---"
        echo "*Код создан автоматически скриптом add-new-photos.sh*"
        
    } > "$code_file"
    
    log "Готовый код создан: $code_file"
    
    # Показать краткий пример кода
    echo ""
    echo "=== ГОТОВЫЙ КОД ДЛЯ ВСТАВКИ ==="
    echo ""
    echo "{{< gallery >}}"
    for filename in "${accessible_files[@]}"; do
        echo "{{< figure src=\"$S3_BASE_URL/images/$filename\" >}}"
    done
    echo "{{< /gallery >}}"
    echo ""
    echo "Полный код сохранен в: $code_file"
fi

# Создание отчета
report_file="./migration-s3/upload-report-$(date +%Y%m%d_%H%M%S).txt"
{
    echo "Отчет о загрузке фотографий"
    echo "=========================="
    echo "Дата: $(date)"
    echo "Путь к исходным файлам: $photos_path"
    echo "Описание: $description"
    echo "Batch prefix: $batch_prefix"
    echo ""
    echo "Статистика:"
    echo "- Найдено изображений: $total_images"
    echo "- Загружено успешно: $uploaded_count"
    echo "- Ошибок загрузки: $failed_count"
    echo "- Доступно через CDN: ${#accessible_files[@]}"
    echo ""
    echo "Загруженные файлы:"
    for filename in "${uploaded_files[@]}"; do
        echo "- $filename"
    done
    echo ""
    if [ ${#failed_files[@]} -gt 0 ]; then
        echo "Файлы с ошибками:"
        for filename in "${failed_files[@]}"; do
            echo "- $filename"
        done
        echo ""
    fi
    echo "Готовый код в: $code_file"
} > "$report_file"

# Финальная статистика
log ""
log "=== ИТОГОВАЯ СТАТИСТИКА ==="
log "Найдено изображений: $total_images"
log "Загружено в S3: $uploaded_count"
log "Доступно через CDN: ${#accessible_files[@]}"
log "Ошибок: $failed_count"
log ""

if [ ${#accessible_files[@]} -gt 0 ]; then
    log "🎉 ЗАГРУЗКА ЗАВЕРШЕНА! Новые изображения доступны через S3."
    log "Готовый код для вставки в пост: $code_file"
else
    error_log "❌ НЕ УДАЛОСЬ ЗАГРУЗИТЬ ИЗОБРАЖЕНИЯ. Проверьте настройки S3."
    exit 1
fi

# Инструкции для использования
echo ""
echo "=== ИНСТРУКЦИИ ==="
echo "1. Скопируйте код из файла: $code_file"
echo "2. Вставьте код в нужный пост в директории content/post/"
echo "3. Измените alt-тексты и описания по необходимости"
echo "4. Сохраните пост и соберите сайт"
echo ""
echo "Для добавления еще фотографий запустите:"
echo "  $0 /path/to/more/photos \"Описание новых фото\""