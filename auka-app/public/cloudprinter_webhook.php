<?php
require_once __DIR__ . '/../src/Controllers/CloudprinterController.php';
require_once __DIR__ . '/../src/Config/cloudprinter.php';

$config = require __DIR__ . '/../config/cloudprinter.php';
$apiKey = $config['api_key'];
$webhookApiKey = $config['webhook_api_key'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);


if ($data['apikey'] !== $webhookApiKey) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid API Key']);
    exit;
}


if ($data) {
    $controller = new Controllers\CloudprinterController();
    $response = $controller->handleWebhook($data);

    http_response_code(200);
    echo json_encode(['status' => 'Webhook processed']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
}