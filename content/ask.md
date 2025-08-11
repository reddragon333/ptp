+++
# title = 'Спросить'
slug = 'ask'
disableComments = true
+++
{{< rawhtml >}}
<h3 align="center">Задать вопрос</h3>
{{< /rawhtml >}}

Если у Вас есть вопрос или Вы хотите предложить свою локацию / тур для поездки, напишите в форме ниже. Мы вам с радостью ответим!

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

        <div class="form-group checkbox-group">
            <label class="checkbox-container">
                <input type="checkbox" id="privacy_consent" name="privacy_consent" required>
                <span class="checkmark"></span>
                <span class="privacy-text">
                    Я согласен на обработку персональных данных
                    <div class="privacy-details">
                        <p><strong>СОГЛАСИЕ на обработку персональных данных</strong></p>
                        <p>Заполнение чек-бокса и нажатие кнопки является подтверждением Вашего согласия на обработку персональных данных с целью ответа на вопрос, заданный на сайте sleeptrip.ru.</p>
                        <p>Предоставленные персональные данные будут обрабатываться в соответствии с положениями Федерального закона РФ №152-ФЗ от 27.07.2006 «О персональных данных».</p>
                        <p>Я выражаю свое согласие на обработку (включая: сбор, запись, систематизацию, накопление, хранение, уточнение, извлечение, использование, передачу, блокирование, обезличивание, удаление, уничтожение) моих персональных данных Оператором (владельцем сайта sleeptrip.ru) для цели ответа на вопрос, заданный на сайте.</p>
                        <p>Настоящее Согласие действует до момента достижения целей обработки или отзыва согласия на обработку, но не более 1 (одного) месяца с момента предоставления Согласия.</p>
                        <p>Заявление об уточнении персональных данных, отзыве настоящего согласия может быть направлено по электронной почте: <a href="mailto:sleep-trip@ya.ru">sleep-trip@ya.ru</a></p>
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

