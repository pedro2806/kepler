<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Órdenes de Venta</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Órdenes de Venta (Aprobadas)</h1>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success">
                            <h6 class="m-0 font-weight-bold text-white">Pedidos en Curso</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="tablaOrdenes" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID OV</th>
                                        <th>Cliente</th>
                                        <th>Orden Compra (PO)</th>
                                        <th>Origen (Cotización)</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white shadow-sm">
                <div class="container my-auto text-center"><span>Copyright &copy; Kepler Metrología 2026</span></div>
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaOrdenes').DataTable({
                "ajax": {
                    "url": "acciones_ventas.php",
                    "type": "POST",
                    "data": { "opcion": "listarOrdenes" },
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "id", "render": function(data){ return `<b class="text-success">OV-${data}</b>`; } },
                    { "data": "cliente" },
                    { "data": "po_number", "render": function(data){ 
                        return data ? `<span class="badge bg-light text-dark border border-dark">${data}</span>` : '<span class="text-muted small">Pendiente</span>'; 
                    }},
                    { "data": "folio_cotizacion" },
                    { "data": "created", "render": function(data){ return data.split(' ')[0]; } },
                    { "data": null, "render": function(data, type, row) {
                        return row.currency + ' $' + parseFloat(row.total).toLocaleString('es-MX');
                    }},
                    { "data": "status", "render": function(data) {
                        return data === 'OPEN' ? '<span class="badge bg-primary">Abierta</span>' : '<span class="badge bg-secondary">'+data+'</span>';
                    }},
                    { "data": null, "render": function(data, type, row) {
                        return `<button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
                    }}
                ],
                "order": [[ 0, "desc" ]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });
        });
    </script>
</body>
</html>