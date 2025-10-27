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
        'telegram_chat_id' => getenv('TELEGRAM_ADMIN_CHAT_ID'),
        'encryption_key' => getenv('FORMS_ENCRYPTION_KEY') ?: 'default_key_change_me'
    ];
}

/**
 * Простое шифрование данных для безопасного хранения
 */
function encrypt_data($data, $key) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($json, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Расшифровка данных
 */
function decrypt_data($encrypted_data, $key) {
    $data = base64_decode($encrypted_data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
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
    
    if (empty($settings['telegram_bot_token']) || empty($settings['telegram_chat_id'])) {
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
    
    // Отправляем сообщение
    $url = "https://api.telegram.org/bot" . $settings['telegram_bot_token'] . "/sendMessage";
    $data = [
        'chat_id' => $settings['telegram_chat_id'],
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
            return true;
        } else {
            error_log("Ошибка отправки в Telegram: " . $result);
            return false;
        }
    } catch (Exception $e) {
        error_log("Исключение при отправке в Telegram: " . $e->getMessage());
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