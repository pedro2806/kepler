<?php
session_start();
// Verificar sesión activa
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
    <title>Kepler | Catálogo de Servicios</title>

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Catálogo de Productos y Servicios</h1>
                        <div>
                            <button class="btn btn-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#modalImportarCSV">
                                <i class="fas fa-file-csv"></i> Importar Masivo
                            </button>
                            <button class="btn btn-primary shadow-sm" onclick="abrirModalServicio()">
                                <i class="fas fa-plus"></i> Nuevo Servicio
                            </button>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-primary">
                            <h6 class="m-0 font-weight-bold text-white">Listado General (Kepler)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaServicios" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kepler Code</th>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Precio Lista</th>
                                            <th>Moneda</th>
                                            <th>Estatus</th>
                                            <th style="width: 100px;">Acciones</th>
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
        <div class="modal fade" id="modalServicio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-left-primary shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tituloModalServicio">Nuevo Servicio</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formServicio">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="svc_id">
                            
                            <input type="hidden" name="is_recurring" value="0">
                            <input type="hidden" name="hasCommission" value="0">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-primary">Kepler Code</label>
                                    <input type="text" name="keplerCode" id="svc_keplerCode" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tipo de Ítem</label>
                                    <select name="type" id="svc_type" class="form-select" required>
                                        <option value="SERVICE">Servicio (Calibración/Mano de Obra)</option>
                                        <option value="PRODUCT">Producto (Físico/Refacción)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Estatus</label>
                                    <select name="status" id="svc_status" class="form-select">
                                        <option value="ACTIVE">Activo</option>
                                        <option value="INACTIVE">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción del Servicio / Equipo</label>
                                <textarea name="description" id="svc_description" class="form-control" rows="3" required placeholder="Descripción detallada para la cotización..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Precio Unitario</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" id="svc_price" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Moneda</label>
                                    <select name="currency" id="svc_currency" class="form-select">
                                        <option value="MXN">Pesos (MXN)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Servicio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalImportarCSV" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-left-success shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Carga Masiva de Servicios</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formImportarCSV" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <small>
                                    <strong>Formato requerido (.csv):</strong><br>
                                    Col 1: Kepler Code (Único)<br>
                                    Col 2: Descripción<br>
                                    Col 3: Precio (Numérico)<br>
                                    Col 4: Moneda (MXN o USD)<br>
                                    <strong class="text-primary">Col 5: Tipo (Escribir "Producto" o "Servicio")</strong>
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Seleccionar Archivo</label>
                                <input type="file" name="archivoCSV" class="form-control" accept=".csv" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Subir y Procesar
                            </button>
                        </div>
                    </form>
                </div>
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
            // 1. Inicializar DataTable
            var table = $('#tablaServicios').DataTable({
                "ajax": {
                    "url": "acciones_servicios.php",
                    "type": "POST",
                    "data": { "opcion": "listarServicios" },
                    "dataSrc": ""
                },
                "columns": [
                    { 
                        "data": "keplerCode", // Busca el alias definido en el PHP
                        "render": function(data) { 
                            return `<span class="fw-bold text-primary">${data}</span>`; 
                        } 
                    },
                    { "data": "type", "render": function(data) {
                        if (data === 'PRODUCT') {
                            return '<i class="fas fa-box text-warning"></i> Producto';
                        } else {
                            return '<i class="fas fa-tools text-info"></i> Servicio';
                        }
                    }},
                    { "data": "description" },
                    { 
                        "data": "price", 
                        "render": $.fn.dataTable.render.number(',', '.', 2, '$') 
                    },
                    { "data": "currency" },
                    { 
                        "data": "status", 
                        "render": function(data) {
                            if(data === 'ACTIVE') {
                                return '<span class="badge bg-success">Activo</span>';
                            } else {
                                return '<span class="badge bg-secondary">Inactivo</span>';
                            }
                        }
                    },
                    { 
                        "data": null, 
                        "render": function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-warning shadow-sm" title="Editar" onclick="editarServicio(${row.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            `;
                        } 
                    }
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                "order": [[0, "asc"]] // Ordenar por Kepler Code
            });

            // 2. Manejo del Formulario (Guardar)
            $('#formServicio').on('submit', function(e) {
                e.preventDefault();
                
                // Serializamos datos + opcion backend
                let datos = $(this).serialize() + '&opcion=guardarServicio';
                
                $.post('acciones_servicios.php', datos, function(res) {
                    try {
                        let r = JSON.parse(res);
                        if(r.status === 'success') {
                            Swal.fire({
                                title: "¡Guardado!",
                                text: "El servicio se ha actualizado en el catálogo Kepler.",
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#modalServicio').modal('hide');
                            table.ajax.reload(); // Recargar tabla sin refrescar página
                        } else {
                            Swal.fire("Error", r.message, "error");
                        }
                    } catch (error) {
                        console.error(res);
                        Swal.fire("Error crítico", "Respuesta inesperada del servidor", "error");
                    }
                });
            });

            $('#formImportarCSV').on('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                formData.append('opcion', 'importarServiciosCSV');

                // Mostrar alerta de "Cargando..."
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Estamos cargando tus productos al catálogo Kepler.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'acciones_solicitud.php',
                    type: 'POST',
                    data: formData,
                    contentType: false, // Importante para archivos
                    processData: false, // Importante para archivos
                    success: function(res) {
                        let r = JSON.parse(res);
                        if(r.status === 'success') {
                            Swal.fire("Reporte de Carga", r.message, "success");
                            $('#modalImportarCSV').modal('hide');
                            $('#formImportarCSV')[0].reset();
                            $('#tablaServicios').DataTable().ajax.reload();
                        } else {
                            Swal.fire("Error", r.message, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Fallo en la comunicación con el servidor.", "error");
                    }
                });
            });
        });

        // 3. Función para abrir modal en modo "Nuevo"
        function abrirModalServicio() {
            $('#formServicio')[0].reset();
            $('#svc_id').val(''); // Limpiar ID para que el backend sepa que es INSERT
            $('#tituloModalServicio').text('Nuevo Servicio Kepler');
            $('#modalServicio').modal('show');
        }

        // 4. Función para abrir modal en modo "Editar"
        function editarServicio(id) {
            $.post('acciones_servicios.php', { opcion: 'obtenerServicio', id: id }, function(res) {
                try {
                    let d = JSON.parse(res);
                    $('#svc_id').val(d.id);
                    
                    // Aquí cargamos el valor que viene de BD (messCode) en el input visual (keplerCode)
                    $('#svc_keplerCode').val(d.keplerCode); 
                    
                    $('#svc_description').val(d.description);
                    $('#svc_price').val(d.price);
                    $('#svc_currency').val(d.currency);
                    $('#svc_status').val(d.status);
                    $('#svc_type').val(d.type);
                    
                    $('#tituloModalServicio').text('Editar: ' + d.keplerCode);
                    $('#modalServicio').modal('show');
                } catch (error) {
                    Swal.fire("Error", "No se pudieron cargar los datos", "error");
                }
            });
        }
    </script>
</body>
</html>