<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Historial de Cotizaciones</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sb-admin-2.min.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Historial de Cotizaciones</h1>
                        <a href="cotizador_rapido.php" class="btn btn-primary shadow-sm">
                            <i class="fas fa-plus"></i> Nueva Cotización
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary active" onclick="filtrarEstatus('TODOS')">Todas</button>
                                <button type="button" class="btn btn-outline-warning" onclick="filtrarEstatus('DRAFT')">Borradores</button>
                                <button type="button" class="btn btn-outline-success" onclick="filtrarEstatus('SENT')">Enviadas</button>
                                <button type="button" class="btn btn-outline-info" onclick="filtrarEstatus('APPROVED')">Aprobadas</button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-dark">
                            <h6 class="m-0 font-weight-bold text-white">Listado de Folios Emitidos</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaCotizaciones" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Vigencia</th>
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
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        var table;
        $(document).ready(function() {
            table = $('#tablaCotizaciones').DataTable({
                "ajax": {
                    "url": "acciones_cotizacion.php",
                    "type": "POST",
                    "data": { "opcion": "listarCotizaciones" }, // Nueva función en el backend
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "orderCode", "render": function(data) { return `<strong>${data}</strong>`; } },
                    { "data": "cliente" },
                    { "data": "created", "render": function(data) { return data.split(' ')[0]; } }, // Solo fecha
                    { "data": "validThru", "render": function(data) { 
                        // Marcar en rojo si ya venció
                        let fecha = new Date(data);
                        let hoy = new Date();
                        let color = fecha < hoy ? 'text-danger fw-bold' : '';
                        return `<span class="${color}">${data.split(' ')[0]}</span>`;
                    }},
                    { "data": null, "render": function(data, type, row) {
                        // Calcular total aproximado sumando items (o traerlo calculado desde SQL)
                        return `<strong>${row.currency} $${parseFloat(row.total_calculado).toLocaleString('es-MX', {minimumFractionDigits: 2})}</strong>`;
                    }},
                    { "data": "status", "render": function(data) {
                        let badge = 'secondary';
                        let texto = data;
                        if(data === 'DRAFT') { badge = 'warning text-dark'; texto = 'Borrador'; }
                        if(data === 'SENT') { badge = 'primary'; texto = 'Enviada'; }
                        if(data === 'APPROVED') { badge = 'success'; texto = 'Aprobada'; }
                        if(data === 'CANCELED') { badge = 'danger'; texto = 'Cancelada'; }
                        return `<span class="badge bg-${badge}">${texto}</span>`;
                    }},
                    { "data": null, "render": function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <a href="exportar_cotizacion.php?id=${row.id}" target="_blank" class="btn btn-sm btn-danger" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button class="btn btn-sm btn-info" onclick="verDetalles(${row.id})" title="Ver Items">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="convertirAOrden(${row.id})" title="Aprobar / Convertir a OS">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        `;
                    }}
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                "order": [[ 0, "desc" ]] // Ordenar por folio descendente
            });
        });

        function filtrarEstatus(estatus) {
            if(estatus === 'TODOS') {
                table.column(5).search('').draw();
            } else {
                // Buscamos el texto exacto (Borrador, Enviada, etc) o el código según lo que renderizamos
                // Como el render cambia el texto, buscamos el texto visual
                let term = '';
                if(estatus === 'DRAFT') term = 'Borrador';
                if(estatus === 'SENT') term = 'Enviada';
                if(estatus === 'APPROVED') term = 'Aprobada';
                table.column(5).search(term).draw();
            }
            
            // Actualizar clases de botones
            $('.btn-group .btn').removeClass('active');
            $(event.target).addClass('active');
        }

        function convertirAOrden(id) {
    Swal.fire({
        title: 'Aprobar Cotización',
        html: `
            <p>Al convertir esta cotización, se generará una <strong>Orden de Venta</strong>.</p>
            <label class="form-label fw-bold">Ingrese No. Orden de Compra (PO) del Cliente:</label>
            <input type="text" id="txtPO" class="form-control" placeholder="Ej. PO-2026-050">
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Generar Orden',
        confirmButtonColor: '#1cc88a',
        preConfirm: () => {
            const po = Swal.getPopup().querySelector('#txtPO').value;
            if (!po) {
                Swal.showValidationMessage('Debe ingresar el número de PO del cliente');
            }
            return { po: po }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('acciones_ventas.php', { 
                opcion: 'convertirCotizacion', 
                quotation_id: id,
                po_number: result.value.po 
            }, function(res) {
                let r = JSON.parse(res);
                if(r.status === 'success') {
                    Swal.fire('¡Éxito!', 'La Orden de Venta ha sido creada.', 'success');
                    $('#tablaCotizaciones').DataTable().ajax.reload(); // Recargar tabla
                } else {
                    Swal.fire('Error', r.message, 'error');
                }
            });
        }
    });
}
    </script>
</body>
</html>