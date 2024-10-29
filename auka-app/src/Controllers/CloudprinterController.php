<?php

namespace Controllers;

use Models\OrderData;

class CloudprinterController
{
    private $cloudprinterConfig;

    public function __construct()
    {
        $this->cloudprinterConfig = require __DIR__ . '/../Config/cloudprinter.php';
    }

    public function createOrder(OrderData $order)
    {
        $url = "{$this->cloudprinterConfig['api_url']}/orders";
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->cloudprinterConfig['api_key'],
        ];

        $data = json_encode($order);

        // Inicializar cURL para la solicitud
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
