# 🐛 Коллекция ошибок при развертывании на VPS

**Документация всех проблем и решений при установке PTP на VPS**

---

## 📋 Содержание

1. [Git Push Problems](#1-git-push-problems)
2. [S3 Upload Issues](#2-s3-upload-issues)
3. [Telegram Bot Path Issues](#3-telegram-bot-path-issues)
4. [Forms White Page (AJAX/Redirect)](#4-forms-white-page-ajaxredirect)
5. [.env File Parsing Bug](#5-env-file-parsing-bug)
6. [Permission Errors](#6-permission-errors)
7. [Script Line Endings](#7-script-line-endings)

---

## 1. Git Push Problems

### ❌ Проблема
```bash
fatal: The current branch main has no upstream branch.
```

### 🔍 Причина
Локальная ветка `main` не связана с удалённой `github/main`.

### ✅ Решение
```bash
git branch --set-upstream-to=github/main main
git pull --no-rebase
git push
```

### 📝 Урок
Всегда проверять связь веток перед первым push после клонирования.

---

## 2. S3 Upload Issues

### ❌ Проблема
Telegram бот не может загружать фото на S3 REG.RU:
```
which aws
/usr/bin/aws

aws configure list
      Name                    Value             Type    Location
      ----                    -----             ----    --------
   profile                <not set>             None    None
access_key                <not set>             None    None
secret_key                <not set>             None    None
    region                <not set>             None    None
```

### 🔍 Причина
AWS CLI установлен, но не настроен. Пытались установить через pip3, но получили:
```
error: externally-managed-environment
```

### ✅ Решение
Не использовать AWS CLI! Переключиться на **direct HTTP upload** метод:
```python
# В telegram_bot.py использовать:
upload_photo_direct()  # вместо upload_photo_s3()
```

Этот метод использует только `requests` библиотеку и прямой HTTP PUT запрос к S3.

### 📝 Урок
На Ubuntu 24.04+ pip установка блокируется по умолчанию. Лучше использовать HTTP API напрямую без CLI инструментов.

---

## 3. Telegram Bot Path Issues

### ❌ Проблема
```python
fatal: not a git repository
```
Бот не может выполнить git команды.

### 🔍 Причина
В коде бота путь к репозиторию был захардкожен:
```python
GIT_REPO_PATH = '/var/www/hugo-site'  # Неправильный путь!
```

### ✅ Решение
Читать из `.env`:
```python
GIT_REPO_PATH = os.getenv('GIT_REPO_PATH', '/var/www/hugo-source')
TRIPS_JSON_PATH = os.path.join(GIT_REPO_PATH, 'static/data/upcoming-trips.json')
```

В `/var/www/telegram-bot/.env`:
```bash
GIT_REPO_PATH=/var/www/hugo-source
```

### 📝 Урок
Никогда не хардкодить пути. Всегда использовать переменные окружения.

---

## 4. Forms White Page (AJAX/Redirect)

### ❌ Проблема
После отправки формы - **белая страница**, хотя POST данные приходят:
```
=== Request at 2025-10-27 15:02:32 ===
Array (
    [name] => Test
    [email] => test@test.com
    ...
)
```

### 🔍 Причина
**Несовместимость AJAX и HTTP redirect:**

JavaScript в `plan.md`:
```javascript
fetch('/forms/send_plan.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text())
.then(text => {
    const data = JSON.parse(text);  // Ожидает JSON!
    ...
})
```

PHP в `send_plan.php` (старая версия):
```php
header("Location: /plan/?success=" . urlencode($success));  // Редирект!
exit;
```

**fetch() API НЕ следует редиректам автоматически** → получает пустой ответ → белая страница!

### ✅ Решение (3 части)

**1. PHP - вернуть JSON вместо redirect:**
```php
// send_plan.php и send_ask.php
if (isset($success)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $success
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
```

**2. HTML - привязать JavaScript обработчик к форме:**
```html
<form ... onsubmit="return handleFormSubmit(event)">
```

**3. Добавить fallback на случай если переменные не установлены:**
```php
// В конце send_plan.php перед ?>
if (!isset($success) && !isset($error)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Неизвестная ошибка обработки']);
    exit;
}
```

### 📝 Урок
- AJAX формы **ВСЕГДА** должны возвращать JSON, не redirect
- Проверять что обработчик формы **привязан** через `onsubmit`
- История проблем важна - нашли решение в `CHANGELOG.md` от 2025-09-03!

---

## 5. .env File Parsing Bug

### ❌ Проблема
Форма возвращает ошибку "Неизвестная ошибка обработки", хотя в `.env` установлено:
```bash
FORMS_SEND_TELEGRAM=true       # Уведомления в Telegram
FORMS_NOTIFICATIONS=true       # Push-уведомления
```

Проверка настроек показывает:
```
Send telegram: false  ❌
Notifications: false  ❌
```

### 🔍 Причина
**Пробелы и комментарии в .env файле!**

При парсинге строки:
```bash
FORMS_SEND_TELEGRAM=true       # Уведомления в Telegram
```

Функция `load_env_file()` сохраняет:
```php
$_ENV['FORMS_SEND_TELEGRAM'] = 'true       # Уведомления в Telegram'
                                    ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                                    Лишние символы!
```

Затем проверка:
```php
getenv('FORMS_SEND_TELEGRAM') === 'true'  // FALSE!
// 'true       # Уведомления...' !== 'true'
```

**Видимость с `cat -A`:**
```bash
FORMS_SEND_TELEGRAM=true       # M-PM-#M-PM-2...
                         ^^^^^^^^ Пробелы!
```

### ✅ Решение

**Метод 1: Исправить .env файл (быстро):**
```bash
# Убрать всё после # и лишние пробелы
sudo sed -i 's/=\(.*\)#.*/=\1/' /var/www/forms/.env
sudo sed -i 's/= */=/g' /var/www/forms/.env
sudo sed -i 's/ *$//g' /var/www/forms/.env
```

**Результат:**
```bash
FORMS_SEND_EMAIL=false
FORMS_SEND_TELEGRAM=true
FORMS_NOTIFICATIONS=true
```

**Метод 2: Улучшить load_env_file() (надёжно):**
```php
function load_env_file($file_path = '../.env') {
    if (!file_exists($file_path)) {
        return false;
    }

    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Пропускаем комментарии
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Убираем inline комментарии
        if (strpos($line, '#') !== false) {
            $line = substr($line, 0, strpos($line, '#'));
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);  // ← Важно! Убирает пробелы

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}
```

### 📝 Урок
- **ВСЕГДА trim() значения** из .env файлов!
- Использовать `cat -A` для проверки невидимых символов
- .env формат не стандартизирован - парсить осторожно
- Эта проблема встречалась **ДВА раза**:
  1. В начале октября - отступы в начале строк
  2. Сегодня - пробелы перед комментариями

---

## 6. Permission Errors

### ❌ Проблема
```bash
fatal: could not create work tree dir: Permission denied
```

### 🔍 Причина
Неправильные владельцы файлов. Telegram бот работает от `www-data`, но файлы принадлежат `root`.

### ✅ Решение
```bash
# Git репозиторий
sudo chown -R www-data:www-data /var/www/hugo-source

# Формы
sudo chown -R www-data:www-data /var/www/forms
sudo chmod 600 /var/www/forms/.env

# Secure storage
sudo chown -R www-data:www-data /var/secure/forms
sudo chmod 700 /var/secure/forms
```

### 📝 Урок
Всегда проверять кто запускает процесс (`systemctl status service | grep User`) и выставлять права соответственно.

**Создан набор скриптов:** `INFO/Deployment/permissions/*.sh`

---

## 7. Script Line Endings

### ❌ Проблема
```bash
./enable-forms-debug.sh
-bash: ./enable-forms-debug.sh: cannot execute: required file not found
```

### 🔍 Причина
**CRLF line endings** (Windows/Mac) вместо LF (Unix):
```bash
#!/bin/bash\r\n  # ← \r\n вместо \n
```

Bash ищет интерпретатор `/bin/bash\r` который не существует!

### ✅ Решение
```bash
# Конвертировать в Unix формат
sed -i 's/\r$//' /var/www/enable-forms-debug.sh

# Или через dos2unix
sudo apt install dos2unix
dos2unix /var/www/enable-forms-debug.sh
```

### 📝 Урок
При создании скриптов на Mac/Windows для Linux:
- Использовать редактор с LF endings (не CRLF)
- Или конвертировать через `dos2unix` перед запуском

---

## 🎯 Общие уроки

### 1. Документация критична
Создание `CHANGELOG.md` помогло найти решение для AJAX проблемы через 2 месяца!

### 2. Debug логи спасают
```php
file_put_contents("/tmp/debug.log", print_r($data, true), FILE_APPEND);
```
Помогли найти что POST данные приходят, но ответ пустой.

### 3. Системные инструменты
- `cat -A` - показывает невидимые символы
- `ls -la` - проверяет права
- `grep -n` - находит строки с номерами
- `curl -v` - тестирует HTTP запросы

### 4. Порядок проверки при пустом ответе PHP
1. ✅ POST данные приходят? → Debug лог
2. ✅ PHP ошибки? → `/var/log/php8.3-fpm.log`
3. ✅ Content-Type правильный? → `curl -I`
4. ✅ Переменные устанавливаются? → Debug перед выводом
5. ✅ .env загружается? → Тест парсинга
6. ✅ Значения правильные? → `cat -A` для невидимых символов

### 5. Git workflow важен
- Формы теперь в git → автоматический deploy через webhook
- `.env` защищён через `.gitignore`
- Версионирование всех изменений

---

## 📊 Статистика проблем

| Категория | Количество | Критичность |
|-----------|------------|-------------|
| **Права доступа** | 5 | Высокая |
| **Парсинг .env** | 2 | Критическая |
| **AJAX/HTTP** | 1 | Критическая |
| **Пути к файлам** | 3 | Средняя |
| **Line endings** | 1 | Низкая |
| **AWS CLI** | 1 | Средняя |

**Общее время на решение:** ~4 часа чистого времени отладки

**Самая долгая проблема:** Forms white page (2+ часа)

**Самая коварная:** .env пробелы (не видны глазом!)

---

## 🚀 Профилактика

### Перед развертыванием проверить:

1. **Права:**
   ```bash
   ./INFO/Deployment/permissions/check-permissions.sh
   ```

2. **.env файлы:**
   ```bash
   cat -A .env | grep "="  # Проверить на пробелы
   ```

3. **Line endings скриптов:**
   ```bash
   file script.sh  # Должно быть "ASCII text", не "CRLF"
   ```

4. **Git remote:**
   ```bash
   git remote -v
   git branch -vv  # Проверить upstream
   ```

5. **Формы AJAX:**
   - Проверить `onsubmit` привязан
   - PHP возвращает JSON
   - JavaScript ожидает JSON

---

**Создано:** 2025-10-27
**Последнее обновление:** 2025-10-27
**Автор:** Собрано в процессе развертывания PTP на VPS

*Этот документ будет обновляться по мере обнаружения новых проблем.*
