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
    $phone = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $bvs_number = trim($_POST['bvs_number'] ?? '');
    $trip_period = trim($_POST['trip_period'] ?? '');
    $consent = isset($_POST['privacy_consent']) ? 'agree' : '';
    $bvs_file = $_FILES['bvs_file'] ?? null;

    // Серверная валидация обязательных полей
    if (empty($name)) {
        $error = "Обязательное поле: Имя и фамилия.";
    } elseif (empty($trip_period)) {
        $error = "Обязательное поле: Поездка.";
    } elseif (empty($bvs_number)) {
        $error = "Обязательное поле: Номер дрона (БВС).";
    } elseif (empty($email) && empty($telegram)) {
        $error = "Укажите email или Telegram ник (одно из двух обязательно).";
    } elseif (empty($phone)) {
        $error = "Обязательное поле: Телефон.";
    } elseif (empty($consent)) {
        $error = "Для отправки заявки необходимо согласие на обработку персональных данных.";
    } elseif (!empty($email)) {
        $email_validation = validate_email_extended($email);
        if (!$email_validation['valid']) {
            $error = $email_validation['error'];
        }
    }

    // Проверка согласия (дополнительная страховка)
    if (empty($error) && $consent !== 'agree') {
        $error = "Для отправки заявки необходимо согласие на обработку персональных данных.";
    }

    // Проверка загруженного файла
    if (empty($error) && !empty($bvs_file) && $bvs_file['error'] !== UPLOAD_ERR_NO_FILE) {
        // Проверка загруженного файла
        if ($bvs_file['error'] !== UPLOAD_ERR_OK) {
            $error = "Ошибка при загрузке файла. Попробуйте еще раз.";
        } elseif ($bvs_file['size'] > 5242880) { // 5MB
            $error = "Размер файла не должен превышать 5 МБ.";
        } elseif (mime_content_type($bvs_file['tmp_name']) !== 'application/pdf') {
            $error = "Допустим только формат PDF.";
        }
    }

    if (!isset($error)) {
        // Сохраняем загруженный файл во временную папку
        $uploaded_file_path = null;
        if ($bvs_file && $bvs_file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = sys_get_temp_dir() . '/ptp_forms_uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0700, true);
            }
            $uploaded_file_path = $upload_dir . uniqid('bvs_') . '_' . basename($bvs_file['name']);
            move_uploaded_file($bvs_file['tmp_name'], $uploaded_file_path);
        }

        // Подготавливаем данные заявки
        $form_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'telegram' => $telegram,
            'bvs_number' => $bvs_number,
            'trip_period' => $trip_period,
            'consent' => $consent,
            'bvs_file' => $bvs_file ? $bvs_file['name'] : '',
            'bvs_file_path' => $uploaded_file_path
        ];
        
        $success_messages = [];
        $error_messages = [];
        
        // 1. Отправка email (если включено)
        if ($settings['send_email']) {
            $email_sent = send_email_notification($form_data, 'plan');
            if ($email_sent) {
                $success_messages[] = "Email отправлен";
            } else {
                $error_messages[] = "Ошибка отправки email";
            }
        }
        
        // 2. Сохранение в JSON (если включено)
        if ($settings['save_json']) {
            $json_saved = save_application_to_json($form_data, 'plan');
            if ($json_saved) {
                $success_messages[] = "Заявка сохранена";
            } else {
                $error_messages[] = "Ошибка сохранения заявки";
            }
        }
        
        // 3. Уведомление в Telegram (если включено)
        if ($settings['send_telegram'] && $settings['notifications']) {
            $telegram_sent = send_telegram_notification($form_data, 'plan');
            if ($telegram_sent) {
                $success_messages[] = "Уведомление отправлено";
            } else {
                $error_messages[] = "Ошибка уведомления в Telegram";
            }
        }
        
        // Формируем итоговое сообщение
        if (!empty($success_messages)) {
            $success = "Заявка обработана! Мы свяжемся с вами в ближайшее время.";
            // Очистить форму
            $name = $email = $phone = $telegram = $bvs_number = $trip_period = $consent = '';
        }
        
        if (!empty($error_messages) && empty($success_messages)) {
            $error = "Ошибка обработки заявки. Попробуйте еще раз или свяжитесь через Telegram.";
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