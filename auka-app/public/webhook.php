<?php


use Shopify\Webhooks\Registry;

try {
    $response = Registry::process($_SERVER, file_get_contents('php://input'));

    if ($response->isSuccess()) {
        http_response_code(200);
        echo json_encode(['status' => 'Webhook processed successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $response->getErrorMessage()]);
    }
} catch (\Exception $error) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid webhook request: ' . $error->getMessage()]);
}
