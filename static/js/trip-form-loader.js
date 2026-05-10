/**
 * Динамическое заполнение dropdown формы поездками из upcoming-trips.json
 * Синхронизировано с Telegram Bot управлением
 */

class TripFormLoader {
    constructor(selectId = 'trip_period', jsonPath = '/data/upcoming-trips.json') {
        this.selectId = selectId;
        this.jsonPath = jsonPath;
    }

    async loadTripsData() {
        try {
            const response = await fetch(this.jsonPath);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Ошибка загрузки данных поездок для формы:', error);
            return null;
        }
    }

    async populateTripsDropdown() {
        console.log(`🔍 Ищем dropdown с ID: "${this.selectId}"`);
        const select = document.getElementById(this.selectId);
        if (!select) {
            console.error(`❌ Dropdown с ID "${this.selectId}" не найден`);
            console.log('📋 Доступные элементы с ID:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));
            return;
        }

        console.log(`✅ Dropdown найден, загружаем данные из: ${this.jsonPath}`);
        const data = await this.loadTripsData();
        if (!data || !data.trips) {
            console.error('❌ Не удалось загрузить данные поездок');
            return;
        }

        console.log(`📊 Данные загружены: ${data.trips.length} поездок всего`);

        // Фильтруем только активные поездки и сортируем по порядку
        const activeTrips = data.trips
            .filter(trip => trip.active === true)
            .sort((a, b) => (a.order || 0) - (b.order || 0));

        // Очищаем существующие опции (кроме первой пустой)
        const firstOption = select.querySelector('option[value=""]');
        select.innerHTML = '';
        
        // Возвращаем первую пустую опцию
        if (firstOption) {
            select.appendChild(firstOption);
        } else {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = '';
            select.appendChild(emptyOption);
        }

        // Добавляем активные поездки
        activeTrips.forEach(trip => {
            const option = document.createElement('option');
            const label = trip.period ? `${trip.title} (${trip.period})` : trip.title;
            option.value = label;
            option.textContent = label;
            select.appendChild(option);
        });

        // Если только одна активная поездка — автовыбираем
        if (activeTrips.length === 1) {
            select.selectedIndex = 1; // 0 = placeholder, 1 = единственная поездка
            select.dispatchEvent(new Event('change', { bubbles: true }));
            console.log(`🎯 Единственная поездка автовыбрана: ${activeTrips[0].title}`);
        }

        console.log(`✅ Dropdown формы заполнен: ${activeTrips.length} активных поездок`);
    }

    // Метод для обновления (можно вызывать для перезагрузки)
    async refresh() {
        await this.populateTripsDropdown();
    }
}

// Глобальная переменная для доступа к загрузчику
let tripFormLoader;

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли на странице dropdown для поездок
    const tripSelect = document.getElementById('trip_period');
    if (tripSelect) {
        tripFormLoader = new TripFormLoader();
        tripFormLoader.populateTripsDropdown();
        
        console.log('🗓️ Загрузчик поездок для формы инициализирован');
    }
});

// Функция для обновления dropdown (можно вызывать извне)
function refreshTripFormDropdown() {
    if (tripFormLoader) {
        tripFormLoader.refresh();
    }
}

// Экспорт для использования в других скриптах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TripFormLoader;
}