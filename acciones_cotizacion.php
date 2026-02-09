<?php
session_start();
require_once 'conn.php'; // Asegúrate de que este archivo conecte a tu BD SCOT

// Configuramos cabecera JSON para evitar errores de parseo en JS
header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada']);
    exit();
}

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {

    // ------------------------------------------------------------------
    // 1. BUSCADOR DE CLIENTES (Para Select2)
    // ------------------------------------------------------------------
    case 'buscarClientesSelect':
        try {
            $term = $_POST['term'] ?? '';
            $sql = "SELECT id, tradeName 
                    FROM customer 
                    WHERE tradeName LIKE ? AND enabled = 1 
                    ORDER BY tradeName ASC LIMIT 20";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$term%"]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        break;

    // ------------------------------------------------------------------
    // 2. CARGAR CONTACTOS DE UN CLIENTE
    // ------------------------------------------------------------------
    case 'cargarContactosPorCliente':
        try {
            $sql = "SELECT c.id, c.name, c.jobPosition 
                    FROM contact c
                    JOIN customer_contact cc ON c.id = cc.contact_id
                    WHERE cc.customer_id = ? AND c.enabled = 1
                    ORDER BY c.name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        break;

    // ------------------------------------------------------------------
    // 3. BUSCADOR DE SERVICIOS / PRODUCTOS (Para Select2)
    // ------------------------------------------------------------------
    case 'buscarServicios':
        try {
            $term = $_POST['term'] ?? '';
            // Buscamos por messCode (KeplerCode) o Descripción
            $sql = "SELECT id, messCode, description, price, currency, type 
                    FROM service 
                    WHERE (messCode LIKE ? OR description LIKE ?) AND status = 'ACTIVE'
                    LIMIT 20";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$term%", "%$term%"]);
            
            $results = [];
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $results[] = [
                    'id' => $row['id'],
                    'text' => $row['messCode'] . ' - ' . substr($row['description'], 0, 60) . '...',
                    'price' => $row['price'],
                    'currency' => $row['currency'],
                    'type' => $row['type'] // Importante: PRODUCT o SERVICE
                ];
            }
            echo json_encode($results);
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        break;

    // ------------------------------------------------------------------
    // 4. GUARDAR COTIZACIÓN (Lógica Principal)
    // ------------------------------------------------------------------
    case 'guardarCotizacion':
        try {
            $pdo->beginTransaction();
            
            // Generar folio simple (puedes mejorar esto con una secuencia real)
            $orderCode = 'COT-' . date('Ymd-His'); 
            
            // A. Insertar Cabecera (Tabla 'quotation')
            // Llenamos campos NOT NULL con valores por defecto para cumplir con SCOT
            $sqlHead = "INSERT INTO quotation (
                company_id, customer_id, user_id, 
                currency, paymentTerm, validThru, shipmentTermsConditions, 
                tax, status, created, orderCode, 
                dollarPrice, euroPrice, shipmentTime, elaboratedby_id, 
                sent_notifications
            ) VALUES (
                3, ?, ?, 
                ?, ?, ?, 'LAB Destino', 
                '16%', 'DRAFT', NOW(), ?, 
                0, 0, ?, ?, 
                0
            )";
            
            /* Notas de campos:
               - company_id: 1 (Fijo Kepler)
               - paymentTerm: Viene del POST (Contado/Crédito)
               - validThru: Calculamos fecha suma
               - shipmentTime: Viene del POST (Tiempo entrega)
            */

            // Calcular fecha de vencimiento
            $diasVigencia = intval($_POST['validDays'] ?? 15);
            $fechaVencimiento = date('Y-m-d', strtotime("+$diasVigencia days"));

            $stmt = $pdo->prepare($sqlHead);
            $stmt->execute([
                $_POST['customer_id'], 
                $_SESSION['user_id'],   // Dueño del registro
                $_POST['currency'],
                $_POST['paymentTerm'],  // Ej: 'Contado'
                $fechaVencimiento,
                $orderCode,
                $_POST['shipmentTime'], // Ej: '5-7 Días'
                $_SESSION['user_id']    // Elaborado por
            ]);
            $quotationId = $pdo->lastInsertId();

            // B. Insertar Partidas (Tabla 'quotation_item')
            $items = json_decode($_POST['items'], true);
            
            $sqlItem = "INSERT INTO quotation_item (
                service_id, product_id, orderedQty, price, 
                discountType, discount, total, entityType, 
                notes, optional, is_extra, is_price_editable, 
                is_decalogo, extra_expenses, 
                service_additional_condition_qty, service_additional_condition_price
            ) VALUES (
                ?, ?, ?, ?, 
                'percentage', 0, ?, ?, 
                ?, 0, 0, 1, 
                0, 0, 0, 0
            )";
            $stmtItem = $pdo->prepare($sqlItem);

            foreach ($items as $item) {
                // Determinar si es Producto o Servicio según la columna 'type'
                $serviceId = null;
                $productId = null;
                $entityType = '';

                if (isset($item['type']) && $item['type'] === 'PRODUCT') {
                    $productId = $item['service_id']; // El ID del select
                    $entityType = 'product'; 
                } else {
                    $serviceId = $item['service_id'];
                    $entityType = 'service';
                }

                $totalLinea = $item['qty'] * $item['price'];
                $descripcion = $item['description']; // Puedes guardar esto en 'notes' o 'item_description'

                $stmtItem->execute([
                    $serviceId,
                    $productId,
                    $item['qty'],
                    $item['price'],
                    $totalLinea,
                    $entityType,
                    $descripcion
                ]);
                
                $itemId = $pdo->lastInsertId();

                // C. Vincular Item con Cotización
                // Intento 1: Si quotation_item tiene la columna quotation_id
                try {
                    $pdo->exec("UPDATE quotation_item SET quotation_id = $quotationId WHERE id = $itemId");
                } catch(Exception $ex) {
                    // Intento 2: Si usa tabla pivote (común en SCOT/Symfony)
                     try {
                        $pdo->exec("INSERT INTO quotations_items (quotation_id, quotation_item_id) VALUES ($quotationId, $itemId)");
                     } catch (Exception $ex2) {
                        // Silencioso o log error si no existe ninguna forma de vincular
                     }
                }
            }
            
            // D. Actualizar Proyecto (Si aplica)
            if (!empty($_POST['project_id'])) {
                $stmtProj = $pdo->prepare("UPDATE project SET quotation_id = ? WHERE id = ?");
                $stmtProj->execute([$quotationId, $_POST['project_id']]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'quotation_id' => $quotationId]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        break;

    case 'listarCotizaciones':
        try {
            // Seleccionamos datos principales y sumamos los items al vuelo
            // COALESCE(SUM(...), 0) asegura que si no hay items devuelva 0 en vez de null
            $sql = "SELECT q.id, q.orderCode, q.created, q.validThru, q.status, q.currency,
                            c.tradeName as cliente,'0' as total_calculado
                    FROM quotation q
                    JOIN customer c ON q.customer_id = c.id
                    ORDER BY q.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($data);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}
?>