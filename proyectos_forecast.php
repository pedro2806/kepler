<?php
session_start();
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Forecast de Proyectos</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.2.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Forecast Comercial</h1>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="filtroMes">
                                <option value="current">Mes Actual</option>
                                <option value="next">Siguiente Mes</option>
                                <option value="Q1">Q1 (Ene-Mar)</option>
                                <option value="Q2">Q2 (Abr-Jun)</option>
                                <option value="Q3">Q3 (Jul-Sep)</option>
                                <option value="Q4">Q4 (Oct-Dic)</option>
                            </select>
                            <button class="btn btn-primary shadow-sm" onclick="cargarForecast()">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pipeline</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiTotalPipeline">$0.00</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-funnel-dollar fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Forecast (Cierre Probable)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiForecast">$0.00</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ganados este Mes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiGanados">$0.00</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-trophy fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProyecto">
                        <i class="fas fa-plus"></i> Nuevo Proyecto
                    </button>
                    <hr>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-dark">
                                    <h6 class="m-0 font-weight-bold text-white">Detalle de Proyectos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tablaForecast" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Proyecto</th>
                                                    <th>Cliente</th>
                                                    <th>Etapa</th>
                                                    <th>Fecha Cierre</th>
                                                    <th>Valor (MXN)</th>
                                                    <th>Probabilidad</th>
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

                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Valor por Etapa (Pipeline)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area" style="height: 350px;">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
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
    
    <div class="modal fade" id="modalNuevoProyecto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-left-success shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Registrar Oportunidad / Proyecto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoProyecto">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Nombre del Proyecto</label>
                                <input type="text" name="name" class="form-control" required placeholder="Ej. Calibración Anual 2026">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cliente</label>
                                <select class="form-select" id="slcClienteProyecto" name="customer_id" required>
                                    </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contacto (Vinculado al Cliente)</label>
                                <select class="form-select" id="slcContactoProyecto" name="contact_id" required disabled>
                                    <option value="">Seleccione primero un cliente...</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Valor Estimado</label>
                                <input type="number" step="0.01" name="value" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Moneda</label>
                                <select name="currency" class="form-select">
                                    <option value="MXN">Pesos (MXN)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Cierre Estimada</label>
                                <input type="date" name="closeDate" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Etapa Inicial</label>
                            <select name="stage" class="form-select" required>
                                <option value="Prospecting">Prospección (10%)</option>
                                <option value="Qualification">Calificación (30%)</option>
                                <option value="Proposal">Propuesta (50%)</option>
                                <option value="Negotiation">Negociación (80%)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción / Notas</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Crear Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQuickEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Forecast</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formQuickEdit">
                        <input type="hidden" name="project_id" id="qe_id">
                        <div class="mb-3">
                            <label>Nuevo Valor</label>
                            <input type="number" step="0.01" name="value" id="qe_value" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Fecha de Cierre Estimada</label>
                            <input type="date" name="closeDate" id="qe_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Etapa</label>
                            <select name="stage" id="qe_stage" class="form-select">
                                <option value="Prospecting">Prospección (10%)</option>
                                <option value="Qualification">Calificación (30%)</option>
                                <option value="Proposal">Propuesta (50%)</option>
                                <option value="Negotiation">Negociación (80%)</option>
                                <option value="Closed Won">Cerrada Ganada (100%)</option>
                                <option value="Closed Lost">Cerrada Perdida (0%)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="guardarAjusteForecast()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCotizador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <div class="modal-content border-left-danger shadow">
                <div class="modal-header bg-gradient-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar"></i> Nueva Cotización</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cot_project_id">
                    <input type="hidden" id="cot_customer_id">
                    <input type="hidden" id="cot_currency">

                    <div class="card mb-4 border-bottom-danger">
                        <div class="card-body bg-light">
                            <h6 class="text-danger fw-bold">Agregar Servicio / Equipo</h6>
                            <div class="row align-items-end">
                                <div class="col-md-6 mb-2">
                                    <label>Buscar Servicio (Catálogo)</label>
                                    <select id="slcServicio" class="form-select"></select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Cantidad</label>
                                    <input type="number" id="txtQty" class="form-control" value="1" min="1">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Precio Unit.</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="txtPrecio" class="form-control" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button class="btn btn-danger w-100" id="btnAgregarPartida">
                                        <i class="fas fa-plus-circle"></i> Agregar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted" id="lblDescServicio"></small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Importe</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tblPartidas">
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold" id="lblSubtotal">$0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">IVA (16%):</td>
                                    <td class="text-end fw-bold" id="lblIVA">$0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="bg-danger text-white">
                                    <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                                    <td class="text-end fw-bold" id="lblTotal">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="btnGenerarPDF">
                        <i class="fas fa-save"></i> Guardar y Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            cargarForecast();

            // 1. Inicializar Select2 para Clientes
            $('#slcClienteProyecto').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNuevoProyecto'),
                width: '100%',
                ajax: {
                    url: 'acciones_proyecto.php',
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

            // 2. Select en Cascada: Cargar Contactos al cambiar Cliente
            $('#slcClienteProyecto').on('select2:select', function(e) {
                var clienteId = e.params.data.id;
                $('#slcContactoProyecto').prop('disabled', true).html('<option>Cargando...</option>');
                
                $.post('acciones_proyecto.php', { opcion: 'cargarContactosPorCliente', id: clienteId }, function(res) {
                    let opciones = '<option value="">Seleccione un contacto...</option>';
                    let data = JSON.parse(res);
                    
                    data.forEach(c => {
                        opciones += `<option value="${c.id}">${c.name} (${c.jobPosition || 'N/A'})</option>`;
                    });
                    
                    $('#slcContactoProyecto').html(opciones).prop('disabled', false);
                });
            });

            // 3. Guardar Proyecto
            $('#formNuevoProyecto').on('submit', function(e) {
                e.preventDefault();
                let datos = $(this).serialize() + '&opcion=crearProyecto';
                
                $.post('acciones_proyecto.php', datos, function(res) {
                    let r = JSON.parse(res);
                    if(r.status === 'success') {
                        Swal.fire("¡Creado!", "El proyecto se ha registrado en el forecast.", "success");
                        $('#modalNuevoProyecto').modal('hide');
                        $('#formNuevoProyecto')[0].reset();
                        $('#slcClienteProyecto').val(null).trigger('change');
                        // Si tienes la función cargarForecast(), llámala aquí:
                        if(typeof cargarForecast === 'function') cargarForecast();
                    } else {
                        Swal.fire("Error", r.message, "error");
                    }
                });
            });


            // Variable global actualizada
            var partidasCotizacion = [];

            $('#slcServicio').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCotizador'),
                placeholder: 'Escribe código o descripción...',
                minimumInputLength: 2, // Espera a que escribas 2 letras para llamar
                ajax: {
                    url: 'acciones_servicios.php', // <--- 1. Aquí apunta al archivo PHP
                    type: 'POST',
                    dataType: 'json',
                    data: function (params) { 
                        // 2. Aquí le dice qué "opcion" ejecutar en el PHP
                        return { 
                            opcion: 'buscarServicios', // <--- ESTO LLAMA AL CASE 'buscarServicios'
                            term: params.term          // Lo que el usuario está escribiendo
                        }; 
                    },
                    processResults: function (data) { 
                        return { results: data }; 
                    }
                }
            });

            // Botón Agregar
            $('#btnAgregarPartida').click(function() {
                let idServicio = $('#slcServicio').val();
                let textoServicio = $('#slcServicio option:selected').text();
                let qty = parseFloat($('#txtQty').val());
                let precio = parseFloat($('#txtPrecio').val());
                let type = $('#slcServicio').data('type') || 'SERVICE'; // Default SERVICE

                if(!idServicio || qty <= 0) { return; }

                partidasCotizacion.push({
                    service_id: idServicio,
                    description: textoServicio,
                    qty: qty,
                    price: precio,
                    total: qty * precio,
                    type: type // <--- ENVIAMOS EL TIPO
                });

                renderizarTablaCotizacion();
                
                // Limpiar inputs
                $('#slcServicio').val(null).trigger('change');
                $('#txtQty').val(1);
                $('#txtPrecio').val('');
                $('#lblDescServicio').text('');
            });

            // 3. Renderizar Tabla y Calcular Totales
            function renderizarTablaCotizacion() {
                let html = '';
                let subtotal = 0;

                partidasCotizacion.forEach((item, index) => {
                    subtotal += item.total;
                    html += `
                        <tr>
                            <td>${item.description.split(' - ')[0]}</td>
                            <td>${item.description}</td>
                            <td class="text-center">${item.qty}</td>
                            <td class="text-end">$${item.price.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td class="text-end">$${item.total.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarPartida(${index})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });

                $('#tblPartidas').html(html);
                
                // Totales
                let iva = subtotal * 0.16;
                let total = subtotal + iva;

                $('#lblSubtotal').text('$' + subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2}));
                $('#lblIVA').text('$' + iva.toLocaleString('es-MX', {minimumFractionDigits: 2}));
                $('#lblTotal').text('$' + total.toLocaleString('es-MX', {minimumFractionDigits: 2}));
            }

            // Eliminar partida
            window.eliminarPartida = function(index) {
                partidasCotizacion.splice(index, 1);
                renderizarTablaCotizacion();
            }

            // 4. Abrir Modal desde la Tabla de Proyectos
            window.generarCotizacion = function(projectId, customerId) {
                // Resetear todo
                partidasCotizacion = [];
                renderizarTablaCotizacion();
                
                $('#cot_project_id').val(projectId);
                $('#cot_customer_id').val(customerId);
                $('#cot_currency').val('MXN'); // O traerlo del proyecto con AJAX si es necesario
                
                $('#modalCotizador').modal('show');
            }

            // 5. Guardar Todo y Generar PDF
            $('#btnGenerarPDF').click(function() {
                if(partidasCotizacion.length === 0) {
                    Swal.fire("Error", "La cotización debe tener al menos una partida.", "error");
                    return;
                }

                // Calculamos el total final para enviarlo
                let total = parseFloat($('#lblTotal').text().replace(/[$,]/g, ''));

                let datos = {
                    opcion: 'guardarCotizacion',
                    project_id: $('#cot_project_id').val(),
                    customer_id: $('#cot_customer_id').val(),
                    currency: $('#cot_currency').val(),
                    total: total, // Enviamos el total neto
                    items: JSON.stringify(partidasCotizacion) // Array convertido a JSON string
                };

                $.post('acciones_proyecto.php', datos, function(res) {
                    let r = JSON.parse(res);
                    if(r.status === 'success') {
                        $('#modalCotizador').modal('hide');
                        
                        Swal.fire({
                            title: "¡Cotización Guardada!",
                            text: "El PDF se abrirá en una nueva pestaña.",
                            icon: "success"
                        });

                        // Abrir PDF en nueva pestaña
                        window.open('exportar_cotizacion.php?id=' + r.quotation_id, '_blank');
                    } else {
                        Swal.fire("Error al guardar", r.message, "error");
                    }
                });
            });
        });

        function cargarForecast() {
            let filtro = $('#filtroMes').val();

            // 1. Cargar KPIs
            $.post('acciones_proyecto.php', { opcion: 'kpiForecast', filtro: filtro }, function(res) {
                let data = JSON.parse(res);
                $('#kpiTotalPipeline').text('$' + parseFloat(data.pipeline).toLocaleString('es-MX'));
                $('#kpiForecast').text('$' + parseFloat(data.forecast).toLocaleString('es-MX'));
                $('#kpiGanados').text('$' + parseFloat(data.ganados).toLocaleString('es-MX'));
            });

            // 2. Cargar Tabla
            if ($.fn.DataTable.isDataTable('#tablaForecast')) {
                $('#tablaForecast').DataTable().destroy();
            }

            $('#tablaForecast').DataTable({
                "ajax": {
                    "url": "acciones_proyecto.php",
                    "type": "POST",
                    "data": { "opcion": "listarForecast", "filtro": filtro },
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "name", "render": function(data){ return `<strong>${data}</strong>`; } },
                    { "data": "cliente" },
                    { "data": "stage", "render": function(data){
                        let color = 'secondary';
                        if(data === 'Negotiation') color = 'warning text-dark';
                        if(data === 'Closed Won') color = 'success';
                        return `<span class="badge bg-${color}">${data}</span>`;
                    }},
                    { "data": "closeDate" },
                    { "data": "value", "render": $.fn.dataTable.render.number(',', '.', 2, '$') },
                    { "data": "probabilidad", "render": function(data){
                        return `<div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: ${data}%;" aria-valuenow="${data}" aria-valuemin="0" aria-valuemax="100">${data}%</div>
                                </div>`;
                    }},
                    { "data": null, "render": function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary" onclick="abrirQuickEdit(${row.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="generarCotizacion(${row.id}, ${row.customer_id})">
                                <i class="fas fa-file-pdf"></i>
                            </button>`;
                    }}
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });

            // 3. Cargar Gráfica
            cargarGrafica(filtro);
        }

        // Funciones para edición rápida (Quick Edit)
        function abrirQuickEdit(id) {
            $.post('acciones_proyecto.php', { opcion: 'obtenerProyecto', id: id }, function(res) {
                let d = JSON.parse(res);
                $('#qe_id').val(d.id);
                $('#qe_value').val(d.value);
                $('#qe_date').val(d.closeDate.split(' ')[0]); // Ajuste formato fecha
                $('#qe_stage').val(d.stage);
                $('#modalQuickEdit').modal('show');
            });
        }

        function guardarAjusteForecast() {
            let datos = $('#formQuickEdit').serialize() + '&opcion=actualizarForecastProyecto';
            $.post('acciones_proyecto.php', datos, function(res) {
                $('#modalQuickEdit').modal('hide');
                cargarForecast(); // Recargar todo
            });
        }

        // Variable global para destruir la gráfica anterior antes de crear una nueva
var myChart; 

function cargarGrafica(filtro) {
    $.post('acciones_proyecto.php', { opcion: 'obtenerGraficaForecast', filtro: filtro }, function(res) {
        let data = JSON.parse(res);
        
        let ctx = document.getElementById("myAreaChart");
        
        // Si ya existe una gráfica, la destruimos para no sobreponer datos
        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: "Valor Total",
                    data: data.data,
                    backgroundColor: data.colors,
                    borderColor: data.colors,
                    borderWidth: 1
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 6 } },
                    y: { 
                        ticks: { 
                            maxTicksLimit: 5, padding: 10,
                            // Formato de moneda en el eje Y
                            callback: function(value) { return '$' + value.toLocaleString(); } 
                        },
                        grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleColor: '#6e707e',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
}
    </script>
</body>
</html>