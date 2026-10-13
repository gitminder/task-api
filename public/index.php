<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Serg\TaskApi\Storage;
use Serg\TaskApi\Task;
use Serg\TaskApi\TaskService;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$error = false;
$found = false;
$errorMessage = '';

$taskService = new TaskService();
if ($method === 'GET') {
    if ($path === '/tasks') {
        $found = true;
        echo $taskService->getTasks();
    }
} elseif ($method === 'POST') {
    if ($path === '/tasks') {
        $found = true;
        try {
            $input = file_get_contents('php://input');
            $task = $taskService->addTask($input);
        } catch (\Exception $e) {
            $error = true;
            $errorMessage = $e->getMessage();
        }
    }
} elseif ($method === 'DELETE') {
    if (preg_match('#^/tasks/([1-9][0-9]*)$#', $path, $matches)) {
        $id = filter_var($matches[1], FILTER_VALIDATE_INT);
        if ($id !== false && $taskService->deleteTask($id)) {
            $found = true;
            http_response_code(204);
        }
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
} elseif ($error) {
    echo json_encode(['error' => $errorMessage]);
}
