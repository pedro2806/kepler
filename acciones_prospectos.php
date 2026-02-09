<?php
require_once 'conn.php';
session_start();

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {
    case 'guardarProspecto':
        try {
            // Insertamos respetando los nombres exactos: tradeName, user_id, etc.
            $sql = "INSERT INTO seller_customer (
                        tradeName, contactName, email, phone, 
                        mobile, phoneExtension, status, user_id,
                        street, extNumber, intNumber, neighborhood, 
                        city, state, zipcode, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['tradeName'],
                $_POST['contactName'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['mobile'],
                $_POST['phoneExtension'],
                'NEW', // Estatus inicial por defecto
                $_POST['user_id'], // ID del vendedor (vincular con fos_user.id)
                $_POST['street'],
                $_POST['extNumber'],
                $_POST['intNumber'],
                $_POST['neighborhood'],
                $_POST['city'],
                $_POST['state'],
                $_POST['zipcode'],
                $_POST['notes']
            ]);

            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    break;

    case 'listarProspectos':
        try {            
            $sql = "SELECT sc.id, sc.tradeName, sc.contactName, sc.email, sc.city, sc.phone,
                        u.full_name as vendedor, sc.status
                    FROM seller_customer sc
                    LEFT JOIN fos_user u ON sc.user_id = u.id
                    ORDER BY sc.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

        case 'convertirProspecto':
    try {
        $id = $_POST['id'];
        $pdo->beginTransaction();

        // 1. Obtener todos los datos del prospecto de Kepler
        $stmt = $pdo->prepare("SELECT * FROM seller_customer WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p) throw new Exception("Prospecto no encontrado.");

        // 2. Insertar en la tabla 'customer'
        // Respetamos campos: legalName, tradeName, status, created
        $sqlCustomer = "INSERT INTO customer (legalName, tradeName, created, enabled) 
                        VALUES (?, ?, NOW(), 1)";
        $stmtCust = $pdo->prepare($sqlCustomer);
        $stmtCust->execute([$p['tradeName'], $p['tradeName']]);
        $nuevoClienteId = $pdo->lastInsertId();

        // 3. Insertar en la tabla 'address'
        // Respetamos campos: street, extNumber, intNumber, neighborhood, city, state, zipcode
        $sqlAddress = "INSERT INTO address (
                            street, extNumber, intNumber, neighborhood, 
                            city, state, zipcode, enable, type
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'BILLING')";
        $stmtAddr = $pdo->prepare($sqlAddress);
        $stmtAddr->execute([
            $p['street'],
            $p['extNumber'],
            $p['intNumber'],
            $p['neighborhood'],
            $p['city'],
            $p['state'],
            $p['zipcode']
        ]);
        $nuevaDireccionId = $pdo->lastInsertId();

        // 4. Vincular Cliente con su nueva Dirección (Tabla relacional customers_addresses)
        $sqlRel = "INSERT INTO customers_addresses (customer_id, address_id) VALUES (?, ?)";
        $pdo->prepare($sqlRel)->execute([$nuevoClienteId, $nuevaDireccionId]);

        // 5. Actualizar estatus del prospecto original
        $stmtUpd = $pdo->prepare("UPDATE seller_customer SET status = 'CONVERTED' WHERE id = ?");
        $stmtUpd->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;
}