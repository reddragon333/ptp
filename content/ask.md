+++
title = 'Спросить'
slug = 'ask'
disableComments = true
+++
{{< rawhtml >}}
<p class="page-lead">Есть вопрос, хотите предложить маршрут или узнать условия участия — напишите нам.</p><p class="page-lead" style="font-size:0.9em;margin-top:8px;"><a href="/plan/" style="margin-right:20px;">Календарь поездок →</a> <a href="/gallery/">Галерея →</a></p>
{{< /rawhtml >}}

{{< rawhtml >}}
<link rel="stylesheet" href="/css/step-form.css">
<div class="contact-form-container">

    <!-- Индикатор шагов (2 шага) -->
    <div class="wizard-steps">
        <div class="wizard-step active" id="ask-si-1">
            <div class="step-circle">1</div>
            <div class="step-label">Тема</div>
        </div>
        <div class="wizard-connector" id="ask-sc-1"></div>
        <div class="wizard-step" id="ask-si-2">
            <div class="step-circle">2</div>
            <div class="step-label">Контакт</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var container = document.querySelector('.contact-form-container');
        var form = container.querySelector('.contact-form');

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

        // Загружаем шифрование
        var s = document.createElement('script');
        s.src = '/js/encryption.js';
        document.head.appendChild(s);

        // Wizard (2 шага)
        var currentStep = 1;

        function updateWizard(step) {
            for (var i = 1; i <= 2; i++) {
                var si = document.getElementById('ask-si-' + i);
                si.classList.toggle('active', i === step);
                si.classList.toggle('completed', i < step);
            }
            document.getElementById('ask-sc-1').classList.toggle('completed', step > 1);
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
            if (currentStep === 2) {
                var email = form.querySelector('#ask-email').value.trim();
                var tg = form.querySelector('#ask-telegram').value.trim();
                if (!email && !tg) {
                    var tgField = form.querySelector('#ask-telegram');
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
        [1, 2].forEach(function(n) {
            document.getElementById('ask-si-' + n).addEventListener('click', function() {
                if (n < currentStep) updateWizard(n);
            });
        });

        updateWizard(1);
    });

    function handleAskSubmit(event) {
        event.preventDefault();
        var form = event.target;
        var submitBtn = form.querySelector('.submit-btn');
        submitBtn.textContent = 'Отправляем...';
        submitBtn.disabled = true;

        fetch('/forms/send_ask.php', { method: 'POST', body: new FormData(form) })
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
                    for (var i = 1; i <= 2; i++) {
                        var si = document.getElementById('ask-si-' + i);
                        si.classList.toggle('active', i === 1);
                        si.classList.remove('completed');
                    }
                    document.getElementById('ask-sc-1').classList.remove('completed');
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
                submitBtn.textContent = 'Отправить вопрос';
                submitBtn.disabled = false;
            });
        return false;
    }
    </script>

    <form class="contact-form" action="/forms/send_ask.php" method="POST" onsubmit="return handleAskSubmit(event)">

        <!-- Шаг 1: Тема -->
        <div class="form-step-panel active" data-panel="1">
            <div class="form-group">
                <label for="subject">Тема *</label>
                <select id="subject" name="subject" required>
                    <option value="" disabled selected>Выберите тему</option>
                    <option value="работа сайта">Работа сайта</option>
                    <option value="запланированные мероприятия">Запланированные мероприятия</option>
                    <option value="пройденные маршруты">Пройденные маршруты</option>
                    <option value="условия участия">Условия участия</option>
                    <option value="условия сотрудничества">Условия сотрудничества</option>
                    <option value="хочу предложить поездку">Предложить поездку</option>
                    <option value="контакты для связи">Контакты для связи</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message">Ваш вопрос *</label>
                <textarea id="message" name="message" placeholder="Задайте ваш вопрос или предложите свою идею для поездки..." required></textarea>
            </div>
            <div class="form-wizard-nav">
                <button type="button" class="step-next-btn">Далее →</button>
            </div>
        </div>

        <!-- Шаг 2: Контакт + согласие -->
        <div class="form-step-panel" data-panel="2">
            <div class="form-group">
                <label for="name">Ваше имя *</label>
                <input type="text" id="name" name="name" placeholder="Иван" required>
            </div>
            <div class="contact-row">
                <div class="form-group">
                    <label for="ask-email">Email</label>
                    <input type="email" id="ask-email" name="email" placeholder="ivan@mail.ru">
                </div>
                <div class="form-group">
                    <label for="ask-telegram">Telegram</label>
                    <input type="text" id="ask-telegram" name="telegram" placeholder="@ваш_ник">
                </div>
            </div>
            <div class="form-note">
                <p>Email или Telegram — нужно хотя бы одно</p>
            </div>
            <div class="form-group checkbox-group">
                <label class="checkbox-container">
                    <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                    <span class="checkmark"></span>
                    <span class="privacy-text">
                        Я даю согласие на обработку персональных данных (ФЗ №152) <sup class="fn">1</sup>
                        <div class="privacy-details">
                            <p>Данные будут обрабатываться для ответа на ваш вопрос.</p>
                            <p>Согласие действует 1 месяц. Отзыв согласия: <a href="mailto:sleep-trip@ya.ru">sleep-trip@ya.ru</a></p>
                        </div>
                    </span>
                </label>
            </div>
            <div class="form-wizard-nav">
                <button type="submit" class="submit-btn">Отправить вопрос</button>
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
{{< /rawhtml >}}
