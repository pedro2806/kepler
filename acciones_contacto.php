<?php
require_once 'conn.php';
session_start();
$opcion = $_POST['opcion'] ?? '';
switch ($opcion) {

case 'listarContactos':
    try {
        // Unimos 3 tablas: contact -> customer_contact -> customer
        $sql = "SELECT c.id, c.name, c.jobPosition, c.email, c.mobile, 
                        cust.tradeName as empresa, c.enabled
                FROM contact c
                JOIN customer_contact cc ON c.id = cc.contact_id
                JOIN customer cust ON cc.customer_id = cust.id
                ORDER BY c.name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($res);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

case 'buscarClientesSelect':
    try {
        // Solo traemos clientes activos para el buscador del modal
        $sql = "SELECT id, tradeName FROM customer WHERE enabled = 1 ORDER BY tradeName ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    break;

case 'guardarContacto':
    try {
        $pdo->beginTransaction();

        // 1. Insertar en tabla 'contact'
        $sqlContact = "INSERT INTO contact (
            name, jobPosition, jobArea, email, phone, mobile, enabled
        ) VALUES (?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $pdo->prepare($sqlContact);
        $stmt->execute([
            $_POST['name'],
            $_POST['jobPosition'],
            $_POST['jobArea'], // Campo obligatorio en tu BD
            $_POST['email'],
            $_POST['phone'],
            $_POST['mobile']
        ]);
        $contactId = $pdo->lastInsertId();

        // 2. Vincular con el Cliente (Tabla 'customer_contact')
        $sqlRel = "INSERT INTO customer_contact (customer_id, contact_id) VALUES (?, ?)";
        $stmtRel = $pdo->prepare($sqlRel);
        $stmtRel->execute([$_POST['customer_id'], $contactId]);

        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'obtenerDatosContacto':
    try {
        $id = $_POST['id'];
        // Obtenemos datos del contacto Y el ID de su cliente actual
        $sql = "SELECT c.*, cust.id as empresa_id, cust.tradeName as empresa_nombre
                FROM contact c
                LEFT JOIN customer_contact cc ON c.id = cc.contact_id
                LEFT JOIN customer cust ON cc.customer_id = cust.id
                WHERE c.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

    case 'actualizarContacto':
        try {
            $pdo->beginTransaction();

            // 1. Actualizar datos personales en 'contact'
            $sqlContact = "UPDATE contact SET 
                            name = ?, jobPosition = ?, jobArea = ?, 
                            email = ?, mobile = ?
                        WHERE id = ?";
            $stmt = $pdo->prepare($sqlContact);
            $stmt->execute([
                $_POST['name'],
                $_POST['jobPosition'],
                $_POST['jobArea'],
                $_POST['email'],
                $_POST['mobile'],
                $_POST['contact_id']
            ]);

            // 2. Reasignar empresa en 'customer_contact'
            // Gracias a la UNIQUE KEY en contact_id, podemos actualizar directamente
            $sqlRel = "UPDATE customer_contact SET customer_id = ? WHERE contact_id = ?";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->execute([$_POST['customer_id'], $_POST['contact_id']]);

            $pdo->commit();
            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
}