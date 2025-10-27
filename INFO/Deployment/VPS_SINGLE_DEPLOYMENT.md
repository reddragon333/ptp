# План развертывания PTP на одном VPS

## Обзор

Этот документ описывает развертывание всего проекта PTP на **одном VPS** для тестирования или небольших production сред.

**Компоненты на одном сервере:**
- Hugo статический сайт
- Nginx веб-сервер
- GitHub webhook для автодеплоя
- Telegram Bot для создания контента
- PHP формы с многоканальной отправкой (Email + Telegram + JSON)
- msmtp для email
- REG.RU S3 для хранения медиа

## Требования к VPS

### Минимальная конфигурация:
- **ОС**: Ubuntu 20.04+ / Debian 11+
- **RAM**: 4GB (минимум 2GB)
- **CPU**: 2 ядра
- **Диск**: 40GB SSD
- **Сеть**: 100 Мбит/с

### Рекомендуемая конфигурация:
- **RAM**: 4-8GB
- **CPU**: 2-4 ядра
- **Диск**: 60GB SSD
- **Сеть**: 1 Гбит/с

### Открытые порты:
- 22 (SSH)
- 80 (HTTP)
- 443 (HTTPS)

## Архитектура на одном VPS

```
┌─────────────────────────────────────────────────┐
│              Единый VPS Server                  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │            Nginx (Port 80/443)           │  │
│  │  - Hugo статический сайт                 │  │
│  │  - PHP формы                             │  │
│  │  - Webhook API endpoint                  │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │         Hugo + Git Repository            │  │
│  │  /var/www/hugo-source/                   │  │
│  │  - Исходники сайта                       │  │
│  │  - Content от Telegram бота              │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │       Telegram Bot (Systemd)             │  │
│  │  - Создание постов                       │  │
│  │  - Управление заявками                   │  │
│  │  - Git push в GitHub                     │  │
│  │  - S3 загрузка медиа                     │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │          PHP Forms + msmtp               │  │
│  │  - Обработка заявок                      │  │
│  │  - Email отправка                        │  │
│  │  - Telegram уведомления                  │  │
│  │  - Шифрованное хранилище                 │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │      Secure Storage (Encrypted)          │  │
│  │  /var/secure/forms/                      │  │
│  └──────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
                    ↕
              GitHub Repository
                    ↕
            REG.RU S3 Storage
```

## Общее время развертывания

### Оптимистичный сценарий (все готово):
- **Подготовка**: 30 минут
- **Hugo + Nginx**: 1 час
- **Telegram Bot**: 1.5 часа
- **PHP Forms**: 1 час
- **Тестирование**: 30 минут
- **ИТОГО: 4.5 часа**

### Реалистичный сценарий:
- **ИТОГО: 6-8 часов** (один рабочий день)

### Первый раз:
- **ИТОГО: 8-12 часов** (можно растянуть на 2 дня)

## Важные концепции

### Пользователь www-data

**Что это такое:**
- `www-data` - стандартный системный пользователь в Linux для веб-сервисов
- Используется по умолчанию в Nginx, Apache, PHP-FPM

**Почему мы используем www-data для всего:**
- ✅ Nginx запускается от `www-data`
- ✅ PHP-FPM работает от `www-data`
- ✅ Deploy скрипт выполняется от `www-data`
- ✅ Telegram бот запускается от `www-data`
- ✅ Репозиторий Git принадлежит `www-data`

**Преимущества единого пользователя:**
- Все компоненты имеют одинаковые права доступа
- Нет конфликтов прав при работе с файлами
- Webhook может обновлять репозиторий без проблем
- Бот может читать зашифрованные заявки из `/var/secure/forms/`

**Важно помнить:**
- Все `.env` файлы должны принадлежать `www-data`
- Git репозиторий должен принадлежать `www-data`
- Файлы конфигурации должны иметь права 600 для безопасности

---

## Подготовка перед началом

### Чеклист - получить заранее:

- [ ] **VPS доступ**: SSH root или sudo пользователь
- [ ] **Домен**: тестовый домен с доступом к DNS
- [ ] **GitHub**: Personal Access Token (scope: repo)
- [ ] **Telegram Bot**: Token от @BotFather
- [ ] **Telegram**: Ваш User ID от @userinfobot
- [ ] **Email для msmtp** (выберите один):
  - [ ] **Gmail**: App Password (рекомендуется для международных проектов)
  - [ ] **Yandex**: App Password (рекомендуется для РФ)
  - [ ] **Mail.ru**: обычный пароль (альтернатива)
- [ ] **REG.RU S3**: Access Key + Secret Key
- [ ] **Encryption Key**: Случайная строка 32+ символов

### Получение GitHub Personal Access Token:

**Зачем нужен:** Для клонирования репозитория и автоматических git push операций

**Как получить:**

1. Перейти: https://github.com/settings/tokens
2. Нажать **"Generate new token"** → **"Generate new token (classic)"**
3. Заполнить:
   - **Note**: `VPS PTP Deployment` (описание токена)
   - **Expiration**: 90 days (или No expiration для постоянного доступа)
   - **Scopes**: Выбрать только **`repo`** (полный доступ к приватным репозиториям)
4. Нажать **"Generate token"**
5. **ВАЖНО**: Скопировать токен сразу! Формат: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
6. Сохранить токен в безопасном месте (понадобится в этапе 2.2)

**Безопасность:**
- Никогда не коммитьте токен в Git
- Не делитесь токеном
- Если токен скомпрометирован - удалите его на GitHub и создайте новый

### Получение Telegram Bot Token:

**Зачем нужен:** Для работы Telegram бота, который создаёт контент

**Как получить:**

1. Открыть Telegram и найти бота: [@BotFather](https://t.me/BotFather)
2. Отправить команду: `/newbot`
3. Ввести имя бота (например: `PTP Hugo Bot`)
4. Ввести username бота (должен заканчиваться на `bot`, например: `ptp_hugo_bot`)
5. Скопировать токен. Формат: `1234567890:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`
6. Сохранить токен (понадобится в этапе 4.3)

**Дополнительно:**
- Получить свой User ID: [@userinfobot](https://t.me/userinfobot) → `/start`
- Сохранить User ID (понадобится для ALLOWED_USER_IDS)

### Генерация ключа шифрования:

```bash
# На локальной машине:
openssl rand -base64 32
# Или
python3 -c "import secrets; print(secrets.token_urlsafe(32))"
```

## План развертывания

> **💡 Автоматизация:** Для каждого этапа доступны готовые bash-скрипты в папке [scripts/](scripts/)
>
> **Быстрый старт:** Запустите `./scripts/deploy-all.sh` для автоматического развертывания всех этапов
>
> **Ручная установка:** Следуйте инструкциям ниже для пошагового выполнения

### ЭТАП 1: Базовая подготовка VPS (30-60 минут)

#### 1.1 Подключение и обновление системы (10 минут)

**Автоматизация:** `./scripts/1.1-initial-setup.sh`

```bash
# Подключение к VPS
ssh root@YOUR_VPS_IP
# или
ssh your_user@YOUR_VPS_IP

# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка базовых утилит
sudo apt install -y curl wget htop vim nano tree git
```

#### 1.2 Установка всех необходимых пакетов (20 минут)

**Автоматизация:** `./scripts/1.2-install-packages.sh`

```bash
# Установка основных пакетов
sudo apt install -y \
  nginx \
  git \
  python3 \
  python3-pip \
  python3-venv \
  python3-full \
  php-fpm \
  php-cli \
  php-curl \
  php-json \
  php-mbstring \
  msmtp \
  msmtp-mta \
  certbot \
  python3-certbot-nginx \
  ufw

# Проверка установки
echo "=== Проверка установленных пакетов ==="
git --version && echo "✅ Git OK" || echo "❌ Git failed"
python3 --version && echo "✅ Python OK" || echo "❌ Python failed"
php --version && echo "✅ PHP OK" || echo "❌ PHP failed"
nginx -v && echo "✅ Nginx OK" || echo "❌ Nginx failed"
```

#### 1.3 Установка Hugo Extended (10 минут)

**Автоматизация:** `./scripts/1.3-install-hugo.sh`

```bash
# Скачать Hugo Extended (обязательно Extended версия!)
cd /tmp
wget https://github.com/gohugoio/hugo/releases/download/v0.139.0/hugo_extended_0.139.0_linux-amd64.deb

# Установить
sudo dpkg -i hugo_extended_*.deb

# Проверить
hugo version
# Должно показать: hugo v0.139.0+extended...
```

#### 1.4 Настройка Firewall (5 минут)

**Автоматизация:** `./scripts/1.4-setup-firewall.sh`

```bash
# Настройка UFW
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'
sudo ufw --force enable

# Проверка
sudo ufw status
```

#### 1.5 Создание структуры директорий (5 минут)

**Автоматизация:** `./scripts/1.5-create-directories.sh`

```bash
# Создание всех необходимых директорий
sudo mkdir -p /var/www/{html,hugo-source,api,webhook,telegram-bot,forms,logs}
sudo mkdir -p /var/secure/forms

# Настройка прав доступа
sudo chown -R $USER:www-data /var/www/
sudo chmod -R 775 /var/www/
sudo chmod 700 /var/secure/forms

# Проверка структуры
tree -L 2 /var/www/

# Структура должна быть:
# /var/www/
# ├── html/           - Hugo статический сайт (пересобирается каждый раз)
# ├── hugo-source/    - Исходники Hugo + Git репозиторий
# ├── api/            - Webhook PHP endpoint
# ├── webhook/        - Deploy скрипт
# ├── telegram-bot/   - Telegram бот
# ├── forms/          - PHP формы (НЕ пересобираются Hugo)
# └── logs/           - Логи
```

---

### ЭТАП 2: Git и Hugo (1 час)

#### 2.1 Настройка Git (10 минут)

**Автоматизация:** `./scripts/2.1-setup-git.sh`

```bash
# Настройка Git глобально
git config --global user.name "VPS Bot User"
git config --global user.email "bot@yourdomain.com"

# Проверка
git config --list | grep user
```

#### 2.2 Клонирование репозитория (15 минут)

**Автоматизация:** `./scripts/2.2-clone-repo.sh`

```bash
# ВАРИАНТ 1: HTTPS с Personal Access Token (рекомендуется)
cd /var/www
git clone https://YOUR_USERNAME:YOUR_GITHUB_TOKEN@github.com/YOUR_USERNAME/ptp.git hugo-source

# ВАРИАНТ 2: Обычное клонирование (потом настроить credentials)
cd /var/www
git clone https://github.com/YOUR_USERNAME/ptp.git hugo-source

# Настройка git credentials для автоматизации
echo "https://YOUR_USERNAME:YOUR_GITHUB_TOKEN@github.com" > ~/.git-credentials
git config --global credential.helper store

# Добавить папку в safe.directory
git config --global --add safe.directory /var/www/hugo-source

# ВАЖНО: Изменить владельца на www-data для работы webhook
sudo chown -R www-data:www-data /var/www/hugo-source

# Проверить права
ls -la /var/www/hugo-source/
```

#### 2.3 Первая сборка Hugo (15 минут)

**Автоматизация:** `./scripts/2.3-build-hugo.sh`

```bash
# Перейти в директорию Hugo
cd /var/www/hugo-source

# Проверить что config.toml существует
ls -la config.toml

# Собрать сайт
hugo --destination /var/www/html --cleanDestinationDir

# Проверить что файлы собрались
ls -la /var/www/html/

# Настроить права
sudo chown -R www-data:www-data /var/www/html
```

#### 2.4 Создание Deploy скрипта (10 минут)

**Автоматизация:** `./scripts/2.4-create-deploy-script.sh`

```bash
# Создать скрипт автодеплоя
cat > /var/www/webhook/deploy.sh << 'EOF'
#!/bin/bash
set -e

LOG_FILE="/var/log/hugo-deploy.log"
HUGO_SOURCE="/var/www/hugo-source"
HUGO_OUTPUT="/var/www/html"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> $LOG_FILE
}

log "Starting Hugo deployment"

# Переходим в папку с исходниками
cd $HUGO_SOURCE

# Обновляем код
git pull origin main
log "Git pull completed"

# Собираем Hugo сайт
hugo --destination $HUGO_OUTPUT --cleanDestinationDir
log "Hugo build completed"

# Устанавливаем права
chown -R www-data:www-data $HUGO_OUTPUT
log "Permissions set"

log "Deployment finished successfully"
EOF

# Сделать исполняемым
chmod +x /var/www/webhook/deploy.sh

# Тестовый запуск
sudo /var/www/webhook/deploy.sh
```

#### 2.5 Создание Webhook обработчика (10 минут)

**Автоматизация:** `./scripts/2.5-create-webhook.sh`

```bash
# Создать webhook.php
sudo mkdir -p /var/www/api
sudo tee /var/www/api/webhook.php > /dev/null << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$secret = ''; // ЗАПОЛНИТЕ ПОЗЖЕ или оставьте пустым для тестов
$log_file = '/var/log/hugo-webhook.log';

function writeLog($message) {
    global $log_file;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
}

writeLog("=== Webhook triggered ===");

// Проверяем метод
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    writeLog("ERROR: Wrong method: " . $_SERVER['REQUEST_METHOD']);
    exit('Method not allowed');
}

// Получаем payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    writeLog("ERROR: Invalid JSON payload");
    exit('Invalid payload');
}

// Проверка подписи GitHub (если secret настроен)
if (!empty($secret)) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    if (!hash_equals($expected, $signature)) {
        http_response_code(401);
        writeLog('ERROR: Invalid signature');
        exit('Unauthorized');
    }
}

// Проверяем что это push в main ветку
if (!isset($data['ref']) || $data['ref'] !== 'refs/heads/main') {
    writeLog("INFO: Skipping - not main branch. Ref: " . ($data['ref'] ?? 'unknown'));
    exit('Not main branch');
}

writeLog("INFO: Push to main branch detected");

// Запускаем deploy скрипт
$deploy_script = '/var/www/webhook/deploy.sh';
$output = [];
$return_code = 0;

writeLog("INFO: Starting deploy script...");
exec("sudo $deploy_script 2>&1", $output, $return_code);

$output_str = implode("\n", $output);
writeLog("DEPLOY OUTPUT:\n" . $output_str);

if ($return_code === 0) {
    writeLog("SUCCESS: Deploy completed successfully");
    http_response_code(200);
    echo "Deploy successful";
} else {
    writeLog("ERROR: Deploy failed with code: $return_code");
    http_response_code(500);
    echo "Deploy failed";
}

writeLog("=== Webhook finished ===\n");
?>
EOF

# Установить права
sudo chown www-data:www-data /var/www/api/webhook.php
sudo chmod 644 /var/www/api/webhook.php
```

---

### ЭТАП 3: Nginx и SSL (1 час)

#### 3.1 Настройка Nginx (20 минут)

**Автоматизация:** `./scripts/3.1-setup-nginx.sh`

```bash
# Определить версию PHP-FPM
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "PHP Version: $PHP_VERSION"

# Создать конфигурацию Nginx
sudo tee /etc/nginx/sites-available/ptp-site > /dev/null << EOF
server {
    listen 80;
    server_name YOUR_DOMAIN.COM;  # ЗАМЕНИТЕ на ваш домен

    root /var/www/html;
    index index.html index.htm;

    # Основной Hugo сайт (статический контент)
    location / {
        try_files \$uri \$uri/ =404;
    }

    # API endpoint для webhook
    location /api/ {
        root /var/www;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # PHP формы (отдельная папка, НЕ удаляется при deploy)
    location /forms/ {
        root /var/www;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # Безопасность - блокировка скрытых файлов
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Блокировка .env файлов
    location ~ /\.env {
        deny all;
    }
}
EOF

# Активировать сайт
sudo ln -sf /etc/nginx/sites-available/ptp-site /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Проверить конфигурацию
sudo nginx -t

# Перезапустить Nginx
sudo systemctl restart nginx
sudo systemctl restart php${PHP_VERSION}-fpm

# Проверить статус
sudo systemctl status nginx
```

#### 3.2 Настройка DNS (5 минут + ожидание)

**В панели управления доменом:**

```
Тип: A
Имя: @ (или оставить пустым для корн��вого домена)
Значение: YOUR_VPS_IP
TTL: 300 (5 минут)

# Для поддомена:
Тип: A
Имя: test (или dev)
Значение: YOUR_VPS_IP
TTL: 300
```

**Проверка распространения DNS:**

```bash
# Подождите 5-60 минут, затем проверьте:
nslookup YOUR_DOMAIN.COM
dig YOUR_DOMAIN.COM

# Или проверьте через curl
curl -I http://YOUR_DOMAIN.COM
```

#### 3.3 Установка SSL сертификата (15 минут)

**ВАЖНО: Выполнять ТОЛЬКО после того как DNS работает!**

```bash
# Проверить что домен отвечает
curl -I http://YOUR_DOMAIN.COM

# Получить SSL сертификат
sudo certbot --nginx -d YOUR_DOMAIN.COM

# Certbot автоматически обновит конфигурацию Nginx

# Проверить автопродление
sudo certbot renew --dry-run

# Проверить что HTTPS работает
curl -I https://YOUR_DOMAIN.COM
```

#### 3.4 Настройка sudo прав для webhook (10 минут)

**Автоматизация:** `./scripts/3.3-setup-webhook-sudo.sh`

```bash
# Добавить права для www-data запускать deploy скрипт
echo "www-data ALL=(ALL) NOPASSWD: /var/www/webhook/deploy.sh" | sudo tee /etc/sudoers.d/webhook
sudo chmod 440 /etc/sudoers.d/webhook

# Добавить safe.directory для www-data
sudo -u www-data git config --global --add safe.directory /var/www/hugo-source

# Тестирование
sudo -u www-data sudo /var/www/webhook/deploy.sh

# Если получаете ошибку "fatal: detected dubious ownership", выполните:
# sudo chown -R www-data:www-data /var/www/hugo-source
```

#### 3.5 Создание лог файлов (5 минут)

**Автоматизация:** `./scripts/3.4-create-logs.sh`

```bash
# Создать лог файлы
sudo touch /var/log/hugo-webhook.log /var/log/hugo-deploy.log

# Настроить права
sudo chown www-data:www-data /var/log/hugo-*.log
sudo chmod 664 /var/log/hugo-*.log

# Проверить что логи работают
sudo -u www-data bash -c 'echo "Test log entry" >> /var/log/hugo-webhook.log'
tail /var/log/hugo-webhook.log
```

#### 3.6 Настройка GitHub Webhook (5 минут)

**В GitHub репозитории:**

1. Перейти: Settings → Webhooks → Add webhook
2. Настроить:
   - **Payload URL**: `https://YOUR_DOMAIN.COM/api/webhook.php`
   - **Content type**: `application/json`
   - **Secret**: (оставить пустым для тестов или добавить секрет)
   - **Events**: Just the push event
   - **Active**: ✓

3. Сохранить и проверить Recent Deliveries

**Тестирование webhook:**

```bash
# Сделать тестовый коммит
cd /var/www/hugo-source
echo "Test webhook" >> README.md
git add README.md
git commit -m "Test webhook"
git push origin main

# Проверить логи
tail -f /var/log/hugo-webhook.log
tail -f /var/log/hugo-deploy.log

# Проверить что сайт обновился
curl -I https://YOUR_DOMAIN.COM
```

#### 3.7 Настройка Security Headers (10 минут)

**Что делают Security Headers:**
- 🛡️ Защита от XSS атак
- 🚫 Защита от clickjacking
- 🔐 Принудительное HTTPS
- 🎯 Контроль загрузки ресурсов

**На VPS:**

```bash
# Открыть конфигурацию Nginx
sudo nano /etc/nginx/sites-available/ptp-site

# Найти блок server { для HTTPS (слушает 443 ssl)
# Добавить Security Headers перед закрывающей скобкой }:

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=(), payment=(), usb=()" always;

    # HSTS - принудительное HTTPS (добавлять только после стабильной работы SSL!)
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # CSP - Content Security Policy (тестовый режим, не блокирует)
    add_header Content-Security-Policy-Report-Only "default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://s3.regru.cloud https://s3.storage.selcloud.ru; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self';" always;
}  # ← Перед этой закрывающей скобкой HTTPS блока

# Сохранить: Ctrl+O, Enter, Ctrl+X

# Проверить синтаксис
sudo nginx -t

# Если OK - перезагрузить
sudo systemctl reload nginx
```

**Проверка заголовков:**

```bash
# Проверить что заголовки работают
curl -I https://YOUR_DOMAIN.COM | grep -E "X-Frame|X-Content|Referrer|Permissions|Strict-Transport|Content-Security"

# Должно показать все 6 заголовков
```

**Тестирование CSP:**

```bash
# Откройте сайт в браузере
# Нажмите F12 → Console
# Проверьте нет ли ошибок CSP

# Если всё работает - через неделю можно включить блокировку CSP:
# Заменить Content-Security-Policy-Report-Only на Content-Security-Policy
```

**Онлайн проверка:**

Проверьте безопасность на: https://securityheaders.com/?q=https://YOUR_DOMAIN.COM

**Подробности:** См. [SECURITY_HEADERS.md](SECURITY_HEADERS.md) для детальной информации.

---

### ЭТАП 4: Telegram Bot (1.5-2 часа)

#### 4.1 Python окружение (20 минут)

```bash
# Перейти в директорию бота
cd /var/www/telegram-bot

# Убедиться что python3-venv установлен
sudo apt install -y python3-full python3-venv

# Создать виртуальное окружение
python3 -m venv --system-site-packages telegram_bot_env

# Активировать окружение
source telegram_bot_env/bin/activate

# Проверить что окружение работает
which pip
which python3
# Должно показать: /var/www/telegram-bot/telegram_bot_env/bin/...

# Обновить pip
pip install --upgrade pip

# Создать requirements.txt
cat > requirements.txt << 'EOF'
python-telegram-bot==20.7
requests
python-dotenv
pycryptodome
EOF

# Установить зависимости
pip install -r requirements.txt

# Проверить установку
python3 -c "
import telegram
import requests
import dotenv
from Crypto.Cipher import AES
print('✅ Все модули установлены успешно!')
"
```

#### 4.2 Копирование файлов Telegram бота (15 минут)

**ВАЖНО:** Файлы `telegram/` в .gitignore - нужно копировать вручную!

**На локальной машине:**

```bash
# Проверить что файлы существуют локально
ls -la ~/path/to/ptp/telegram/

# Скопировать на VPS
scp -r ~/path/to/ptp/telegram/ user@YOUR_VPS_IP:/var/www/telegram-bot/

# Пример для вашего пути:
scp -r /Users/kirik/Sync/Projects/Websites/PTP/DEV/ptp/telegram/ user@YOUR_VPS_IP:/var/www/telegram-bot/
```

**На VPS - проверка:**

```bash
# Проверить что файлы скопировались
ls -la /var/www/telegram-bot/telegram/

# Должны быть:
# telegram_bot.py
# applications_reader.py
# s3_helper.py
# requirements.txt

# Установить права
sudo chown -R $USER:www-data /var/www/telegram-bot/
```

#### 4.3 Настройка .env для бота (20 минут)

**Важно про пользователя www-data:**
- Telegram бот запускается от пользователя `www-data` (настроено в systemd)
- `www-data` - стандартный веб-сервер пользователь в Linux
- Используется для: Nginx, PHP-FPM, deploy скрипты, Telegram бот
- Все файлы конфигурации (.env) и репозиторий должны принадлежать `www-data`
- Это обеспечивает единые права доступа для всех компонентов системы

```bash
# Создать .env файл
cat > /var/www/telegram-bot/.env << 'EOF'
# ============================================
# Telegram Bot Configuration
# ============================================

# 1. Создайте бота: https://t.me/BotFather -> /newbot
# 2. Получите токен (формат: 1234567890:ABC-DEF1234ghIkl-zyx57W2v1u123ew11)
TELEGRAM_BOT_TOKEN=YOUR_BOT_TOKEN_HERE

# 3. Найдите ваш User ID: https://t.me/userinfobot -> /start
# 4. Добавьте через запятую ID всех админов
ALLOWED_USER_IDS=123456789,987654321

# 5. Chat ID для уведомлений (обычно ваш User ID)
TELEGRAM_ADMIN_CHAT_ID=123456789

# ============================================
# Forms Configuration (многоканальная отправка)
# ============================================

FORMS_SEND_EMAIL=true          # Отправлять на email через msmtp
FORMS_SEND_TELEGRAM=true       # Уведомления в Telegram
FORMS_SAVE_JSON=true           # Сохранять зашифрованные заявки
FORMS_NOTIFICATIONS=true       # Push-уведомления

# Ключ шифрования: сгенерируйте случайную строку 32+ символов
# openssl rand -base64 32
# ВАЖНО: Этот ключ используется для:
#   - PHP формы шифруют заявки этим ключом и сохраняют в /var/secure/forms/
#   - Telegram бот расшифровывает заявки этим же ключом для отображения
#   - Ключ ДОЛЖЕН совпадать с FORMS_ENCRYPTION_KEY в /var/www/forms/.env (этап 5.2)
FORMS_ENCRYPTION_KEY=YOUR_32_CHAR_ENCRYPTION_KEY_HERE

# ============================================
# REG.RU S3 Configuration (для загрузки фото)
# ============================================

# Найти в панели REG.RU: Услуги -> Облачное хранилище -> Настройки API
# Регион REG.RU: ru-1 (Москва) или ru-2 (Санкт-Петербург)
AWS_ACCESS_KEY_ID=YOUR_REGRU_ACCESS_KEY
AWS_SECRET_ACCESS_KEY=YOUR_REGRU_SECRET_KEY
AWS_DEFAULT_REGION=ru-1
AWS_ENDPOINT_URL=https://s3.regru.cloud

# Альтернативные имена переменных (бот поддерживает оба формата)
S3_ACCESS_KEY_ID=YOUR_REGRU_ACCESS_KEY
S3_SECRET_ACCESS_KEY=YOUR_REGRU_SECRET_KEY

# ============================================
# Git Configuration
# ============================================

# Путь к Git репозиторию
GIT_REPO_PATH=/var/www/hugo-source
EOF

# ВАЖНО: Установить права для www-data (бот запускается от этого пользователя)
sudo chown www-data:www-data /var/www/telegram-bot/.env
sudo chmod 600 /var/www/telegram-bot/.env

# Проверить права
ls -la /var/www/telegram-bot/.env
# Должно показать: -rw------- 1 www-data www-data

echo ""
echo "⚠️  ВНИМАНИЕ: Отредактируйте .env файл с вашими настройками!"
echo "sudo nano /var/www/telegram-bot/.env"
echo ""
echo "После редактирования снова установите права:"
echo "sudo chown www-data:www-data /var/www/telegram-bot/.env"
```

**Редактирование .env:**

```bash
nano /var/www/telegram-bot/.env

# Заменить:
# - YOUR_BOT_TOKEN_HERE → токен от BotFather
# - 123456789 → ваш Telegram User ID
# - YOUR_32_CHAR_ENCRYPTION_KEY_HERE → сгенерированный ключ
# - YOUR_REGRU_ACCESS_KEY → ключи от REG.RU S3
```

#### 4.4 Настройка Git для www-data (15 минут)

```bash
# Настроить Git для www-data пользователя
sudo -u www-data git config --global user.name "Telegram Bot"
sudo -u www-data git config --global user.email "bot@yourdomain.com"
sudo -u www-data git config --global credential.helper store

# Добавить safe.directory
sudo -u www-data git config --global --add safe.directory /var/www/hugo-source

# Создать .git-credentials для www-data
sudo -u www-data bash -c 'echo "https://YOUR_USERNAME:YOUR_GITHUB_TOKEN@github.com" > /var/www/.git-credentials'
sudo chmod 600 /var/www/.git-credentials
sudo chown www-data:www-data /var/www/.git-credentials

# Проверить настройки
sudo -u www-data git config --list
```

#### 4.5 Создание Systemd сервиса (10 минут)

```bash
# Создать systemd сервис
sudo tee /etc/systemd/system/telegram-hugo-bot.service > /dev/null << 'EOF'
[Unit]
Description=Telegram Hugo Bot
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/telegram-bot
Environment=PATH=/var/www/telegram-bot/telegram_bot_env/bin:/usr/local/bin:/usr/bin:/bin
Environment=PYTHONHTTPSVERIFY=0
Environment=AWS_CLI_AUTO_PROMPT=off
Environment=PYTHONWARNINGS=ignore
ExecStart=/var/www/telegram-bot/telegram_bot_env/bin/python3 telegram/telegram_bot.py
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

# Перезагрузить systemd
sudo systemctl daemon-reload

# Включить автозапуск
sudo systemctl enable telegram-hugo-bot

# Запустить бота
sudo systemctl start telegram-hugo-bot

# Проверить статус
sudo systemctl status telegram-hugo-bot

# Смотреть логи в реальном времени
sudo journalctl -u telegram-hugo-bot -f
```

#### 4.6 Тестирование Telegram бота (10 минут)

```bash
# Проверить что бот запущен
sudo systemctl status telegram-hugo-bot

# Проверить логи на ошибки
sudo journalctl -u telegram-hugo-bot --no-pager -n 50

# В Telegram:
# 1. Найти вашего бота
# 2. Отправить /start
# 3. Проверить кнопки главного меню

# Если есть ошибки - проверить:
cd /var/www/telegram-bot
source telegram_bot_env/bin/activate
python3 telegram/telegram_bot.py
# Увидите детальные ошибки
```

---

### ЭТАП 5: PHP Forms с Email (1-1.5 часа)

#### 5.1 Копирование PHP файлов форм (10 минут)

**ВАЖНО:** PHP формы НЕ должны быть в `/var/www/html/`, так как Hugo удаляет всё из этой папки при каждом deploy (`--cleanDestinationDir`). Используем отдельную папку `/var/www/forms/`.

**На локальной машине:**

```bash
# Скопировать файлы форм на VPS в отдельную папку
scp -r ~/path/to/ptp/forms/* user@YOUR_VPS_IP:/var/www/forms/

# Пример:
scp -r /Users/kirik/Sync/Projects/Websites/PTP/DEV/ptp/forms/* user@YOUR_VPS_IP:/var/www/forms/
```

**На VPS - проверка:**

```bash
# Проверить файлы
ls -la /var/www/forms/

# Должны быть:
# send_plan.php
# send_ask.php
# forms_helper.php

# Установить права
sudo chown -R www-data:www-data /var/www/forms/
sudo chmod 644 /var/www/forms/*.php

# Проверить что Hugo не удалит эти файлы при deploy
echo "✅ Формы в /var/www/forms/ - Hugo их не трогает"
echo "✅ Доступ через: https://YOUR_DOMAIN.COM/forms/send_plan.php"
```

#### 5.2 Создание .env для форм (10 минут)

```bash
# Создать .env для PHP форм в отдельной папке forms/
cat > /var/www/forms/.env << 'EOF'
# ============================================
# Forms Configuration
# ============================================

FORMS_SEND_EMAIL=true          # Отправлять на email через msmtp
FORMS_SEND_TELEGRAM=true       # Уведомления в Telegram
FORMS_SAVE_JSON=true           # Сохранять зашифрованные заявки
FORMS_NOTIFICATIONS=true       # Push-уведомления

# Ключ шифрования (ДОЛЖЕН СОВПАДАТЬ с ключом в Telegram боте!)
# ВАЖНО: Используйте тот же ключ, что и в /var/www/telegram-bot/.env
# Этот ключ шифрует заявки, а бот их расшифровывает для отображения
FORMS_ENCRYPTION_KEY=SAME_KEY_AS_TELEGRAM_BOT

# Telegram настройки
TELEGRAM_BOT_TOKEN=YOUR_BOT_TOKEN
TELEGRAM_ADMIN_CHAT_ID=YOUR_ADMIN_CHAT_ID
EOF

# Установить права
chmod 600 /var/www/forms/.env
sudo chown www-data:www-data /var/www/forms/.env

echo "⚠️  Отредактируйте .env файл!"
echo "nano /var/www/forms/.env"
```

#### 5.3 Настройка msmtp для Email (30 минут)

> **Выберите один из почтовых сервисов**: Gmail, Yandex или Mail.ru

##### ВАРИАНТ A: Gmail (рекомендуется для международных проектов)

**Получение Gmail App Password:**

1. Включить 2FA: https://myaccount.google.com/security
2. Создать App Password: https://myaccount.google.com/apppasswords
3. Выбрать: Mail → Other → "VPS PTP Bot"
4. Сохранить 16-символьный пароль

**Запустить скрипт:** `./scripts/5.3a-setup-msmtp-gmail.sh`

##### ВАРИАНТ B: Yandex (рекомендуется для РФ)

**Получение Yandex App Password:**

1. Перейти: https://passport.yandex.ru/profile
2. Безопасность → Пароли приложений
3. Создать пароль для "Почта"
4. Сохранить 16-символьный пароль
5. Включить SMTP: https://mail.yandex.ru/ → Настройки → Почтовые программы → "Разрешить доступ"

**Запустить скрипт:** `./scripts/5.3b-setup-msmtp-yandex.sh`

##### ВАРИАНТ C: Mail.ru (альтернатива)

**Примечание:** Mail.ru использует обычный пароль от почты (не App Password)

**Запустить скрипт:** `./scripts/5.3c-setup-msmtp-mailru.sh`

---

**Ручная настройка (если предпочитаете):**

```bash
# Установить msmtp (если не установлен)
sudo apt install -y msmtp msmtp-mta

# Создать конфигурацию msmtp - ВЫБЕРИТЕ ОДИН ИЗ ВАРИАНТОВ:

# === ВАРИАНТ A: Gmail ===
sudo tee /etc/msmtprc > /dev/null << 'EOF'
defaults
auth           on
tls            on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile        /var/log/msmtp.log

account        gmail
host           smtp.gmail.com
port           587
from           YOUR_EMAIL@gmail.com
user           YOUR_EMAIL@gmail.com
password       YOUR_GMAIL_APP_PASSWORD

account default : gmail
EOF

# === ВАРИАНТ B: Yandex ===
sudo tee /etc/msmtprc > /dev/null << 'EOF'
defaults
auth           on
tls            on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile        /var/log/msmtp.log

account        yandex
host           smtp.yandex.ru
port           587
from           YOUR_EMAIL@yandex.ru
user           YOUR_EMAIL@yandex.ru
password       YOUR_YANDEX_APP_PASSWORD

account default : yandex
EOF

# === ВАРИАНТ C: Mail.ru ===
sudo tee /etc/msmtprc > /dev/null << 'EOF'
defaults
auth           on
tls            on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile        /var/log/msmtp.log

account        mailru
host           smtp.mail.ru
port           587
from           YOUR_EMAIL@mail.ru
user           YOUR_EMAIL@mail.ru
password       YOUR_MAILRU_PASSWORD

account default : mailru
EOF

# Установить права
sudo chmod 600 /etc/msmtprc
sudo chown root:mail /etc/msmtprc

# Создать лог файл
sudo touch /var/log/msmtp.log
sudo chown www-data:adm /var/log/msmtp.log
sudo chmod 664 /var/log/msmtp.log
```

**Редактирование конфигурации:**

```bash
sudo nano /etc/msmtprc

# Заменить в зависимости от выбранного сервиса:
# Gmail: YOUR_EMAIL@gmail.com и YOUR_GMAIL_APP_PASSWORD
# Yandex: YOUR_EMAIL@yandex.ru и YOUR_YANDEX_APP_PASSWORD
# Mail.ru: YOUR_EMAIL@mail.ru и YOUR_MAILRU_PASSWORD
```

**Сравнение сервисов:**

| Параметр | Gmail | Yandex | Mail.ru |
|----------|-------|--------|---------|
| Лимит/день | 500-2000 | ~500 | ~1000 |
| Тип пароля | App Password | App Password | Обычный |
| Доступность в РФ | Может блокироваться | Стабильно | Стабильно |
| Надёжность | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

#### 5.4 Настройка PHP для использования msmtp (10 минут)

**Автоматизация:** `./scripts/5.4-setup-php-msmtp.sh`

```bash
# Определить версию PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Настраиваем PHP версии: $PHP_VERSION"

# Настроить sendmail_path для PHP-FPM
echo "sendmail_path = /usr/bin/msmtp -t" | sudo tee -a /etc/php/$PHP_VERSION/fpm/php.ini

# Настроить sendmail_path для PHP CLI
echo "sendmail_path = /usr/bin/msmtp -t" | sudo tee -a /etc/php/$PHP_VERSION/cli/php.ini

# Перезапустить PHP-FPM
sudo systemctl restart php$PHP_VERSION-fpm
```

#### 5.5 Тестирование Email (10 минут)

```bash
# Тест 1: msmtp напрямую
echo "Test from VPS - msmtp" | msmtp YOUR_EMAIL@gmail.com

# Проверить логи
sudo tail /var/log/msmtp.log

# Тест 2: PHP mail функция
echo "<?php mail('YOUR_EMAIL@gmail.com', 'Test Subject', 'Test from PHP'); echo 'Mail sent'; ?>" | php

# Тест 3: Проверка из браузера
echo '<?php
$to = "YOUR_EMAIL@gmail.com";
$subject = "Test from PHP web";
$message = "This is a test email from PHP running via Nginx";
$headers = "From: webmaster@yourdomain.com";

if(mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>' | sudo tee /var/www/html/test_email.php

# Открыть в браузере:
# https://YOUR_DOMAIN.COM/test_email.php

# Удалить тестовый файл после проверки
sudo rm /var/www/html/test_email.php
```

#### 5.6 Создание безопасного хранилища (5 минут)

```bash
# Создать директорию для зашифрованных заявок
sudo mkdir -p /var/secure/forms

# Установить строгие права доступа
sudo chmod 700 /var/secure/forms
sudo chown www-data:www-data /var/secure/forms

# Проверить права
ls -la /var/secure/

# Должно показать: drwx------ www-data www-data forms
```

---

### ЭТАП 6: Тестирование системы (1 час)

#### 6.1 Проверка всех ��омпонентов (20 минут)

```bash
echo "=== Проверка статуса всех сервисов ==="

# Nginx
sudo systemctl status nginx | grep "Active:"

# PHP-FPM
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
sudo systemctl status php$PHP_VERSION-fpm | grep "Active:"

# Telegram Bot
sudo systemctl status telegram-hugo-bot | grep "Active:"

# Проверка Hugo
hugo version

# Проверка Git
cd /var/www/hugo-source
git status

# Проверка файлов
echo -e "\n=== Проверка ключевых файлов ==="
ls -la /var/www/html/index.html && echo "✅ Hugo site OK"
ls -la /var/www/api/webhook.php && echo "✅ Webhook OK"
ls -la /var/www/forms/send_plan.php && echo "✅ PHP Forms OK"
ls -la /var/www/telegram-bot/telegram/telegram_bot.py && echo "✅ Telegram Bot OK"

echo -e "\n=== Все компоненты проверены ==="
```

#### 6.2 Тест Telegram бота (15 минут)

**В Telegram:**

```
1. Найти вашего бота
2. Отправить: /start
3. Проверить кнопки:
   - 📝 Управление постами
   - 🗓️ Управление календарем поездок
   - 📋 Заявки

4. Попробовать создать пост:
   - Нажать "📝 Управление постами"
   - "➕ Создать пост"
   - Следовать инструкциям

5. Проверить Git sync:
   - "🔄 Синхронизация Git"
```

**Проверка логов:**

```bash
# Логи бота
sudo journalctl -u telegram-hugo-bot -f

# Если ошибки - запуск вручную для диагностики
cd /var/www/telegram-bot
source telegram_bot_env/bin/activate
python3 telegram/telegram_bot.py
```

#### 6.3 Тест PHP форм и Email (15 минут)

```bash
# Создать тестовую HTML страницу с формой
cat > /var/www/html/test_form.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Form</title>
</head>
<body>
    <h1>Test Plan Form</h1>
    <form action="/forms/send_plan.php" method="POST">
        <label>Имя: <input type="text" name="name" value="Test User" required></label><br>
        <label>Email: <input type="email" name="email" value="test@example.com" required></label><br>
        <label>Телефон: <input type="tel" name="phone" value="+79001234567"></label><br>
        <label>Telegram: <input type="text" name="telegram" value="@testuser"></label><br>
        <label>Поездка: <input type="text" name="trip_period" value="Сентябрь 2025"></label><br>
        <label>БВС: <input type="text" name="bvs_number" value="БВС-001"></label><br>
        <button type="submit">Отправить</button>
    </form>
</body>
</html>
EOF

echo "Откройте в браузере: https://YOUR_DOMAIN.COM/test_form.html"
echo "Заполните и отправьте форму"
echo ""
echo "Проверьте:"
echo "1. Email пришел на почту"
echo "2. Уведомление в Telegram боте"
echo "3. Файл создался: ls -la /var/secure/forms/"
echo "4. Логи msmtp: sudo tail /var/log/msmtp.log"
```

#### 6.4 Тест GitHub Webhook (10 минут)

```bash
# Сделать тестовое изменение
cd /var/www/hugo-source
echo "<!-- Test webhook $(date) -->" >> README.md
git add README.md
git commit -m "Test webhook deployment"
git push origin main

# Проверить логи webhook
tail -30 /var/log/hugo-webhook.log

# Проверить логи deploy
tail -30 /var/log/hugo-deploy.log

# Проверить что сайт обновился
curl -I https://YOUR_DOMAIN.COM

# В GitHub проверить:
# Settings → Webhooks → Recent Deliveries
# Должен быть зеленый чекмарк
```

---

## Troubleshooting (Устранение проблем)

### Telegram Bot не запускается

**Проблема:** `exit-code status=2`

```bash
# Диагностика
cd /var/www/telegram-bot
source telegram_bot_env/bin/activate

# Проверка синтаксиса
python3 -m py_compile telegram/telegram_bot.py

# Проверка зависимостей
python3 -c "import telegram, requests, dotenv; print('OK')"

# Запуск вручную
python3 telegram/telegram_bot.py
# Увидите точную ошибку
```

**Решение:** Установить недостающие модули:

```bash
pip install python-telegram-bot==20.7 requests python-dotenv pycryptodome
sudo systemctl restart telegram-hugo-bot
```

### Permission denied: .env файл

**Проблема:** `PermissionError: [Errno 13] Permission denied: '/var/www/telegram-bot/.env'`

**Причина:** Бот запускается от `www-data`, а файл `.env` принадлежит другому пользователю

**Решение:**

```bash
# Проверить текущие права
ls -la /var/www/telegram-bot/.env

# Изменить владельца на www-data
sudo chown www-data:www-data /var/www/telegram-bot/.env
sudo chmod 600 /var/www/telegram-bot/.env

# Проверить что права установлены
ls -la /var/www/telegram-bot/.env
# Должно показать: -rw------- 1 www-data www-data

# Перезапустить бота
sudo systemctl restart telegram-hugo-bot

# Проверить статус
sudo systemctl status telegram-hugo-bot
```

**Важно:** Все файлы, используемые ботом, должны принадлежать `www-data`, так как systemd сервис запускает бота от этого пользователя.

### Email не отправляется

**Проблема:** Формы не отправляют email

```bash
# Проверка msmtp
msmtp --version

# Проверка конфигурации
sudo cat /etc/msmtprc

# Проверка логов
sudo tail -f /var/log/msmtp.log

# Тест отправки
echo "test" | msmtp YOUR_EMAIL@gmail.com

# Проверка PHP
php -i | grep sendmail_path
# Должно показать: /usr/bin/msmtp -t
```

**Решение:** Проверить Gmail App Password и настройки msmtp

### Webhook возвращает 404

**Проблема:** GitHub webhook получает 404

```bash
# Проверка файла
ls -la /var/www/api/webhook.php

# Проверка Nginx конфигурации
sudo nginx -t

# Проверка логов Nginx
sudo tail -f /var/log/nginx/error.log

# Проверка PHP-FPM
sudo systemctl status php*-fpm

# Тест webhook вручную
curl -X POST https://YOUR_DOMAIN.COM/api/webhook.php
```

**Решение:** Убедиться что:
1. Файл webhook.php существует в `/var/www/api/`
2. Nginx настроен на обработку `/api/` локации
3. PHP-FPM запущен

### Hugo build fails

**Проблема:** Deploy скрипт падает при сборке Hugo

```bash
# Проверка вручную
cd /var/www/hugo-source
hugo --destination /var/www/html --cleanDestinationDir

# Проверка логов
tail -50 /var/log/hugo-deploy.log

# Проверка конфигурации Hugo
cat config.toml
```

**Решение:** Проверить синтаксис в markdown файлах

### Git конфликты

**Проблема:** Telegram bot не может сделать push

```bash
# Проверка Git статуса
cd /var/www/hugo-source
sudo -u www-data git status

# Сброс к origin/main
sudo -u www-data git fetch origin main
sudo -u www-data git reset --hard origin/main

# Проверка credentials
sudo -u www-data git config --list | grep credential
```

### Git dubious ownership (detected dubious ownership)

**Проблема:** Deploy скрипт падает с ошибкой `fatal: detected dubious ownership in repository`

**Причина:** Репозиторий принадлежит одному пользователю (например, `ptp`), а скрипт запускается от другого (`www-data`)

**Решение:**

```bash
# Вариант 1: Изменить владельца репозитория на www-data (рекомендуется)
sudo chown -R www-data:www-data /var/www/hugo-source

# Вариант 2: Добавить safe.directory для www-data
sudo -u www-data git config --global --add safe.directory /var/www/hugo-source

# Проверить что ошибка исчезла
sudo -u www-data sudo /var/www/webhook/deploy.sh
tail -20 /var/log/hugo-deploy.log
```

### S3 Upload Errors

**Проблема:** "argument of type 'NoneType' is not iterable"

**Решение:** Проверить systemd конфигурацию из [VPS2_DEPLOYMENT.md:645-674](../VPS2_DEPLOYMENT.md#L645-L674)

---

## Финальный Checklist

### После успешного развертывания:

- [ ] ✅ Hugo сайт доступен по HTTPS
- [ ] ✅ SSL сертификат установлен и работает
- [ ] ✅ GitHub webhook настроен и работает (тестовый push)
- [ ] ✅ Telegram бот отвечает на /start
- [ ] ✅ Создание поста через бота работает
- [ ] ✅ Git sync через бота работает
- [ ] ✅ PHP формы доступны
- [ ] ✅ Email отправка работает (тест)
- [ ] ✅ Telegram уведомления от форм работают
- [ ] ✅ Зашифрованное хранилище работает (/var/secure/forms/)
- [ ] ✅ Все логи пишутся корректно
- [ ] ✅ Systemd сервисы в статусе Active

### Безопасность:

- [ ] UFW firewall включен (22, 80, 443)
- [ ] .env файлы имеют права 600
- [ ] /var/secure/forms/ имеет права 700
- [ ] Git credentials защищены
- [ ] Регулярные обновления настроены

### Мониторинг:

```bash
# Команды для мониторинга
sudo systemctl status nginx telegram-hugo-bot
sudo journalctl -u telegram-hugo-bot -f
tail -f /var/log/hugo-webhook.log
tail -f /var/log/msmtp.log
```

---

## Следующие шаги

### 1. Регулярное обслуживание

```bash
# Еженедельно
sudo apt update && sudo apt upgrade -y
sudo certbot renew --dry-run

# Ежемесячно
# Проверка дискового пространства
df -h

# Очистка старых логов
sudo journalctl --vacuum-time=30d
```

### 2. Backup стратегия

```bash
# GitHub = backup контента (автоматически)
# Создать backup .env файлов (вручную)
cp /var/www/telegram-bot/.env ~/backup/.env.telegram
cp /var/www/forms/.env ~/backup/.env.forms

# Снапшоты VPS (в панели провайдера)
# Рекомендация: раз в неделю
```

### 3. Улучшения (опционально)

- Настроить fail2ban для защиты от брутфорса
- Добавить rate limiting в Nginx
- Настроить автоматическое удаление старых заявок (30+ дней)
- Добавить CAPTCHA на формы
- Настроить мониторинг uptime (UptimeRobot)

---

## Полезные команды

### Управление сервисами

```bash
# Перезапуск всех компонентов
sudo systemctl restart nginx
sudo systemctl restart php*-fpm
sudo systemctl restart telegram-hugo-bot

# Просмотр логов
sudo journalctl -u telegram-hugo-bot -f
tail -f /var/log/hugo-webhook.log
tail -f /var/log/hugo-deploy.log
tail -f /var/log/msmtp.log

# Проверка статуса
sudo systemctl status nginx telegram-hugo-bot php*-fpm
```

### Git операции

```bash
# Обновление Hugo сайта вручную
cd /var/www/hugo-source
git pull origin main
hugo --destination /var/www/html --cleanDestinationDir

# Проверка Git конфигурации бота
sudo -u www-data git config --list
```

### Диагностика

```bash
# Проверка всех компонентов
systemctl status nginx telegram-hugo-bot
curl -I https://YOUR_DOMAIN.COM
ls -la /var/www/html/
ls -la /var/secure/forms/

# Тест email
echo "test" | msmtp YOUR_EMAIL@gmail.com

# Проверка PHP
php -v
php -m | grep -E 'curl|json|mbstring'
```

---

## Документация

### Связанные документы:
- [VPS1_DEPLOYMENT.md](VPS1_DEPLOYMENT.md) - Детали Hugo + Webhook
- [VPS2_DEPLOYMENT.md](VPS2_DEPLOYMENT.md) - Детали Telegram Bot + Forms
- [PROJECT_ARCHITECTURE.md](PROJECT_ARCHITECTURE.md) - Архитектура проекта
- [VPS_DEBUG_GUIDE.md](VPS_DEBUG_GUIDE.md) - Отладка проблем

### Официальные ресурсы:
- Hugo: https://gohugo.io/documentation/
- python-telegram-bot: https://docs.python-telegram-bot.org/
- Nginx: https://nginx.org/en/docs/
- Certbot: https://certbot.eff.org/

---

## Поддержка

**При возникновении проблем:**

1. Проверить логи (journalctl, /var/log/)
2. Использовать раздел Troubleshooting
3. Запустить компоненты вручную для диагностики
4. Проверить права доступа к файлам
5. Убедиться что все .env переменные настроены

**Успешного развертывания! 🚀**

---

*Последнее обновление: 2025-10-25*
*Версия документа: 1.3*
