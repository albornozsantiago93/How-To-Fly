<?php

namespace Models;

class ShopifyOrderData {
    public $id;
    public $email;
    public $line_items;
    public $total_price;
    public $currency;
    public $shipping_address;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->line_items = $data['line_items'];
        $this->total_price = $data['total_price'];
        $this->currency = $data['currency'];
        $this->shipping_address = $data['shipping_address'];
    }
}

