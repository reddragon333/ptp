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
            <input type="text" id="subject" name="subject" placeholder="Например: Вопрос о поездке в Карелию" required>
        </div>

        <div class="form-group">
            <label for="message">Ваш вопрос *</label>
            <textarea id="message" name="message" placeholder="Задайте ваш вопрос или предложите свою идею для поездки..." required></textarea>
        </div>

        <button type="submit" class="submit-btn">
            Отправить вопрос
        </button>
    </form>
</div>
{{< /rawhtml >}}

