<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Все поля обязательны для заполнения.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email адрес.";
    } else {
        // Подготовка email
        $to = "info@sleeptrip.ru"; // ЗАМЕНИТЕ на ваш email!
        $email_subject = "Вопрос с сайта: " . $subject;
        $email_body = "Новый вопрос с сайта:\n\n";
        $email_body .= "Имя: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Тема: " . $subject . "\n\n";
        $email_body .= "Сообщение:\n" . $message . "\n\n";
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
            $success = "Сообщение отправлено! Мы ответим вам в ближайшее время.";
            // Очистить форму
            $name = $email = $subject = $message = '';
        } else {
            $error = "Ошибка отправки. Попробуйте еще раз или свяжитесь через Telegram.";
        }
    }
}

// Перенаправление обратно на страницу с результатом
if (isset($success)) {
    header("Location: /ask/?success=" . urlencode($success));
    exit;
} elseif (isset($error)) {
    header("Location: /ask/?error=" . urlencode($error));
    exit;
}
?>