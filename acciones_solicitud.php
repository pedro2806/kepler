<?php
require_once 'conn.php';
session_start();

$opcion = $_POST['opcion'] ?? '';

switch ($opcion) {
    case 'empleados':
        try {
            // Consultamos a los ingenieros de la tabla fos_user
            // Filtramos por enabled para solo mostrar personal activo
            $stmt = $pdo->prepare("SELECT employee_number as noEmpleado, full_name as nombre 
                                FROM fos_user 
                                WHERE enabled = 1 AND full_name IS NOT NULL 
                                ORDER BY full_name ASC");
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            echo json_encode($resultados);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'consultarCiudades':
        try {
            // Obtenemos ciudades y estados únicos de la tabla address
            $stmt = $pdo->prepare("SELECT DISTINCT city as ciudad, state as estado 
                                FROM address 
                                WHERE city IS NOT NULL AND state IS NOT NULL 
                                ORDER BY state, city ASC");
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            echo json_encode($resultados);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'generarSolicitud':
        try {
            // Recibimos los datos del formulario de Kepler
            $responsable  = $_POST['responsable'];
            $responsable2 = $_POST['responsable2'];
            $responsable3 = $_POST['responsable3'];
            $area         = $_POST['area'];
            $ciudad       = $_POST['ciudad'];
            $cliente      = $_POST['cliente'];
            $ot           = $_POST['ot'];
            $fecha        = $_POST['fechaPlaneada'];
            $duracion     = $_POST['duracion'];
            $viaje        = $_POST['duracionViaje'];
            $estatus      = $_POST['estatus'];
            $comentarios  = $_POST['comentarios'];

            // Insertamos en la tabla activity (ajustado a tu estructura SQL)
            $sql = "INSERT INTO activity (
                        user_id, 
                        plannedDate, 
                        duration, 
                        tripTime, 
                        status, 
                        description,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            
            // Nota: Aquí guardamos la descripción combinando cliente y OT para Kepler
            $descripcionLarga = "Cliente: $cliente | OT: $ot | Área: $area | Ciudad: $ciudad | Obs: $comentarios";
            
            $stmt->execute([
                $responsable,
                $fecha,
                $duracion,
                $viaje,
                $estatus,
                $descripcionLarga
            ]);

            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

        // Dentro del switch ($opcion) en acciones_solicitud.php

    case 'listarActividades':
        try {
            // Unimos activity con fos_user para mostrar el nombre del ingeniero
            $sql = "SELECT a.id, a.plannedDate, u.full_name as ingeniero, a.description, a.duration, a.status 
                    FROM activity a
                    LEFT JOIN fos_user u ON a.user_id = u.employee_number
                    ORDER BY a.plannedDate DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            
            echo json_encode($resultados);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

case 'actualizarEstatus':
    try {
        $id = $_POST['id'];
        $nuevoEstatus = $_POST['estatus'];
        $usuarioId = $_SESSION['user_id']; // ID interno de la tabla fos_user

        // 1. Obtener el estatus anterior para la descripción del log
        $stmtOld = $pdo->prepare("SELECT status FROM activity WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldStatus = $stmtOld->fetchColumn();

        // 2. Actualizar el estatus en la tabla principal
        $sql = "UPDATE activity SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevoEstatus, $id]);

        // 3. Registrar en el historial (activity_log)
        $logSql = "INSERT INTO activity_log (activity_id, createdby_id, created, action, description, origin) 
                    VALUES (?, ?, NOW(), 'UPDATE_STATUS', ?, 'CRM_KEPLER')";
        $logStmt = $pdo->prepare($logSql);
        $descripcionLog = "Cambio de estatus de '$oldStatus' a '$nuevoEstatus'";
        $logStmt->execute([$id, $usuarioId, $descripcionLog]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    break;

case 'verHistorial':
    try {
        $id = $_POST['id'];
        // Consultamos el log uniendo con fos_user para ver quién hizo el cambio
        $sql = "SELECT l.created as fecha, u.full_name as usuario, l.description as detalle
                FROM activity_log l
                JOIN fos_user u ON l.createdby_id = u.id
                WHERE l.activity_id = ?
                ORDER BY l.created DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll();
        echo json_encode($historial);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    break;
}