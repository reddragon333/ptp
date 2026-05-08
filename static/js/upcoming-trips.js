/**
 * Динамическая загрузка карточек предстоящих поездок
 * Загружает данные из JSON файла и генерирует HTML карточки
 */

class UpcomingTripsLoader {
    constructor(containerId = 'trips-grid', jsonPath = '/data/upcoming-trips.json') {
        this.containerId = containerId;
        this.jsonPath = jsonPath;
        this.tripsData = null;
    }

    async loadTripsData() {
        try {
            const response = await fetch(this.jsonPath);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            this.tripsData = await response.json();
            return this.tripsData;
        } catch (error) {
            console.error('Ошибка загрузки данных поездок:', error);
            return null;
        }
    }

    generateTripCard(trip) {
        const metaHtml = trip.meta ? trip.meta.map(meta => 
            `<span>${meta}</span>`
        ).join('') : '';

        return `
            <div class="trip-card" data-value="${trip.title}" onclick="selectTrip('${trip.title}')" style="cursor:pointer" title="Нажмите чтобы подать заявку">
                <div class="trip-image">
                    <img src="${trip.image}" alt="${trip.title}" loading="lazy">
                    <div class="trip-overlay">
                        <span class="trip-period">${trip.period}</span>
                    </div>
                </div>
                <div class="trip-content">
                    <h3>${trip.title}</h3>
                    <div class="trip-details">
                        <p>${trip.description}</p>
                        <div class="trip-meta">
                            ${metaHtml}
                        </div>
                    </div>
                    <!-- Кнопка выбора поездки убрана - dropdown заполняется автоматически -->
                </div>
            </div>
        `;
    }

    async renderTrips() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Контейнер с ID "${this.containerId}" не найден`);
            return;
        }

        // Показываем индикатор загрузки
        container.innerHTML = '<div class="loading">Загрузка поездок...</div>';

        const data = await this.loadTripsData();
        if (!data || !data.trips) {
            container.innerHTML = '<div class="error">Ошибка загрузки данных поездок</div>';
            return;
        }

        // Фильтруем только активные поездки и сортируем по порядку
        const activeTrips = data.trips
            .filter(trip => trip.active === true)
            .sort((a, b) => (a.order || 0) - (b.order || 0));

        if (activeTrips.length === 0) {
            container.innerHTML = '<div class="no-trips">Нет активных поездок</div>';
            return;
        }

        // Генерируем HTML для всех активных поездок
        const tripsHtml = activeTrips.map(trip => this.generateTripCard(trip)).join('');
        container.innerHTML = tripsHtml;

        console.log(`✅ Загружено ${activeTrips.length} активных поездок`);

        // Заполняем радиокнопки для формы
        const radioContainer = document.getElementById('trip-radios');
        const hiddenSelect = document.getElementById('trip_period');
        if (radioContainer && hiddenSelect) {
            radioContainer.innerHTML = '';
            hiddenSelect.innerHTML = '<option value="" disabled selected></option>';
            activeTrips.forEach((trip, i) => {
                // Radio button
                const label = document.createElement('label');
                label.className = 'trip-radio-label';
                label.innerHTML = `<input type="radio" name="trip_radio" value="${trip.title} (${trip.period})" onchange="document.getElementById('trip_period').value=this.value;this.closest('.trip-radio-group').querySelectorAll('.trip-radio-label').forEach(l=>l.classList.remove('selected'));this.closest('.trip-radio-label').classList.add('selected')"><span class="trip-radio-text"><strong>${trip.title}</strong><br><small>${trip.period}</small></span>`;
                radioContainer.appendChild(label);
                // Hidden select option
                const opt = document.createElement('option');
                opt.value = `${trip.title} (${trip.period})`;
                opt.text = `${trip.title} (${trip.period})`;
                hiddenSelect.appendChild(opt);
            });
        }
    }

    // Метод для обновления данных (можно вызывать для перезагрузки)
    async refresh() {
        await this.renderTrips();
    }

    // Получить данные о конкретной поездке
    getTripById(id) {
        if (!this.tripsData || !this.tripsData.trips) return null;
        return this.tripsData.trips.find(trip => trip.id === id);
    }

    // Получить все активные поездки
    getActiveTrips() {
        if (!this.tripsData || !this.tripsData.trips) return [];
        return this.tripsData.trips.filter(trip => trip.active === true);
    }
}

// Глобальная переменная для доступа к загрузчику
let upcomingTripsLoader;

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли на странице контейнер для поездок
    const tripsContainer = document.getElementById('trips-grid');
    if (tripsContainer) {
        upcomingTripsLoader = new UpcomingTripsLoader();
        upcomingTripsLoader.renderTrips();
        
        console.log('🗓️ Загрузчик предстоящих поездок инициализирован');
    }
});

// Функция для обновления поездок (можно вызывать извне)
function refreshUpcomingTrips() {
    if (upcomingTripsLoader) {
        upcomingTripsLoader.refresh();
    }
}

// Выбор поездки по клику на карточку
function selectTrip(tripTitle) {
    const select = document.getElementById('trip_period');
    if (select) {
        // Ищем option с текстом содержащим название поездки
        for (let opt of select.options) {
            if (opt.text.includes(tripTitle) || opt.value.includes(tripTitle)) {
                select.value = opt.value;
                break;
            }
        }
    }
    // Scroll к форме
    const form = document.querySelector('.travel-form-container');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Экспорт для использования в других скриптах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UpcomingTripsLoader;
}