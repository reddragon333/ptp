<?php
/**
 * Упрощенная отправка заявки на поездку прямо в Telegram
 * Без файлов, только текст
 */

// CORS заголовки для безопасности
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешен']);
    exit;
}

// Telegram настройки - ЗАМЕНИТЕ НА ВАШИ!
$telegram_bot_token = 'YOUR_BOT_TOKEN_HERE';
$telegram_chat_id = 'YOUR_CHAT_ID_HERE';

// Получаем данные из формы
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$telegram = trim($_POST['telegram'] ?? '');
$bvs_number = trim($_POST['bvs_number'] ?? '');
$trip_period = trim($_POST['trip_period'] ?? '');
$consent = isset($_POST['privacy_consent']) ? 'yes' : 'no';
$age_consent = isset($_POST['age_consent']) ? 'yes' : 'no';

// Валидация
$errors = [];

if (empty($name)) {
    $errors[] = 'Имя обязательно';
}

if (empty($email) && empty($telegram)) {
    $errors[] = 'Укажите email или Telegram ник';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email адрес';
}

if ($consent !== 'yes') {
    $errors[] = 'Необходимо согласие на обработку данных';
}

if ($age_consent !== 'yes') {
    $errors[] = 'Необходимо подтверждение совершеннолетия';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode('. ', $errors)]);
    exit;
}

// Формируем сообщение для Telegram
$message = "🎒 *Новая заявка на поездку*\n\n";
$message .= "👤 *Имя:* " . htmlspecialchars($name) . "\n";

if (!empty($email)) {
    $message .= "📧 *Email:* " . htmlspecialchars($email) . "\n";
}

if (!empty($phone)) {
    $message .= "📱 *Телефон:* " . htmlspecialchars($phone) . "\n";
}

if (!empty($telegram)) {
    $message .= "💬 *Telegram:* " . htmlspecialchars($telegram) . "\n";
}

if (!empty($trip_period)) {
    $message .= "🗓️ *Поездка:* " . htmlspecialchars($trip_period) . "\n";
}

if (!empty($bvs_number)) {
    $message .= "✈️ *БВС/Дополнительно:* " . htmlspecialchars($bvs_number) . "\n";
}

$message .= "\n⏰ *Время подачи:* " . date('Y-m-d H:i:s');

// Отправляем в Telegram
$telegram_api_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $telegram_api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'chat_id' => $telegram_chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($http_code !== 200 || !$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка отправки сообщения']);
    exit;
}

$telegram_response = json_decode($response, true);

if (!$telegram_response['ok']) {
    http_response_code(500);
    echo json_encode(['error' => 'Telegram API ошибка: ' . $telegram_response['description']]);
    exit;
}

// Успех!
echo json_encode([
    'success' => true,
    'message' => 'Заявка успешно отправлена!'
]);
?>