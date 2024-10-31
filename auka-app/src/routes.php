<?php

require_once __DIR__ . '/controllers/ShopifyController.php';
require_once __DIR__ . '/controllers/CloudprinterController.php';
require_once __DIR__ . '/Utils/RouteHelper.php';

use Utils\RouteHelper;

$shopifyController = new Controllers\ShopifyController();
$cloudprinterController = new Controllers\CloudprinterController();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');

switch ($uri) {
    case '/cloudprinter/webhook':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require 'cloudprinter_webhook.php';
        } else {
            RouteHelper::respondMethodNotAllowed();
        }
        break;

    case '/webhook':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require 'webhook.php';
        } else {
            RouteHelper::respondMethodNotAllowed();
        }
        break;

    default:
        RouteHelper::respondWithBadRequest('Ruta no válida');
        break;
}


