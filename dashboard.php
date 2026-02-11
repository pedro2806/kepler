<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kepler | Dashboard Principal</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800">Tablero de Control Kepler</h1>
                        <a href="cotizacion.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Cotizar Ahora
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ventas (Mes Actual)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiVentas">$0.00</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pipeline (En Negociación)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiPipeline">$0.00</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-project-diagram fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cotizaciones Enviadas</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiCotizaciones">0</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-file-invoice fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Clientes Nuevos (Año)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiClientes">0</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Ventas (Últimos 6 meses)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area" style="height: 320px;">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="tablaMiniCotizaciones">
                                            <thead>
                                                <tr>
                                                    <th>Folio</th>
                                                    <th>Cliente</th>
                                                    <th class="text-end">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2"><i class="fas fa-circle text-success"></i> Ganada</span>
                                        <span class="mr-2"><i class="fas fa-circle text-warning"></i> Enviada</span>
                                    </div>
                                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            cargarDashboard();
        });

        function cargarDashboard() {
            // 1. Obtener KPIs y Datos de Gráfica
            $.post('acciones_dashboard.php', { opcion: 'obtenerKPIs' }, function(res) {
                let data = res;
                if(data.error) return;

                // Renderizar Textos
                $('#kpiVentas').text('$' + parseFloat(data.ventas_mes).toLocaleString('es-MX'));
                $('#kpiPipeline').text('$' + parseFloat(data.pipeline).toLocaleString('es-MX'));
                $('#kpiCotizaciones').text(data.cotizaciones_pendientes);
                $('#kpiClientes').text(data.clientes_nuevos);

                // Renderizar Gráfica
                let ctx = document.getElementById("myAreaChart");
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.chart_labels,
                        datasets: [{
                            label: "Ventas MXN",
                            lineTension: 0.3,
                            backgroundColor: "rgba(28, 200, 138, 0.05)",
                            borderColor: "rgba(28, 200, 138, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(28, 200, 138, 1)",
                            pointBorderColor: "rgba(28, 200, 138, 1)",
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                            pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: data.chart_data,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                        scales: {
                            x: { grid: { display: false, drawBorder: false } },
                            y: { 
                                ticks: { 
                                    maxTicksLimit: 5, padding: 10, 
                                    callback: function(value) { return '$' + value.toLocaleString(); } 
                                },
                                grid: { color: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2] }
                            },
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }, 'json');

            // 2. Obtener Tabla Miniatura
            $.post('acciones_dashboard.php', { opcion: 'ultimasCotizaciones' }, function(res) {
                let html = '';
                res.forEach(c => {
                    let color = 'text-secondary';
                    if(c.status === 'APPROVED' || c.status === 'Closed Won') color = 'text-success';
                    if(c.status === 'SENT') color = 'text-warning';

                    html += `
                        <tr>
                            <td><i class="fas fa-circle ${color} small"></i> ${c.orderCode}</td>
                            <td>${c.tradeName.substring(0, 15)}...</td>
                            <td class="text-end">$${parseFloat(c.total).toLocaleString('es-MX')}</td>
                        </tr>
                    `;
                });
                $('#tablaMiniCotizaciones tbody').html(html);
            }, 'json');
        }
    </script>
</body>
</html>