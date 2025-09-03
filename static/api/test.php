<?php
/**
 * Простейший тест PHP на сервере
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'status' => 'PHP работает!',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'current_dir' => __DIR__,
    'files' => [],
    'post_data' => $_POST
];

// Проверяем файлы в директории
if (is_dir(__DIR__)) {
    $files = scandir(__DIR__);
    $response['files'] = array_diff($files, ['.', '..']);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>