<?php
namespace App\Webhook\Handlers;

use Shopify\Webhooks\Handler;
use Controllers\ShopifyController;

class OrderCreateHandler implements Handler
{
    public function handle(string $topic, string $shop, array $requestBody): void
    {
        $shopifyController = new ShopifyController();
        $shopifyController->handleOrderCreation($requestBody);
    }
}
