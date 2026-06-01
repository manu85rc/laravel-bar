<?php

use App\PrinterService;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/print' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (! $input || ! isset($input['device']) || ! isset($input['ticket'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid payload']);
        exit;
    }

    // Validate device path to prevent path traversal
    $device = $input['device'];
    if (! is_string($device) || preg_match('/\.\.\/|\.\.\\\\|\|/', $device) || strlen($device) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid device path']);
        exit;
    }

    ob_start();
    require_once __DIR__.'/../vendor/autoload.php';
    ob_end_clean();

    $service = new PrinterService;
    $result = $service->printTicket($device, $input['ticket']);

    if (! $result['success']) {
        http_response_code(500);
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

if ($uri === '/health' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Not found']);
