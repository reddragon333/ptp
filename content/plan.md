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
    

Желаете отправиться в путешествие?
Просто заполните форму ниже:
1. Для полётов на дронах из списка ниже - минимум за 7 дней до поездки.

Обсудить детали поездок с полётами можно в Телеграм:   https://t.me/polet_bvs

2. Для поездки без полётов на дронах - можно предложить в комментариях свой вариант (направление, даты, профиль попутчиков) минимум за 2-3 дня.

Обсудить детали поездок без полётов можно в Телеграм:   https://t.me/sleeptrip_rec 

Важно! Поездки проходят рано утром (выезд из Москвы в 5-6 утра) с целью минимизировать время нахождения в пробках. Проверьте папку "Нежелательные", так как ответное письмо на заявку может попасть туда в зависимости от почтового клиента.

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
            <label for="bvs_number"><strong>Учётный номер БВС</strong> (если уже направляли ранее) или предложите <strong>направление/даты поездки</strong> без БВС (необязательно)</label>
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
            <label style="font-weight: 600; margin-bottom: 1rem; display: block;">Согласие на обработку персональных данных *<sup>1</sup></label>
            <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; background: #fafafa;">
                <label style="display: flex; align-items: flex-start; font-weight: normal; margin-bottom: 0.75rem; cursor: pointer;">
                    <span style="font-size: 18px; margin-right: 0.5rem; margin-top: 0.1rem;">✅</span>
                    <input type="radio" name="consent" value="agree" required style="margin-right: 0.75rem; margin-top: 0.3rem; width: 16px; height: 16px; accent-color: #28a745;">
                    <span style="line-height: 1.5;">Я согласен на <strong>обработку персональных данных</strong> для оформления заявки и гарантирую, что передаю свои персональные данные</span>
                </label>
                <label style="display: flex; align-items: center; font-weight: normal; cursor: pointer;">
                    <span style="font-size: 18px; margin-right: 0.5rem;">❌</span>
                    <input type="radio" name="consent" value="disagree" style="margin-right: 0.75rem; width: 16px; height: 16px; accent-color: #dc3545;">
                    <span><strong>Не согласен</strong> на обработку персональных данных</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label style="font-weight: 600; margin-bottom: 1rem; display: block;">Укажите свой возраст *<sup>2</sup></label>
            <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; background: #fafafa;">
                <label style="display: flex; align-items: center; font-weight: normal; margin-bottom: 0.75rem; cursor: pointer;">
                    <span style="font-size: 18px; margin-right: 0.5rem;">✅</span>
                    <input type="radio" name="age" value="18+" required style="margin-right: 0.75rem; width: 16px; height: 16px; accent-color: #28a745;">
                    <span>Мне <strong>более 18 лет</strong></span>
                </label>
                <label style="display: flex; align-items: center; font-weight: normal; cursor: pointer;">
                    <span style="font-size: 18px; margin-right: 0.5rem;">❌</span>
                    <input type="radio" name="age" value="under18" style="margin-right: 0.75rem; width: 16px; height: 16px; accent-color: #dc3545;">
                    <span>Мне <strong>менее 18 лет</strong></span>
                </label>
            </div>
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

Предоставленные персональные данные будут обрабатываться в соответствии с положениями № 152-ФЗ от 27.07.2006 «О персональных данных». Настоящее согласие предоставляется путем заполнения «чек-бокса» (проставления «галочки»/ «веб-метки» в графе «Я гарантирую, что передаю свои персональные данные, согласен на их обработку для оформления заявки» в форме обратной связи на сайте sleeptrip.ru) и нажатия соответствующей кнопки. Более подробная информация будет направлена на электронную почту.

**<sup>2</sup> Подтверждение совершеннолетия:**

Участие в поездках и мероприятиях разрешено только лицам, достигшим совершеннолетия (18 лет). В случае, если Вам менее 18 лет, для участия в поездке необходимо согласие и личное присутствие законных представителей (родителей, опекунов).

