<?php

namespace Controllers;

use Models\OrderData;

class ShopifyController
{
    public function __construct()
    {
        
    }

    public function handleOrderCreation($orderData)
    {
        $order = new OrderData($orderData);

        // Llamar al método del controlador de Cloudprinter para crear la orden
        $cloudprinterController = new CloudprinterController();
        $cloudprinterController->createOrder($order);
    }

    // Método para manejar la actualización de órdenes desde el webhook
    public function handleOrderUpdate($orderData)
    {
        // logica pendiente para la actualización de la orden
    }
}
