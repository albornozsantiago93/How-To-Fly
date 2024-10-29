<?php

namespace Models;

class OrderData
{
    public $id;
    public $customerName;
    public $customerAddress;
    public $productTitle;
    public $quantity;

    public function __construct($orderData)
    {
        $this->id = $orderData['id'];
        $this->customerName = $orderData['customer']['first_name'] . ' ' . $orderData['customer']['last_name'];
        $this->customerAddress = $orderData['customer']['default_address'];
        $this->productTitle = $orderData['line_items'][0]['title'];
        $this->quantity = $orderData['line_items'][0]['quantity'];
    }

    // Método para preparar los datos para el payload de Cloudprinter
    public function toCloudprinterPayload()
    {
        return [
            'id'=> $this->id,
            'customer_name' => $this->customerName,
            'customer_address' => $this->customerAddress,
            'product' => $this->productTitle,
            'quantity' => $this->quantity,
        ];
    }
}
