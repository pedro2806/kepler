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
    <title>Kepler | Catálogo de Clientes</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Catálogo de Clientes Oficiales</h1>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-dark">
                            <h6 class="m-0 font-weight-bold text-white">Clientes Activos en Kepler</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaClientes" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre / Razón Social</th>
                                            <th>Nombre Comercial</th>
                                            <th>Fecha de Alta</th>
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
                <div class="container my-auto text-center">
                    <span>Copyright &copy; Kepler Metrología 2026</span>
                </div>
            </footer>
        </div>
    </div>


    <div class="modal fade" id="modalDireccion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-left-info shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloDireccion">Ubicación del Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="cuerpoDireccion">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-left-warning shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente Kepler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarCliente">
                    <div class="modal-body">
                        <input type="hidden" name="cliente_id" id="edit_cliente_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Razón Social</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Comercial</label>
                                <input type="text" name="tradeName" id="edit_tradeName" class="form-control" required>
                            </div>
                        </div>
                        <hr>
                        <p class="text-primary fw-bold">Actualizar Dirección Fiscal</p>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Calle</label>
                                <input type="text" name="street" id="edit_street" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" id="edit_city" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                    </div>
                </form>
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
            $('#tablaClientes').DataTable({
                "ajax": {
                    "url": "acciones_clientes.php",
                    "type": "POST",
                    "data": { "opcion": "listarClientesOficiales" },
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "id" },
                    { "data": "legalName" },
                    { "data": "tradeName" },
                    { "data": "created" },
                    { "data": "enabled", "render": function(data) {
                        return `<span class="badge bg-success">${data == 1 ? 'Activo' : 'Inactivo'}</span>`;
                    }},
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-info" onclick="verDireccion(${row.id}, '${row.tradeName}')">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="abrirModalEdicion(${row.id})">
                                    <i class="fas fa-edit"></i>
                                </button>`;
                        }
                    }
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                "order": [[0, "desc"]]
            });
        });

        function verDireccion(clienteId, nombre) {
            $('#tituloDireccion').text('Ubicación: ' + nombre);
            $('#cuerpoDireccion').html('<div class="text-center"><div class="spinner-border text-info"></div></div>');
            $('#modalDireccion').modal('show');

            $.ajax({
                url: 'acciones_clientes.php',
                type: 'POST',
                data: { opcion: 'obtenerDireccionCliente', id: clienteId },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        let html = `
                            <p><strong>Calle:</strong> ${res.data.street || 'N/A'}</p>
                            <p><strong>Número:</strong> Ext. ${res.data.extNumber || 'S/N'} | Int. ${res.data.intNumber || 'S/N'}</p>
                            <p><strong>Colonia:</strong> ${res.data.neighborhood || 'N/A'}</p>
                            <p><strong>Ciudad:</strong> ${res.data.city || 'N/A'}, ${res.data.state || ''}</p>
                            <p><strong>C.P.:</strong> ${res.data.zipcode || 'N/A'}</p>
                        `;
                        $('#cuerpoDireccion').html(html);
                    } else {
                        $('#cuerpoDireccion').html('<div class="alert alert-warning">No se encontró dirección registrada.</div>');
                    }
                }
            });
        }

        function abrirModalEdicion(id) {
            $.post('acciones_clientes.php', { opcion: 'obtenerDatosCompletosCliente', id: id }, function(res) {
                if(res.status === 'success') {
                    $('#edit_cliente_id').val(res.data.id);
                    $('#edit_name').val(res.data.name);
                    $('#edit_tradeName').val(res.data.tradeName);
                    $('#edit_street').val(res.data.street);
                    $('#edit_city').val(res.data.city);
                    $('#modalEditarCliente').modal('show');
                }
            }, 'json');
        }

        $('#formEditarCliente').on('submit', function(e) {
            e.preventDefault();
            const datos = $(this).serialize() + '&opcion=actualizarCliente';
            $.post('acciones_clientes.php', datos, function(res) {
                if(res.status === 'success') {
                    Swal.fire('¡Actualizado!', 'Los datos de Kepler han sido actualizados.', 'success');
                    $('#modalEditarCliente').modal('hide');
                    $('#tablaClientes').DataTable().ajax.reload();
                }
            }, 'json');
        });
    </script>
</body>
</html>