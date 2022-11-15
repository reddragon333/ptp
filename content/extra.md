+++
title = 'Найти попутчика'
slug = 'extra'
image = 'images/pic02.jpg'
# description = 'здесь можно добавить подпись'
disableComments = false
+++

{{< rawhtml >}}
<details>
<summary>Выпадающий список</summary>

какой-то текст
+ <details>
    <summary>Еще список</summary>

    еще немного текста
    + <details>
        <summary>И заключительный список</summary>
        еще текст
        текст
      </details>
   </details>
</details>
{{< /rawhtml >}}

форма для заполнения - дата поездки, город (вся важная инфа коротко с ссылкой на подробное описание трипа)

Выберите из календаря ниже поездки

{{< rawhtml >}}
<div data-tockify-component="mini" data-tockify-calendar="testcalendar1111tqtq">
</div>
<script data-cfasync="false" data-tockify-script="embed" src="https://public.tockify.com/browser/embed.js">
</script>
{{< /rawhtml >}}
