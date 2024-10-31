<?php

namespace Models;

class CloudprinterOrderData {
    public $reference;
    public $email;
    public $addresses;
    public $items;

    public function __construct($shopifyOrder) {
        $this->reference = $shopifyOrder->id;
        $this->email = $shopifyOrder->email;
        $this->addresses = $this->mapAddress($shopifyOrder);
        $this->items = $this->mapItems($shopifyOrder->line_items);
    }

    private function mapAddress($shopifyOrder) {
        $address = $shopifyOrder->shipping_address;
        return [
            [
                "type" => "delivery",
                "company" => $address['company'] ?? '',
                "firstname" => $address['first_name'],
                "lastname" => $address['last_name'],
                "street1" => $address['address1'],
                "street2" => $address['address2'] ?? '',
                "zip" => $address['zip'],
                "city" => $address['city'],
                "country" => $address['country_code'],
                "email" => $shopifyOrder->email,
                "phone" => $address['phone'] ?? ''
            ]
        ];
    }
    

    private function mapItems($lineItems) {
        $items = [];
        foreach ($lineItems as $item) {
            $items[] = [
                "reference" => $item['id'],
                "product" => $item['sku'],  // Ajustar según el SKU necesario en Cloudprinter
                "shipping_level" => "cp_saver",  // Ajusta si hay un nivel específico
                "title" => $item['title'],
                "count" => $item['quantity'],
                "files" => [
                    [
                        "type" => "cover",
                        "url" => $item['cover_url'],  // Cambia esto por la URL real
                        "md5sum" => md5_file($item['cover_url'])
                    ],
                    [
                        "type" => "book",
                        "url" => $item['book_url'],  // Cambia esto por la URL real
                        "md5sum" => md5_file($item['book_url'])
                    ]
                ],
                "options" => [
                    [
                        "type" => "total_pages",
                        "count" => $item['page_count'] // Ajusta según las páginas necesarias
                    ]
                ]
            ];
        }
        return $items;
    }
    
}
