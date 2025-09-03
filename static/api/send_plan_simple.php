<?php
/**
 * Упрощенная отправка заявки на поездку через существующую инфраструктуру
 * Использует forms_helper.php и .env настройки
 */

// Отладочная информация
error_log("send_plan_simple.php запущен");

// Проверяем текущую директорию и файлы
$current_dir = __DIR__;
$available_files = scandir($current_dir);

error_log("Текущая директория: " . $current_dir);
error_log("Файлы в директории: " . implode(', ', $available_files));

// Проверяем существование файлов (в той же папке /api/)
$helper_path = __DIR__ . '/forms_helper.php';
$env_path = __DIR__ . '/.env';

if (!file_exists($helper_path)) {
    error_log("ОШИБКА: forms_helper.php не найден: " . $helper_path);
    http_response_code(500);
    echo json_encode([
        'error' => 'forms_helper.php не найден',
        'path' => $helper_path,
        'current_dir' => $current_dir,
        'files' => $available_files
    ]);
    exit;
}

if (!file_exists($env_path)) {
    error_log("ОШИБКА: .env не найден: " . $env_path);
    http_response_code(500);
    echo json_encode([
        'error' => '.env файл не найден',
        'path' => $env_path,
        'current_dir' => $current_dir,
        'files' => $available_files
    ]);
    exit;
}

require_once $helper_path;

// Загружаем настройки из .env
load_env_file($env_path);
$settings = get_forms_settings();

error_log("Настройки загружены: " . json_encode($settings));

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

// Проверяем настройки Telegram
if (!$settings['send_telegram']) {
    http_response_code(500);
    echo json_encode(['error' => 'Отправка в Telegram отключена']);
    exit;
}

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

// Отправляем в Telegram через готовую функцию
$telegram_result = send_telegram_message($message, $settings);

if (!$telegram_result['success']) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка отправки в Telegram: ' . $telegram_result['error']]);
    exit;
}

// Успех!
echo json_encode([
    'success' => true,
    'message' => 'Заявка успешно отправлена!'
]);
?>