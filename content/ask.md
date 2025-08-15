+++
# title = 'Спросить'
slug = 'ask'
disableComments = true
+++
{{< rawhtml >}}
<h3 align="center">Задать вопрос</h3>
{{< /rawhtml >}}

Если у Вас есть вопрос или Вы хотите предложить свою локацию / тур для поездки, напишите в форме ниже. Мы вам с радостью ответим! 11112222

**Календарь поездок** можно посмотреть [здесь](/plan/)

{{< rawhtml >}}
<div class="contact-form-container">
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
            document.querySelector('.contact-form-container').insertBefore(messageDiv, document.querySelector('.contact-form'));
        }
        
        if (error) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'form-message form-error';
            messageDiv.textContent = error;
            document.querySelector('.contact-form-container').insertBefore(messageDiv, document.querySelector('.contact-form'));
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
        const script = document.createElement('script');
        script.src = '/js/encryption.js';
        script.onload = function() {
            console.log('✅ Скрипт шифрования загружен для формы ask');
        };
        script.onerror = function() {
            console.error('❌ Ошибка загрузки скрипта шифрования');
        };
        document.head.appendChild(script);
    });
    </script>

    <form class="contact-form" action="/send_ask.php" method="POST">
        <div class="form-group">
            <label for="name">Ваше имя *</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="telegram">Ник в Telegram</label>
            <input type="text" id="telegram" name="telegram" placeholder="@ваш_ник">
        </div>

        <div class="form-group">
            <label for="subject">Тема *</label>
            <select id="subject" name="subject" required>
                <option value="">Выберите тему...</option>
                <option value="работа сайта">Работа сайта</option>
                <option value="запланированные мероприятия">Запланированные мероприятия</option>
                <option value="пройденные маршруты">Пройденные маршруты</option>
                <option value="условия участия">Условия участия</option>
                <option value="условия сотрудничества">Условия сотрудничества</option>
                <option value="хочу предложить поездку">Хочу предложить поездку</option>
                <option value="контакты для связи">Контакты для связи</option>
            </select>
        </div>

        <div class="form-group">
            <label for="message">Ваш вопрос *</label>
            <textarea id="message" name="message" placeholder="Задайте ваш вопрос или предложите свою идею для поездки..." required></textarea>
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
                    Я согласен на обработку персональных данных в соответствии с ФЗ №152 <sup>1</sup>
                    <div class="privacy-details">
                        <p>Данные будут обрабатываться для ответа на ваш вопрос.</p>
                        <p>Согласие действует 1 месяц. Отзыв согласия: <a href="mailto:sleep-trip@ya.ru">sleep-trip@ya.ru</a></p>
                    </div>
                </span>
            </label>
        </div>

        <button type="submit" class="submit-btn">
            Отправить вопрос
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

Я выражаю свое согласие на обработку, включая: сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (предоставление, доступ), блокирование, обезличивание, удаление, уничтожение, своих персональных данных (в случае предоставления мной) Оператору (владельцу сайта sleeptrip.ru) для целей:

- обеспечения получения разрешения на полёты БВС и направления мне информационных сообщений о статусе получения такого разрешения;
- ответа на вопрос, заданный на сайте sleeptrip.ru.

Я согласен и разрешаю Оператору обрабатывать мои персональные данные с использованием средств автоматизации или без использования таких средств (смешанная обработка).

Я согласен с тем, что мои персональные данные будут переданы третьим лицам – Единой системе организации воздушного движения, сервису "Небосвод" (skyarc.ru) или сервису СППИ (https://sppi.ivprf.ru), а также сотрудникам полиции (в случае взаимодействия с ними на местности) для реализации целей обработки персональных данных - получения разрешения на полёты БВС. Без передачи данных указанным организациям (сервисам) реализация целей обработки персональных данных будет невозможна.

Настоящее Согласие действует до момента достижения целей обработки или отзыва согласия на обработку, но не более 1 (одного) месяца с момента предоставления Согласия.

