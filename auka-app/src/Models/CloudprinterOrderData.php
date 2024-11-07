<?php

namespace Models;

class CloudprinterOrderData 
{
    public $reference;
    public $email;
    public $addresses;
    public $items;
    
    public function __construct($shopifyOrder)
    {
        $this->reference = $shopifyOrder->id;
        $this->email = $shopifyOrder->email;
        $this->addresses = $this->mapAddress($shopifyOrder);
        $this->items = $this->mapItems($shopifyOrder->line_items);
    }

    private function mapAddress($shopifyOrder)
    {
        $address = $shopifyOrder->shipping_address;
        return [[
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
        ]];
    }

    private function mapItems($lineItems)
    {
        $items = [];
        foreach ($lineItems as $item) {
            $coverUrl = $item['cover_url'] ?? null;
            $bookUrl = $item['book_url'] ?? null;
            $page_count = $item['page_count'] ?? 324; // Valor por defecto

            $items[] = [
                "reference" => $item['id'],
                "product" => $item['sku'],
                "shipping_level" => "cp_saver",
                "title" => $item['title'],
                "count" => $item['quantity'],
                "files" => [
                    [
                        "type" => "cover",
                        "url" => $coverUrl,
                        "md5sum" => $coverUrl ? md5_file($coverUrl) : null
                    ],
                    [
                        "type" => "book",
                        "url" => $bookUrl,
                        "md5sum" => $bookUrl ? md5_file($bookUrl) : null
                    ]
                ],
                "options" => [
                    [
                        "type" => "total_pages",
                        "count" => $page_count
                    ]
                ]
            ];
        }
        return $items;
    }
}
