// Ленивая загрузка фоновых изображений галереи через Intersection Observer
(function () {
  if (!('IntersectionObserver' in window)) {
    // Fallback для старых браузеров — грузим сразу
    document.querySelectorAll('.img[data-bg]').forEach(function (el) {
      el.style.backgroundImage = el.getAttribute('data-bg');
    });
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        var el = entry.target;
        el.style.backgroundImage = el.getAttribute('data-bg');
        observer.unobserve(el);
      }
    });
  }, {
    rootMargin: '200px 0px' // начинаем грузить за 200px до появления
  });

  document.querySelectorAll('.img[data-bg]').forEach(function (el) {
    observer.observe(el);
  });
})();
