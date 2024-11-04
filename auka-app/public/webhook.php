<?php

use Shopify\Webhooks\Registry;
use Controllers\ShopifyController;

    try 
    {
        // Obtén el cuerpo de la solicitud
        $body = file_get_contents('php://input');
        $hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
        $topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'];

        // Clave secreta de la API de Shopify
        $secret = 'UpjYFvSqb7JlhILNX8sJkUDh7kxnGmUaNnlW2vnRABs=';

        // Calcula la firma HMAC
        $calculatedHmac = base64_encode(hash_hmac('sha256', $body, $secret, true));

        // Verifica que las firmas coincidan

        // if (!hash_equals($hmacHeader, $calculatedHmac)) {
        //     http_response_code(403);
        //     echo json_encode(['error' => 'Invalid HMAC signature']);
        //     exit;
        // }

        $response = true; // Registry::process($_SERVER, $body);

        if ($response) //->isSuccess()
        {
            $shopifyController = new ShopifyController();

            switch ($topic) 
            {
                case 'orders/create':
                    echo json_encode(['status' => 'OrderCreate']);
                    $shopifyController->handleOrderCreation(json_decode($body, true));
                break;
                
                case 'fulfillments/update':
                    echo json_encode(['status' => 'FullfillmentUpdate']);
                    $fulfillmentData = json_decode($body, true);
                    $shopifyController->updateFulfillmentTracking($fulfillmentData['fulfillment']['id'], [
                        'tracking_number' => $fulfillmentData['fulfillment']['tracking_number'],
                        'tracking_urls' => $fulfillmentData['fulfillment']['tracking_urls'],
                        'notify_customer' => true
                    ]);
                break;
                
                default:
                    throw new \Exception("Unhandled webhook topic: $topic");
                break;
            }
            http_response_code(200);
            echo json_encode(['status' => 'Webhook processed successfully']);
        }
    } 
    catch (\Exception $error) 
    {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid webhook request: ' . $error->getMessage()]);
    }
?>