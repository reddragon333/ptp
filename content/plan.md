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
<link rel="stylesheet" href="/css/step-form.css">

<!-- Подключаем скрипт загрузки карточек поездок -->
<script src="/js/upcoming-trips.js"></script>
{{< /rawhtml >}}

Хотите присоединится к поездке? Ознакомьтесь с условиями участия и заполните форму

## Условия участия

### Дронослёты
- **Подача заявки:** минимум за 5 дней до даты поездки
- **Обсуждение деталей:** [Telegram чат "Полёты БВС"](https://t.me/polet_bvs)

### Важная информация
- **Время выезда:** обычно рано утром, в зависимости от удалённости локации
- **Email:** проверьте папку "Нежелательные" - ответ может попасть туда

{{< rawhtml >}}
<div class="travel-form-container">
    <!-- Сообщения об успехе/ошибке -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const error = urlParams.get('error');
        
        if (success) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'form-message form-success';
            messageDiv.textContent = success;
            document.querySelector('.travel-form-container').insertBefore(messageDiv, document.querySelector('.travel-form'));
        }
        
        if (error) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'form-message form-error';
            messageDiv.textContent = error;
            document.querySelector('.travel-form-container').insertBefore(messageDiv, document.querySelector('.travel-form'));
        }

        // Русские сообщения валидации
        const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(function(input) {
            input.addEventListener('invalid', function() {
                if (input.type === 'checkbox') {
                    input.setCustomValidity('Пожалуйста, отметьте этот пункт для продолжения');
                } else if (input.type === 'email') {
                    input.setCustomValidity('Пожалуйста, введите корректный email адрес');
                } else if (input.tagName === 'SELECT') {
                    input.setCustomValidity('Пожалуйста, выберите один из вариантов');
                } else {
                    input.setCustomValidity('Пожалуйста, заполните это поле');
                }
            });
            
            input.addEventListener('input', function() {
                input.setCustomValidity('');
            });
        });

        // Загружаем скрипт шифрования
        const encryptionScript = document.createElement('script');
        encryptionScript.src = '/js/encryption.js';
        encryptionScript.onload = function() {};
        encryptionScript.onerror = function() {};
        document.head.appendChild(encryptionScript);

        // Загружаем скрипт динамического заполнения поездок
        const tripScript = document.createElement('script');
        tripScript.src = '/js/trip-form-loader.js';
        tripScript.onload = function() {
            if (typeof TripFormLoader !== 'undefined') {
                window.tripFormLoader = new TripFormLoader();
                window.tripFormLoader.populateTripsDropdown();
            }
        };
        tripScript.onerror = function() {};
        document.head.appendChild(tripScript);
    });

    // Обработчик отправки формы
    function handleFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('.submit-btn');
        
        // Показываем состояние загрузки
        submitBtn.textContent = 'Отправляем...';
        submitBtn.disabled = true;
        
        fetch('/forms/send_plan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                throw new Error('Сервер вернул не JSON: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            // Очищаем предыдущие сообщения
            form.parentNode.querySelectorAll('.form-message').forEach(el => el.remove());
            if (data.success) {
                // Успех
                const successDiv = document.createElement('div');
                successDiv.className = 'form-message form-success';
                successDiv.textContent = data.message;
                form.parentNode.insertBefore(successDiv, form);
                form.reset();
            } else {
                // Ошибка
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-message form-error';
                errorDiv.textContent = data.error;
                form.parentNode.insertBefore(errorDiv, form);
            }
        })
        .catch(error => {
            // Ошибка сети
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-message form-error';
            errorDiv.textContent = 'Ошибка отправки формы. Попробуйте еще раз.';
            form.parentNode.insertBefore(errorDiv, form);
        })
        .finally(() => {
            // Восстанавливаем кнопку
            submitBtn.textContent = 'Отправить';
            submitBtn.disabled = false;
        });
        
        return false;
    }

    </script>


<!-- Шаги формы -->
<div class="form-steps-indicator">
    <span class="step-dot active" data-step="1">Поездка</span>
    <span class="step-connector"></span>
    <span class="step-dot" data-step="2">Данные</span>
    <span class="step-connector"></span>
    <span class="step-dot" data-step="3">Отправка</span>
</div>

    <form class="travel-form" action="/forms/send_plan.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">

        <!-- ШАГ 1: Выбор поездки -->
        <div class="form-step active" data-step="1">
        <div class="form-group">
            <label>Выберите мероприятие</label>
            <div id="trip-radios" class="trip-radio-group">
                <!-- Радиокнопки загрузятся из JSON -->
            </div>
            <!-- Hidden select для совместимости с формой -->
            <select id="trip_period" name="trip_period" style="display:none">
                <option value="" disabled selected></option>
            </select>
        </div>
        <div class="form-nav"><button type="button" class="button" onclick="goStep(2)">Далее</button></div>
        </div><!-- /step1 -->

        <!-- ШАГ 2: Персональные данные + БВС -->
        <div class="form-step" data-step="2">
        <div class="form-group">
            <label for="name">Фамилия, имя *</label>
            <input type="text" id="name" name="name" placeholder="Введите Вашу фамилию и имя" required>
        </div>
        <div class="form-group">
            <label for="phone">Телефон *</label>
            <input type="tel" id="phone" name="phone" placeholder="Введите телефон" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="ivan@mail.ru">
        </div>
        <div class="form-group">
            <label for="telegram">Ник в Telegram</label>
            <input type="text" id="telegram" name="telegram" placeholder="@ваш_ник">
        </div>
        <p style="font-size:0.8em;color:#888;margin:4px 0 16px;">* Укажите email или Telegram — одно из двух обязательно</p>
        <div class="form-group">
            <label for="bvs_number">Учётный номер БВС *</label>
            <textarea id="bvs_number" name="bvs_number" placeholder="БВС от 0,15 кг — учётный номер, например: 123456789AB&#10;БВС до 0,15 кг — серийный номер с фюзеляжа" style="font-size:0.8em;" required></textarea>
        </div>
        <div class="form-group">
            <label for="bvs_file">Уведомление о постановке на учёт (.pdf)<br><small style="font-weight:400; color:#888;">Для БВС от 0,15 кг обязательно. Для БВС до 0,15 кг — фото с серийным номером запросим отдельно</small></label>
            <div style="margin-top: 10px;">
                <label for="bvs_file" class="file-btn"><i class="icon fa-arrow-down"></i>&nbsp;&nbsp;Выбрать файл</label>
                <span id="file-name" class="file-name-display"></span>
            </div>
            <input type="file" id="bvs_file" name="bvs_file" accept=".pdf" style="display: none;">
        </div>
        <script>
        document.getElementById('bvs_file').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : '';
            document.getElementById('file-name').textContent = fileName ? '✓ ' + fileName : '';
        });
        </script>
        <div class="form-nav"><button type="button" class="button" onclick="goStep(1)">Назад</button><button type="button" class="button" onclick="goStep(3)">Далее</button></div>
        </div><!-- /step2 -->

        <!-- ШАГ 3: Согласия + отправка -->
        <div class="form-step" data-step="3">
        <div class="form-group checkbox-group">
            <label class="checkbox-container">
                <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                <span class="checkmark"></span>
                <span class="privacy-text">
                    Я выражаю своё согласие на обработку персональных данных <sup class="fn">1</sup>
                    <div class="privacy-details">
                        <p>Персональные данные обрабатываются исключительно для получения разрешения на полёты БВС. Согласие действует 1 месяц. Отзыв согласия – заявление на sleep-trip@ya.ru</p>
                    </div>
                </span>
            </label>
        </div>
        <div class="form-group checkbox-group">
            <label class="checkbox-container">
                <input type="checkbox" id="age_consent" name="age_consent" required>
                <span class="checkmark"></span>
                <span class="privacy-text">
                    Мне есть 18 лет <sup class="fn">2</sup>
                </span>
            </label>
        </div>
        <div class="form-nav">
            <button type="button" class="button" onclick="goStep(2)">Назад</button>
            <button type="submit" class="submit-btn">Отправить</button>
        </div>
        </div><!-- /step3 -->
    </form>

<script>
function goStep(n) {
    // Validate current step before moving forward
    const currentStep = document.querySelector('.form-step.active');
    const currentN = parseInt(currentStep.dataset.step);

    if (n > currentN) {
        // Validate required fields in current step
        const required = currentStep.querySelectorAll('[required]');
        let valid = true;
        required.forEach(input => {
            if (!input.value.trim()) {
                input.reportValidity();
                valid = false;
            }
        });
        if (!valid) return;
    }

    // Switch steps
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.querySelector(`.form-step[data-step="${n}"]`).classList.add('active');

    // Update indicators
    document.querySelectorAll('.step-dot').forEach(d => {
        const dn = parseInt(d.dataset.step);
        d.classList.remove('active', 'done');
        if (dn === n) d.classList.add('active');
        else if (dn < n) d.classList.add('done');
    });
    document.querySelectorAll('.step-connector').forEach((c, i) => {
        c.classList.toggle('done', i < n - 1);
    });

    // Scroll to form top
    document.querySelector('.form-steps-indicator').scrollIntoView({ behavior: 'smooth' });
}
</script>

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

