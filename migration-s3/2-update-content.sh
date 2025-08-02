#!/bin/bash

# Скрипт для обновления ссылок на изображения в контенте
# Заменяет все ссылки /images/ на S3 URLs

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

log "Начало обновления ссылок в контенте..."

# Функция для обновления файла
update_file() {
    local file_path="$1"
    local backup_path="$BACKUP_DIR/$(basename "$file_path").backup"
    
    # Создание backup
    cp "$file_path" "$backup_path"
    
    # Временный файл для изменений
    local temp_file="$TEMP_DIR/$(basename "$file_path").tmp"
    
    # Замена ссылок в файле
    sed -E "s|['\"]/?images/([^'\"]*)['\"]|\"$S3_BASE_URL/images/\1\"|g" "$file_path" > "$temp_file"
    
    # Проверка на изменения
    if ! diff -q "$file_path" "$temp_file" &> /dev/null; then
        mv "$temp_file" "$file_path"
        log "Обновлен файл: $file_path"
        return 0
    else
        rm "$temp_file"
        return 1
    fi
}

# Счетчики
updated_files=0
total_files=0

# Обновление всех markdown файлов в content/
log "Обновление markdown файлов..."
while IFS= read -r -d '' file; do
    total_files=$((total_files + 1))
    if update_file "$file"; then
        updated_files=$((updated_files + 1))
    fi
done < <(find "$CONTENT_DIR" -name "*.md" -print0)

# Обновление конфигурационных файлов
log "Обновление конфигурационных файлов..."

# config.toml
if [ -f "$CONFIG_FILE" ]; then
    total_files=$((total_files + 1))
    if update_file "$CONFIG_FILE"; then
        updated_files=$((updated_files + 1))
    fi
fi

# config-prod.toml
if [ -f "$CONFIG_PROD_FILE" ]; then
    total_files=$((total_files + 1))
    if update_file "$CONFIG_PROD_FILE"; then
        updated_files=$((updated_files + 1))
    fi
fi

# Обновление HTML файлов в layouts (если есть хардкод)
log "Проверка HTML файлов в layouts..."
while IFS= read -r -d '' file; do
    total_files=$((total_files + 1))
    if update_file "$file"; then
        updated_files=$((updated_files + 1))
    fi
done < <(find "./layouts" -name "*.html" -print0 2>/dev/null || true)

# Обновление CSS файлов (фоновые изображения)
log "Обновление CSS файлов..."
while IFS= read -r -d '' file; do
    total_files=$((total_files + 1))
    # Специальная обработка для CSS - замена url() функций
    backup_path="$BACKUP_DIR/$(basename "$file").backup"
    cp "$file" "$backup_path"
    
    temp_file="$TEMP_DIR/$(basename "$file").tmp"
    sed -E "s|url\(['\"]?/?images/([^'\"]*)['\"]?\)|url(\"$S3_BASE_URL/images/\1\")|g" "$file" > "$temp_file"
    
    if ! diff -q "$file" "$temp_file" &> /dev/null; then
        mv "$temp_file" "$file"
        updated_files=$((updated_files + 1))
        log "Обновлен CSS файл: $file"
    else
        rm "$temp_file"
    fi
done < <(find "./static/css" "./themes" -name "*.css" -print0 2>/dev/null || true)

# Создание файла с отчетом об изменениях
report_file="$TEMP_DIR/content-changes-report.txt"
log "Создание отчета об изменениях..."

{
    echo "Отчет об обновлении контента"
    echo "============================"
    echo "Дата: $(date)"
    echo "Обновлено файлов: $updated_files из $total_files"
    echo "S3 Base URL: $S3_BASE_URL"
    echo ""
    echo "Измененные файлы:"
    echo "-----------------"
    
    # Список измененных файлов
    find "$BACKUP_DIR" -name "*.backup" | while read -r backup; do
        original="${backup%.backup}"
        if [ -f "$original" ]; then
            echo "- $original"
        fi
    done
    
    echo ""
    echo "Примеры замен:"
    echo "-------------"
    echo "Было: 'images/photo.jpg'"
    echo "Стало: '$S3_BASE_URL/images/photo.jpg'"
    echo ""
    echo "Было: ![Alt](/images/photo.jpg)"
    echo "Стало: ![Alt]($S3_BASE_URL/images/photo.jpg)"
    
} > "$report_file"

log "Отчет создан: $report_file"

# Проверка синтаксиса Hugo (если доступно)
if command -v hugo &> /dev/null; then
    log "Проверка синтаксиса Hugo..."
    if hugo --verbose --printPathWarnings 2>&1 | grep -i error; then
        error_log "Найдены ошибки в Hugo конфигурации"
    else
        log "Hugo синтаксис корректен"
    fi
fi

# Финальная статистика
log "Обновление контента завершено!"
log "Проверено файлов: $total_files"
log "Обновлено файлов: $updated_files"
log "Backup создан в: $BACKUP_DIR"

# Создание файла для отката изменений
rollback_script="$TEMP_DIR/rollback-content.sh"
{
    echo "#!/bin/bash"
    echo "# Скрипт для отката изменений контента"
    echo "set -e"
    echo ""
    find "$BACKUP_DIR" -name "*.backup" | while read -r backup; do
        original="${backup%.backup}"
        if [ -f "$original" ]; then
            echo "cp '$backup' '$original'"
        fi
    done
    echo ""
    echo "echo 'Откат завершен'"
} > "$rollback_script"
chmod +x "$rollback_script"

log "Скрипт отката создан: $rollback_script"
log "Скрипт 2 завершен. Теперь запустите: ./3-update-config.sh"