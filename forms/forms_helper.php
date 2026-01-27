<?php
/**
 * Вспомогательные функции для обработки форм
 * Поддержка настроек из .env файла
 */

/**
 * Загрузка переменных из .env файла
 */
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

        // Убираем inline комментарии (всё после #)
        if (strpos($line, '#') !== false) {
            $line = substr($line, 0, strpos($line, '#'));
        }

        // Проверяем что есть знак =
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);  // ВАЖНО: убирает пробелы до и после значения

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

/**
 * Получение настроек форм из .env
 */
function get_forms_settings() {
    return [
        'send_email' => getenv('FORMS_SEND_EMAIL') === 'true',
        'send_telegram' => getenv('FORMS_SEND_TELEGRAM') === 'true',
        'save_json' => getenv('FORMS_SAVE_JSON') === 'true',
        'notifications' => getenv('FORMS_NOTIFICATIONS') === 'true',
        'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN'),
        'telegram_chat_ids' => array_filter(array_map('trim', explode(',', getenv('TELEGRAM_ADMIN_CHAT_ID') ?: ''))),
        'encryption_key' => getenv('FORMS_ENCRYPTION_KEY') ?: 'default_key_change_me'
    ];
}

/**
 * Проверка email на заблокированные домены
 *
 * @param string $email Email адрес для проверки
 * @return bool true если домен заблокирован, false если разрешён
 */
function is_blocked_email_domain($email) {
    $blocked_domains = [
        '.ua',      // Украина
        '.fr',      // Франция (спам)
        '.cn',      // Китай
        '.com.cn',
        '.net.cn',
        '.org.cn',
        '.gov.cn',
        '.edu.cn',
        '.ac.cn'
    ];

    $email_parts = explode('@', strtolower($email));
    if (count($email_parts) !== 2) {
        return false;
    }

    $domain = $email_parts[1];

    foreach ($blocked_domains as $blocked) {
        if ($domain === ltrim($blocked, '.') ||
            substr($domain, -strlen($blocked)) === $blocked) {
            error_log("Заблокирован email с доменом: $domain (правило: $blocked) от IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            return true;
        }
    }

    return false;
}

/**
 * Расширенная валидация email
 *
 * @param string $email Email для валидации
 * @return array ['valid' => bool, 'error' => string]
 */
function validate_email_extended($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'error' => 'Некорректный формат email адреса.'
        ];
    }

    if (is_blocked_email_domain($email)) {
        return [
            'valid' => false,
            'error' => 'К сожалению, регистрация с этого домена временно недоступна.'
        ];
    }

    return ['valid' => true, 'error' => ''];
}

/**
 * Простое шифрование данных для безопасного хранения
 * Совместимо с Python Crypto.Cipher.AES + unpad
 */
function encrypt_data($data, $key) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $iv = random_bytes(16);

    // openssl_encrypt с параметром 0 сам добавляет PKCS7 padding и возвращает base64
    $encrypted_b64 = openssl_encrypt($json, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
    // Декодируем base64 чтобы получить бинарные данные
    $encrypted_raw = base64_decode($encrypted_b64);
    // Добавляем IV в начало и кодируем все вместе
    return base64_encode($iv . $encrypted_raw);
}

/**
 * Расшифровка данных
 * Совместимо с Python Crypto.Cipher.AES + unpad
 */
function decrypt_data($encrypted_data, $key) {
    $data = base64_decode($encrypted_data);
    $iv = substr($data, 0, 16);
    $encrypted_raw = substr($data, 16);
    // Кодируем обратно в base64 для openssl_decrypt
    $encrypted_b64 = base64_encode($encrypted_raw);
    // openssl_decrypt с параметром 0 сам убирает PKCS7 padding
    $decrypted = openssl_decrypt($encrypted_b64, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
    return json_decode($decrypted, true);
}

/**
 * Сохранение заявки в зашифрованный JSON файл
 */
function save_application_to_json($form_data, $form_type) {
    $settings = get_forms_settings();
    
    if (!$settings['save_json']) {
        return true; // Сохранение отключено
    }
    
    // Создаем безопасную директорию для хранения (вне веб-доступа)
    $secure_dir = '/var/secure/forms/';
    if (!is_dir($secure_dir)) {
        mkdir($secure_dir, 0700, true);
    }
    
    // Подготавливаем данные для сохранения
    $application = [
        'id' => uniqid('app_'),
        'type' => $form_type,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $form_data,
        'status' => 'new'
    ];
    
    try {
        // Шифруем данные
        $encrypted_data = encrypt_data($application, $settings['encryption_key']);
        
        // Сохраняем в файл с временной меткой
        $filename = $secure_dir . $form_type . '_' . date('Y-m-d') . '.json';
        
        // Загружаем существующие данные или создаем новый массив
        $existing_data = [];
        if (file_exists($filename)) {
            $file_content = file_get_contents($filename);
            if ($file_content) {
                $existing_data = json_decode($file_content, true) ?: [];
            }
        }
        
        // Добавляем новую заявку
        $existing_data[] = [
            'id' => $application['id'],
            'timestamp' => $application['timestamp'],
            'encrypted_data' => $encrypted_data
        ];
        
        // Сохраняем обновленный файл
        file_put_contents($filename, json_encode($existing_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        chmod($filename, 0600); // Только владелец может читать
        
        return $application['id'];
        
    } catch (Exception $e) {
        error_log("Ошибка сохранения заявки: " . $e->getMessage());
        return false;
    }
}

/**
 * Отправка уведомления в Telegram
 */
function send_telegram_notification($form_data, $form_type) {
    $settings = get_forms_settings();

    if (!$settings['send_telegram'] || !$settings['notifications']) {
        return true; // Отправка отключена
    }

    if (empty($settings['telegram_bot_token']) || empty($settings['telegram_chat_ids'])) {
        error_log("Telegram настройки не настроены");
        return false;
    }

    // Подготавливаем сообщение
    $message = "🔔 *Новая заявка*\n\n";
    $message .= "📝 *Тип:* " . ($form_type === 'plan' ? 'Заявка на поездку' : 'Вопрос') . "\n";
    $message .= "👤 *Имя:* " . $form_data['name'] . "\n";
    $message .= "📧 *Email:* " . $form_data['email'] . "\n";

    if ($form_type === 'plan') {
        $message .= "📱 *Телефон:* " . ($form_data['phone'] ?: 'не указан') . "\n";
        $message .= "✈️ *Поездка:* " . ($form_data['trip_period'] ?: 'не выбрана') . "\n";
        $message .= "🚁 *БВС/Направление:* " . ($form_data['bvs_number'] ?: 'не указано') . "\n";
    } else {
        if (!empty($form_data['telegram'])) {
            $message .= "📱 *Telegram:* " . $form_data['telegram'] . "\n";
        }
        if (!empty($form_data['phone'])) {
            $message .= "☎️ *Телефон:* " . $form_data['phone'] . "\n";
        }
        $message .= "💬 *Тема:* " . $form_data['subject'] . "\n";
        $message .= "📝 *Сообщение:* " . substr($form_data['message'], 0, 200) . "...\n";
    }

    $message .= "\n⏰ *Время:* " . date('Y-m-d H:i:s');

    // Отправляем сообщение всем администраторам
    $url = "https://api.telegram.org/bot" . $settings['telegram_bot_token'] . "/sendMessage";
    $all_sent = true;
    $sent_count = 0;

    foreach ($settings['telegram_chat_ids'] as $chat_id) {
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            ]
        ]);

        try {
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result, true);

            if ($response && $response['ok']) {
                $sent_count++;
            } else {
                error_log("Ошибка отправки в Telegram (chat_id: $chat_id): " . $result);
                $all_sent = false;
            }
        } catch (Exception $e) {
            error_log("Исключение при отправке в Telegram (chat_id: $chat_id): " . $e->getMessage());
            $all_sent = false;
        }
    }

    if ($sent_count > 0) {
        error_log("Уведомление отправлено $sent_count админам из " . count($settings['telegram_chat_ids']));
        return true;
    }

    return false;
}

/**
 * Отправка уведомления на Email через msmtp с поддержкой файлов
 */
function send_email_notification($form_data, $form_type) {
    $settings = get_forms_settings();

    if (!$settings['send_email']) {
        return true; // Отправка email отключена
    }

    // Получаем admin email из .env
    $admin_email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
    $from_email = getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_USERNAME') ?: 'noreply@yourdomain.com';
    $from_name = getenv('SMTP_FROM_NAME') ?: 'PTP Robot';

    // Формируем тему письма
    $subject = ($form_type === 'plan')
        ? '🎫 Новая заявка на поездку - ' . $form_data['name']
        : '❓ Новый вопрос - ' . ($form_data['subject'] ?? 'Без темы');

    // Формируем тело письма
    $message = "Новая заявка с сайта\n\n";
    $message .= "Тип: " . ($form_type === 'plan' ? 'Заявка на поездку' : 'Вопрос') . "\n";
    $message .= "Время: " . date('Y-m-d H:i:s') . "\n\n";

    $message .= "=== ДАННЫЕ ЗАЯВИТЕЛЯ ===\n";
    $message .= "Имя: " . $form_data['name'] . "\n";
    $message .= "Email: " . $form_data['email'] . "\n";

    if ($form_type === 'plan') {
        $message .= "Телефон: " . ($form_data['phone'] ?: 'не указан') . "\n";
        $message .= "Telegram: " . ($form_data['telegram'] ?: 'не указан') . "\n";
        $message .= "Поездка: " . ($form_data['trip_period'] ?: 'не выбрана') . "\n";
        $message .= "БВС/Направление: " . ($form_data['bvs_number'] ?: 'не указано') . "\n";
    } else {
        if (!empty($form_data['telegram'])) {
            $message .= "Telegram: " . $form_data['telegram'] . "\n";
        }
        if (!empty($form_data['phone'])) {
            $message .= "Телефон: " . $form_data['phone'] . "\n";
        }
        $message .= "Тема: " . $form_data['subject'] . "\n";
        $message .= "\n=== СООБЩЕНИЕ ===\n";
        $message .= $form_data['message'] . "\n";
    }

    // Проверяем наличие файла для отправки
    $attachment_file = $form_data['bvs_file_path'] ?? null;
    $has_attachment = $attachment_file && file_exists($attachment_file);

    // Создаем boundary для multipart письма если есть файл
    $boundary = "----=_Part_" . md5(time() . rand());

    if ($has_attachment) {
        // Заголовки письма с файлом (multipart)
        $headers = "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: " . $form_data['email'] . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Формируем multipart тело письма
        $multipart_message = "--$boundary\r\n";
        $multipart_message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $multipart_message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $multipart_message .= $message . "\r\n";

        // Добавляем файл
        $file_content = file_get_contents($attachment_file);
        $file_name = basename($attachment_file);
        // Извлекаем оригинальное имя файла (после bvs_)
        if (preg_match('/bvs_\w+_(.+)$/', $file_name, $matches)) {
            $file_name = $matches[1];
        }

        $multipart_message .= "--$boundary\r\n";
        $multipart_message .= "Content-Type: application/pdf; name=\"$file_name\"\r\n";
        $multipart_message .= "Content-Transfer-Encoding: base64\r\n";
        $multipart_message .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n\r\n";
        $multipart_message .= chunk_split(base64_encode($file_content)) . "\r\n";

        $multipart_message .= "--$boundary--\r\n";

        $email_message = $multipart_message;
    } else {
        // Заголовки письма без файла
        $headers = "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: " . $form_data['email'] . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $email_message = $message;
    }

    // Отправка письма
    try {
        error_log("=== Attempting mail() ===");
        error_log("To: $admin_email | From: $from_email | Subject: $subject | Has attachment: " . ($has_attachment ? 'YES' : 'NO'));
        $result = mail($admin_email, $subject, $email_message, $headers);
        error_log("mail() returned: " . ($result ? 'TRUE' : 'FALSE'));

        if ($result) {
            error_log("Email отправлен успешно на: $admin_email");

            // Удаляем временный файл после отправки
            if ($has_attachment && file_exists($attachment_file)) {
                unlink($attachment_file);
                error_log("Временный файл удален: $attachment_file");
            }

            return true;
        } else {
            error_log("Ошибка отправки email на: $admin_email");
            return false;
        }
    } catch (Exception $e) {
        error_log("Исключение при отправке email: " . $e->getMessage());
        return false;
    }
}

/**
 * Получение списка всех заявок (для Telegram бота)
 */
function get_all_applications() {
    $settings = get_forms_settings();
    $secure_dir = '/var/secure/forms/';
    
    if (!is_dir($secure_dir)) {
        return [];
    }
    
    $applications = [];
    $files = glob($secure_dir . '*.json');
    
    foreach ($files as $file) {
        $file_content = file_get_contents($file);
        if (!$file_content) continue;
        
        $file_data = json_decode($file_content, true);
        if (!$file_data) continue;
        
        foreach ($file_data as $item) {
            try {
                $decrypted = decrypt_data($item['encrypted_data'], $settings['encryption_key']);
                $applications[] = $decrypted;
            } catch (Exception $e) {
                error_log("Ошибка расшифровки заявки: " . $e->getMessage());
            }
        }
    }
    
    // Сортируем по времени (новые сначала)
    usort($applications, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $applications;
}

/**
 * Отметить заявку как рассмотренную
 */
function mark_application_as_reviewed($app_id) {
    $settings = get_forms_settings();
    $secure_dir = '/var/secure/forms/';
    $files = glob($secure_dir . '*.json');
    
    foreach ($files as $file) {
        $file_content = file_get_contents($file);
        if (!$file_content) continue;
        
        $file_data = json_decode($file_content, true);
        if (!$file_data) continue;
        
        $updated = false;
        foreach ($file_data as &$item) {
            try {
                $decrypted = decrypt_data($item['encrypted_data'], $settings['encryption_key']);
                if ($decrypted['id'] === $app_id) {
                    $decrypted['status'] = 'reviewed';
                    $item['encrypted_data'] = encrypt_data($decrypted, $settings['encryption_key']);
                    $updated = true;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        
        if ($updated) {
            file_put_contents($file, json_encode($file_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return true;
        }
    }
    
    return false;
}

/**
 * Очистка старых заявок (старше 30 дней)
 */
function cleanup_old_applications() {
    $secure_dir = '/var/secure/forms/';
    if (!is_dir($secure_dir)) return;
    
    $files = glob($secure_dir . '*.json');
    $cutoff_date = strtotime('-30 days');
    
    foreach ($files as $file) {
        // Извлекаем дату из имени файла
        if (preg_match('/(\d{4}-\d{2}-\d{2})\.json$/', $file, $matches)) {
            $file_date = strtotime($matches[1]);
            if ($file_date < $cutoff_date) {
                unlink($file);
                error_log("Удален старый файл заявок: " . basename($file));
            }
        }
    }
}

// Автоматическая очистка старых файлов (вызывается при каждом обращении к формам)
if (rand(1, 100) === 1) { // 1% вероятность
    cleanup_old_applications();
}
?>