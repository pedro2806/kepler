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
    <title>Kepler | Gestión de Prospectos</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Prospectos (Seller Customer)</h1>
                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProspecto">
                            <i class="fas fa-user-plus fa-sm text-white-50"></i> Nuevo Prospecto
                        </button>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3" style="background-color: #1a2a3a;">
                            <h6 class="m-0 font-weight-bold text-white">Cartera de Clientes Potenciales</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaProspectos" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Contacto</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Vendedor (Kepler)</th>
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
    modal nuevo prospecto
    <div class="modal fade" id="modalNuevoProspecto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-left-primary shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Prospecto Kepler Metrología</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoProspecto">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Comercial (Empresa)</label>
                                <input type="text" name="tradeName" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Contacto</label>
                                <input type="text" name="contactName" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Móvil</label>
                                <input type="text" name="mobile" class="form-control">
                            </div>
                        </div>
                        <hr>
                        <p class="text-primary fw-bold small">Ubicación</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Calle</label>
                                <input type="text" name="street" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Ext.</label>
                                <input type="text" name="extNumber" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Int.</label>
                                <input type="text" name="intNumber" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estado</label>
                                <input type="text" name="state" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">C.P.</label>
                                <input type="text" name="zipcode" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vendedor Asignado</label>
                            <select name="user_id" id="slcVendedor" class="form-select" required></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary px-4">Guardar Prospecto</button>
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
            $('#tablaProspectos').DataTable({
                ajax: {
                    url: 'acciones_prospectos.php',
                    type: 'POST',
                    data: { opcion: 'listarProspectos' },
                    dataSrc: ''
                },
                columns: [
                    { data: 'tradeName' },
                    { data: 'contactName' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'vendedor' },
                    {
                        data: 'status',
                        render: function (data) {
                            return `<span class="badge bg-info text-dark">${data}</span>`;
                        }
                    },
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-success" title="Convertir a Cliente" 
                                        onclick="convertirACliente(${row.id}, '${row.tradeName}')">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>`;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            // Cargar vendedores al abrir el modal
                $('#modalNuevoProspecto').on('show.bs.modal', function () {
                    $.post('acciones_prospectos.php', {opcion: 'obtenerVendedores'}, function(data) {
                        let options = '<option value="">Seleccione vendedor...</option>';
                        JSON.parse(data).forEach(v => {
                            options += `<option value="${v.id}">${v.full_name}</option>`;
                        });
                        $('#slcVendedor').html(options);
                    });
                });

                // Guardar Prospecto
                $('#formNuevoProspecto').on('submit', function(e) {
                    e.preventDefault();
                    let datos = $(this).serialize() + '&opcion=guardarProspecto';
                    
                    $.post('acciones_prospectos.php', datos, function(res) {
                        let r = JSON.parse(res);
                        if(r.status === 'success') {
                            Swal.fire("¡Éxito!", "Prospecto registrado en Kepler.", "success");
                            $('#modalNuevoProspecto').modal('hide');
                            $('#tablaProspectos').DataTable().ajax.reload();
                        } else {
                            Swal.fire("Error", r.message, "error");
                        }
                    });
                });
        });

    // Convertir prospecto a cliente
        function convertirACliente(id, empresa) {
            Swal.fire({
                title: '¿Convertir a Cliente?',
                html: `¿Realmente quieres convertir a <strong>${empresa}</strong> en un cliente formal de Kepler?<br><small class="text-muted">Esta acción transferirá los datos a la tabla de clientes oficiales.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a', // Verde éxito
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, convertir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'acciones_prospectos.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { opcion: 'convertirProspecto', id: id },
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire('¡Éxito!', 'El prospecto ahora es un cliente oficial.', 'success');
                                $('#tablaProspectos').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>