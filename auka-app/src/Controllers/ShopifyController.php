<?php

namespace Controllers;

use Models\ShopifyOrderData;
use Models\CloudprinterOrderData;
use Shopify\Clients\Rest;

class ShopifyController
{
    private $client;
    private $cloudprinterController;

    public function __construct($cloudprinterController = null)
    {
        $config = require __DIR__ . '/../Config/shopify.php';
        $this->client = new Rest($config['shop_domain'], $config['access_token']);
        
        if ($cloudprinterController !== null) {
            $this->cloudprinterController = $cloudprinterController;
        }
    }
    
    public function handleOrderCreation($orderData)
    {
        $shopifyOrder = new ShopifyOrderData($orderData);

        try {
            $response = $this->client->post('orders.json', [
                'order' => [
                    'line_items' => $shopifyOrder->line_items,
                    'email' => $shopifyOrder->email,
                    'shipping_address' => $shopifyOrder->shipping_address,
                    'total_price' => $shopifyOrder->total_price,
                ]
            ]);

            // Mapeo de datos para Cloudprinter
            $cloudprinterOrderData = new CloudprinterOrderData($shopifyOrder);

            $cloudprinterResponse = $this->cloudprinterController->createOrder($cloudprinterOrderData);

            return json_encode([
                'status' => 'Order created successfully in Shopify and sent to Cloudprinter',
                'shopify_response' => $response->getDecodedBody(),
                'cloudprinter_response' => $cloudprinterResponse
            ]);

        } catch (\Exception $e) {
            return json_encode(['error' => 'Error creating order: ' . $e->getMessage()]);
        }
    }

    public function updateFulfillmentTracking($fulfillmentId, $trackingData)
    {
        try {
            $response = $this->client->post("fulfillments/{$fulfillmentId}/update_tracking.json", [
                'fulfillment' => $trackingData
            ]);

            return json_encode([
                'status' => 'Tracking updated successfully',
                'response' => $response->getDecodedBody()
            ]);
        } catch (\Exception $e) {
            return json_encode(['error' => 'Error updating tracking: ' . $e->getMessage()]);
        }
    }


}
