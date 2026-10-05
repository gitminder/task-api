<?php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && $path === '/tasks') {
    echo json_encode([]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not Found']);
