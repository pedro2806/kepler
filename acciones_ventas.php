<?php
session_start();
require_once 'conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error']); exit(); }

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {

    // 1. LISTAR ÓRDENES DE VENTA
    case 'listarOrdenes':
        try {
            $sql = "SELECT so.id, so.po_number, so.created, so.status, so.total, so.currency,
                           c.tradeName as cliente,
                           q.orderCode as folio_cotizacion
                    FROM salesorder so
                    JOIN customer c ON so.customer_id = c.id
                    LEFT JOIN quotation q ON so.quotation_id = q.id
                    ORDER BY so.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        break;

    // 2. CONVERTIR COTIZACIÓN A ORDEN DE VENTA
    case 'convertirCotizacion':
        try {
            $pdo->beginTransaction();
            
            $cotizacionId = $_POST['quotation_id'];
            $poNumber = $_POST['po_number']; // El cliente nos mandó su PO
            
            // A. Obtener datos de la cotización
            $stmtQ = $pdo->prepare("SELECT * FROM quotation WHERE id = ?");
            $stmtQ->execute([$cotizacionId]);
            $qData = $stmtQ->fetch(PDO::FETCH_ASSOC);
            
            if(!$qData) throw new Exception("Cotización no encontrada");

            // B. Calcular total real sumando items (si no está guardado en cabecera)
            $stmtSum = $pdo->prepare("SELECT SUM(total) FROM quotation_item WHERE quotation_id = ?");
            $stmtSum->execute([$cotizacionId]);
            $total = $stmtSum->fetchColumn() ?: 0;

            // C. Insertar en SalesOrder
            $sqlSO = "INSERT INTO salesorder (
                        quotation_id, customer_id, contact_id, user_id, 
                        po_number, status, total, currency, created
                      ) VALUES (?, ?, ?, ?, ?, 'OPEN', ?, ?, NOW())";
            $stmtSO = $pdo->prepare($sqlSO);
            $stmtSO->execute([
                $cotizacionId,
                $qData['customer_id'],
                $qData['contact_id'],
                $_SESSION['user_id'],
                $poNumber,
                $total,
                $qData['currency']
            ]);
            $salesOrderId = $pdo->lastInsertId();

            // D. Copiar Items de Cotización a Orden
            $stmtItems = $pdo->prepare("SELECT * FROM quotation_item WHERE quotation_id = ?");
            $stmtItems->execute([$cotizacionId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            $sqlInsItem = "INSERT INTO salesorder_item (
                                salesorder_id, service_id, product_id, qty, price, total, description, type
                           ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsItem = $pdo->prepare($sqlInsItem);

            foreach($items as $row) {
                // Mapear campos (ajusta si tus nombres de columna varían)
                $tipo = $row['entityType'] ?? 'SERVICE'; 
                $desc = $row['item_description'] ?? $row['notes']; 
                
                $stmtInsItem->execute([
                    $salesOrderId,
                    $row['service_id'],
                    $row['product_id'],
                    $row['orderedQty'],
                    $row['price'],
                    $row['total'],
                    $desc,
                    $tipo
                ]);
            }

            // E. Actualizar estatus de la Cotización a APROBADA
            $pdo->prepare("UPDATE quotation SET status = 'APPROVED' WHERE id = ?")
                ->execute([$cotizacionId]);

            $pdo->commit();
            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
}
?>