<?php

namespace Models;

class ShopifyOrderData {
    public $id;
    public $email;
    public $line_items;
    public $total_price;
    public $currency;
    public $shipping_address;
    public $created_at;
    public $processed_at;
    public $reference;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->line_items = $data['line_items'] ?? [];
        $this->total_price = $data['total_price'] ?? '0.00';
        $this->currency = $data['currency'] ?? 'USD';
        $this->shipping_address = $data['shipping_address'] ?? [];
        $this->created_at = $data['created_at'] ?? '';
        $this->processed_at = $data['processed_at'] ?? '';
        $this->reference = $data['reference'] ?? (string) $data['id'];
    }
}
