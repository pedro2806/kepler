<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Cotizador Rápido</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .bg-kepler { background-color: #1a2a3a; color: white; }
        .total-box { font-size: 1.5rem; font-weight: bold; text-align: right; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    
                    <div class="card shadow mb-4 border-left-primary">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Nueva Cotización</h6>
                        </div>
                        <div class="card-body">
                            <form id="formCabecera">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Cliente</label>
                                        <select class="form-select" id="slcCliente" required></select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3" style="display: none;">
                                        <label class="fw-bold">Contacto Atención</label>
                                        <select class="form-select" id="slcContacto" required disabled>
                                            <option value="">Seleccione cliente primero...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">Moneda</label>
                                        <select class="form-select" id="slcMoneda">
                                            <option value="MXN">Pesos (MXN)</option>
                                            <option value="USD">Dólares (USD)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">Vigencia</label>
                                        <select class="form-select" id="slcVigencia">
                                            <option value="15">15 Días</option>
                                            <option value="30">30 Días</option>
                                            <option value="7">7 Días</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">Condiciones Pago</label>
                                        <select class="form-select" id="slcPago">
                                            <option value="Contado">Contado</option>
                                            <option value="Credito 15 dias">Crédito 15 días</option>
                                            <option value="Credito 30 dias">Crédito 30 días</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">Tiempo Entrega</label>
                                        <input type="text" class="form-control" id="txtEntrega" value="5-7 Días Hábiles">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header bg-kepler py-3">
                            <h6 class="m-0 font-weight-bold text-white">Detalle de Servicios y Productos</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end mb-4 p-3 bg-light rounded border">
                                <div class="col-md-5">
                                    <label class="small fw-bold">Buscar Item (Kepler Code)</label>
                                    <select class="form-select" id="slcItemBusqueda"></select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small fw-bold">Cantidad</label>
                                    <input type="number" id="txtQty" class="form-control" value="1" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Precio Unitario</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="txtPrecio" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-success w-100" id="btnAgregar">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted" id="lblDetalleItem">---</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Cant.</th>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th class="text-end">P. Unitario</th>
                                            <th class="text-end">Importe</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblPartidas"></tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end mt-4">
                                <div class="col-md-4">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td class="text-end">Subtotal:</td>
                                            <td class="text-end fw-bold" id="lblSubtotal">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-end">IVA (16%):</td>
                                            <td class="text-end fw-bold" id="lblIVA">$0.00</td>
                                        </tr>
                                        <tr class="border-top border-dark">
                                            <td class="text-end fs-5 fw-bold">TOTAL:</td>
                                            <td class="total-box text-primary" id="lblTotal">$0.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-secondary" onclick="window.location.href='index.php'">Cancelar</button>
                            <button class="btn btn-primary btn-lg" id="btnGuardarCotizacion">
                                <i class="fas fa-save"></i> Generar Cotización
                            </button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        var partidas = [];

        $(document).ready(function() {
            // 1. Configurar Select2 de Clientes
            $('#slcCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar Cliente...',
                ajax: {
                    url: 'acciones_cotizacion.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function (params) { 
                        return { opcion: 'buscarClientesSelect', term: params.term }; },
                    processResults: function (data) { 
                        return { results: data.map(i => ({ id: i.id, text: i.tradeName })) }; 
                    }
                }
            });

            // 2. Cargar Contactos al elegir Cliente
            $('#slcCliente').on('select2:select', function(e) {
                let idCliente = e.params.data.id;
                $('#slcContacto').prop('disabled', true).html('<option>Cargando...</option>');
                $.post('acciones_cotizacion.php', { opcion: 'cargarContactosPorCliente', id: idCliente }, function(res) {
                    let data = JSON.parse(res);
                    let ops = '<option value="">Seleccione...</option>';
                    data.forEach(c => ops += `<option value="${c.id}">${c.name}</option>`);
                    $('#slcContacto').html(ops).prop('disabled', false);
                });
            });

            // 3. Configurar Select2 de Busqueda Items (Servicios/Productos)
            $('#slcItemBusqueda').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar código o descripción...',
                minimumInputLength: 2,
                ajax: {
                    url: 'acciones_cotizacion.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function (params) { return { opcion: 'buscarServicios', term: params.term }; },
                    processResults: function (data) { return { results: data }; }
                }
            });

            // 4. Al seleccionar un Item
            $('#slcItemBusqueda').on('select2:select', function(e) {
                let d = e.params.data;
                $('#txtPrecio').val(d.price);
                $('#lblDetalleItem').text(d.text + ' (' + d.currency + ')');
                $('#slcItemBusqueda').data('info', d); // Guardar objeto completo
            });

            // 5. Botón Agregar
            $('#btnAgregar').click(function() {
                let dataItem = $('#slcItemBusqueda').data('info');
                if(!dataItem) return Swal.fire("Error", "Seleccione un producto/servicio.", "warning");
                
                let qty = parseFloat($('#txtQty').val());
                let precio = parseFloat($('#txtPrecio').val());

                partidas.push({
                    service_id: dataItem.id,
                    description: dataItem.text,
                    qty: qty,
                    price: precio,
                    total: qty * precio,
                    type: dataItem.type || 'SERVICE'
                });

                renderTabla();
                // Reset inputs
                $('#slcItemBusqueda').val(null).trigger('change');
                $('#txtQty').val(1);
                $('#txtPrecio').val('');
                $('#lblDetalleItem').text('---');
                $('#slcItemBusqueda').removeData('info');
            });

            // 6. Botón Guardar Cotización
            $('#btnGuardarCotizacion').click(function() {
                let cliente = $('#slcCliente').val();
                let contacto = $('#slcContacto').val();
                
                if(!cliente || !contacto) return Swal.fire("Faltan datos", "Seleccione Cliente y Contacto.", "warning");
                if(partidas.length === 0) return Swal.fire("Vacío", "Agregue al menos una partida.", "warning");

                let datos = {
                    opcion: 'guardarCotizacion',
                    customer_id: cliente,
                    contact_id: contacto,
                    currency: $('#slcMoneda').val(),
                    paymentTerm: $('#slcPago').val(),
                    shipmentTime: $('#txtEntrega').val(),
                    validDays: $('#slcVigencia').val(),
                    items: JSON.stringify(partidas),
                    project_id: null // INDICA QUE NO HAY PROYECTO
                };

                $.post('acciones_cotizacion.php', datos, function(res) {
                    let r = JSON.parse(res);
                    if(r.status === 'success') {
                        Swal.fire({
                            title: "¡Cotización Creada!",
                            text: "Se ha generado correctamente.",
                            icon: "success"
                        }).then(() => {
                            window.open('exportar_cotizacion.php?id=' + r.quotation_id, '_blank');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", r.message, "error");
                    }
                });
            });
        });

        function renderTabla() {
            let html = '';
            let subtotal = 0;
            partidas.forEach((p, i) => {
                subtotal += p.total;
                let icono = p.type === 'PRODUCT' ? '<i class="fas fa-box text-warning"></i>' : '<i class="fas fa-tools text-info"></i>';
                html += `
                    <tr>
                        <td>${p.qty}</td>
                        <td>${icono}</td>
                        <td>${p.description}</td>
                        <td class="text-end">$${p.price.toFixed(2)}</td>
                        <td class="text-end fw-bold">$${p.total.toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-outline-danger" onclick="eliminar(${i})"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
            });
            $('#tblPartidas').html(html);
            $('#lblSubtotal').text('$' + subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2}));
            $('#lblIVA').text('$' + (subtotal * 0.16).toLocaleString('es-MX', {minimumFractionDigits: 2}));
            $('#lblTotal').text('$' + (subtotal * 1.16).toLocaleString('es-MX', {minimumFractionDigits: 2}));
        }
        
        window.eliminar = function(i) {
            partidas.splice(i, 1);
            renderTabla();
        }
    </script>
</body>
</html>