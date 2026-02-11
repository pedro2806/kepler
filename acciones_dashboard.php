<?php
session_start();
require_once 'conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit(); }

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {
    case 'obtenerKPIs':
        try {
            $response = [];

            // 1. VENTAS DEL MES (Órdenes de Venta creadas este mes)
            $sqlVentas = "SELECT COALESCE(SUM(total), 0) as total 
                            FROM salesorder 
                            WHERE MONTH(created) = MONTH(CURRENT_DATE()) 
                            AND YEAR(created) = YEAR(CURRENT_DATE())
                            AND status != 'CANCELLED'";
            $stmt = $pdo->query($sqlVentas);
            $response['ventas_mes'] = $stmt->fetchColumn();

            // 2. PIPELINE DE PROYECTOS (Valor total en juego)
            // Sumamos proyectos que no estén ganados ni perdidos (Activos)
            $sqlProj = "SELECT COALESCE(SUM(value), 0) 
                        FROM project 
                        WHERE stage NOT IN ('Closed Won', 'Closed Lost') 
                        AND is_recycled = 0";
            $stmt = $pdo->query($sqlProj);
            $response['pipeline'] = $stmt->fetchColumn();

            // 3. COTIZACIONES PENDIENTES (Enviadas pero no aprobadas aún)
            $sqlCot = "SELECT COUNT(*) FROM quotation WHERE status = 'SENT'";
            $stmt = $pdo->query($sqlCot);
            $response['cotizaciones_pendientes'] = $stmt->fetchColumn();

            // 4. NUEVOS CLIENTES (Este año)
            $sqlCust = "SELECT COUNT(*) FROM customer 
                        WHERE YEAR(created) = YEAR(CURRENT_DATE()) 
                        AND enabled = 1";
            $stmt = $pdo->query($sqlCust);
            $response['clientes_nuevos'] = $stmt->fetchColumn();

            // 5. GRÁFICA: Ventas de los últimos 6 meses
            $labels = [];
            $dataVentas = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $mes = date('Y-m', strtotime("-$i months")); // Ej: 2026-01
                $nombreMes = date('M', strtotime("-$i months"));
                
                $sqlMes = "SELECT COALESCE(SUM(total), 0) FROM salesorder 
                            WHERE DATE_FORMAT(created, '%Y-%m') = '$mes' 
                            AND status != 'CANCELLED'";
                $stmt = $pdo->query($sqlMes);
                
                $labels[] = $nombreMes;
                $dataVentas[] = $stmt->fetchColumn();
            }
            $response['chart_labels'] = $labels;
            $response['chart_data'] = $dataVentas;

            echo json_encode($response);

        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    case 'ultimasCotizaciones':
        // Listado rápido para la tabla del dashboard
        $sql = "SELECT q.id, q.orderCode, c.tradeName, '1000' as total, q.status 
                FROM quotation q 
                JOIN customer c ON q.customer_id = c.id 
                ORDER BY q.id DESC LIMIT 5";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
}
?>