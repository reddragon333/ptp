+++
title = 'Предстоящие поездки'
slug = 'plan'
disableComments = true
+++
{{< rawhtml >}}
<!-- Календарь поездок из upcoming-trips.json -->
<div id="trips-grid" class="trips-calendar">
    <!-- Карточки поездок будут загружены динамически -->
</div>

<!-- Подключаем стили для карточек поездок -->
<link rel="stylesheet" href="/css/trips-calendar.css">

<!-- Подключаем скрипт загрузки карточек поездок -->
<script src="/js/upcoming-trips.js"></script>
{{< /rawhtml >}}

Хотите присоединиться к поездке? Ознакомьтесь с условиями участия и заполните форму

## Условия участия

### Дронослёты
- **Подача заявки:** минимум за 5 дней до даты поездки
- **Обсуждение деталей:** [Telegram чат "Полёты БВС"](https://t.me/polet_bvs)

### Важная информация
- **Время выезда:** обычно рано утром, в зависимости от удалённости локации
- **Email:** проверьте папку "Нежелательные" - ответ может попасть туда

{{< rawhtml >}}
<div class="travel-form-container">

    <!-- Индикатор шагов -->
    <div class="wizard-steps">
        <div class="wizard-step active" id="plan-si-1">
            <div class="step-circle">1</div>
            <div class="step-label">Поездка</div>
        </div>
        <div class="wizard-connector" id="plan-sc-1"></div>
        <div class="wizard-step" id="plan-si-2">
            <div class="step-circle">2</div>
            <div class="step-label">О вас</div>
        </div>
        <div class="wizard-connector" id="plan-sc-2"></div>
        <div class="wizard-step" id="plan-si-3">
            <div class="step-circle">3</div>
            <div class="step-label">Согласие</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var container = document.querySelector('.travel-form-container');
        var form = container.querySelector('.travel-form');

        if (urlParams.get('success')) {
            var m = document.createElement('div');
            m.className = 'form-message form-success';
            m.textContent = urlParams.get('success');
            container.insertBefore(m, form);
        }
        if (urlParams.get('error')) {
            var e = document.createElement('div');
            e.className = 'form-message form-error';
            e.textContent = urlParams.get('error');
            container.insertBefore(e, form);
        }

        // Русские сообщения валидации
        container.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(f) {
            f.addEventListener('invalid', function() {
                if (f.type === 'checkbox') f.setCustomValidity('Пожалуйста, отметьте этот пункт для продолжения');
                else if (f.type === 'email') f.setCustomValidity('Пожалуйста, введите корректный email адрес');
                else if (f.tagName === 'SELECT') f.setCustomValidity('Пожалуйста, выберите один из вариантов');
                else f.setCustomValidity('Пожалуйста, заполните это поле');
            });
            f.addEventListener('input', function() { f.setCustomValidity(''); });
        });

        // Шифрование
        var encScript = document.createElement('script');
        encScript.src = '/js/encryption.js';
        document.head.appendChild(encScript);

        // Загрузка поездок в dropdown
        var tripSelect = document.getElementById('trip_period');

        function showTripFallback() {
            if (tripSelect.options.length > 1) return; // уже загружено
            tripSelect.style.display = 'none';
            tripSelect.removeAttribute('required');

            var hint = document.createElement('p');
            hint.className = 'form-note-inline';
            hint.style.cssText = 'color:#c0392b; font-size:0.88em; margin:4px 0 8px;';
            hint.textContent = 'Не удалось загрузить список — введите название поездки.';

            var fallbackInput = document.createElement('input');
            fallbackInput.type = 'text';
            fallbackInput.id = 'trip_period_fallback';
            fallbackInput.name = 'trip_period';
            fallbackInput.placeholder = 'Название поездки (напишите вручную)';
            fallbackInput.required = true;

            var group = tripSelect.closest('.form-group');
            group.appendChild(hint);
            group.appendChild(fallbackInput);

            // Привязываем русское сообщение валидации
            fallbackInput.addEventListener('invalid', function() {
                fallbackInput.setCustomValidity('Пожалуйста, заполните это поле');
            });
            fallbackInput.addEventListener('input', function() {
                fallbackInput.setCustomValidity('');
            });
        }

        var tripScript = document.createElement('script');
        tripScript.src = '/js/trip-form-loader.js';
        tripScript.onload = function() {
            if (typeof TripFormLoader !== 'undefined') {
                try {
                    window.tripFormLoader = new TripFormLoader();
                    var result = window.tripFormLoader.populateTripsDropdown();
                    // Если populateTripsDropdown возвращает Promise — ждём его
                    if (result && typeof result.then === 'function') {
                        result.catch(function() { showTripFallback(); });
                    }
                } catch(e) {
                    showTripFallback();
                }
            } else {
                showTripFallback();
            }
            // Страховочный таймер: если через 3 сек список пуст — показываем fallback
            setTimeout(showTripFallback, 3000);
        };
        tripScript.onerror = function() { showTripFallback(); };
        document.head.appendChild(tripScript);

        // Wizard
        var currentStep = 1;

        function updateWizard(step) {
            for (var i = 1; i <= 3; i++) {
                var si = document.getElementById('plan-si-' + i);
                si.classList.toggle('active', i === step);
                si.classList.toggle('completed', i < step);
            }
            for (var j = 1; j <= 2; j++) {
                document.getElementById('plan-sc-' + j).classList.toggle('completed', j < step);
            }
            form.querySelectorAll('.form-step-panel').forEach(function(p, idx) {
                p.classList.toggle('active', idx + 1 === step);
            });
            currentStep = step;
        }

        function validatePanel() {
            var panel = form.querySelector('.form-step-panel.active');
            var fields = panel.querySelectorAll('input[required], select[required], textarea[required]');
            var valid = true;
            fields.forEach(function(f) {
                if (!f.checkValidity()) {
                    if (valid) f.reportValidity();
                    valid = false;
                }
            });
            // Шаг 2: email или telegram обязателен
            if (currentStep === 2) {
                var email = form.querySelector('#plan-email').value.trim();
                var tg = form.querySelector('#plan-telegram').value.trim();
                if (!email && !tg) {
                    var tgField = form.querySelector('#plan-telegram');
                    tgField.setCustomValidity('Укажите email или Telegram ник');
                    tgField.reportValidity();
                    valid = false;
                }
            }
            return valid;
        }

        form.querySelectorAll('.step-next-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (validatePanel()) updateWizard(currentStep + 1);
            });
        });

        // Клик по пройденному шагу — вернуться назад
        [1, 2, 3].forEach(function(n) {
            document.getElementById('plan-si-' + n).addEventListener('click', function() {
                if (n < currentStep) updateWizard(n);
            });
        });

        // Клик по карточке поездки → предвыбор в dropdown (или fallback input) + scroll к форме
        document.querySelectorAll('[data-trip-id]').forEach(function(card) {
            card.addEventListener('click', function() {
                var tripId = card.getAttribute('data-trip-id');
                var select = form.querySelector('#trip_period');
                var fallback = form.querySelector('#trip_period_fallback');
                if (fallback) {
                    // Fallback режим: вставляем текст карточки в input
                    var tripLabel = card.querySelector('.trip-title, .trip-name, h3, h2') || card;
                    fallback.value = tripLabel.textContent.trim() || tripId;
                } else if (select && tripId) {
                    for (var i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === tripId) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                }
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        updateWizard(1);
    });

    function handlePlanSubmit(event) {
        event.preventDefault();
        var form = event.target;
        var submitBtn = form.querySelector('.submit-btn');
        submitBtn.textContent = 'Отправляем...';
        submitBtn.disabled = true;

        fetch('/forms/send_plan.php', { method: 'POST', body: new FormData(form) })
            .then(function(r) { return r.text(); })
            .then(function(text) {
                try { return JSON.parse(text); } catch(e) { throw new Error('Ошибка сервера'); }
            })
            .then(function(data) {
                form.parentNode.querySelectorAll('.form-message').forEach(function(m) { m.remove(); });
                var div = document.createElement('div');
                if (data.success) {
                    div.className = 'form-message form-success';
                    div.textContent = data.message;
                    form.reset();
                    // Сброс wizard
                    for (var i = 1; i <= 3; i++) {
                        var si = document.getElementById('plan-si-' + i);
                        si.classList.toggle('active', i === 1);
                        si.classList.remove('completed');
                    }
                    for (var j = 1; j <= 2; j++) document.getElementById('plan-sc-' + j).classList.remove('completed');
                    form.querySelectorAll('.form-step-panel').forEach(function(p, idx) { p.classList.toggle('active', idx === 0); });
                } else {
                    div.className = 'form-message form-error';
                    div.textContent = data.error;
                }
                form.parentNode.insertBefore(div, form);
            })
            .catch(function() {
                form.parentNode.querySelectorAll('.form-message').forEach(function(m) { m.remove(); });
                var div = document.createElement('div');
                div.className = 'form-message form-error';
                div.textContent = 'Ошибка отправки формы. Попробуйте ещё раз.';
                form.parentNode.insertBefore(div, form);
            })
            .finally(function() {
                submitBtn.textContent = 'Отправить';
                submitBtn.disabled = false;
            });
        return false;
    }
    </script>

    <form class="travel-form" action="/forms/send_plan.php" method="POST" enctype="multipart/form-data" onsubmit="return handlePlanSubmit(event)">

        <!-- Шаг 1: Поездка -->
        <div class="form-step-panel active" data-panel="1">
            <div class="form-group">
                <label for="trip_period">Выберите мероприятие *</label>
                <select id="trip_period" name="trip_period" required>
                    <option value="" disabled selected>Выберите поездку из списка</option>
                    <!-- Опции будут загружены динамически из upcoming-trips.json -->
                </select>
            </div>
            <div class="form-note">
                <p>Если нужной поездки нет — напишите нам через раздел <a href="/ask/">«Спросить»</a></p>
            </div>
            <div class="form-wizard-nav">
                <button type="button" class="step-next-btn">Далее →</button>
            </div>
        </div>

        <!-- Шаг 2: О вас -->
        <div class="form-step-panel" data-panel="2">
            <div class="form-group">
                <label for="name">Имя и фамилия *</label>
                <input type="text" id="name" name="name" placeholder="Иванов Иван" required>
            </div>
            <p class="form-section-title">Контакты</p>
            <div class="form-group">
                <label for="phone">Телефон *</label>
                <input type="tel" id="phone" name="phone" placeholder="+7 900 000-00-00" required>
            </div>
            <div class="contact-row">
                <div class="form-group">
                    <label for="plan-email">Email</label>
                    <input type="email" id="plan-email" name="email" placeholder="ivan@mail.ru">
                </div>
                <div class="form-group">
                    <label for="plan-telegram">Telegram</label>
                    <input type="text" id="plan-telegram" name="telegram" placeholder="@ваш_ник">
                </div>
            </div>
            <div class="form-note">
                <p>Email или Telegram — нужно хотя бы одно</p>
            </div>
            <p class="form-section-title">Дрон (БВС)</p>
            <div class="form-group">
                <label for="bvs_number">Номер дрона (БВС) *</label>
                <textarea id="bvs_number" name="bvs_number" placeholder="БВС от 0,15 кг — учётный номер, например: 123456789AB&#10;БВС до 0,15 кг — серийный номер с фюзеляжа" required></textarea>
            </div>
            <div class="form-group">
                <label for="bvs_file">Уведомление о постановке на учёт (.pdf)<br><small style="font-weight:400; color:#888;">Для БВС от 0,15 кг обязательно. Для БВС до 0,15 кг — фото с серийным номером запросим отдельно</small></label>
                <div style="margin-top: 10px;">
                    <label for="bvs_file" class="file-btn"><i class="icon fa-arrow-up"></i>&nbsp;&nbsp;Выбрать файл</label>
                    <span id="file-name" class="file-name-display"></span>
                </div>
                <input type="file" id="bvs_file" name="bvs_file" accept=".pdf" style="display: none;">
            </div>
            <script>
            document.getElementById('bvs_file').addEventListener('change', function(e) {
                var name = e.target.files[0] ? e.target.files[0].name : '';
                document.getElementById('file-name').textContent = name ? '✓ ' + name : '';
            });
            </script>
            <div class="form-wizard-nav">
                <button type="button" class="step-next-btn">Далее →</button>
            </div>
        </div>

        <!-- Шаг 3: Согласие -->
        <div class="form-step-panel" data-panel="3">
            <div class="form-group checkbox-group">
                <label class="checkbox-container">
                    <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                    <span class="checkmark"></span>
                    <span class="privacy-text">
                        Я даю согласие на обработку персональных данных (ФЗ №152) <sup class="fn">1</sup>
                        <div class="privacy-details">
                            <p>Данные обрабатываются для получения разрешения на полёты БВС.</p>
                            <p>Согласие действует 1 месяц. Отзыв согласия: <a href="mailto:sleep-trip@ya.ru">sleep-trip@ya.ru</a></p>
                        </div>
                    </span>
                </label>
            </div>
            <div class="form-group checkbox-group">
                <label class="checkbox-container">
                    <input type="checkbox" id="age_consent" name="age_consent" required>
                    <span class="checkmark"></span>
                    <span class="privacy-text">
                        Мне исполнилось 18 лет <sup class="fn">2</sup>
                    </span>
                </label>
            </div>
            <div class="form-wizard-nav">
                <button type="submit" class="submit-btn">Отправить заявку</button>
            </div>
        </div>

    </form>
</div>
{{< /rawhtml >}}

{{< rawhtml >}}
<details class="legal-details">
<summary><sup class="fn">1</sup> Постановка «галочки» в чекбоксе является подтверждением вашего согласия — читать полный текст (ФЗ №152)</summary>
<div class="legal-text">
<p>Заполнение «чек-бокса» (проставление «галочки»/«веб-метки» на сайте sleeptrip.ru в графе «Я согласен на обработку персональных данных») и нажатие соответствующей кнопки и (или) направление персональных данных (фамилия, имя, телефон, имя (ник) в Телеграм) в ответном письме на адрес: sleep-trip@ya.ru является подтверждением Вашего согласия на обработку персональных данных либо с целью получения разрешения на полёты беспилотного воздушного судна (БВС), либо с целью ответа на вопрос, заданный на сайте sleeptrip.ru.</p>
<p>Предоставленные персональные данные будут обрабатываться в соответствии с положениями Федерального закона Российской Федерации №152-ФЗ от 27.07.2006 «О персональных данных».</p>
<p>Заявление об уточнении персональных данных, отзыве настоящего согласия может быть направлено по электронной почте по адресу: <a href="mailto:sleep-trip@ya.ru">sleep-trip@ya.ru</a>.</p>
<p><strong>СОГЛАСИЕ НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ</strong></p>
<p>Я выражаю свое согласие на обработку, включая: сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (предоставление, доступ), блокирование, обезличивание, удаление, уничтожение, своих персональных данных (в случае предоставления мной) Оператору (владельцу сайта sleeptrip.ru) для целей:</p>
<ul>
<li>обеспечения получения разрешения на полёты БВС и направления мне информационных сообщений о статусе получения такого разрешения;</li>
<li>ответа на вопрос, заданный на сайте sleeptrip.ru.</li>
</ul>
<p>Я согласен и разрешаю Оператору обрабатывать мои персональные данные с использованием средств автоматизации или без использования таких средств (смешанная обработка).</p>
<p>Я согласен с тем, что мои персональные данные будут переданы третьим лицам – Единой системе организации воздушного движения, сервису «Небосвод» (skyarc.ru) или сервису СППИ (sppi.ivprf.ru), а также сотрудникам полиции (в случае взаимодействия с ними на местности) для реализации целей обработки персональных данных — получения разрешения на полёты БВС. Без передачи данных указанным организациям реализация целей обработки персональных данных будет невозможна.</p>
<p>Настоящее Согласие действует до момента достижения целей обработки или отзыва согласия на обработку, но не более 1 (одного) месяца с момента предоставления Согласия.</p>
</div>
</details>
<p class="legal-note"><sup class="fn">2</sup> Отметка подтверждает, что участнику поездки исполнилось 18 лет.</p>
{{< /rawhtml >}}
