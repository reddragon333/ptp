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

{{< rawhtml >}}
<!-- CTA блок: присоединиться — full-width bars -->
<div class="plan-cta-section">
    <h2 class="plan-cta-title">Хотите присоединиться?</h2>
    <div class="plan-cta-bars">
        <a href="#conditions-block" class="plan-cta-bar" id="cta-conditions-bar" onclick="event.preventDefault();revealConditions();">
            <span class="plan-cta-bar-text"><i class="fa fa-list-alt"></i> Условия участия</span>
            <span class="plan-cta-bar-hint">Требования к заявкам, срокам и оборудованию</span>
            <i class="fa fa-chevron-down plan-cta-bar-arrow"></i>
        </a>
        <a href="#plan-form-anchor" class="plan-cta-bar" onclick="event.preventDefault();document.getElementById('plan-form-anchor').scrollIntoView({behavior:'smooth',block:'start'});">
            <span class="plan-cta-bar-text"><i class="fa fa-pencil-square-o"></i> Подать заявку</span>
            <span class="plan-cta-bar-hint">Заполните форму — выберите поездку и укажите данные</span>
            <i class="fa fa-chevron-down plan-cta-bar-arrow"></i>
        </a>
    </div>
</div>

<!-- Условия участия: два блока -->
<div id="conditions-block" class="plan-conditions-section">
    <div class="plan-condition-block">
        <div class="plan-condition-header">
            <svg class="plan-drone-svg plan-drone-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- 4 rotors -->
                <ellipse cx="5" cy="6" rx="3.5" ry="1.2" stroke="currentColor" stroke-width="1.3" fill="none"/>
                <ellipse cx="19" cy="6" rx="3.5" ry="1.2" stroke="currentColor" stroke-width="1.3" fill="none"/>
                <ellipse cx="5" cy="18" rx="3.5" ry="1.2" stroke="currentColor" stroke-width="1.3" fill="none"/>
                <ellipse cx="19" cy="18" rx="3.5" ry="1.2" stroke="currentColor" stroke-width="1.3" fill="none"/>
                <!-- Arms -->
                <line x1="8" y1="8" x2="10.5" y2="10.5" stroke="currentColor" stroke-width="1.4"/>
                <line x1="16" y1="8" x2="13.5" y2="10.5" stroke="currentColor" stroke-width="1.4"/>
                <line x1="8" y1="16" x2="10.5" y2="13.5" stroke="currentColor" stroke-width="1.4"/>
                <line x1="16" y1="16" x2="13.5" y2="13.5" stroke="currentColor" stroke-width="1.4"/>
                <!-- Body -->
                <rect x="10" y="10" width="4" height="4" rx="1" fill="currentColor"/>
                <!-- Camera dot -->
                <circle cx="12" cy="15.8" r="0.8" fill="currentColor"/>
            </svg>
            <h4>Дронослёты</h4>
        </div>
        <ul class="plan-condition-list">
            <li>Заявка — минимум за <strong>7 дней</strong> до поездки</li>
            <li>Подробности и координация — <a href="https://t.me/polet_bvs">чат «Полёты БВС»</a></li>
        </ul>
    </div>
    <div class="plan-condition-block">
        <div class="plan-condition-header">
            <i class="fa fa-info-circle"></i>
            <h4>Важная информация</h4>
        </div>
        <ul class="plan-condition-list">
            <li>Выезд рано утром — точное время зависит от локации</li>
            <li>Проверяйте папку «Спам» / «Нежелательные» после отправки заявки</li>
        </ul>
    </div>
</div>

<div id="plan-form-anchor"></div>

<script>
function revealConditions() {
    var block = document.getElementById('conditions-block');
    block.classList.add('plan-conditions-highlighted');
    block.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function() {
        block.classList.remove('plan-conditions-highlighted');
    }, 2000);
}
</script>

<style>
/* === CTA Section — Full-width bars === */
.plan-cta-section {
    margin: 2rem 0 1.5rem;
    text-align: center;
}
.plan-cta-title {
    font-size: 1.3rem !important;
    font-weight: 700 !important;
    margin: 0 0 1.2rem 0 !important;
    padding: 0 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    color: #1a202c;
}
.plan-cta-bars {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}
.plan-cta-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;
    padding: 1rem 1.4rem;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(74, 143, 200, 0.18);
    border-radius: 12px;
    text-decoration: none;
    color: #1a202c;
    cursor: pointer;
    transition: all 0.25s ease;
}
.plan-cta-bar:hover {
    background: rgba(74, 143, 200, 0.08);
    border-color: rgba(74, 143, 200, 0.35);
    box-shadow: 0 6px 24px rgba(74, 143, 200, 0.12);
    transform: translateY(-1px);
}
.plan-cta-bar-text {
    font-size: 0.95rem;
    font-weight: 700;
    white-space: nowrap;
}
.plan-cta-bar-text i {
    color: #4a8fc8;
    margin-right: 0.4rem;
}
.plan-cta-bar-hint {
    flex: 1;
    font-size: 0.8rem;
    color: #5a6a7a;
    text-align: left;
}
.plan-cta-bar-arrow {
    color: #4a8fc8;
    font-size: 0.8rem;
    transition: transform 0.2s ease;
}
.plan-cta-bar:hover .plan-cta-bar-arrow {
    transform: translateY(2px);
}

/* === Conditions Section === */
.plan-conditions-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin: 1.5rem 0;
    transition: all 0.4s ease;
}
.plan-conditions-section.plan-conditions-highlighted .plan-condition-block {
    border-color: rgba(74, 143, 200, 0.5);
    box-shadow: 0 0 0 3px rgba(74, 143, 200, 0.15), 0 6px 24px rgba(74, 143, 200, 0.12);
}
.plan-condition-block {
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(0, 0, 0, 0.07);
    border-radius: 14px;
    padding: 1.1rem 1.3rem;
    transition: all 0.35s ease;
}
.plan-condition-block:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}
.plan-condition-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.7rem;
}
.plan-condition-header i,
.plan-condition-header .plan-drone-svg {
    color: #4a8fc8;
    font-size: 1rem;
}
.plan-condition-header h4 {
    margin: 0 !important;
    font-size: 0.92rem !important;
    font-weight: 700 !important;
    color: #1a202c;
    padding: 0 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* Animated drone SVG icon */
.plan-drone-svg {
    display: inline-block;
    animation: droneFloat 3s ease-in-out infinite;
    vertical-align: middle;
}
@keyframes droneFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.plan-condition-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.plan-condition-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.82rem;
    line-height: 1.5;
    color: #3a4a5a;
    padding: 0.3rem 0;
}
.plan-condition-list li i {
    flex-shrink: 0;
    width: 1.1em;
    text-align: center;
    color: #4a8fc8;
    margin-top: 0.15em;
    font-size: 0.85rem;
}
.plan-condition-list a {
    color: #4a7aab;
    text-decoration: none;
    border-bottom: 1px solid rgba(74, 122, 171, 0.3);
}
.plan-condition-list a:hover {
    color: #2a5a8b;
    border-bottom-color: rgba(42, 90, 139, 0.5);
}

/* === Form width: match trip cards and condition blocks (full content width) === */
.travel-form-container {
    max-width: none;
}

/* === Mobile responsive === */
@media (max-width: 600px) {
    .plan-conditions-section {
        grid-template-columns: 1fr;
    }
    .plan-cta-bar {
        flex-wrap: wrap;
        padding: 0.9rem 1rem;
        gap: 0.4rem;
    }
    .plan-cta-bar-text {
        font-size: 0.9rem;
    }
    .plan-cta-bar-hint {
        width: 100%;
        order: 3;
        font-size: 0.75rem;
        padding-left: 1.4rem;
    }
    .plan-cta-bar-arrow {
        order: 2;
        margin-left: auto;
    }
}
</style>
{{< /rawhtml >}}

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
