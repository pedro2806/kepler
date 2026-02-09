<?php
// Configuración de la base de datos
$host = 'localhost';
$db   = 'kepler'; // Cambia esto por el nombre real de tu BD
$user = 'root';            // Usuario de MySQL
$pass = '';                // Contraseña de MySQL
$charset = 'utf8mb4';

// Opciones de PDO para mayor seguridad y manejo de errores
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    ///echo "Conexión exitosa a la base de datos Kepler CRM.";
} catch (\PDOException $e) {
    // En producción, no deberías mostrar $e->getMessage() directamente
    die("Error de conexión al CRM Kepler: " . $e->getMessage());
}