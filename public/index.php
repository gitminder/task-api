<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Serg\TaskApi\Storage;
use Serg\TaskApi\Task;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$error = false;
$found = false;
$errorMessage = '';

if ($method === 'GET') {
    if ($path === '/tasks') {
        $found = true;
        $storage = new Storage();
        echo json_encode($storage->getAll());
    }
} elseif ($method === 'POST') {
    if ($path === '/tasks') {
        $found = true;
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = true;
            $errorMessage = json_last_error_msg();
        } else {

            if (!Task::parseTaskFormat($data)){
                $error = true;
                $errorMessage = 'Invalid task format';
            } else {
                $storage = new Storage();
                $storage->add($data);
                $storage->save();
            }
        }
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
} elseif ($error) {
    echo json_encode(['error' => $errorMessage]);
}
