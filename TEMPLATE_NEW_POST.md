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
{{< yandex-map id="ВАША_КАРТА_ID" title="Название локации" >}}

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
- Карты: {{< yandex-map id="..." >}}

Примечание: кнопка "Вернуться наверх" добавляется автоматически 
-->