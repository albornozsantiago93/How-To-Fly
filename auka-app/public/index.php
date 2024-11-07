<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/routes.php';

use Shopify\Context;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Webhook\Handlers\OrderCreateHandler;
use App\Webhook\Handlers\FulfillmentUpdateHandler;

//Configuracion de context Shopify
$config = require __DIR__ . '/../src/config/shopify.php';

Context::initialize(
    $config['api_key'],
    $config['api_secret'],
    $config['scopes'],
    $config['host'],
    new \Shopify\Auth\FileSessionStorage(__DIR__ . '/../storage/sessions')
);

// Registra los handlers para los webhooks específicos
Registry::addHandler(Topics::ORDERS_CREATE, new OrderCreateHandler());
Registry::addHandler(Topics::FULFILLMENTS_UPDATE, new FulfillmentUpdateHandler());
