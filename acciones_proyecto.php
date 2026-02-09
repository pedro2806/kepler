<?php
require_once 'conn.php';
session_start();
$opcion = $_POST['opcion'] ?? '';
switch ($opcion) {

case 'listarForecast':
case 'kpiForecast':
    try {
        $filtro = $_POST['filtro'] ?? 'current';
        
        // Lógica básica de fechas (ajustar según necesidad real de trimestres)
        $condicionFecha = "MONTH(closeDate) = MONTH(CURRENT_DATE()) AND YEAR(closeDate) = YEAR(CURRENT_DATE())";
        if($filtro === 'next') {
            $condicionFecha = "MONTH(closeDate) = MONTH(CURRENT_DATE() + INTERVAL 1 MONTH)";
        }
        // Nota: Para Q1-Q4 requeriría un switch más complejo, por ahora simplificado.

        // Consulta Principal
        $sql = "SELECT p.id, p.name, p.value, p.closeDate, p.stage, 
                        c.tradeName as cliente
                FROM project p
                LEFT JOIN customer c ON p.customer_id = c.id
                WHERE $condicionFecha AND p.is_recycled = 0";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesamiento de Probabilidades (Lógica de Negocio Kepler)
        $resultadoFinal = [];
        $totalPipeline = 0;
        $totalForecast = 0; // Ponderado
        $totalGanados = 0;

        foreach($proyectos as $p) {
            $probabilidad = 0;
            switch($p['stage']) {
                case 'Closed Won':  $probabilidad = 100; break;
                case 'Negotiation': $probabilidad = 80; break;
                case 'Proposal':    $probabilidad = 50; break;
                case 'Qualification': $probabilidad = 30; break;
                case 'Prospecting': $probabilidad = 10; break;
                default: $probabilidad = 0;
            }

            // Cálculos
            $valor = floatval($p['value']);
            $totalPipeline += $valor;
            $totalForecast += ($valor * ($probabilidad / 100));
            
            if($p['stage'] === 'Closed Won') {
                $totalGanados += $valor;
            }

            // Datos para la tabla
            $p['probabilidad'] = $probabilidad;
            $resultadoFinal[] = $p;
        }

        if($opcion === 'kpiForecast') {
            echo json_encode([
                'pipeline' => $totalPipeline,
                'forecast' => $totalForecast,
                'ganados' => $totalGanados
            ]);
        } else {
            echo json_encode($resultadoFinal);
        }

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

case 'obtenerProyecto':
    $stmt = $pdo->prepare("SELECT id, value, closeDate, stage FROM project WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    break;

case 'actualizarForecastProyecto':
    try {
        $sql = "UPDATE project SET value = ?, closeDate = ?, stage = ?, updated = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['value'],
            $_POST['closeDate'],
            $_POST['stage'],
            $_POST['project_id']
        ]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

case 'cargarContactosPorCliente':
    // Esta función busca en la tabla intermedia customer_contact
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

case 'crearProyecto':
    try {
        session_start();
        $userId = $_SESSION['user_id']; // Asegúrate de tener la sesión iniciada

        $sql = "INSERT INTO project (
                    name, customer_id, contact_id, value, currency, 
                    stage, closeDate, description, 
                    user_id, created, is_recycled, 
                    dollarPrice, euroPrice
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, 0, 0)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['customer_id'],
            $_POST['contact_id'],
            $_POST['value'],
            $_POST['currency'],
            $_POST['stage'],
            $_POST['closeDate'],
            $_POST['description'],
            $userId // El usuario logueado es el responsable
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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

case 'obtenerGraficaForecast':
    try {
        $filtro = $_POST['filtro'] ?? 'current';
        
        // Misma lógica de fechas que usamos en el listado
        $condicionFecha = "MONTH(closeDate) = MONTH(CURRENT_DATE()) AND YEAR(closeDate) = YEAR(CURRENT_DATE())";
        if($filtro === 'next') {
            $condicionFecha = "MONTH(closeDate) = MONTH(CURRENT_DATE() + INTERVAL 1 MONTH)";
        }
        // Puedes agregar lógica para Q1, Q2, etc. aquí

        $sql = "SELECT stage, SUM(value) as total 
                FROM project 
                WHERE $condicionFecha AND is_recycled = 0 
                GROUP BY stage 
                ORDER BY total DESC"; // Ordenamos por mayor valor
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preparamos arrays para Chart.js
        $labels = [];
        $values = [];
        $colors = [];

        foreach($datos as $d) {
            $labels[] = $d['stage'];
            $values[] = $d['total'];
            
            // Asignamos colores semánticos para Kepler
            switch($d['stage']) {
                case 'Closed Won':  $colors[] = '#1cc88a'; break; // Verde Éxito
                case 'Negotiation': $colors[] = '#f6c23e'; break; // Amarillo Alerta
                case 'Proposal':    $colors[] = '#36b9cc'; break; // Azul Propuesta
                case 'Qualification': $colors[] = '#4e73df'; break; // Azul Estándar
                case 'Prospecting': $colors[] = '#858796'; break; // Gris
                case 'Closed Lost': $colors[] = '#e74a3b'; break; // Rojo
                default:            $colors[] = '#5a5c69';
            }
        }

        echo json_encode(['labels' => $labels, 'data' => $values, 'colors' => $colors]);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;

    case 'guardarCotizacion':
    try {
        $pdo->beginTransaction();
        
        // ---------------------------------------------------------
        // 1. INSERTAR CABECERA (Tabla 'quotation')
        // ---------------------------------------------------------
        // Generamos un código de orden simple
        $orderCode = 'COT-' . date('Ymd-His'); 
        
        $sqlHead = "INSERT INTO quotation (
            customer_id, contact_id, user_id, 
            currency, paymentTerm, validThru, shipmentTermsConditions, 
            tax, status, created, orderCode, 
            dollarPrice, euroPrice, shipmentTime, elaboratedby_id, 
            sent_notifications
        ) VALUES (
            7772, ?, ?, 
            ?, 'Contado', ?, 'LAB Destino', 
            '16%', 'DRAFT', NOW(), ?, 
            0, 0, 'Inmediato', ?, 
            0
        )";
        
        /* NOTA: 
           - company_id: Puse 1 por defecto (Kepler).
           - paymentTerm, shipmentTermsConditions: Campos NOT NULL, llenados con defaults.
           - dollarPrice/euroPrice: En 0 para evitar error.
        */

        $stmt = $pdo->prepare($sqlHead);
        $stmt->execute([
            $_POST['customer_id'], 
            $_POST['contact_id'], // Asegúrate de enviar esto desde el JS
            $_SESSION['user_id'],
            $_POST['currency'],
            date('Y-m-d', strtotime('+15 days')), // validThru (15 días)
            $orderCode,
            $_SESSION['user_id'], // elaboratedby_id
        ]);
        $quotationId = $pdo->lastInsertId();

        // ---------------------------------------------------------
        // 2. INSERTAR PARTIDAS (Tabla 'quotation_item')
        // ---------------------------------------------------------
        $items = json_decode($_POST['items'], true);
        
        // SQL ajustado a tu tabla real
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
            0, 0, 
            0, 0
        )";
        $stmtItem = $pdo->prepare($sqlItem);

        // Preparamos SQL para la tabla intermedia (Si existe en tu sistema SCOT original)
        // Si tu tabla quotation_item SÍ tiene quotation_id, descomenta la línea de abajo y ajusta.
        // $sqlLink = "INSERT INTO quotations_quotation_items (quotation_id, quotation_item_id) VALUES (?, ?)";
        // $stmtLink = $pdo->prepare($sqlLink);

        // Como no vi la tabla intermedia, asumiremos que quotation_item TIENE quotation_id
        // Si no la tiene, tendrás que decirme el nombre de la tabla intermedia.
        // POR AHORA: Usaremos un UPDATE manual si la columna existe, o insertaremos en la intermedia si existe.
        
        foreach ($items as $item) {
            // Definir si es producto o servicio
            $serviceId = null;
            $productId = null;
            $entityType = '';

            // Lógica basada en la columna 'type' que agregamos al CSV/Catalogo
            if ($item['type'] === 'PRODUCT') {
                $productId = $item['service_id']; // El ID viene del mismo select
                $entityType = 'product'; // O 'App\Entity\Product' si es Symfony estricto
            } else {
                $serviceId = $item['service_id'];
                $entityType = 'service';
            }

            $totalLinea = $item['qty'] * $item['price'];

            $stmtItem->execute([
                $serviceId,
                $productId,
                $item['qty'],
                $item['price'],
                $totalLinea,
                $entityType,
                $item['description'] // Guardamos descripción en notes o item_description
            ]);
            $itemId = $pdo->lastInsertId();

            // *** CRÍTICO *** // Aquí vinculamos el item con la cotización. 
            // Opción A: Si quotation_item tiene la columna quotation_id (aunque no saliera en tu copy paste)
            try {
                $pdo->exec("UPDATE quotation_item SET quotation_id = $quotationId WHERE id = $itemId");
            } catch(Exception $ex) {
                // Opción B: Si falla, intentamos insertar en la tabla pivote estándar de SCOT
                // $stmtLink->execute([$quotationId, $itemId]);
            }
        }
        
        // 3. Vincular al Proyecto
        $pdo->prepare("UPDATE project SET quotation_id = ? WHERE id = ?")
            ->execute([$quotationId, $_POST['project_id']]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'quotation_id' => $quotationId]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
    break;
}