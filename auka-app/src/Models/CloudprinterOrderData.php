<?php

namespace Models;

class CloudprinterOrderData 
{
    public $apikey;
    public $id;
    public $date;
    public $priority;
    public $shipping_date;
    public $creator;
    public $client;
    public $costs;
    public $shipping;
    public $addresses;
    public $files;
    public $items;
    public $email; 
    public $reference;

    public function __construct($shopifyOrder)
    {
        $config = require __DIR__ . '/../Config/shopify.php';
        $this->apikey = $config['cloudprinter_api_key'] ?? ''; // Verificar que la clave esté definida
        $this->id = (string) $shopifyOrder->id;
        $this->date = $shopifyOrder->created_at ?? '';
        $this->priority = "2"; // Valor fijo, ajusta según sea necesario
        $this->shipping_date = $shopifyOrder->processed_at ?? $shopifyOrder->created_at;
        $this->email = $shopifyOrder->email ?? '';
        $this->reference = $shopifyOrder->reference ?? (string) $shopifyOrder->id; // Asignar referencia

        // Datos del creador
        $this->creator = [
            "id" => 1,
            "name" => "Cloudprinter.com",
            "version" => "2.1",
            "date" => $shopifyOrder->created_at ?? '',
        ];

        // Cliente
        $this->client = [
            "id" => 1,
            "name" => "Cloudprinter.com",
            "date" => $shopifyOrder->created_at ?? '',
            "reference" => $this->reference,
        ];

        // Costos
        $this->costs = [
            "currency" => $shopifyOrder->currency ?? '',
            "shipping" => $shopifyOrder->current_shipping_price_set['shop_money']['amount'] ?? '0.00',
            "items" => $shopifyOrder->current_subtotal_price ?? '0.00',
            "vat" => "0.0000", // Asume VAT en 0; ajusta si es necesario
            "total" => $shopifyOrder->total_price ?? '0.00',
        ];

        // Detalles de envío
        $this->shipping = [
            "method" => $shopifyOrder->shipping_lines[0]['title'] ?? 'standard',
            "consignor" => "The Book Company",
            "invoice" => [
                "shipments" => 1,
                "currency" => $shopifyOrder->currency ?? '',
                "total" => $shopifyOrder->current_shipping_price_set['shop_money']['amount'] ?? '0.00',
            ],
            "proforma_invoice" => [
                "currency" => $shopifyOrder->currency ?? '',
                "total" => $shopifyOrder->total_price ?? '0.00',
                "weight" => "173.6986", // Peso fijo; ajusta si es necesario
            ],
        ];

        $this->addresses = $this->mapAddress($shopifyOrder);
        $this->files = $this->mapFiles($shopifyOrder->line_items ?? []);
        $this->items = $this->mapItems($shopifyOrder->line_items ?? []);
    }

    private function mapAddress($shopifyOrder)
    {
        $address = $shopifyOrder->shipping_address ?? [];
        return [[
            "type" => "delivery",
            "company" => $address['company'] ?? '',
            "name" => ($address['first_name'] ?? '') . ' ' . ($address['last_name'] ?? ''),
            "street1" => $address['address1'] ?? '',
            "street2" => $address['address2'] ?? '',
            "zip" => $address['zip'] ?? '',
            "city" => $address['city'] ?? '',
            "country" => $address['country_code'] ?? '',
            "state" => $address['province_code'] ?? '',
            "email" => $shopifyOrder->email ?? '',
            "phone" => $address['phone'] ?? '',
        ]];
    }

    private function mapFiles($lineItems)
    {
        $files = [];
        foreach ($lineItems as $item) {
            if (isset($item['cover_url'])) {
                $files[] = [
                    "type" => "cover",
                    "format" => "pdf",
                    "url" => $item['cover_url'],
                    "md5sum" => md5_file($item['cover_url']),
                    "size" => filesize($item['cover_url']),
                ];
            }
            if (isset($item['book_url'])) {
                $files[] = [
                    "type" => "book",
                    "format" => "pdf",
                    "url" => $item['book_url'],
                    "md5sum" => md5_file($item['book_url']),
                    "size" => filesize($item['book_url']),
                ];
            }
        }
        return $files;
    }

    private function mapItems($lineItems)
    {
        $items = [];
        foreach ($lineItems as $item) {
            $coverUrl = $item['cover_url'] ?? null;
            $bookUrl = $item['book_url'] ?? null;
            $page_count = $item['page_count'] ?? '44'; // Valor por defecto

            $items[] = [
                "id" => (string) $item['id'],
                "count" => $item['quantity'] ?? 1,
                "title" => $item['title'] ?? '',
                "product" => $item['sku'] ?? '',
                "desc" => $item['name'] ?? '',
                "pages" => $page_count,
                "files" => [
                    [
                        "type" => "cover",
                        "format" => "pdf",
                        "url" => $coverUrl,
                        "md5sum" => $coverUrl ? md5_file($coverUrl) : null,
                        "size" => $coverUrl ? filesize($coverUrl) : null,
                    ],
                    [
                        "type" => "book",
                        "format" => "pdf",
                        "url" => $bookUrl,
                        "md5sum" => $bookUrl ? md5_file($bookUrl) : null,
                        "size" => $bookUrl ? filesize($bookUrl) : null,
                    ]
                ],
                "options" => [
                    [
                        "option" => "pageblock_80off",
                        "desc" => "Pageblock paper 80gsm Offset",
                        "count" => $page_count,
                        "type" => "type_main_paper"
                    ],
                    [
                        "option" => "cover_finish_gloss",
                        "desc" => "Cover lamination Gloss finish",
                        "count" => "1",
                        "type" => "type_book_cover_finish"
                    ]
                ]
            ];
        }
        return $items;
    }
}
