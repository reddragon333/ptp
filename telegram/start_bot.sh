#!/bin/bash

# Скрипт запуска Telegram бота

echo "🤖 Запуск Telegram бота..."

# Проверка наличия Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 не найден. Установите Python3."
    exit 1
fi

# Проверка наличия pip
if ! command -v pip3 &> /dev/null; then
    echo "❌ pip3 не найден. Установите pip3."
    exit 1
fi

# Установка зависимостей
echo "📦 Установка зависимостей..."
pip3 install -r requirements.txt

# Проверка переменной окружения
if [ -z "$TELEGRAM_BOT_TOKEN" ]; then
    echo "⚠️  Не установлен TELEGRAM_BOT_TOKEN"
    echo "Установите токен бота:"
    echo "export TELEGRAM_BOT_TOKEN='ваш_токен_здесь'"
    echo ""
    echo "Или создайте файл .env и добавьте туда:"
    echo "TELEGRAM_BOT_TOKEN=ваш_токен_здесь"
    echo ""
    read -p "Введите токен бота: " token
    export TELEGRAM_BOT_TOKEN="$token"
fi

# Запуск бота
echo "🚀 Запуск бота..."
python3 telegram_bot.py