<?php

namespace Controllers;

use Models\ShopifyOrderData;
use Models\CloudprinterOrderData;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Auth\FileSessionStorage;

class ShopifyController
{
    private $client;
    private $cloudprinterController;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/shopify.php';
        $path = __DIR__ . '/../storage/sessions'; // Define el path correcto para el almacenamiento de sesiones
        $exportPath = __DIR__ . '/../output'; // Path para ver post de salida, ordear creation

        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }

        if (!is_dir($exportPath)) {
            if (!mkdir($exportPath, 0777, true) && !is_dir($exportPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $exportPath));
            }
        }

        // Inicializa el Contexto de Shopify si aún no está inicializado
        Context::initialize(
            $config['api_key'],               // API Key
            $config['api_secret'],            // Secret Key
            $config['scopes'],                // Scopes
            $config['host'],                  // Hostname
            new FileSessionStorage($path) 
        );
        
        $this->client = new Rest($config['shop_domain'], $config['access_token']);
        $this->cloudprinterController = new CloudprinterController();
    }
    
    

    //CREAR ORDEN
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
            
            // Exportar la respuesta a un archivo JSON para verificar
            $orderDetails = [
            'shopify_response' => $response->getDecodedBody(),
            'order_data' => $orderData
            ];

            file_put_contents(__DIR__ . '/../output/order_details.json', json_encode($orderDetails, JSON_PRETTY_PRINT));

            return json_encode([
                'status' => 'Order created successfully in Shopify and sent to Cloudprinter',
                'shopify_response' => $response->getDecodedBody(),
                'cloudprinter_response' => $cloudprinterResponse
            ]);



        } 
        catch (\Exception $e) 
        {
            return json_encode(['error' => 'Error creating order: ' . $e->getMessage()]);
        }
    }




    //UPDATE TRACKING
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

?>
