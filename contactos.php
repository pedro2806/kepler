<?php
session_start();
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Directorio de Contactos</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 text-gray-800">Directorio de Contactos</h1>
                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoContacto">
                            <i class="fas fa-user-plus"></i> Nuevo Contacto
                        </button>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-primary">
                            <h6 class="m-0 font-weight-bold text-white">Agenda Kepler</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover" id="tablaContactos" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Puesto / Área</th>
                                        <th>Empresa (Cliente)</th>
                                        <th>Contacto Directo</th>
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
                <div class="container my-auto text-center">
                    <span>Copyright &copy; Kepler Metrología 2026</span>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="modalNuevoContacto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Nuevo Contacto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoContacto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Asignar a Cliente:</label>
                            <select class="form-select" id="slcCliente" name="customer_id" required>
                                <option value="">Buscar empresa...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej. Ing. Juan Pérez">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Puesto</label>
                                <input type="text" name="jobPosition" class="form-control" placeholder="Ej. Gte. Calidad">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Área (Obligatorio)</label>
                                <input type="text" name="jobArea" class="form-control" required placeholder="Ej. Mantenimiento">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="mobile" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <input type="hidden" name="phone" value=""> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Contacto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-left-warning shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Editar / Reasignar Contacto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarContacto">
                    <div class="modal-body">
                        <input type="hidden" name="contact_id" id="edit_contact_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Empresa Asignada (Cliente):</label>
                            <select class="form-select" id="slcClienteEdit" name="customer_id" required>
                                </select>
                            <small class="text-muted">Cambia esto solo si quieres mover el contacto a otra empresa.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Puesto</label>
                                <input type="text" name="jobPosition" id="edit_jobPosition" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" name="jobArea" id="edit_jobArea" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="mobile" id="edit_mobile" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // 1. Inicializar Select2 para búsqueda de clientes
            $('#slcCliente').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNuevoContacto'),
                width: '100%',
                ajax: {
                    url: 'acciones_contacto.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { opcion: 'buscarClientesSelect' }; // En un caso real, podrías enviar params.term para filtrar en SQL
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function(item) {
                                return { id: item.id, text: item.tradeName };
                            })
                        };
                    }
                }
            });

            // 2. Cargar opciones iniciales de clientes (opcional, para que no salga vacío)
            $.post('acciones_contacto.php', {opcion: 'buscarClientesSelect'}, function(data) {
                let d = JSON.parse(data);
                d.forEach(c => {
                    let option = new Option(c.tradeName, c.id, false, false);
                    $('#slcCliente').append(option);
                });
            });

            // 3. DataTable
            $('#tablaContactos').DataTable({
                "ajax": {
                    "url": "acciones_contacto.php",
                    "type": "POST",
                    "data": { "opcion": "listarContactos" },
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "name", "render": function(data) { return `<span class="fw-bold">${data}</span>`; }},
                    { "data": null, "render": function(data, type, row) { 
                        return `<small>${row.jobPosition || ''}</small><br><span class="badge bg-light text-dark border">${row.jobArea || 'N/A'}</span>`; 
                    }},
                    { "data": "empresa" },
                    { "data": null, "render": function(data, type, row) {
                        let html = '';
                        if(row.email) html += `<div><i class="fas fa-envelope text-gray-500"></i> ${row.email}</div>`;
                        if(row.mobile) html += `<div><i class="fas fa-mobile-alt text-gray-500"></i> ${row.mobile}</div>`;
                        return html;
                    }},
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return `<button class="btn btn-sm btn-warning" onclick="abrirModalEditar(${row.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>`;
                        }
                    }
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });

            // 4. Guardar Contacto
            $('#formNuevoContacto').on('submit', function(e) {
                e.preventDefault();
                let datos = $(this).serialize() + '&opcion=guardarContacto';
                $.post('acciones_contacto.php', datos, function(res) {
                    let r = JSON.parse(res);
                    if(r.status === 'success') {
                        Swal.fire("Guardado", "Contacto registrado correctamente en Kepler", "success");
                        $('#modalNuevoContacto').modal('hide');
                        $('#formNuevoContacto')[0].reset();
                        $('#slcCliente').val(null).trigger('change');
                        $('#tablaContactos').DataTable().ajax.reload();
                    } else {
                        Swal.fire("Error", r.message, "error");
                    }
                });
            });
        });

        // Inicializar Select2 en el modal de edición
        $('#slcClienteEdit').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditarContacto'),
            width: '100%',
            ajax: {
                url: 'acciones_contacto.php',
                type: 'POST',
                dataType: 'json',
                data: function (params) { return { opcion: 'buscarClientesSelect' }; },
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return { id: item.id, text: item.tradeName };
                        })
                    };
                }
            }
        });

        function abrirModalEditar(id) {
            $.post('acciones_contacto.php', { opcion: 'obtenerDatosContacto', id: id }, function(res) {
                if(res.status === 'success') {
                    const d = res.data;
                    
                    // Llenar campos de texto
                    $('#edit_contact_id').val(d.id);
                    $('#edit_name').val(d.name);
                    $('#edit_jobPosition').val(d.jobPosition);
                    $('#edit_jobArea').val(d.jobArea);
                    $('#edit_mobile').val(d.mobile);
                    $('#edit_email').val(d.email);

                    // Pre-cargar el Select2 con la empresa actual
                    // Creamos una opción manual para que Select2 la reconozca sin buscar de nuevo
                    var option = new Option(d.empresa_nombre, d.empresa_id, true, true);
                    $('#slcClienteEdit').append(option).trigger('change');

                    $('#modalEditarContacto').modal('show');
                }
            }, 'json');
        }

        // Guardar los cambios
        $('#formEditarContacto').on('submit', function(e) {
            e.preventDefault();
            let datos = $(this).serialize() + '&opcion=actualizarContacto';
            
            $.post('acciones_contacto.php', datos, function(res) {
                if(res.status === 'success') {
                    Swal.fire('¡Actualizado!', 'El contacto ha sido modificado y/o reasignado.', 'success');
                    $('#modalEditarContacto').modal('hide');
                    $('#tablaContactos').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        });
    </script>
</body>
</html>