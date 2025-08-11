+++
slug = 'plan'
# description = 'Выберите поездку из календаря'
disableComments = true
+++
{{< rawhtml >}}
<h3 align="center">Выберите поездку</h3>
{{< /rawhtml >}}

{{< rawhtml >}}
<div data-tockify-component="calendar" data-tockify-calendar="sleeptrip.calendar">
</div>
<script data-cfasync="false" data-tockify-script="embed" src="https://public.tockify.com/browser/embed.js">
</script>
{{< /rawhtml >}}

## Как участвовать в поездках

### Поездки с полетами дронов
- **Подача заявки:** минимум за 7 дней до поездки
- **Обсуждение деталей:** [Telegram канал](https://t.me/polet_bvs)

### Поездки без дронов
- **Подача заявки:** минимум за 2-3 дня до поездки
- **Свои варианты:** можете предложить направление, даты и профиль участников
- **Обсуждение деталей:** [Telegram канал](https://t.me/sleeptrip_rec)

### Важная информация
- **Время выезда:** рано утром (5-6 утра из Москвы) для избежания пробок
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

        // Русификация file input
        const fileInput = document.getElementById('pdf_file');
        const fileText = document.getElementById('pdf_file_text');
        if (fileInput && fileText) {
            fileInput.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : '';
                if (fileName) {
                    fileText.textContent = fileName;
                    fileText.classList.add('file-selected');
                } else {
                    fileText.textContent = 'Выберите PDF файл';
                    fileText.classList.remove('file-selected');
                }
            });
        }
    });

    </script>

    <form class="travel-form" action="/send_plan.php" method="POST">
        <div class="form-group">
            <label for="name">Имя *</label>
            <input type="text" id="name" name="name" placeholder="Введите Ваше имя" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" placeholder="Введите Ваш email" required>
        </div>

        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" placeholder="Введите телефон">
        </div>

        <div class="form-group">
            <label for="telegram">Ник в Telegram</label>
            <input type="text" id="telegram" name="telegram" placeholder="@ваш_ник">
        </div>

        <div class="form-group">
            <label for="bvs_number"><strong>Учётный номер БВС</strong> (если уже направляли ранее) или предложите <strong>направление/даты поездки</strong> без БВС</label>
            <textarea id="bvs_number" name="bvs_number" placeholder="Свой вариант поездки или учётный номер дрона"></textarea>
        </div>

        <div class="form-group">
            <label for="trip_period">Выберите поездку</label>
            <select id="trip_period" name="trip_period">
                <option value=""></option>
                <option value="Полёты в июне 2025 года">Полёты в июне 2025 года</option>
                <option value="Полёты в июле 2025 года">Полёты в июле 2025 года</option>
                <option value="Полёты в августе 2025 года">Полёты в августе 2025 года</option>
                <option value="Полёты в сентябре 2025 года">Полёты в сентябре 2025 года</option>
                <option value="Свой вариант без БВС">Свой вариант без БВС</option>
            </select>
        </div>

        <div class="form-group">
            <label for="pdf_file">Прикрепить PDF файл</label>
            <div class="file-input-wrapper" onclick="document.getElementById('pdf_file').click()">
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="file-input-hidden">
                <span class="file-input-text" id="pdf_file_text">Выберите PDF файл</span>
            </div>
            <div class="file-info">
                <small>Максимальный размер файла: 10 МБ</small>
            </div>
        </div>

        <div class="form-group checkbox-group">
            <label class="checkbox-container">
                <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                <span class="checkmark"></span>
                <span class="privacy-text">
                    Я согласен на обработку персональных данных в соответствии с ФЗ №152 *<sup>1</sup>
                    <div class="privacy-details">
                        <p>Данные будут обрабатываться для получения разрешений на полёты БВС и/или организации поездки.</p>
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
                    Мне есть 18 лет *<sup>2</sup>
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

**<sup>1</sup> Согласие на обработку персональных данных:**

Заполнение чек-бокса и нажатие кнопки является подтверждением Вашего согласия на обработку персональных данных с целью получения разрешения на полёты БВС и/или организации поездки на сайте sleeptrip.ru.

Предоставленные персональные данные будут обрабатываться в соответствии с положениями Федерального закона РФ №152-ФЗ от 27.07.2006 «О персональных данных».

Я выражаю свое согласие на обработку (включая: сбор, запись, систематизацию, накопление, хранение, уточнение, извлечение, использование, передачу, блокирование, обезличивание, удаление, уничтожение) моих персональных данных Оператором (владельцем сайта sleeptrip.ru) для целей:
- обеспечения получения разрешения на полёты БВС и направления информационных сообщений о статусе получения разрешения;
- организации поездки и направления информации о поездке.

Я согласен с тем, что мои персональные данные могут быть переданы третьим лицам – Единой системе организации воздушного движения, сервису "Небосвод" (skyarc.ru) или сервису СППИ (https://sppi.ivprf.ru), а также сотрудникам полиции (при необходимости) для получения разрешения на полёты БВС.

Настоящее Согласие действует до момента достижения целей обработки или отзыва согласия на обработку, но не более 1 (одного) месяца с момента предоставления Согласия.

Заявление об уточнении персональных данных, отзыве настоящего согласия может быть направлено по электронной почте: sleep-trip@ya.ru

**<sup>2</sup> Подтверждение совершеннолетия:**

Участие в поездках и мероприятиях разрешено только лицам, достигшим совершеннолетия (18 лет). В случае, если Вам менее 18 лет, для участия в поездке необходимо согласие и личное присутствие законных представителей (родителей, опекунов).

