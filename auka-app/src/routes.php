<?php
require_once 'controllers/ShopifyController.php';
require_once 'controllers/CloudprinterController.php';
require_once 'Utils/RouteHelper.php';

use Utils\RouteHelper;
$shopifyController = new Controllers\ShopifyController();
$cloudprinterController = new Controllers\CloudprinterController();


// Obtener la ruta de la solicitud
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');

switch ($uri) 
{
    case '/cloudprinter/order':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            $input = file_get_contents('php://input');
            $orderData = json_decode($input, true);
            RouteHelper::handleCloudprinterOrder($cloudprinterController, $orderData);
        } 
        else 
        {
            RouteHelper::respondMethodNotAllowed();
        }
        break;

    case '/webhook/order-created':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            RouteHelper::handleOrderCreationWebhook($shopifyController);
        } 
        else 
        {
            RouteHelper::respondMethodNotAllowed();
        }
        break;

    case '/webhook/order-updated':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            RouteHelper::handleOrderUpdateWebhook($shopifyController);
        } 
        else 
        {
            RouteHelper::respondMethodNotAllowed();
        }
        break;

    default:
        RouteHelper::respondWithBadRequest("Ruta no válida");
        break;
}
