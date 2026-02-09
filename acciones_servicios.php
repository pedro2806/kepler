<?php
require('conn.php');
$opcion = $_POST['opcion'] ?? '';
switch ($opcion) {

case 'listarServicios':
    try {
        // ALIAS: messCode AS keplerCode
        $sql = "SELECT id, messCode as keplerCode, description, price, currency, status 
                FROM service 
                ORDER BY messCode ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

case 'guardarServicio':
    try {
        $id = $_POST['id'];
        
        // Validamos el tipo enviado desde el formulario
        $tipo = $_POST['type']; // Puede ser 'SERVICE' o 'PRODUCT'

        if (empty($id)) {
            // INSERT
            $sql = "INSERT INTO service (
                        messCode, description, price, currency, status, 
                        type,  /* <--- Agregamos este campo */
                        is_recurring, hasCommission, is_decalogo, supplierPrice, created
                    ) VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['keplerCode'],
                $_POST['description'],
                $_POST['price'],
                $_POST['currency'],
                $_POST['status'],
                $tipo // Guardamos 'SERVICE' o 'PRODUCT'
            ]);
        } else {
            // UPDATE
            $sql = "UPDATE service SET 
                        messCode = ?, description = ?, price = ?, 
                        currency = ?, status = ?, type = ?, updated = NOW()
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['keplerCode'],
                $_POST['description'],
                $_POST['price'],
                $_POST['currency'],
                $_POST['status'],
                $tipo,
                $id
            ]);
        }
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'El Kepler Code ya existe.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    break;

case 'obtenerServicio':
    try {
        // Al obtener uno solo, también aplicamos el alias
        $stmt = $pdo->prepare("SELECT id, messCode as keplerCode, description, price, currency, status FROM service WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

case 'buscarServicios':
    // Actualizamos también el buscador del cotizador
    try {
        $term = $_POST['term'] ?? '';
        $sql = "SELECT id, messCode as keplerCode, description, price, currency, type 
                FROM service 
                WHERE (messCode LIKE ? OR description LIKE ?) AND status = 'ACTIVE'
                LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$term%", "%$term%"]);
        
        $results = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'id' => $row['id'],
                // Mostramos Kepler Code en el select
                'text' => $row['keplerCode'] . ' - ' . substr($row['description'], 0, 60) . '...',
                'price' => $row['price'],
                'currency' => $row['currency']
            ];
        }
        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    break;

case 'importarServiciosCSV':
    try {
        if (!isset($_FILES['archivoCSV']) || $_FILES['archivoCSV']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo.");
        }

        $tmpName = $_FILES['archivoCSV']['tmp_name'];
        $handle = fopen($tmpName, "r");

        if ($handle === FALSE) throw new Exception("No se pudo abrir el archivo.");

        $pdo->beginTransaction();
        
        $insertados = 0;
        $errores = 0;
        $fila = 0;

        // Actualizamos el INSERT para incluir la columna 'type'
        $sql = "INSERT INTO service (
                    messCode, description, price, currency, type, status, 
                    is_recurring, hasCommission, is_decalogo, supplierPrice, created
                ) VALUES (?, ?, ?, ?, ?, 'ACTIVE', 0, 0, 0, 0, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $checkStmt = $pdo->prepare("SELECT id FROM service WHERE messCode = ?");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $fila++;
            
            // Ignorar cabecera si la columna de precio no es numérica
            if ($fila === 1 && !is_numeric($data[2])) continue;

            // Mapeo de columnas
            $keplerCode  = trim($data[0]);
            $description = utf8_encode(trim($data[1]));
            $price       = floatval($data[2]);
            $currency    = strtoupper(trim($data[3]));
            $rawType     = strtoupper(trim($data[4] ?? 'SERVICE')); // Por defecto SERVICIO

            // Lógica inteligente para definir el TIPO
            // Acepta: PRODUCT, PRODUCTO, PROD, P -> PRODUCT
            // Todo lo demás -> SERVICE
            if (in_array($rawType, ['PRODUCT', 'PRODUCTO', 'PROD', 'P'])) {
                $finalType = 'PRODUCT';
            } else {
                $finalType = 'SERVICE';
            }

            // Validaciones
            if (empty($keplerCode) || empty($description) || $price < 0) {
                $errores++;
                continue;
            }

            // Verificar duplicados
            $checkStmt->execute([$keplerCode]);
            if ($checkStmt->fetch()) {
                $errores++; 
                continue;
            }

            // Insertar con el tipo correcto
            if ($stmt->execute([$keplerCode, $description, $price, $currency, $finalType])) {
                $insertados++;
            } else {
                $errores++;
            }
        }

        fclose($handle);
        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => "Carga finalizada: $insertados ítems agregados (Productos/Servicios), $errores errores/duplicados."
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

}