<?php
require_once 'forms_helper.php';

// Загружаем настройки из .env
load_env_file(__DIR__ . '/.env');
$settings = get_forms_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $bvs_number = trim($_POST['bvs_number'] ?? '');
    $trip_period = trim($_POST['trip_period'] ?? '');
    $consent = isset($_POST['privacy_consent']) ? 'agree' : '';
    
    // Валидация
    if (empty($name) || empty($consent)) {
        $error = "Обязательные поля: Имя, Согласие на обработку данных.";
    } elseif (empty($email) && empty($telegram)) {
        $error = "Укажите email или Telegram ник (одно из двух обязательно).";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email адрес.";
    } elseif ($consent !== 'agree') {
        $error = "Для отправки заявки необходимо согласие на обработку персональных данных.";
    } else {
        // Подготавливаем данные заявки
        $form_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'telegram' => $telegram,
            'bvs_number' => $bvs_number,
            'trip_period' => $trip_period,
            'consent' => $consent
        ];
        
        $success_messages = [];
        $error_messages = [];
        
        // 1. Отправка email (если включено)
        if ($settings['send_email']) {
            $to = "test@yourdomain.com"; // ЗАМЕНИТЕ на ваш тестовый email!
            $email_subject = "Новая заявка на поездку от " . $name;
            $email_body = "Новая заявка на планирование поездки:\n\n";
            $email_body .= "Имя: " . $name . "\n";
            $email_body .= "Email: " . $email . "\n";
            $email_body .= "Телефон: " . ($phone ?: 'не указан') . "\n";
            $email_body .= "Telegram: " . ($telegram ?: 'не указан') . "\n";
            $email_body .= "Учётный номер БВС/Вариант поездки: " . ($bvs_number ?: 'не указано') . "\n";
            $email_body .= "Период поездки: " . ($trip_period ?: 'не выбран') . "\n";
            $email_body .= "Согласие на обработку данных: Да\n\n";
            $email_body .= "---\n";
            $email_body .= "Отправлено с: " . $_SERVER['HTTP_HOST'] . "\n";
            $email_body .= "Дата: " . date('Y-m-d H:i:s') . "\n";
            $email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            
            $headers = "From: noreply@sleeptrip.ru\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            if (mail($to, $email_subject, $email_body, $headers)) {
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