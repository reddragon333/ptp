<?php
require_once 'forms_helper.php';

// Загружаем настройки из .env
load_env_file(__DIR__ . '/.env');
$settings = get_forms_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация
    if (empty($name) || empty($subject) || empty($message)) {
        $error = "Имя, тема и сообщение обязательны для заполнения.";
    } elseif (empty($email) && empty($telegram)) {
        $error = "Укажите email или Telegram ник (одно из двух обязательно).";
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