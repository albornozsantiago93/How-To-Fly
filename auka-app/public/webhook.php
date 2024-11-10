<?php

use Shopify\Webhooks\Registry;
use Controllers\ShopifyController;

    try 
    {
        //Obtén el cuerpo de la solicitud
        $body = file_get_contents('php://input');

                    // $config = require __DIR__ . '/../src/Config/shopify.php';
                    // $secret = $config['webhook_code'];

        
                    // $headers = [
                    //     'X-Shopify-Hmac-Sha256' => $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '',
                    //     'X-Shopify-Topic' => $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '',
                    //     'X-Shopify-Shop-Domain' => $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '',
                    // ];
                
                    // $calculatedHmac = base64_encode(hash_hmac('sha256', $body, $secret, true));
                
                    // if (!hash_equals($headers['X-Shopify-Hmac-Sha256'], $calculatedHmac)) {
                    //     throw new \Exception("Invalid HMAC signature");
                    // }

        //$response = Registry::process($_SERVER, $body);
        $response =true;

        if ($response)//->isSuccess() 
        //if ($response->isSuccess())
        {
            $topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'];
            $shopifyController = new ShopifyController();

            switch ($topic) 
            {
                case 'orders/create':
                    echo json_encode(['status' => 'OrderCreate']);
                    $shopifyController->handleOrderCreation(json_decode($body, true));
                break;


                case 'orders/updated':
                    echo json_encode(['status' => 'OrderUpdated']);
                    //$shopifyController->handleOrderUpdate($webhookData);
                break;


                case 'orders/fulfilled':
                    echo json_encode(['status' => 'OrderFulfilled']);
                    //$shopifyController->handleOrderFulfillment($webhookData);
                break;

                
                case 'fulfillments/update':
                    echo json_encode(['status' => 'FullfillmentUpdate']);
                    $fulfillmentData = json_decode($body, true);
                    $shopifyController->updateFulfillmentTracking($fulfillmentData['fulfillment']['id'] ?? null, [
                        'tracking_number' => $fulfillmentData['fulfillment']['tracking_number'] ?? null,
                        'tracking_urls' => $fulfillmentData['fulfillment']['tracking_urls'] ?? null,
                        'notify_customer' => true
                    ]);
                break;
                
                
                default:
                    throw new \Exception("Unhandled webhook topic: $topic");
                break;
            }
            http_response_code(200);
        }
    } 
    catch (\Exception $error) 
    {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid webhook request: ' . $error->getMessage() ]);
        //echo json_encode(['error' => $headers]);
    }
?>