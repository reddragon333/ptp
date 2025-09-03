<?php
require_once 'forms_helper.php';

// Загружаем настройки из .env
load_env_file('.env');
$settings = get_forms_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $bvs_number = trim($_POST['bvs_number'] ?? '');
    $trip_period = trim($_POST['trip_period'] ?? '');
    $consent = isset($_POST['privacy_consent']) ? 'agree' : '';
    $age_consent = isset($_POST['age_consent']) ? 'agree' : '';

    // Валидация
    if (empty($name) || empty($consent) || empty($age_consent)) {
        $error = "Имя и согласия обязательны для заполнения.";
    } elseif (empty($email) && empty($telegram)) {
        $error = "Укажите email или Telegram ник (одно из двух обязательно).";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email адрес.";
    } else {
        // Формируем данные заявки
        $form_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'telegram' => $telegram,
            'bvs_number' => $bvs_number,
            'trip_period' => $trip_period,
            'consent' => $consent,
            'age_consent' => $age_consent
        ];
        
        $success_messages = [];
        $error_messages = [];
        
        // Отправка в Telegram (если включено)
        if ($settings['send_telegram']) {
            $telegram_message = "🎒 *Новая заявка на поездку*\n\n";
            $telegram_message .= "👤 *Имя:* " . $name . "\n";
            if (!empty($email)) $telegram_message .= "📧 *Email:* " . $email . "\n";
            if (!empty($phone)) $telegram_message .= "📱 *Телефон:* " . $phone . "\n";
            if (!empty($telegram)) $telegram_message .= "💬 *Telegram:* " . $telegram . "\n";
            if (!empty($trip_period)) $telegram_message .= "🗓️ *Поездка:* " . $trip_period . "\n";
            if (!empty($bvs_number)) $telegram_message .= "✈️ *БВС/Дополнительно:* " . $bvs_number . "\n";
            $telegram_message .= "\n⏰ *Время:* " . date('Y-m-d H:i:s');
            
            $telegram_result = send_telegram_message($telegram_message, $settings);
            if ($telegram_result['success']) {
                $success_messages[] = 'Заявка отправлена в Telegram!';
            } else {
                $error_messages[] = 'Ошибка отправки в Telegram: ' . $telegram_result['error'];
            }
        }
        
        // Результат
        if (!empty($success_messages)) {
            $success = implode(' ', $success_messages);
            header('Location: /plan/?success=' . urlencode($success));
            exit;
        } else {
            $error = !empty($error_messages) ? implode(' ', $error_messages) : 'Произошла ошибка при отправке заявки.';
        }
    }
}

// Показ ошибки
if (isset($error)) {
    header('Location: /plan/?error=' . urlencode($error));
    exit;
}
?>