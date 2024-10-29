<?php
namespace Utils;


class RouteHelper
{
    public static function handleOrderCreationWebhook($shopifyController)
    {
        $input = file_get_contents('php://input');
        $orderData = json_decode($input, true);

        if ($orderData) {
            $shopifyController->handleOrderCreation($orderData);
            self::respondWithSuccess("Webhook de creación de pedido procesado correctamente");
        } else {
            self::respondWithBadRequest("Datos de orden no válidos en el webhook");
        }
    }

    public static function handleOrderUpdateWebhook($shopifyController)
    {
        $input = file_get_contents('php://input');
        $orderData = json_decode($input, true);

        if ($orderData) {
            $shopifyController->handleOrderUpdate($orderData);
            self::respondWithSuccess("Webhook de actualización de pedido procesado correctamente");
        } else {
            self::respondWithBadRequest("Datos de orden no válidos en el webhook de actualización");
        }
    }

    public static function handleCloudprinterOrder($cloudprinterController, $orderData)
    {
        $response = $cloudprinterController->createOrder($orderData);
        echo json_encode($response);
    }

    public static function respondWithSuccess($message)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => $message]);
    }

    public static function respondWithBadRequest($message)
    {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $message]);
    }

    public static function respondMethodNotAllowed()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['error' => 'Método no permitido']);
    }
}
