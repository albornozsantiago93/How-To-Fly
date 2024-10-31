<?php

namespace App\Webhook\Handlers;

use Shopify\Webhooks\Handler;
use Controllers\ShopifyController;

class FulfillmentUpdateHandler implements Handler
{
    public function handle(string $topic, string $shop, array $requestBody): void
    {
        $shopifyController = new ShopifyController();
        $shopifyController->updateFulfillmentTracking($requestBody['fulfillment']['id'], [
            'tracking_number' => $requestBody['fulfillment']['tracking_number'],
            'tracking_urls' => $requestBody['fulfillment']['tracking_urls'],
            'notify_customer' => true
        ]);
    }
}
