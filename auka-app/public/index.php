<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/routes.php';


use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Webhook\Handlers\OrderCreateHandler;
use App\Webhook\Handlers\FulfillmentUpdateHandler;


// Registra los handlers para los webhooks específicos
Registry::addHandler(Topics::ORDERS_CREATE, new OrderCreateHandler());
Registry::addHandler(Topics::FULFILLMENTS_UPDATE, new FulfillmentUpdateHandler());
