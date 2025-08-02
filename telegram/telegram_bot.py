#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import logging
import requests
import re
import subprocess
from datetime import datetime
from pathlib import Path
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup, ReplyKeyboardMarkup, KeyboardButton
from telegram.ext import Application, CommandHandler, MessageHandler, CallbackQueryHandler, ContextTypes, filters
import json

# Настройка логирования
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# Токен бота (замените на ваш токен)
BOT_TOKEN = os.getenv('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE')

# Пути к Hugo проекту
HUGO_PROJECT_PATH = os.path.join(os.path.dirname(os.path.dirname(__file__)))
CONTENT_POST_PATH = os.path.join(HUGO_PROJECT_PATH, 'content', 'post')
PUBLIC_IMAGES_PATH = os.path.join(HUGO_PROJECT_PATH, 'public', 'images')
PLAN_FILE_PATH = os.path.join(HUGO_PROJECT_PATH, 'content', 'plan.md')

# Создаем папки если их нет
os.makedirs(CONTENT_POST_PATH, exist_ok=True)
os.makedirs(PUBLIC_IMAGES_PATH, exist_ok=True)

# Состояния бота
class BotStates:
    MAIN_MENU = 'main_menu'
    CREATE_POST = 'create_post'
    POST_TEXT = 'post_text'
    POST_PHOTO = 'post_photo'
    POST_VIDEO = 'post_video'
    POST_YOUTUBE = 'post_youtube'
    POST_LOCATION = 'post_location'
    TRAVEL_CALENDAR = 'travel_calendar'
    TRAVEL_TEXT = 'travel_text'
    TRAVEL_PHOTO = 'travel_photo'
    POST_LOCATION_NAME = 'post_location_name'
    POST_DESCRIPTION = 'post_description'
    CALENDAR_MANAGE = 'calendar_manage'
    CALENDAR_ADD_MONTH = 'calendar_add_month'
    CALENDAR_ADD_SPECIAL = 'calendar_add_special'
    CALENDAR_REMOVE = 'calendar_remove'

# Хранилище состояний пользователей
user_states = {}
user_data = {}

def load_data(filename):
    """Загрузка данных из JSON файла"""
    try:
        with open(filename, 'r', encoding='utf-8') as f:
            return json.load(f)
    except (FileNotFoundError, json.JSONDecodeError):
        return []

def save_data(filename, data):
    """Сохранение данных в JSON файл"""
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

def generate_post_slug(title, date):
    """Генерация slug для поста в формате Hugo"""
    # Убираем специальные символы и заменяем пробелы на дефисы
    slug = re.sub(r'[^\w\s-]', '', title.lower())
    slug = re.sub(r'[-\s]+', '-', slug).strip('-')
    
    # Добавляем дату в формате YYYYMMDD
    date_str = date.strftime('%Y%m%d')
    
    # Если slug пустой, используем дату
    if not slug:
        slug = f"post-{date_str}"
    else:
        slug = f"{slug}-{date_str}"
    
    return slug

def download_telegram_file(file_id, file_name, context):
    """Скачивание файла из Telegram"""
    try:
        file = context.bot.get_file(file_id)
        file_path = os.path.join(PUBLIC_IMAGES_PATH, file_name)
        
        # Скачиваем файл
        response = requests.get(file.file_path)
        with open(file_path, 'wb') as f:
            f.write(response.content)
        
        return f"images/{file_name}"
    except Exception as e:
        logger.error(f"Ошибка при скачивании файла: {e}")
        return None

def create_hugo_post(post_data):
    """Создание поста в формате Hugo"""
    try:
        # Создаем slug и имя файла
        post_date = datetime.now()
        post_slug = generate_post_slug(post_data.get('title', 'Новый пост'), post_date)
        file_name = f"{post_slug}.md"
        file_path = os.path.join(CONTENT_POST_PATH, file_name)
        
        # Генерируем front matter
        front_matter = f"""+++
title = '{post_data.get('title', 'Новый пост')}'
slug = '{post_slug}'
date = "{post_date.strftime('%Y-%m-%dT%H:%M:%S')}"
"""
        
        # Добавляем описание если есть
        if post_data.get('description'):
            front_matter += f"description = '{post_data['description']}'\n"
        
        # Добавляем изображение если есть
        if post_data.get('main_image'):
            front_matter += f"image = '{post_data['main_image']}'\n"
        
        front_matter += "+++\n\n"
        
        # Основной текст поста
        content = post_data.get('text', '')
        
        # Добавляем фотографии в галерею
        if post_data.get('photos'):
            if len(post_data['photos']) > 1:
                content += "\n\n## Фотографии\n\n"
                content += "{{< gallery dir=\"/images/\" />}}\n"
            elif len(post_data['photos']) == 1:
                # Если одна фотография, то она уже указана как main_image в front matter
                pass
        
        # Добавляем YouTube видео
        if post_data.get('youtube_links'):
            content += "\n\n## Видео\n\n"
            for link in post_data['youtube_links']:
                video_id = extract_youtube_id(link)
                if video_id:
                    content += f'{{< youtube {video_id} >}}\n\n'
        
        # Добавляем локации
        if post_data.get('locations'):
            content += "\n\n## Локации\n\n"
            for location in post_data['locations']:
                content += f"📍 [Посмотреть на карте]({location})\n\n"
        
        # Добавляем кнопку "Назад наверх"
        content += "\n{{< rawhtml >}}\n{{< back-to-top >}}\n{{< /rawhtml >}}\n"
        
        # Записываем файл
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(front_matter + content)
        
        return file_path
        
    except Exception as e:
        logger.error(f"Ошибка при создании поста: {e}")
        return None

def extract_youtube_id(url):
    """Извлечение ID видео из YouTube URL"""
    patterns = [
        r'(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([^&\n?#]+)',
        r'youtube\.com/watch\?.*v=([^&\n?#]+)'
    ]
    
    for pattern in patterns:
        match = re.search(pattern, url)
        if match:
            return match.group(1)
    return None

def add_travel_calendar_entry(entry_data):
    """Добавление записи в календарь поездок"""
    try:
        # Читаем существующий файл plan.md
        with open(PLAN_FILE_PATH, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Находим место для вставки новой записи (после календаря)
        calendar_end = content.find('</script>')
        if calendar_end == -1:
            return False
        
        # Создаем запись
        new_entry = f"\n\n---\n\n### 📅 {datetime.now().strftime('%d.%m.%Y')}\n\n"
        new_entry += f"**{entry_data.get('title', 'Новая поездка')}**\n\n"
        new_entry += entry_data.get('text', '') + "\n\n"
        
        if entry_data.get('photo'):
            new_entry += f"![Фото поездки]({entry_data['photo']})\n\n"
        
        # Вставляем запись
        insert_position = content.find('\n\nЖелаете отправиться в путешествие?')
        if insert_position != -1:
            content = content[:insert_position] + new_entry + content[insert_position:]
        else:
            content += new_entry
        
        # Записываем обновленный файл
        with open(PLAN_FILE_PATH, 'w', encoding='utf-8') as f:
            f.write(content)
        
        return True
        
    except Exception as e:
        logger.error(f"Ошибка при добавлении записи в календарь: {e}")
        return False

def get_current_travel_options():
    """Получение текущих опций поездок из plan.md"""
    try:
        with open(PLAN_FILE_PATH, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Ищем секцию select с опциями
        select_start = content.find('<select id="trip_period" name="trip_period">')
        select_end = content.find('</select>')
        
        if select_start == -1 or select_end == -1:
            return []
        
        select_content = content[select_start:select_end]
        
        # Извлекаем опции
        import re
        options = re.findall(r'<option value="([^"]*)"[^>]*>([^<]*)</option>', select_content)
        # Фильтруем пустые опции и исключаем "Свой вариант без БВС"
        filtered_options = [(value, text) for value, text in options 
                          if value and text and value != "Свой вариант без БВС"]
        
        return filtered_options
        
    except Exception as e:
        logger.error(f"Ошибка при чтении опций календаря: {e}")
        return []

def add_travel_option(option_text):
    """Добавление новой опции поездки в plan.md"""
    try:
        with open(PLAN_FILE_PATH, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Ищем место для вставки новой опции (перед "Свой вариант без БВС")
        target = '<option value="Свой вариант без БВС">Свой вариант без БВС</option>'
        target_pos = content.find(target)
        
        if target_pos == -1:
            return False
        
        # Создаем новую опцию
        new_option = f'                <option value="{option_text}">{option_text}</option>\n                '
        
        # Вставляем новую опцию
        new_content = content[:target_pos] + new_option + content[target_pos:]
        
        # Записываем обновленный файл
        with open(PLAN_FILE_PATH, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        return True
        
    except Exception as e:
        logger.error(f"Ошибка при добавлении опции поездки: {e}")
        return False

def remove_travel_option(option_text):
    """Удаление опции поездки из plan.md"""
    try:
        with open(PLAN_FILE_PATH, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Ищем и удаляем опцию
        pattern = f'                <option value="{re.escape(option_text)}">{re.escape(option_text)}</option>\n'
        new_content = re.sub(pattern, '', content)
        
        # Если не нашли точно такую строку, попробуем без лишних пробелов
        if new_content == content:
            pattern = f'<option value="{re.escape(option_text)}">{re.escape(option_text)}</option>'
            new_content = re.sub(pattern + r'\s*', '', content)
        
        if new_content != content:
            with open(PLAN_FILE_PATH, 'w', encoding='utf-8') as f:
                f.write(new_content)
            return True
        else:
            return False
        
    except Exception as e:
        logger.error(f"Ошибка при удалении опции поездки: {e}")
        return False

def run_git_command(command, cwd=None):
    """Выполнение Git команды"""
    try:
        if cwd is None:
            cwd = HUGO_PROJECT_PATH
        
        result = subprocess.run(
            command, 
            shell=True, 
            cwd=cwd,
            capture_output=True, 
            text=True,
            timeout=30
        )
        
        if result.returncode == 0:
            logger.info(f"Git команда выполнена: {command}")
            return True, result.stdout
        else:
            logger.error(f"Ошибка Git команды: {command}, {result.stderr}")
            return False, result.stderr
            
    except subprocess.TimeoutExpired:
        logger.error(f"Таймаут Git команды: {command}")
        return False, "Timeout"
    except Exception as e:
        logger.error(f"Исключение при выполнении Git команды: {e}")
        return False, str(e)

def git_add_commit_push(files, commit_message):
    """Добавление, коммит и пуш файлов в Git"""
    try:
        # Добавляем файлы
        for file_path in files:
            success, output = run_git_command(f"git add \"{file_path}\"")
            if not success:
                return False, f"Ошибка при добавлении файла {file_path}: {output}"
        
        # Создаем коммит
        success, output = run_git_command(f"git commit -m \"{commit_message}\"")
        if not success:
            return False, f"Ошибка при создании коммита: {output}"
        
        # Пушим изменения
        success, output = run_git_command("git push")
        if not success:
            return False, f"Ошибка при пуше: {output}"
        
        return True, "Изменения успешно отправлены в репозиторий"
        
    except Exception as e:
        logger.error(f"Ошибка Git операций: {e}")
        return False, str(e)

def transliterate_city_name(city_name):
    """Транслитерация названия города с русского на латиницу"""
    # Словарь для известных городов и направлений
    known_cities = {
        'москва': 'Moscow',
        'питер': 'Piter',
        'санкт-петербург': 'Piter',
        'спб': 'Piter',
        'тула': 'Tula',
        'тверь': 'Tver',
        'ярославль': 'Yaroslavl',
        'владимир': 'Vladimir',
        'серпухов': 'Serpuhov',
        'дмитров': 'Dmitrov',
        'калязин': 'Kalyazin',
        'кавказ': 'Kavkaz',
        'мурманск': 'Murmansk',
        'калининград': 'Kaliningrad',
        'кбр': 'KBR',
        'кабардино-балкария': 'KBR',
        'покров': 'Pokrov',
        'сергиев': 'Sergiev',
        'сергиев посад': 'Sergiev',
        'ростов': 'Rostov',
        'рязань': 'Ryazan',
        'спирово': 'Spirovo',
        'алексин': 'Aleksin',
        'алтай': 'Altai',
        'клин': 'Klin',
        'коломна': 'Kolomna',
        'можайск': 'Mojaisk',
        'дубна': 'Dubna',
        'кашира': 'Kashira',
        'волоколамск': 'Volok',
        'елки-палки': 'Elki-palki',
        'беломорск': 'Belayagora',
        'ржев': 'Rzhev',
        'ржев': 'Rzhev'
    }
    
    # Приводим к нижнему регистру
    city_lower = city_name.lower().strip()
    
    # Проверяем известные города
    if city_lower in known_cities:
        return known_cities[city_lower]
    
    # Простая транслитерация для неизвестных названий
    translit_dict = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
        'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
        ' ': '', '-': '', '_': ''
    }
    
    result = ''
    for char in city_lower:
        if char in translit_dict:
            result += translit_dict[char]
        elif char.isalpha():
            result += char
    
    # Делаем первую букву заглавной
    return result.capitalize() if result else 'Unknown'

def generate_image_filename(location, date, image_number, extension='jpg'):
    """Генерация имени файла изображения по схеме Location-YYYYMMDD-N.ext"""
    date_str = date.strftime('%Y%m%d')
    location_latin = transliterate_city_name(location)
    return f"{location_latin}-{date_str}-{image_number}.{extension}"

def get_main_keyboard():
    """Главная клавиатура"""
    keyboard = [
        [KeyboardButton("📝 Создать пост")],
        [KeyboardButton("🌍 Хочу поехать")],
        [KeyboardButton("📅 Управление календарём")]
    ]
    return ReplyKeyboardMarkup(keyboard, resize_keyboard=True)

def get_post_creation_keyboard():
    """Клавиатура для создания поста"""
    keyboard = [
        [InlineKeyboardButton("📸 Добавить фото", callback_data="post_photo")],
        [InlineKeyboardButton("🎥 Добавить видео", callback_data="post_video")],
        [InlineKeyboardButton("🔗 YouTube ссылка", callback_data="post_youtube")],
        [InlineKeyboardButton("📍 Локация (Яндекс.Карты)", callback_data="post_location")],
        [InlineKeyboardButton("✅ Опубликовать пост", callback_data="publish_post")],
        [InlineKeyboardButton("❌ Отмена", callback_data="cancel")]
    ]
    return InlineKeyboardMarkup(keyboard)

def get_photo_quality_keyboard():
    """Клавиатура для выбора качества фото (не используется, оставлена для совместимости)"""
    keyboard = [
        [InlineKeyboardButton("🔙 Назад", callback_data="back_to_post")]
    ]
    return InlineKeyboardMarkup(keyboard)

def get_calendar_management_keyboard():
    """Клавиатура для управления календарём"""
    keyboard = [
        [InlineKeyboardButton("➕ Добавить месячную поездку", callback_data="calendar_add_monthly")],
        [InlineKeyboardButton("✨ Добавить специальную поездку", callback_data="calendar_add_special")],
        [InlineKeyboardButton("❌ Удалить поездку", callback_data="calendar_remove")],
        [InlineKeyboardButton("📋 Просмотреть список", callback_data="calendar_list")],
        [InlineKeyboardButton("🔙 Назад", callback_data="back_to_main")]
    ]
    return InlineKeyboardMarkup(keyboard)

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Команда /start"""
    user_id = update.effective_user.id
    user_states[user_id] = BotStates.MAIN_MENU
    user_data[user_id] = {}
    
    welcome_text = (
        "🤖 Добро пожаловать в бот предназначенный для загрузки контента на сайт \"Пока ты спал\"!\n\n"
        "Выберите действие:"
    )
    
    await update.message.reply_text(
        welcome_text,
        reply_markup=get_main_keyboard()
    )

async def handle_main_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка главного меню"""
    user_id = update.effective_user.id
    text = update.message.text
    
    if text == "📝 Создать пост":
        user_states[user_id] = BotStates.CREATE_POST
        user_data[user_id] = {
            'type': 'post',
            'title': '',
            'description': '',
            'location': '',
            'text': '',
            'photos': [],
            'videos': [],
            'youtube_links': [],
            'locations': [],
            'main_image': None
        }
        
        await update.message.reply_text(
            "📝 Создание нового поста для Hugo сайта\n\n"
            "Сначала введите заголовок поста:",
            reply_markup=None
        )
        user_states[user_id] = BotStates.POST_DESCRIPTION
        
    elif text == "🌍 Хочу поехать":
        user_states[user_id] = BotStates.TRAVEL_CALENDAR
        user_data[user_id] = {
            'type': 'travel',
            'title': '',
            'text': '',
            'photo': None
        }
        
        await update.message.reply_text(
            "🌍 Добавление записи в календарь поездок\n\n"
            "Введите название поездки:",
            reply_markup=None
        )
        user_states[user_id] = BotStates.TRAVEL_TEXT
        
    elif text == "📅 Управление календарём":
        user_states[user_id] = BotStates.CALENDAR_MANAGE
        await update.message.reply_text(
            "📅 **Управление календарём поездок**\n\n"
            "Выберите действие:",
            reply_markup=get_calendar_management_keyboard()
        )

async def handle_post_description(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка описания поста"""
    user_id = update.effective_user.id
    text = update.message.text
    
    if not user_data[user_id].get('title'):
        # Первое сообщение - заголовок
        user_data[user_id]['title'] = text
        await update.message.reply_text(
            f"✅ Заголовок сохранен: {text}\n\n"
            "Теперь введите краткое описание для превью (1-2 слова):\n"
            "Например: Поход, Экскурсия, Отдых, Приключение..."
        )
    elif not user_data[user_id].get('description'):
        # Второе сообщение - описание
        user_data[user_id]['description'] = text
        await update.message.reply_text(
            f"✅ Описание сохранено: {text}\n\n"
            "Теперь введите направление/город поездки (на русском):\n"
            "Это нужно для организации фото по названиям файлов.\n"
            "Например: Москва, Питер, Алтай, Кавказ, Тула..."
        )
        user_states[user_id] = BotStates.POST_LOCATION_NAME

async def handle_post_location_name(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка названия локации для поста"""
    user_id = update.effective_user.id
    text = update.message.text
    
    # Третье сообщение - локация
    user_data[user_id]['location'] = text
    location_latin = transliterate_city_name(text)
    await update.message.reply_text(
        f"✅ Направление сохранено: {text} → {location_latin}\n\n"
        "Теперь введите основной текст поста:"
    )
    user_states[user_id] = BotStates.POST_TEXT
    
async def handle_post_text(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка основного текста поста"""
    user_id = update.effective_user.id
    text = update.message.text
    
    # Четвертое сообщение - основной текст
    user_data[user_id]['text'] = text
    await update.message.reply_text(
        f"✅ Текст поста сохранен!\n\n"
        f"📝 Заголовок: {user_data[user_id]['title']}\n"
        f"📋 Описание: {user_data[user_id]['description']}\n"
        f"📍 Направление: {user_data[user_id]['location']}\n\n"
        "Теперь выберите дополнительные опции:",
        reply_markup=get_post_creation_keyboard()
    )

async def handle_travel_text(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка текста для календаря поездок"""
    user_id = update.effective_user.id
    text = update.message.text
    
    if not user_data[user_id].get('title'):
        # Первое сообщение - название
        user_data[user_id]['title'] = text
        await update.message.reply_text(
            f"✅ Название сохранено: {text}\n\n"
            "Теперь опишите детали поездки:"
        )
    else:
        # Второе сообщение - описание
        user_data[user_id]['text'] = text
        
        keyboard = [
            [InlineKeyboardButton("📸 Добавить фото", callback_data="travel_photo")],
            [InlineKeyboardButton("✅ Сохранить", callback_data="save_travel")],
            [InlineKeyboardButton("❌ Отмена", callback_data="cancel")]
        ]
        
        await update.message.reply_text(
            f"✅ Описание сохранено!\n\n"
            f"Название: {user_data[user_id]['title']}\n"
            f"Описание: {text}\n\n"
            "Хотите добавить фото?",
            reply_markup=InlineKeyboardMarkup(keyboard)
        )

async def handle_callback_query(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка callback запросов"""
    query = update.callback_query
    await query.answer()
    
    user_id = query.from_user.id
    data = query.data
    
    if data == "post_photo":
        user_states[user_id] = BotStates.POST_PHOTO
        await query.edit_message_text(
            "📸 Отправьте фото как файл через Telegram:\n\n"
            "Для сохранения качества обязательно отправляйте фото как документ/файл!\n"
            "Первое фото будет главным (в превью), остальные добавятся в галерею."
        )
        
    elif data == "post_video":
        user_states[user_id] = BotStates.POST_VIDEO
        await query.edit_message_text(
            "🎥 Отправьте видео:"
        )
        
    elif data == "post_youtube":
        user_states[user_id] = BotStates.POST_YOUTUBE
        await query.edit_message_text(
            "🔗 Отправьте ссылку на YouTube видео:"
        )
        
    elif data == "post_location":
        user_states[user_id] = BotStates.POST_LOCATION
        await query.edit_message_text(
            "📍 Отправьте ссылку на Яндекс.Карты:"
        )
        
    elif data == "travel_photo":
        user_states[user_id] = BotStates.TRAVEL_PHOTO
        await query.edit_message_text(
            "📸 Отправьте фото как файл для календаря поездок:"
        )
        
    elif data == "publish_post":
        await publish_hugo_post(query, user_id, context)
        
    elif data == "save_travel":
        await save_travel_entry(query, user_id, context)
        
    elif data == "calendar_add_monthly":
        user_states[user_id] = BotStates.CALENDAR_ADD_MONTH
        await query.edit_message_text(
            "➕ **Добавление месячной поездки**\n\n"
            "Введите название поездки в формате:\n"
            "`Полёты в [месяц] [год] года`\n\n"
            "Например:\n"
            "• Полёты в октябре 2025 года\n"
            "• Полёты в ноябре 2025 года\n"
            "• Полёты в декабре 2025 года"
        )
        
    elif data == "calendar_add_special":
        user_states[user_id] = BotStates.CALENDAR_ADD_SPECIAL
        await query.edit_message_text(
            "✨ **Добавление специальной поездки**\n\n"
            "Введите название специальной поездки:\n\n"
            "Например:\n"
            "• Новогодние каникулы в горах\n"
            "• Майские праздники на природе\n"
            "• Летний фестиваль"
        )
        
    elif data == "calendar_remove":
        await handle_calendar_remove_callback(query, user_id, context)
        
    elif data == "calendar_list":
        await handle_calendar_list_callback(query, user_id, context)
        
    elif data == "back_to_main":
        user_states[user_id] = BotStates.MAIN_MENU
        user_data[user_id] = {}
        await query.edit_message_text("Выберите действие:")
        await context.bot.send_message(
            chat_id=user_id,
            text="Главное меню:",
            reply_markup=get_main_keyboard()
        )
        
    elif data == "cancel":
        user_states[user_id] = BotStates.MAIN_MENU
        user_data[user_id] = {}
        await query.edit_message_text("❌ Операция отменена")
        await context.bot.send_message(
            chat_id=user_id,
            text="Выберите действие:",
            reply_markup=get_main_keyboard()
        )
        
    elif data.startswith("remove_trip_"):
        trip_value = data.replace("remove_trip_", "")
        success = remove_travel_option(trip_value)
        
        if success:
            # Создаем Git коммит
            commit_message = f"Удалена поездка: {trip_value}\n\n🤖 Создано через Telegram бота"
            git_success, git_message = git_add_commit_push(["content/plan.md"], commit_message)
            
            message = f"✅ Поездка удалена!\n\n"
            message += f"❌ Удалено: {trip_value}\n"
            message += f"💡 Обновлен файл: content/plan.md\n"
            
            # Добавляем информацию о Git операции
            if git_success:
                message += f"🔄 {git_message}"
            else:
                message += f"⚠️ Git ошибка: {git_message}"
        else:
            message = "❌ Ошибка при удалении поездки"
        
        await query.edit_message_text(
            message,
            reply_markup=InlineKeyboardMarkup([[
                InlineKeyboardButton("🔙 Назад", callback_data="back_to_main")
            ]])
        )
        
    elif data == "back_to_post":
        await query.edit_message_text(
            "📝 Создание поста\n\n"
            "Выберите дополнительные опции:",
            reply_markup=get_post_creation_keyboard()
        )

async def handle_photo(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка фотографий"""
    user_id = update.effective_user.id
    state = user_states.get(user_id)
    
    if state == BotStates.POST_PHOTO:
        # Проверяем, что фото отправлено как документ
        if update.message.document:
            photo = update.message.document
            
            # Генерируем имя файла по схеме Location-YYYYMMDD-N.ext
            current_date = datetime.now()
            file_extension = 'jpg'
            if hasattr(photo, 'file_name') and photo.file_name:
                file_extension = photo.file_name.split('.')[-1].lower()
            
            location = user_data[user_id].get('location', 'Unknown')
            image_number = len(user_data[user_id]['photos']) + 1
            file_name = generate_image_filename(location, current_date, image_number, file_extension)
            
            # Скачиваем файл
            image_path = download_telegram_file(photo.file_id, file_name, context)
            
            if image_path:
                user_data[user_id]['photos'].append(image_path)
                
                # Первое фото делаем главным
                if not user_data[user_id].get('main_image'):
                    user_data[user_id]['main_image'] = image_path
                    status = "главное фото для превью"
                else:
                    status = "дополнительное фото для галереи"
                
                await update.message.reply_text(
                    f"✅ Фото добавлено как {status}!\n"
                    f"📸 Всего фото: {len(user_data[user_id]['photos'])}\n"
                    f"💾 Сохранено: {image_path}",
                    reply_markup=get_post_creation_keyboard()
                )
            else:
                await update.message.reply_text(
                    "❌ Ошибка при загрузке фото",
                    reply_markup=get_post_creation_keyboard()
                )
        else:
            # Если фото отправлено обычным способом
            await update.message.reply_text(
                "⚠️ Пожалуйста, отправьте фото как файл/документ!\n\n"
                "Это необходимо для сохранения качества.\n"
                "Нажмите на скрепку и выберите 'Файл'.",
                reply_markup=get_post_creation_keyboard()
            )
        
    elif state == BotStates.TRAVEL_PHOTO:
        # Проверяем, что фото отправлено как документ
        if update.message.document:
            photo = update.message.document
            
            # Генерируем имя файла
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            file_extension = 'jpg'
            if hasattr(photo, 'file_name') and photo.file_name:
                file_extension = photo.file_name.split('.')[-1].lower()
            
            file_name = f"travel_{timestamp}.{file_extension}"
            
            # Скачиваем файл
            image_path = download_telegram_file(photo.file_id, file_name, context)
            
            if image_path:
                user_data[user_id]['photo'] = image_path
                
                keyboard = [
                    [InlineKeyboardButton("✅ Сохранить", callback_data="save_travel")],
                    [InlineKeyboardButton("❌ Отмена", callback_data="cancel")]
                ]
                
                await update.message.reply_text(
                    f"✅ Фото добавлено!\n"
                    f"💾 Сохранено: {image_path}",
                    reply_markup=InlineKeyboardMarkup(keyboard)
                )
            else:
                await update.message.reply_text("❌ Ошибка при загрузке фото")
        else:
            # Если фото отправлено обычным способом
            await update.message.reply_text(
                "⚠️ Пожалуйста, отправьте фото как файл/документ!\n\n"
                "Это необходимо для сохранения качества.\n"
                "Нажмите на скрепку и выберите 'Файл'."
            )

async def handle_video(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка видео"""
    user_id = update.effective_user.id
    state = user_states.get(user_id)
    
    if state == BotStates.POST_VIDEO:
        video = update.message.video
        
        # Генерируем имя файла по схеме Location-YYYYMMDD-N.mp4
        current_date = datetime.now()
        location = user_data[user_id].get('location', 'Unknown')
        video_number = len(user_data[user_id]['videos']) + 1
        file_name = generate_image_filename(location, current_date, video_number, 'mp4')
        
        # Скачиваем файл
        video_path = download_telegram_file(video.file_id, file_name, context)
        
        if video_path:
            user_data[user_id]['videos'].append(video_path)
            await update.message.reply_text(
                f"✅ Видео добавлено к посту!\n"
                f"Сохранено: {video_path}",
                reply_markup=get_post_creation_keyboard()
            )
        else:
            await update.message.reply_text(
                "❌ Ошибка при загрузке видео",
                reply_markup=get_post_creation_keyboard()
            )

async def handle_text_input(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка текстового ввода"""
    user_id = update.effective_user.id
    state = user_states.get(user_id)
    text = update.message.text
    
    if state == BotStates.POST_YOUTUBE:
        user_data[user_id]['youtube_links'].append(text)
        await update.message.reply_text(
            "✅ YouTube ссылка добавлена!",
            reply_markup=get_post_creation_keyboard()
        )
        
    elif state == BotStates.POST_LOCATION:
        user_data[user_id]['locations'].append(text)
        await update.message.reply_text(
            "✅ Локация добавлена!",
            reply_markup=get_post_creation_keyboard()
        )

async def publish_hugo_post(query, user_id, context):
    """Публикация поста в Hugo"""
    post_data = user_data[user_id].copy()
    
    # Создаем Hugo пост
    file_path = create_hugo_post(post_data)
    
    if file_path:
        # Подготавливаем файлы для Git коммита
        files_to_commit = []
        
        # Добавляем markdown файл поста
        relative_post_path = os.path.relpath(file_path, HUGO_PROJECT_PATH)
        files_to_commit.append(relative_post_path)
        
        # Добавляем все загруженные изображения и видео
        for photo_path in post_data.get('photos', []):
            if photo_path.startswith('images/'):
                # Конвертируем относительный путь в путь от корня проекта
                full_image_path = f"public/{photo_path}"
                files_to_commit.append(full_image_path)
        
        for video_path in post_data.get('videos', []):
            if video_path.startswith('images/'):
                # Конвертируем относительный путь в путь от корня проекта
                full_video_path = f"public/{video_path}"
                files_to_commit.append(full_video_path)
        
        # Создаем Git коммит
        commit_message = f"Добавлен новый пост: {post_data['title']}\n\n🤖 Создано через Telegram бота"
        git_success, git_message = git_add_commit_push(files_to_commit, commit_message)
        
        # Формирование сообщения о публикации
        message = f"✅ Пост опубликован в Hugo!\n\n"
        message += f"📝 Заголовок: {post_data['title']}\n"
        message += f"📋 Описание: {post_data['description']}\n"
        message += f"📄 Файл: {os.path.basename(file_path)}\n"
        
        if post_data['photos']:
            message += f"📸 Фото: {len(post_data['photos'])} шт.\n"
        if post_data['videos']:
            message += f"🎥 Видео: {len(post_data['videos'])} шт.\n"
        if post_data['youtube_links']:
            message += f"🔗 YouTube: {len(post_data['youtube_links'])} ссылок\n"
        if post_data['locations']:
            message += f"📍 Локации: {len(post_data['locations'])} шт.\n"
        
        message += f"\n💡 Файл сохранен: content/post/{os.path.basename(file_path)}\n"
        
        # Добавляем информацию о Git операции
        if git_success:
            message += f"🔄 {git_message}"
        else:
            message += f"⚠️ Git ошибка: {git_message}"
        
        await query.edit_message_text(message)
    else:
        await query.edit_message_text("❌ Ошибка при создании поста")
    
    # Возврат в главное меню
    user_states[user_id] = BotStates.MAIN_MENU
    user_data[user_id] = {}
    
    await query.message.reply_text(
        "Выберите действие:",
        reply_markup=get_main_keyboard()
    )

async def save_travel_entry(query, user_id, context):
    """Сохранение записи в календарь поездок"""
    travel_data = user_data[user_id].copy()
    
    # Добавляем запись в план
    success = add_travel_calendar_entry(travel_data)
    
    if success:
        # Подготавливаем файлы для Git коммита
        files_to_commit = ["content/plan.md"]
        
        # Добавляем фото если есть
        if travel_data.get('photo') and travel_data['photo'].startswith('images/'):
            full_image_path = f"public/{travel_data['photo']}"
            files_to_commit.append(full_image_path)
        
        # Создаем Git коммит
        commit_message = f"Обновлен календарь поездок: {travel_data['title']}\n\n🤖 Создано через Telegram бота"
        git_success, git_message = git_add_commit_push(files_to_commit, commit_message)
        
        message = f"✅ Запись добавлена в календарь поездок!\n\n"
        message += f"📝 Название: {travel_data['title']}\n"
        message += f"📋 Описание: {travel_data['text']}\n"
        
        if travel_data.get('photo'):
            message += f"📸 Фото: {travel_data['photo']}\n"
        
        message += f"\n💡 Обновлен файл: content/plan.md\n"
        
        # Добавляем информацию о Git операции
        if git_success:
            message += f"🔄 {git_message}"
        else:
            message += f"⚠️ Git ошибка: {git_message}"
        
        await query.edit_message_text(message)
    else:
        await query.edit_message_text("❌ Ошибка при добавлении записи")
    
    # Возврат в главное меню
    user_states[user_id] = BotStates.MAIN_MENU
    user_data[user_id] = {}
    
    await query.message.reply_text(
        "Выберите действие:",
        reply_markup=get_main_keyboard()
    )

async def handle_calendar_add_monthly(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка добавления месячной поездки"""
    user_id = update.effective_user.id
    text = update.message.text.strip()
    
    # Проверяем формат ввода (должен содержать месяц и год)
    if not text:
        await update.message.reply_text(
            "❌ Пожалуйста, введите название поездки.\n"
            "Например: Полёты в октябре 2025 года"
        )
        return
    
    # Добавляем опцию в план
    success = add_travel_option(text)
    
    if success:
        # Создаем Git коммит
        commit_message = f"Добавлена месячная поездка: {text}\n\n🤖 Создано через Telegram бота"
        git_success, git_message = git_add_commit_push(["content/plan.md"], commit_message)
        
        message = f"✅ Месячная поездка добавлена!\n\n"
        message += f"📅 Название: {text}\n"
        message += f"💡 Обновлен файл: content/plan.md\n"
        
        # Добавляем информацию о Git операции
        if git_success:
            message += f"🔄 {git_message}"
        else:
            message += f"⚠️ Git ошибка: {git_message}"
        
        await update.message.reply_text(message, reply_markup=get_main_keyboard())
    else:
        await update.message.reply_text(
            "❌ Ошибка при добавлении поездки",
            reply_markup=get_main_keyboard()
        )
    
    # Возврат в главное меню
    user_states[user_id] = BotStates.MAIN_MENU
    user_data[user_id] = {}

async def handle_calendar_add_special(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Обработка добавления специальной поездки"""
    user_id = update.effective_user.id
    text = update.message.text.strip()
    
    if not text:
        await update.message.reply_text(
            "❌ Пожалуйста, введите название специальной поездки.\n"
            "Например: Новогодние каникулы в горах"
        )
        return
    
    # Добавляем опцию в план
    success = add_travel_option(text)
    
    if success:
        # Создаем Git коммит
        commit_message = f"Добавлена специальная поездка: {text}\n\n🤖 Создано через Telegram бота"
        git_success, git_message = git_add_commit_push(["content/plan.md"], commit_message)
        
        message = f"✅ Специальная поездка добавлена!\n\n"
        message += f"✨ Название: {text}\n"
        message += f"💡 Обновлен файл: content/plan.md\n"
        
        # Добавляем информацию о Git операции
        if git_success:
            message += f"🔄 {git_message}"
        else:
            message += f"⚠️ Git ошибка: {git_message}"
        
        await update.message.reply_text(message, reply_markup=get_main_keyboard())
    else:
        await update.message.reply_text(
            "❌ Ошибка при добавлении поездки",
            reply_markup=get_main_keyboard()
        )
    
    # Возврат в главное меню
    user_states[user_id] = BotStates.MAIN_MENU
    user_data[user_id] = {}

async def handle_calendar_remove_callback(query, user_id, context):
    """Обработка удаления поездки из календаря"""
    options = get_current_travel_options()
    
    if not options:
        await query.edit_message_text(
            "❌ Нет доступных поездок для удаления",
            reply_markup=InlineKeyboardMarkup([[
                InlineKeyboardButton("🔙 Назад", callback_data="back_to_main")
            ]])
        )
        return
    
    # Создаем клавиатуру с опциями для удаления
    keyboard = []
    for value, text in options:
        keyboard.append([InlineKeyboardButton(
            f"❌ {text}", 
            callback_data=f"remove_trip_{value}"
        )])
    keyboard.append([InlineKeyboardButton("🔙 Назад", callback_data="back_to_main")])
    
    await query.edit_message_text(
        "❌ **Удаление поездки**\n\n"
        "Выберите поездку для удаления:",
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def handle_calendar_list_callback(query, user_id, context):
    """Показать список текущих поездок"""
    options = get_current_travel_options()
    
    if not options:
        message = "📋 **Список поездок**\n\n❌ Пока нет добавленных поездок"
    else:
        message = "📋 **Список поездок**\n\n"
        for i, (value, text) in enumerate(options, 1):
            message += f"{i}. {text}\n"
    
    await query.edit_message_text(
        message,
        reply_markup=InlineKeyboardMarkup([[
            InlineKeyboardButton("🔙 Назад", callback_data="back_to_main")
        ]])
    )

def main():
    """Основная функция запуска бота"""
    # Создание приложения
    application = Application.builder().token(BOT_TOKEN).build()
    
    # Добавление обработчиков
    application.add_handler(CommandHandler("start", start))
    application.add_handler(CallbackQueryHandler(handle_callback_query))
    
    # Обработчики для разных типов контента
    application.add_handler(MessageHandler(filters.PHOTO | filters.Document.IMAGE, handle_photo))
    application.add_handler(MessageHandler(filters.VIDEO, handle_video))
    
    # Обработчик текстовых сообщений
    application.add_handler(MessageHandler(
        filters.TEXT & ~filters.COMMAND,
        lambda update, context: handle_message_by_state(update, context)
    ))
    
    print("🤖 Hugo Bot запущен!")
    print(f"📁 Посты сохраняются в: {CONTENT_POST_PATH}")
    print(f"📸 Изображения сохраняются в: {PUBLIC_IMAGES_PATH}")
    print(f"🌍 Календарь поездок: {PLAN_FILE_PATH}")
    application.run_polling()

async def handle_message_by_state(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """Маршрутизация сообщений по состояниям"""
    user_id = update.effective_user.id
    state = user_states.get(user_id, BotStates.MAIN_MENU)
    
    if state == BotStates.MAIN_MENU:
        await handle_main_menu(update, context)
    elif state == BotStates.POST_DESCRIPTION:
        await handle_post_description(update, context)
    elif state == BotStates.POST_LOCATION_NAME:
        await handle_post_location_name(update, context)
    elif state == BotStates.POST_TEXT:
        await handle_post_text(update, context)
    elif state == BotStates.TRAVEL_TEXT:
        await handle_travel_text(update, context)
    elif state == BotStates.CALENDAR_ADD_MONTH:
        await handle_calendar_add_monthly(update, context)
    elif state == BotStates.CALENDAR_ADD_SPECIAL:
        await handle_calendar_add_special(update, context)
    elif state in [BotStates.POST_YOUTUBE, BotStates.POST_LOCATION]:
        await handle_text_input(update, context)
    else:
        await update.message.reply_text(
            "Используйте /start для начала работы с ботом."
        )

if __name__ == '__main__':
    main()