+++
slug = 'plan-old'
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
    

Желаете отправиться в путешествие?
Просто заполните форму ниже:
1. Для полётов на дронах из списка ниже - минимум за 7 дней до поездки.

Обсудить детали поездок с полётами можно в Телеграм:   https://t.me/polet_bvs

2. Для поездки без полётов на дронах - можно предложить в комментариях свой вариант (направление, даты, профиль попутчиков) минимум за 2-3 дня.

Обсудить детали поездок без полётов можно в Телеграм:   https://t.me/sleeptrip_rec 

Важно! Поездки проходят рано утром (выезд из Москвы в 5-6 утра) с целью минимизировать время нахождения в пробках. Проверьте папку "Нежелательные", так как ответное письмо на заявку может попасть туда в зависимости от почтового клиента.

{{< rawhtml >}}
<div style="background: red; color: white; padding: 10px; text-align: center; margin: 10px 0;">
    НОВАЯ ФОРМА ЗАГРУЖЕНА - ЕСЛИ ВЫ ВИДИТЕ ЭТО, ФОРМА ОБНОВИЛАСЬ
</div>
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
            <label for="bvs_number">Учётный номер БВС (если уже направляли ранее) или предложите направление/даты поездки без БВС (необязательно)</label>
            <textarea id="bvs_number" name="bvs_number" placeholder="Свой вариант поездки или учётный номер дрона"></textarea>
        </div>

        <div class="form-group">
            <label for="trip_period">Выпадающий список</label>
            <select id="trip_period" name="trip_period">
                <option value="">Выберите период</option>
                <option value="Полёты в июне 2025 года">Полёты в июне 2025 года</option>
                <option value="Полёты в июле 2025 года">Полёты в июле 2025 года</option>
                <option value="Полёты в августе 2025 года">Полёты в августе 2025 года</option>
                <option value="Полёты в сентябре 2025 года">Полёты в сентябре 2025 года</option>
                <option value="Свой вариант без БВС">Свой вариант без БВС</option>
            </select>
        </div>

        <div class="form-group">
            <label>Согласие на обработку персональных данных *</label>
            <div style="margin-top: 0.5rem;">
                <label style="display: flex; align-items: flex-start; font-weight: normal; margin-bottom: 0.5rem;">
                    <input type="radio" name="consent" value="agree" required style="margin-right: 0.5rem; margin-top: 0.2rem; width: auto;">
                    <span>Я согласен на обработку персональных данных для оформления заявки, гарантирую, что передаю свои персональные данные</span>
                </label>
                <label style="display: flex; align-items: center; font-weight: normal;">
                    <input type="radio" name="consent" value="disagree" style="margin-right: 0.5rem; width: auto;">
                    <span>Не согласен на обработку персональных данных</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Укажите свой возраст *</label>
            <div style="margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; font-weight: normal; margin-bottom: 0.5rem;">
                    <input type="radio" name="age" value="18+" required style="margin-right: 0.5rem; width: auto;">
                    <span>Мне более 18 лет</span>
                </label>
                <label style="display: flex; align-items: center; font-weight: normal;">
                    <input type="radio" name="age" value="under18" style="margin-right: 0.5rem; width: auto;">
                    <span>Мне менее 18 лет</span>
                </label>
            </div>
        </div>

        <button type="submit" class="submit-btn">
            Отправить
        </button>
    </form>
</div>
{{< /rawhtml >}}


