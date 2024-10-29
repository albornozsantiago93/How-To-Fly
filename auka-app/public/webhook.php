<?php
require_once __DIR__ . '/../src/Controllers/ShopifyController.php';

$shopifyController = new Controllers\ShopifyController();

// Lee los datos del webhook
$webhookData = file_get_contents('php://input');
$data = json_decode($webhookData, true);

// Lee el tipo de evento desde el encabezado
$eventType = $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '';

switch ($eventType) 
{
    case 'orders/create':
        $shopifyController->handleOrderCreation($data);
        break;

    case 'fulfillments/update':
        $shopifyController->handleOrderUpdate($data);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Evento no manejado']);
        exit();
}

http_response_code(200);
echo json_encode(['status' => 'Webhook procesado correctamente']);
?>
