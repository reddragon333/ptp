+++
slug = 'plan'
# description = 'Выберите поездку из календаря'
disableComments = true
+++
{{< rawhtml >}}
<h3 align="center">Предстоящие поездки</h3>

<!-- Календарь поездок из upcoming-trips.json -->
<div id="trips-grid" class="trips-calendar">
    <!-- Карточки поездок будут загружены динамически -->
</div>

<!-- Подключаем стили для карточек поездок -->
<link rel="stylesheet" href="/css/trips-calendar.css">

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
        encryptionScript.onload = function() {
            console.log('✅ Скрипт шифрования загружен для формы plan');
        };
        encryptionScript.onerror = function() {
            console.error('❌ Ошибка загрузки скрипта шифрования');
        };
        document.head.appendChild(encryptionScript);

        // Загружаем скрипт динамического заполнения поездок
        const tripScript = document.createElement('script');
        tripScript.src = '/js/trip-form-loader.js';
        tripScript.onload = function() {
            console.log('✅ Скрипт загрузчика поездок загружен');
            // Принудительно инициализируем после загрузки скрипта
            if (typeof TripFormLoader !== 'undefined') {
                window.tripFormLoader = new TripFormLoader();
                window.tripFormLoader.populateTripsDropdown();
                console.log('🔄 Принудительная инициализация загрузчика поездок');
            }
        };
        tripScript.onerror = function() {
            console.error('❌ Ошибка загрузки скрипта поездок');
        };
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
            console.log('HTTP статус:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            return response.text(); // Сначала получаем как текст
        })
        .then(text => {
            console.log('Ответ сервера:', text);
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                throw new Error('Сервер вернул не JSON: ' + text.substring(0, 100));
            }
        })
        .then(data => {
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

    <form class="travel-form" action="/forms/send_plan.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
        <div class="form-group">
            <label for="name">Фамилия, имя *</label>
            <input type="text" id="name" name="name" placeholder="Введите Вашу фамилию и имя" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="ivan@mail.ru">
        </div>

        <div class="form-group">
            <label for="phone">Телефон *</label>
            <input type="tel" id="phone" name="phone" placeholder="Введите телефон" required>
        </div>

        <div class="form-group">
            <label for="telegram">Ник в Telegram</label>
            <input type="text" id="telegram" name="telegram" placeholder="@ваш_ник">
        </div>

        <div class="form-note">
            <p>* Укажите email или Telegram ник (одно из двух обязательно)</p>
        </div>

        <div class="form-group">
            <label for="bvs_number"><strong>Учётный номер для БВС от 0,15 кг или серийный номер для БВС до 0,15 кг *</strong></label>
            <textarea id="bvs_number" name="bvs_number" placeholder="Укажите учётный номер для БВС от 0,15 кг или серийный номер для БВС до 0,15 кг" required></textarea>
        </div>

        <div class="form-group">
            <label for="bvs_file">Загрузите уведомление в .pdf о постановке на учёт БВС от 0,15 кг, заявки без указанного уведомления не будут рассмотрены. Для БВС до 0,15 кг будут отдельно запрошены фотографии с серийным номером на фюзеляже БВС</label>
            <div style="margin-top: 10px;">
                <label for="bvs_file" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; border-radius: 4px; cursor: pointer; font-weight: bold;">Выбрать файл</label>
                <span id="file-name" style="margin-left: 10px; color: #666;"></span>
            </div>
            <input type="file" id="bvs_file" name="bvs_file" accept=".pdf" style="display: none;">
        </div>

        <script>
        document.getElementById('bvs_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('file-name').textContent = fileName ? '✓ ' + fileName : '';
        });
        </script>

        <div class="form-group">
            <label for="trip_period">Выберите мероприятие</label>
            <select id="trip_period" name="trip_period">
                <option value=""></option>
                <!-- Опции будут загружены динамически из upcoming-trips.json -->
            </select>
        </div>

        <!-- Загрузка файлов временно отключена -->

        <div class="form-group checkbox-group">
            <label class="checkbox-container">
                <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                <span class="checkmark"></span>
                <span class="privacy-text">
                    Я выражаю своё согласие на обработку персональных данных
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
                    Мне есть 18 лет <sup>2</sup>
                </span>
            </label>
        </div>

        <button type="submit" class="submit-btn">
            Отправить
        </button>
    </form>
</div>
{{< /rawhtml >}}

---

### Пояснения к форме:

<sup>1</sup> **Согласие на обработку персональных данных:**

Заполнение «чек-бокса» (проставление «галочки»/ «веб-метки» на сайте sleeptrip.ru в графе «Я согласен на обработку персональных данных») и нажатие соответствующей кнопки и (или) направление персональных данных (фамилия, имя, телефон, имя (ник) в Телеграм) в ответном письме на адрес: sleep-trip@ya.ru является подтверждением Вашего согласия на обработку персональных данных либо с целью получения разрешения на полёты беспилотного воздушного судна (БВС), либо с целью ответа на вопрос, заданный на сайте sleeptrip.ru.

Предоставленные персональные данные будут обрабатываться в соответствии с положениями Федерального закона Российской Федерации №152-ФЗ от 27.07.2006 «О персональных данных».

Заявление об уточнении персональных данных, отзыве настоящего согласия может быть направлено по электронной почте по адресу: sleep-trip@ya.ru.

<div style="text-align: center; font-weight: bold; margin: 20px 0;">СОГЛАСИЕ НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ</div>

Я выражаю свое согласие на обработку, включая: сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (предоставление, доступ), блокирование, обезличивание, удаление, уничтожение, своих персональных данных (в случае предоставления мной) Оператору (владельцу сайта sleeptrip.ru) для целей:

- обеспечения получения разрешения на полёты БВС и направления мне информационных сообщений о статусе получения такого разрешения;
- ответа на вопрос, заданный на сайте sleeptrip.ru.

Я согласен и разрешаю Оператору обрабатывать мои персональные данные с использованием средств автоматизации или без использования таких средств (смешанная обработка).

Я согласен с тем, что мои персональные данные будут переданы третьим лицам – Единой системе организации воздушного движения, сервису "Небосвод" (skyarc.ru) или сервису СППИ (https://sppi.ivprf.ru), а также сотрудникам полиции (в случае взаимодействия с ними на местности) для реализации целей обработки персональных данных - получения разрешения на полёты БВС. Без передачи данных указанным организациям (сервисам) реализация целей обработки персональных данных будет невозможна.

Настоящее Согласие действует до момента достижения целей обработки или отзыва согласия на обработку, но не более 1 (одного) месяца с момента предоставления Согласия.

<sup>2</sup> **Подтверждение совершеннолетия:** Поставление отметки в данном поле подтверждает, что вам исполнилось 18 лет.

---
