<?php
require_once 'forms_helper.php';

// Загружаем настройки из .env
load_env_file(__DIR__ . '/.env');
$settings = get_forms_settings();

// --- Rate limiting ---
function check_rate_limit(string $ip, int $max_requests = 3, int $window_seconds = 600): bool {
    $dir = '/tmp/form_ratelimit';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . md5($ip) . '.txt';
    $now = time();
    $timestamps = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $ts = (int)trim($line);
            if ($ts > 0 && ($now - $ts) < $window_seconds) {
                $timestamps[] = $ts;
            }
        }
    }
    if (count($timestamps) >= $max_requests) {
        return false; // превышен лимит
    }
    $timestamps[] = $now;
    file_put_contents($file, implode("\n", $timestamps) . "\n", LOCK_EX);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка rate limit
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $client_ip = trim(explode(',', $client_ip)[0]);
    if (!check_rate_limit($client_ip)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Слишком много запросов. Попробуйте через несколько минут.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Серверная валидация обязательных полей
    if (empty($name)) {
        $error = "Обязательное поле: Имя.";
    } elseif (empty($email) && empty($telegram)) {
        $error = "Укажите email или Telegram ник (одно из двух обязательно).";
    } elseif (empty($subject)) {
        $error = "Обязательное поле: Тема вопроса.";
    } elseif (empty($message)) {
        $error = "Обязательное поле: Сообщение.";
    } elseif (!empty($email)) {
        $email_validation = validate_email_extended($email);
        if (!$email_validation['valid']) {
            $error = $email_validation['error'];
        }
    }

    if (empty($error)) {
        // Подготавливаем данные заявки
        $form_data = [
            'name' => $name,
            'email' => $email,
            'telegram' => $telegram,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message
        ];
        
        $success_messages = [];
        $error_messages = [];
        
        // 1. Отправка email (если включено)
        if ($settings['send_email']) {
            $email_sent = send_email_notification($form_data, 'ask');
            if ($email_sent) {
                $success_messages[] = "Email отправлен";
            } else {
                $error_messages[] = "Ошибка отправки email";
            }
        }
        
        // 2. Сохранение в JSON (если включено)
        if ($settings['save_json']) {
            $json_saved = save_application_to_json($form_data, 'ask');
            if ($json_saved) {
                $success_messages[] = "Вопрос сохранен";
            } else {
                $error_messages[] = "Ошибка сохранения вопроса";
            }
        }
        
        // 3. Уведомление в Telegram (если включено)
        if ($settings['send_telegram'] && $settings['notifications']) {
            $telegram_sent = send_telegram_notification($form_data, 'ask');
            if ($telegram_sent) {
                $success_messages[] = "Уведомление отправлено";
            } else {
                $error_messages[] = "Ошибка уведомления в Telegram";
            }
        }
        
        // Формируем итоговое сообщение
        if (!empty($success_messages)) {
            $success = "Сообщение отправлено! Мы ответим вам в ближайшее время.";
            // Очистить форму
            $name = $email = $telegram = $phone = $subject = $message = '';
        }
        
        if (!empty($error_messages) && empty($success_messages)) {
            $error = "Ошибка отправки. Попробуйте еще раз или свяжитесь через Telegram.";
        }
    }
}

// Возвращаем JSON для AJAX совместимости
if (isset($success)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $success
    ], JSON_UNESCAPED_UNICODE);
    exit;
} elseif (isset($error)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>