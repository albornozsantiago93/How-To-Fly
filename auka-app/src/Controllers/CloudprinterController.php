<?php
namespace Controllers;

use Models\CloudprinterOrderData;
use GuzzleHttp\Client;
use CloudPrinter\CloudCore\Client\CloudCoreClient;
use CloudPrinter\CloudCore\Model\Order;
use CloudPrinter\CloudCore\Model\Address;
use CloudPrinter\CloudCore\Model\OrderItem;



class CloudprinterController
{
    private $client;
    private $apiKey;
    private $cloudCoreClient;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/cloudprinter.php';
        $this->client = new Client(['base_uri' => $config['base_url_sandbox'] . '/cloudcore/1.0/']);
        $this->apiKey = $config['api_key'];
        $this->cloudCoreClient = new CloudCoreClient($this->apiKey); // , 'sandbox' o 'live' para producción
    }

    public function createOrder(CloudprinterOrderData $orderData)
    {
        $order = new Order();
        $order->setReference($orderData->reference)
            ->setEmail($orderData->email);

        // Agregar direcciones
        foreach ($orderData->addresses as $addressData) {
            $address = new Address();
            $address->setFirstname($addressData['firstname'])
                    ->setLastname($addressData['lastname'])
                    ->setStreet($addressData['street1'])
                    ->setCity($addressData['city'])
                    ->setCountry($addressData['country']);
            $order->addAddress($address);
        }

        // Agregar ítems
        foreach ($orderData->items as $itemData) {
            $item = new OrderItem();
            $item->setReference($itemData['reference'])
                ->setProduct($itemData['product'])
                ->setCount($itemData['count']);
            $order->addItem($item);
        }

        $response = $this->cloudCoreClient->order->create($order);

        if ($response) {
            return [
                'status' => 'success',
                'order_id' => $item->getReference()
            ];
        } else {
            return [
                'status' => 'failed',
                'error' => $response->getError()
            ];
        }
    }

    
    public function handleWebhook(array $data)
    {
        $eventType = $data['type'] ?? '';

        switch ($eventType) 
        {
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
