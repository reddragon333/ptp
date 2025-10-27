<?php
// Проверка расширений PHP
if (!extension_loaded('openssl')) {
    die('OpenSSL extension required for PDF encryption');
}

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
        // Функция создания зашифрованного PDF
        function createEncryptedPDF($data, $password = null) {
            // Если пароль не указан, генерируем случайный
            if (!$password) {
                $password = bin2hex(random_bytes(8));
            }
            
            // Создаем простой PDF контент
            $pdf_content = "%PDF-1.4\n";
            $pdf_content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
            $pdf_content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
            $pdf_content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
            $pdf_content .= "4 0 obj\n<< /Length " . strlen($data) . " >>\nstream\n";
            $pdf_content .= "BT /F1 12 Tf 72 720 Td (" . $data . ") Tj ET\n";
            $pdf_content .= "endstream\nendobj\n";
            $pdf_content .= "xref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \n0000000125 00000 n \n0000000202 00000 n \n";
            $pdf_content .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n" . strlen($pdf_content) . "\n%%EOF";
            
            // Шифруем содержимое
            $encrypted = openssl_encrypt($pdf_content, 'AES-256-CBC', $password, 0, substr(hash('sha256', $password), 0, 16));
            
            return ['content' => $encrypted, 'password' => $password];
        }
        
        // Подготовка данных для PDF
        $pdf_data = "Заявка на планирование поездки\n\n";
        $pdf_data .= "Имя: " . $name . "\n";
        $pdf_data .= "Email: " . $email . "\n";
        $pdf_data .= "Телефон: " . ($phone ?: 'не указан') . "\n";
        $pdf_data .= "Учётный номер БВС/Вариант поездки: " . ($bvs_number ?: 'не указано') . "\n";
        $pdf_data .= "Период поездки: " . ($trip_period ?: 'не выбран') . "\n";
        $pdf_data .= "Дата отправки: " . date('Y-m-d H:i:s') . "\n";
        
        // Создаем зашифрованный PDF
        $encrypted_pdf = createEncryptedPDF($pdf_data);
        
        // Подготовка email
        $to = "test@yourdomain.com"; // ЗАМЕНИТЕ на ваш тестовый email!
        $email_subject = "Новая заявка на поездку от " . $name;
        $email_body = "Новая заявка на планирование поездки:\n\n";
        $email_body .= "Имя: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Телефон: " . ($phone ?: 'не указан') . "\n";
        $email_body .= "Учётный номер БВС/Вариант поездки: " . ($bvs_number ?: 'не указано') . "\n";
        $email_body .= "Период поездки: " . ($trip_period ?: 'не выбран') . "\n";
        $email_body .= "Возраст 18+: " . ($age_confirm === '18+' ? 'Подтверждено' : 'Не подтверждено') . "\n";
        $email_body .= "Согласие на обработку данных: " . ($consent === 'agree' ? 'Да' : 'Нет') . "\n\n";
        $email_body .= "К письму приложен зашифрованный PDF файл с данными заявки.\n";
        $email_body .= "Пароль для расшифровки: " . $encrypted_pdf['password'] . "\n\n";
        $email_body .= "---\n";
        $email_body .= "Отправлено с: " . $_SERVER['HTTP_HOST'] . "\n";
        $email_body .= "Дата: " . date('Y-m-d H:i:s') . "\n";
        $email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        
        // Создаем временный файл для вложения
        $temp_file = tempnam(sys_get_temp_dir(), 'form_data_');
        file_put_contents($temp_file, base64_decode($encrypted_pdf['content']));
        
        // Подготовка MIME сообщения с вложением
        $boundary = "----=" . md5(uniqid());
        $headers = "From: noreply@yourdomain.com\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $email_body . "\r\n\r\n";
        
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: application/pdf; name=\"form_data_encrypted.pdf\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"form_data_encrypted.pdf\"\r\n\r\n";
        $message .= chunk_split($encrypted_pdf['content']) . "\r\n";
        $message .= "--$boundary--\r\n";
        
        // Отправка email
        if (mail($to, $email_subject, $message, $headers)) {
            $success = "Заявка отправлена! Зашифрованный PDF приложен к письму. Мы свяжемся с вами в ближайшее время.";
            // Очистить временный файл
            unlink($temp_file);
            // Очистить форму
            $name = $email = $phone = $bvs_number = $trip_period = $consent = $age_confirm = '';
        } else {
            $error = "Ошибка отправки. Попробуйте еще раз или свяжитесь через Telegram.";
            unlink($temp_file);
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