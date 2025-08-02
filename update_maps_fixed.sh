#!/bin/bash

# Скрипт для обновления всех Яндекс карт в постах

echo "Обновление карт в постах..."

# Найдем все файлы с картами
files=$(grep -l "api-maps.yandex.ru" content/post/*.md)

for file in $files; do
    echo "Обновляем: $file"
    
    # Заменяем старый формат на новый
    sed -i '' 's|<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=\([^&]*\)&amp;width=[0-9]*&amp;height=[0-9]*&amp;lang=ru_RU&amp;scroll=true"></script>|<div class="yandex-map-container">\
<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=\1\&amp;width=800\&amp;height=400\&amp;lang=ru_RU\&amp;scroll=true"></script>\
</div>|g' "$file"
    
    # Заменяем старые кнопки "Вернуться в начало" на новые
    sed -i '' 's|<a href="#">Вернуться в начало страницы</a>|{{< back-to-top >}}|g' "$file"
done

echo "Готово! Обновлено карт в $(echo "$files" | wc -l) постах."