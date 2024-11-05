<?php

namespace Controllers;

use Models\ShopifyOrderData;
use Models\CloudprinterOrderData;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Auth\FileSessionStorage;
use PDO;

class ShopifyController
{
    private $client;
    private $cloudprinterController;
    private $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/shopify.php';
        $path = __DIR__ . '/../storage/sessions'; // Define el path correcto para el almacenamiento de sesiones
        $dbConfig = require __DIR__ . '/../Config/database.php'; // Archivo de configuración de la base de datos

            try 
            {
                $this->db = new PDO("sqlsrv:Server={$dbConfig['host']};Database={$dbConfig['database']}", $dbConfig['username'], $dbConfig['password']);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } 
            catch (\PDOException $e) 
            {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }

                /////////////////////////////////////////////////////////////////////////////////////////////
                if (!is_dir($path)) {
                    if (!mkdir($path, 0777, true) && !is_dir($path)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
                    }
                }
                /////////////////////////////////////////////////////////////////////////////////////////////

        // Inicializa el Contexto de Shopify si aún no está inicializado
        Context::initialize(
            $config['api_key'],
            $config['api_secret'],
            $config['scopes'],
            $config['host'],
            new FileSessionStorage($path) 
        );
        
        $this->client = new Rest($config['shop_domain'], $config['access_token']);
        $this->cloudprinterController = new CloudprinterController();
    }
    
    
    //CREAR ORDEN
    public function handleOrderCreation($orderData)
    {
        $shopifyOrder = new ShopifyOrderData($orderData);
        $cloudprinterResponse = null;

        try {

            $cloudprinterOrderData = new CloudprinterOrderData($shopifyOrder);
            $cloudprinterResponse = $this->cloudprinterController->createOrder($cloudprinterOrderData);
            
            return json_encode([
                'status' => 'Order created successfully in Shopify and sent to Cloudprinter',
                'cloudprinter_response' => $cloudprinterResponse
            ]);
        } 
        catch (\Exception $e) 
        {
            return json_encode(['error' => 'Error creating order: ' . $e->getMessage()]);
        }
        finally 
        {
            $this->saveOrderRecords($shopifyOrder, $cloudprinterResponse, 'POST');
        }
    }
    
    //finally dejar registro agregar estado
    //mappear estados internos entre clod y shop
    //guardo json que envio y recibo

    //UPDATE TRACKING
    public function updateFulfillmentTracking($fulfillmentId, $trackingData)
    {
        try 
        {
            $response = $this->client->post("fulfillments/{$fulfillmentId}/update_tracking.json", [
                'fulfillment' => $trackingData
            ]);

            return json_encode([
                'status' => 'Tracking updated successfully',
                'response' => $response->getDecodedBody()
            ]);
        } 
        catch (\Exception $e) 
        {
            return json_encode(['error' => 'Error updating tracking: ' . $e->getMessage()]);
        }
    }

    
    // Metodo para manejar la actualización de la orden
    public function handleOrderUpdate($orderData)
    {
        try 
        {
            // Ejemplo: actualizar el estado de una orden en una base de datos local o enviar un correo de confirmación
            
        } 
        catch (\Exception $e) 
        {
            return json_encode(['error' => 'Error updating order: ' . $e->getMessage()]);
        }
    }


    //Metodo para manejar la orden cumplida
    public function handleOrderFulfillment($orderData)
    {
        try 
        {
            // Ejemplo: notificar al cliente que la orden fue cumplida o actualizar inventario
        } 
        catch (\Exception $e) 
        {
            return json_encode(['error' => 'Error handling order fulfillment: ' . $e->getMessage()]);
        }
    }

    private function saveOrderRecords($shopifyOrder, $cloudprinterResponse, $requestType = 'POST') {
        // Inserción en la tabla Orders
        $stmtOrder = $this->db->prepare("
            INSERT INTO Orders (ShopifyOrderID, Email, TotalPrice, Currency, ShippingAddress, CreatedAt)
            VALUES (:shopify_order_id, :email, :total_price, :currency, :shipping_address, GETDATE())
        ");
        $stmtOrder->execute([
            ':shopify_order_id' => $shopifyOrder->id,
            ':email' => $shopifyOrder->email,
            ':total_price' => $shopifyOrder->total_price,
            ':currency' => $shopifyOrder->currency,
            ':shipping_address' => json_encode($shopifyOrder->shipping_address)
        ]);
    
        // Asignar CloudPrinterOrderID o fallback a OrderID
        $cloudPrinterOrderID = $cloudprinterResponse['order_id'] ?? $shopifyOrder->id;
    
        // Inserción en la tabla OrderHeaders
        $stmtHeader = $this->db->prepare("
            INSERT INTO OrderHeaders (InternalRequestID, ShopifyOrderID, CloudPrinterOrderID, RequestType, RequestDate, Response, Status)
            VALUES (:internal_request_id, :shopify_order_id, :cloudprinter_order_id, :request_type, GETDATE(), :response, :status)
        ");
        $stmtHeader->execute([
            ':internal_request_id' => uniqid(),
            ':shopify_order_id' => $shopifyOrder->id,
            ':cloudprinter_order_id' => $cloudPrinterOrderID,
            ':request_type' => $requestType,
            ':response' => json_encode($cloudprinterResponse),
            ':status' => $cloudprinterResponse['status'] ?? 'Pending'
        ]);
    
        // Obtener el ID del registro en OrderHeaders
        $orderHeaderId = $this->db->lastInsertId();
    
        // Inserción en la tabla OrderLogs
        $stmtLog = $this->db->prepare("
            INSERT INTO OrderLogs (OrderHeaderID, LogDate, LogStatus, Comment)
            VALUES (:order_header_id, GETDATE(), :log_status, :comment)
        ");
        $stmtLog->execute([
            ':order_header_id' => $orderHeaderId,
            ':log_status' => 'Order Created',
            ':comment' => 'Order successfully created and recorded'
        ]);
    }   
    

}

?>
