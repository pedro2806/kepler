<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kepler | Seguimiento de Actividades</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div class="modal fade" id="modalEstatus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-left-warning shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Estatus de Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id_actividad">
                    <label class="form-label fw-bold">Nuevo Estatus para Kepler:</label>
                    <select id="edit_slcEstatus" class="form-select">
                        <option value="Pendientedeinformacion">Pendiente de información</option>
                        <option value="Programadasinconfirmar">Programada sin confirmar</option>
                        <option value="Servicioconfirmadoparasuejecucion">Servicio confirmado para su ejecución</option>
                        <option value="Fechareservadasininformación">Fecha reservada sin información</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarNuevoEstatus()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Seguimiento de Actividades - Kepler</h1>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Listado General de Servicios</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaSeguimiento" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha Planeada</th>
                                            <th>Ingeniero</th>
                                            <th>Descripción (Cliente / OT)</th>
                                            <th>Duración</th>
                                            <th>Estatus</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white shadow-sm">
                <div class="container my-auto text-center">
                    <span>Copyright &copy; Kepler Metrología 2026</span>
                </div>
            </footer>
        </div>
    </div>


    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-history"></i> Historial de Cambios - Kepler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Detalle del Cambio</th>
                            </tr>
                        </thead>
                        <tbody id="listaHistorial"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaSeguimiento').DataTable({
                "ajax": {
                    "url": "acciones_solicitud.php",
                    "type": "POST",
                    "data": { "opcion": "listarActividades" },
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "id" },
                    { "data": "plannedDate" },
                    { "data": "ingeniero" },
                    { "data": "description" },
                    { "data": "duration", "render": function(data) { return data + ' hrs'; } },
                    { "data": "status", "render": function(data) {
                        let clase = "badge bg-secondary";
                        if(data === 'Servicioconfirmadoparasuejecucion') clase = "badge bg-success";
                        if(data === 'Pendientedeinformacion') clase = "badge bg-danger";
                        if(data === 'Programadasinconfirmar') clase = "badge bg-warning text-dark";
                        return `<span class="${clase}">${data}</span>`;
                    }},
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-warning" title="Editar Estatus" onclick="abrirModalEstatus(${row.id}, '${row.status}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" title="Ver Historial" onclick="verHistorial(${row.id})">
                                    <i class="fas fa-history"></i>
                                </button>`;
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "order": [[1, "desc"]]
            });
        });

        function abrirModalEstatus(id, statusActual) {
    $('#edit_id_actividad').val(id);
    $('#edit_slcEstatus').val(statusActual);
    $('#modalEstatus').modal('show');
}

function guardarNuevoEstatus() {
    const id = $('#edit_id_actividad').val();
    const nuevoEstatus = $('#edit_slcEstatus').val();

    $.ajax({
        url: 'acciones_solicitud.php',
        type: 'POST',
        data: {
            opcion: 'actualizarEstatus',
            id: id,
            estatus: nuevoEstatus
        },
        success: function(response) {
            Swal.fire("¡Actualizado!", "El estatus de Kepler ha cambiado.", "success");
            $('#modalEstatus').modal('hide');
            $('#tablaSeguimiento').DataTable().ajax.reload(); // Recarga la tabla sin refrescar la página
        }
    });
}

function verHistorial(id) {
    $.ajax({
        url: 'acciones_solicitud.php',
        type: 'POST',
        data: { opcion: 'verHistorial', id: id },
        dataType: 'json',
        success: function(data) {
            let html = '';
            if (data.length > 0) {
                data.forEach(item => {
                    html += `<tr>
                        <td class="small">${item.fecha}</td>
                        <td class="fw-bold">${item.usuario}</td>
                        <td>${item.detalle}</td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center">No hay cambios registrados aún.</td></tr>';
            }
            $('#listaHistorial').html(html);
            $('#modalHistorial').modal('show');
        }
    });
}
    </script>
</body>
</html>