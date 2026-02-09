<?php
require_once 'conn.php';
session_start();

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {
case 'listarClientesOficiales':
    try {
        // Seleccionamos de la tabla customer que acabamos de alimentar
        $sql = "SELECT id, legalName, tradeName, created, enabled
                FROM customer 
                WHERE enabled IN (0,1)
                ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($resultados);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

    case 'obtenerDireccionCliente':
        try {
            $id = $_POST['id'];
            // Cruzamos customer -> customers_addresses -> address
            $sql = "SELECT a.* FROM address a
                    JOIN customers_addresses ca ON a.id = ca.address_id
                    WHERE ca.customer_id = ? 
                    LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $direccion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($direccion) {
                echo json_encode(['status' => 'success', 'data' => $direccion]);
            } else {
                echo json_encode(['status' => 'empty']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'obtenerDatosCompletosCliente':
        $sql = "SELECT c.id, c.legalName, c.tradeName, a.street, a.city 
                FROM customer c
                LEFT JOIN customers_addresses ca ON c.id = ca.customer_id
                LEFT JOIN address a ON ca.address_id = a.id
                WHERE c.id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['id']]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        break;

    case 'actualizarCliente':
        try {
            $pdo->beginTransaction();
            // 1. Actualizar tabla customer
            $stmt1 = $pdo->prepare("UPDATE customer SET legalName = ?, tradeName = ? WHERE id = ?");
            $stmt1->execute([$_POST['name'], $_POST['tradeName'], $_POST['cliente_id']]);

            // 2. Actualizar tabla address (vía relación)
            $stmt2 = $pdo->prepare("UPDATE address a 
                                    JOIN customers_addresses ca ON a.id = ca.address_id 
                                    SET a.street = ?, a.city = ? 
                                    WHERE ca.customer_id = ?");
            $stmt2->execute([$_POST['street'], $_POST['city'], $_POST['cliente_id']]);

            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
}