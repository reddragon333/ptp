#!/bin/bash

# Скрипт для проверки успешности миграции на S3
# Проверяет доступность изображений, работу сайта и целостность ссылок

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

log "Начало проверки миграции на S3..."

# Счетчики для статистики
total_checks=0
successful_checks=0
failed_checks=0
warnings=0

# Функция для проверки доступности URL
check_url() {
    local url="$1"
    local description="$2"
    
    total_checks=$((total_checks + 1))
    
    if curl -s -I "$url" | grep -q "200 OK"; then
        successful_checks=$((successful_checks + 1))
        log "✓ $description: $url"
        return 0
    else
        failed_checks=$((failed_checks + 1))
        error_log "✗ $description: $url"
        return 1
    fi
}

# 1. Проверка доступности S3 bucket
log "1. Проверка доступности S3 bucket..."
if aws s3 ls "s3://$S3_BUCKET/images/" &> /dev/null; then
    log "✓ S3 bucket доступен"
else
    error_log "✗ S3 bucket недоступен"
    exit 1
fi

# 2. Проверка случайных изображений из S3
log "2. Проверка доступности изображений в S3..."

# Получение списка изображений из S3
s3_images=$(aws s3 ls "s3://$S3_BUCKET/images/" --recursive | grep -E "\.(jpg|jpeg|png|gif|webp)$" | awk '{print $4}' | head -10)

if [ -z "$s3_images" ]; then
    error_log "Изображения не найдены в S3"
    exit 1
fi

# Проверка случайных изображений
echo "$s3_images" | while read -r s3_key; do
    if [ -n "$s3_key" ]; then
        image_url="$S3_BASE_URL/$s3_key"
        check_url "$image_url" "Изображение в S3"
    fi
done

# 3. Проверка специальных файлов
log "3. Проверка специальных файлов..."
special_files=(
    "images/favicon.ico"
    "images/DESKTOP_NEW_1.jpg"
    "images/bg-winter.jpg"
    "images/bg-spring.jpg"
    "images/bg-summer.jpg"
    "images/bg-autumn.jpg"
)

for file in "${special_files[@]}"; do
    special_url="$S3_BASE_URL/$file"
    check_url "$special_url" "Специальный файл" || warnings=$((warnings + 1))
done

# 4. Анализ контента на наличие старых ссылок
log "4. Проверка контента на наличие старых ссылок..."

old_links_count=0
if [ -d "$CONTENT_DIR" ]; then
    # Поиск ссылок, начинающихся с /images/ (старые локальные ссылки)
    old_links=$(grep -r "images/" "$CONTENT_DIR" | grep -v "$S3_BASE_URL" | grep -E "\.(jpg|jpeg|png|gif|webp)" || true)
    
    if [ -n "$old_links" ]; then
        old_links_count=$(echo "$old_links" | wc -l)
        warnings=$((warnings + old_links_count))
        error_log "Найдено $old_links_count старых ссылок на изображения:"
        echo "$old_links" | head -5 | while read -r link; do
            error_log "  $link"
        done
    else
        log "✓ Старые ссылки не найдены"
    fi
fi

# 5. Проверка синтаксиса Hugo
log "5. Проверка синтаксиса Hugo..."
if command -v hugo &> /dev/null; then
    hugo_output=$(hugo --verbose --printPathWarnings 2>&1 || true)
    
    if echo "$hugo_output" | grep -qi "error"; then
        error_log "Hugo сообщает об ошибках:"
        echo "$hugo_output" | grep -i "error" | head -3
        warnings=$((warnings + 1))
    else
        log "✓ Hugo синтаксис корректен"
    fi
else
    log "⚠ Hugo не установлен, пропуск проверки синтаксиса"
    warnings=$((warnings + 1))
fi

# 6. Проверка размера репозитория
log "6. Проверка размера репозитория..."
repo_size_mb=$(du -sm . | cut -f1)
if [ "$repo_size_mb" -lt 1000 ]; then
    log "✓ Размер репозитория: ${repo_size_mb}MB (хорошо)"
else
    error_log "⚠ Размер репозитория: ${repo_size_mb}MB (все еще большой)"
    warnings=$((warnings + 1))
fi

# 7. Проверка отсутствия дубликатов изображений
log "7. Проверка на дубликаты изображений..."
if [ -d "./public/images" ] || [ -d "./static/images" ]; then
    error_log "⚠ Локальные изображения все еще присутствуют"
    error_log "  Рекомендуется удалить после успешной проверки:"
    error_log "  - ./public/images"
    error_log "  - ./static/images"
    warnings=$((warnings + 1))
else
    log "✓ Локальные изображения удалены"
fi

# 8. Тестирование с помощью Hugo server (опционально)
log "8. Тестирование локального сервера Hugo..."
if command -v hugo &> /dev/null; then
    # Запуск Hugo сервера в фоне на короткое время
    hugo server --bind 127.0.0.1 --port 1313 --disableFastRender &
    hugo_pid=$!
    
    # Ждем запуска сервера
    sleep 5
    
    # Проверка доступности главной страницы
    if curl -s "http://localhost:1313/" > /dev/null; then
        log "✓ Локальный сервер Hugo работает"
    else
        error_log "✗ Локальный сервер Hugo недоступен"
        warnings=$((warnings + 1))
    fi
    
    # Остановка сервера
    kill $hugo_pid 2>/dev/null || true
    wait $hugo_pid 2>/dev/null || true
else
    log "⚠ Hugo не установлен, пропуск тестирования сервера"
fi

# 9. Создание отчета
log "9. Создание отчета..."
report_file="./migration-s3/migration-verification-report.md"

{
    echo "# Отчет о проверке миграции на S3"
    echo ""
    echo "**Дата проверки:** $(date)"
    echo "**S3 Bucket:** $S3_BUCKET"
    echo "**CDN URL:** $S3_BASE_URL"
    echo ""
    echo "## Результаты проверки"
    echo ""
    echo "- ✅ **Успешных проверок:** $successful_checks"
    echo "- ❌ **Неудачных проверок:** $failed_checks"
    echo "- ⚠️ **Предупреждений:** $warnings"
    echo "- 📊 **Общее количество проверок:** $total_checks"
    echo ""
    
    if [ $failed_checks -eq 0 ] && [ $warnings -eq 0 ]; then
        echo "## ✅ Статус: УСПЕШНО"
        echo ""
        echo "Миграция прошла успешно! Все изображения доступны через S3."
    elif [ $failed_checks -eq 0 ] && [ $warnings -gt 0 ]; then
        echo "## ⚠️ Статус: УСПЕШНО С ПРЕДУПРЕЖДЕНИЯМИ"
        echo ""
        echo "Миграция прошла в целом успешно, но есть предупреждения, которые стоит рассмотреть."
    else
        echo "## ❌ Статус: ТРЕБУЕТСЯ ВНИМАНИЕ"
        echo ""
        echo "Обнаружены проблемы, которые требуют исправления."
    fi
    
    echo ""
    echo "## Рекомендации"
    echo ""
    
    if [ -d "./public/images" ] || [ -d "./static/images" ]; then
        echo "1. **Удалите локальные изображения** после подтверждения работы:"
        echo "   \`\`\`bash"
        echo "   rm -rf ./public/images"
        echo "   rm -rf ./static/images"
        echo "   \`\`\`"
        echo ""
    fi
    
    echo "2. **Настройте CloudFront CDN** для улучшения производительности"
    echo "3. **Настройте мониторинг** доступности изображений"
    echo "4. **Регулярно проверяйте** работу ссылок"
    echo ""
    echo "## Следующие шаги"
    echo ""
    echo "1. Протестируйте сайт в браузере"
    echo "2. Проверьте работу галерей и изображений"
    echo "3. Убедитесь в корректности отображения на разных устройствах"
    echo "4. Используйте \`add-new-photos.sh\` для добавления новых изображений"
    echo ""
    echo "---"
    echo "*Отчет создан автоматически скриптом 4-verify-migration.sh*"
    
} > "$report_file"

# 10. Финальная статистика
log "Проверка миграции завершена!"
log "Отчет создан: $report_file"
log ""
log "=== ИТОГОВАЯ СТАТИСТИКА ==="
log "Успешных проверок: $successful_checks"
log "Неудачных проверок: $failed_checks"
log "Предупреждений: $warnings"
log "Общее количество проверок: $total_checks"
log ""

if [ $failed_checks -eq 0 ] && [ $warnings -eq 0 ]; then
    log "🎉 МИГРАЦИЯ УСПЕШНА! Все изображения работают через S3."
    log "Теперь вы можете использовать add-new-photos.sh для добавления новых изображений."
elif [ $failed_checks -eq 0 ]; then
    log "✅ МИГРАЦИЯ ЗАВЕРШЕНА с предупреждениями. Проверьте отчет."
else
    log "❌ ОБНАРУЖЕНЫ ПРОБЛЕМЫ. Необходимо исправить ошибки."
    exit 1
fi

# Показать путь к скриптам для дальнейшего использования
log ""
log "Для добавления новых изображений используйте:"
log "  ./migration-s3/add-new-photos.sh /path/to/new/photos"
log ""
log "Для отката изменений (если нужно):"
log "  ./migration-temp/rollback-content.sh"