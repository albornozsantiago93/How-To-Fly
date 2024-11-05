<?php
$config = require_once __DIR__ . '/Config/database.php';

try 
{
    $conn = new PDO($config['dsn'], $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
} 
catch (PDOException $e) 
{
    die("Error en la conexión: " . $e->getMessage());
}

?>
