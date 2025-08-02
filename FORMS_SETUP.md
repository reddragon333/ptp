# Настройка форм обратной связи

## Копирование PHP файлов на сервер

```bash
# Скопировать обработчики форм в корень веб-сервера
sudo cp static/send_plan.php /var/www/html/
sudo cp static/send_ask.php /var/www/html/

# Установить права
sudo chown www-data:www-data /var/www/html/send_*.php
sudo chmod 644 /var/www/html/send_*.php
```

## Настройка email

Отредактируйте файлы и замените email получателя:

```bash
# В файле send_plan.php
sudo nano /var/www/html/send_plan.php
# Найти строку: $to = "info@sleeptrip.ru";
# Заменить на ваш email

# В файле send_ask.php  
sudo nano /var/www/html/send_ask.php
# Найти строку: $to = "info@sleeptrip.ru";
# Заменить на ваш email
```

## Настройка SMTP (через msmtp)

```bash
# Установить msmtp
sudo apt update && sudo apt install msmtp msmtp-mta

# Создать конфигурацию
sudo tee /etc/msmtprc > /dev/null <<'EOF'
defaults
auth           on
tls            on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile        /var/log/msmtp.log

# Gmail настройки (замените на ваши)
account        gmail
host           smtp.gmail.com
port           587
from           your-email@gmail.com
user           your-email@gmail.com
password       your-app-password

# Установить Gmail как аккаунт по умолчанию
account default : gmail
EOF

# Установить права
sudo chmod 644 /etc/msmtprc
sudo chown root:mail /etc/msmtprc

# Создать лог файл
sudo touch /var/log/msmtp.log
sudo chown www-data:adm /var/log/msmtp.log
sudo chmod 664 /var/log/msmtp.log

# Настроить PHP для использования msmtp
echo "sendmail_path = /usr/bin/msmtp -t" | sudo tee -a /etc/php/*/apache2/php.ini
echo "sendmail_path = /usr/bin/msmtp -t" | sudo tee -a /etc/php/*/cli/php.ini

# Перезапустить Apache
sudo systemctl restart apache2
```

## Тестирование форм

1. Откройте страницы `/plan/` и `/ask/`
2. Заполните и отправьте формы
3. Проверьте логи: `sudo tail -f /var/log/msmtp.log`
4. Проверьте почту

## Альтернатива: использование существующего qform.io

Если предпочитаете оставить текущие формы qform.io, можно:

1. Оставить существующие формы как есть
2. PHP обработчики использовать как резервный вариант
3. Настроить webhook'и в qform.io для интеграции с внутренними системами

## Безопасность

- PHP файлы включают базовую валидацию
- Email headers защищены от инъекций
- Логируется IP и timestamp
- Рекомендуется добавить CAPTCHA для защиты от спама

## Интеграция с внутренними системами

Для интеграции с базой данных или CRM добавьте в PHP файлы:

```php
// Пример записи в базу данных
try {
    $pdo = new PDO('mysql:host=localhost;dbname=travel', $db_user, $db_pass);
    $stmt = $pdo->prepare("INSERT INTO requests (name, email, type, destination, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $trip_type, $destination]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}
```