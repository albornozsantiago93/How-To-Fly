<?php
namespace Controllers;

use Models\CloudprinterOrderData;
use GuzzleHttp\Client;

class CloudprinterController
{
    private $client;
    private $apiKey;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/cloudprinter.php';
        $this->client = new Client(['base_uri' => 'https://api.cloudprinter.com/cloudcore/1.0/']);
        $this->apiKey = $config['api_key'];

    }
    
    public function handleWebhook(array $data)
    {
        $eventType = $data['type'] ?? '';

        switch ($eventType) {
            case 'ItemProduce':
                $this->handleOrderShipped($data);
                break;

            case 'ItemShipped':
                $this->handleOrderShipped($data);
                break;

                
            default:
                // Log event no reconocido
                $this->handleOrderFailed($data);
                break;
        }

        return ['status' => 'Event processed'];
    }

    
    
    private function createOrder(CloudprinterOrderData $orderData)
    {
        $response = $this->client->post('orders/add', [
            'json' => [
                'apikey' => $this->apiKey,
                'reference' => $orderData->reference,
                'email' => $orderData->email,
                'addresses' => $orderData->addresses,
                'items' => $orderData->items
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
    

    private function handleOrderStatusUpdate(array $data)
    {
        // Lógica para procesar la actualización de estado de un pedido
        // Puedes acceder a detalles específicos de $data y procesarlos
        // Ejemplo: actualizar base de datos o enviar notificaciones
    }

    private function handleOrderShipped(array $data)
    {
        // Lógica para manejar cuando un pedido ha sido enviado
        // Procesa detalles de $data como tracking y actualiza tu sistema
    }

    private function handleOrderFailed(array $data)
    {
        // Lógica para manejar fallos en pedidos
        // Registra el error y toma las acciones correspondientes
    }


}
