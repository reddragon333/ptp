<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bvs_number = trim($_POST['bvs_number'] ?? '');
    $trip_period = trim($_POST['trip_period'] ?? '');
    $consent = isset($_POST['consent']) ? trim($_POST['consent']) : '';
    $age_confirm = isset($_POST['age_confirm']) ? trim($_POST['age_confirm']) : '';
    
    // Валидация
    if (empty($name) || empty($email) || empty($consent) || empty($age_confirm)) {
        $error = "Обязательные поля: Имя, Email, Согласие на обработку данных, Подтверждение возраста.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email адрес.";
    } elseif ($consent !== 'agree') {
        $error = "Для отправки заявки необходимо согласие на обработку персональных данных.";
    } elseif ($age_confirm !== '18+') {
        $error = "Для отправки заявки необходимо подтверждение возраста 18+.";
    } else {
        // Подготовка email
        $to = "info@sleeptrip.ru"; // ЗАМЕНИТЕ на ваш email!
        $email_subject = "Новая заявка на поездку от " . $name;
        $email_body = "Новая заявка на планирование поездки:\n\n";
        $email_body .= "Имя: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Телефон: " . ($phone ?: 'не указан') . "\n";
        $email_body .= "Учётный номер БВС/Вариант поездки: " . ($bvs_number ?: 'не указано') . "\n";
        $email_body .= "Период поездки: " . ($trip_period ?: 'не выбран') . "\n";
        $email_body .= "Возраст 18+: " . ($age_confirm === '18+' ? 'Подтверждено' : 'Не подтверждено') . "\n";
        $email_body .= "Согласие на обработку данных: " . ($consent === 'agree' ? 'Да' : 'Нет') . "\n\n";
        $email_body .= "---\n";
        $email_body .= "Отправлено с: " . $_SERVER['HTTP_HOST'] . "\n";
        $email_body .= "Дата: " . date('Y-m-d H:i:s') . "\n";
        $email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        
        $headers = "From: noreply@sleeptrip.ru\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Отправка email
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success = "Заявка отправлена! Мы свяжемся с вами в ближайшее время.";
            // Очистить форму
            $name = $email = $phone = $bvs_number = $trip_period = $consent = $age_confirm = '';
        } else {
            $error = "Ошибка отправки. Попробуйте еще раз или свяжитесь через Telegram.";
        }
    }
}

// Перенаправление обратно на страницу с результатом
if (isset($success)) {
    header("Location: /plan/?success=" . urlencode($success));
    exit;
} elseif (isset($error)) {
    header("Location: /plan/?error=" . urlencode($error));
    exit;
}
?>