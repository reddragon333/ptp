+++
title = 'Планирование поездки'
slug = 'plan'
# image = "https://s3.regru.cloud/sleeptrip-dev/images/pic02.jpg"
# description = 'здесь можно добавить подпись'
disableComments = true
+++

{{< rawhtml >}}
<div data-tockify-component="calendar" data-tockify-calendar="sleeptrip.calendar">
</div>
<script data-cfasync="false" data-tockify-script="embed" src="https://public.tockify.com/browser/embed.js">
</script>
{{< /rawhtml >}}

## Как забронировать поездку

Выберите подходящую поездку из календаря выше и заполните форму ниже:

### 🚁 Поездки с полётами на дронах
- **Бронирование**: минимум за **7 дней** до поездки
- **Обсуждение деталей**: [Telegram @polet_bvs](https://t.me/polet_bvs)

### 🚗 Обычные поездки  
- **Бронирование**: минимум за **2-3 дня** до поездки
- **Свои предложения**: направление, даты, профиль попутчиков
- **Обсуждение деталей**: [Telegram @sleeptrip_rec](https://t.me/sleeptrip_rec)

---

### ⚠️ Важная информация

- **Время выезда**: рано утром (5-6 утра из Москвы) для избежания пробок
- **Проверьте папку "Спам"**: ответные письма могут попадать в нежелательную почту
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
            <label for="phone">Телефон (необязательно)</label>
            <input type="tel" id="phone" name="phone" placeholder="Введите телефон">
        </div>

        <div class="form-group">
            <label for="bvs_number">Дополнительная информация<br>
            <small style="color: #666; font-size: 0.9em;">Учётный номер БВС или свой вариант поездки</small></label>
            <textarea id="bvs_number" name="bvs_number" placeholder="Например: номер дрона, направление, даты или особые пожелания"></textarea>
        </div>

        <div class="form-group">
            <label for="trip_period">Выберите поездку</label>
            <select id="trip_period" name="trip_period" style="background: white; color: #000; font-size: 16px; padding: 15px; border: 1px solid #ccc; width: 100%; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=\"black\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7 10l5 5 5-5z\"/></svg>'); background-repeat: no-repeat; background-position: right 10px center; background-size: 20px;">
                <option value="" style="color: #000;">Нажмите чтобы выбрать поездку</option>
                <option value="Август 2025 (с дронами)" style="color: #000;">Август 2025 (с дронами)</option>
                <option value="Сентябрь 2025 (с дронами)" style="color: #000;">Сентябрь 2025 (с дронами)</option>
                <option value="Октябрь 2025 (с дронами)" style="color: #000;">Октябрь 2025 (с дронами)</option>
                <option value="Свой вариант" style="color: #000;">Свой вариант</option>
            </select>
        </div>

        <div class="form-group">
            <label class="checkbox-label" style="display: flex; align-items: flex-start; font-weight: 400; padding: 0.75rem; border-radius: 8px; transition: all 0.3s ease; cursor: pointer; border: 1px solid #e1e5e9; position: relative;">
                <input type="checkbox" name="consent" value="agree" required style="width: 18px; height: 18px; margin-right: 0.75rem; margin-top: 0.2rem; accent-color: #27ae60; cursor: pointer;">
                <span style="cursor: pointer; user-select: none; line-height: 1.5;">
                    Согласие на обработку персональных данных<br>
                    <small style="color: #666; font-size: 0.9em;">Разрешаю использовать мои данные для оформления заявки</small>
                </span>
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label" style="display: flex; align-items: flex-start; font-weight: 400; padding: 0.75rem; border-radius: 8px; transition: all 0.3s ease; cursor: pointer; border: 1px solid #e1e5e9; position: relative;">
                <input type="checkbox" name="age_confirm" value="18+" required style="width: 18px; height: 18px; margin-right: 0.75rem; margin-top: 0.2rem; accent-color: #27ae60; cursor: pointer;">
                <span style="cursor: pointer; user-select: none; line-height: 1.5;">
                    Подтверждение совершеннолетия<br>
                    <small style="color: #666; font-size: 0.9em;">Мне исполнилось 18 лет</small>
                </span>
            </label>
        </div>

        <button type="submit" class="submit-btn">
            Отправить
        </button>
    </form>
</div>
{{< /rawhtml >}}

