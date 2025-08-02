+++
title = 'Название поста'
slug = 'slug-posta'
image = 'images/post-main-photo.jpg'
date = "2025-01-01T00:00:00"
description = 'Краткое описание поста'
disqus_identifier = '999'
+++

<!-- УНИВЕРСАЛЬНЫЙ ШАБЛОН ПОСТА -->
<!-- Выберите нужные элементы и удалите ненужные -->

Введение к посту...

<!-- ОСНОВНОЕ ИЗОБРАЖЕНИЕ -->
![Описание главного фото](/images/post-main-photo.jpg)

## Основной контент

Основной текст поста...

<!-- ДОПОЛНИТЕЛЬНЫЕ ФОТОГРАФИИ -->
![Описание фото](/images/additional-photo.jpg)

<!-- ВИДЕО (YouTube) -->
{{< youtube id="VIDEO_ID" >}}

<!-- КАРТА ЯНДЕКС -->
<!-- 📍 ЛОКАЦИЯ (ОБЯЗАТЕЛЬНОЕ ПОЛЕ) -->
---

📍 Локация
{{< rawhtml >}}
<div class="yandex-map-container">
<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3AВАША_КАРТА_ID&amp;width=800&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
</div>
{{< /rawhtml >}}

{{< rawhtml >}}
{{< back-to-top >}}
{{< /rawhtml >}}

<!-- ГАЛЕРЕЯ ФОТОГРАФИЙ -->
{{< load-photoswipe >}}
{{< gallery caption-effect="fade" >}}
{{< figure src="images/gallery-1.jpg" >}}
{{< figure src="images/gallery-2.jpg" >}}
{{< figure src="images/gallery-3.jpg" >}}
{{< /gallery >}}

Заключение...

<!-- 
ИНСТРУКЦИЯ:
Используйте нужные элементы и удалите ненужные для вашего поста:
- Фотографии: обычные ![...] или галерея {{< gallery >}}
- Видео: {{< youtube id="..." >}}
- 📍 Локация: ОБЯЗАТЕЛЬНОЕ ПОЛЕ - всегда добавляйте карту с местоположением
- Карты: {{< yandex-map id="..." >}}

Примечание: 
- Всегда добавляйте дивайдер (---) перед 📍 Локация
- Используйте {{< back-to-top >}} вместо ручной ссылки
- Размер карты: 800x400 для лучшего отображения 
-->