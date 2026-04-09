// Плавное появление lazy-loaded изображений
// Работает с img[loading="lazy"] — добавляет класс .loaded после загрузки
(function () {
  function onImageLoad(img) {
    img.classList.add('loaded');
  }

  // Обработать все lazy-loaded изображения
  document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
    if (img.complete && img.naturalHeight > 0) {
      // Уже загружено (из кэша)
      onImageLoad(img);
    } else {
      img.addEventListener('load', function () {
        onImageLoad(img);
      });
      // На случай ошибки — всё равно показать (без анимации)
      img.addEventListener('error', function () {
        onImageLoad(img);
      });
    }
  });

  // Для динамически добавляемых изображений (MutationObserver)
  if ('MutationObserver' in window) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeName === 'IMG' && node.getAttribute('loading') === 'lazy') {
            if (node.complete && node.naturalHeight > 0) {
              onImageLoad(node);
            } else {
              node.addEventListener('load', function () { onImageLoad(node); });
              node.addEventListener('error', function () { onImageLoad(node); });
            }
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }
})();
