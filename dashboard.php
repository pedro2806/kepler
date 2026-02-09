<?php
    session_start();
    // Validación de sesión
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
    <title>KEPLER CRM | Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body id="page-top">

    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>
                    <div>
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle"></i> Esta es la página principal del sistema Kepler CRM. Desde aquí puedes acceder a las diferentes funcionalidades y módulos disponibles según tus permisos.
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="row">
                        Bienvenido al panel de control, <?php echo htmlspecialchars($_SESSION['username']); ?>.
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white shadow-sm">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; KEPLER 2026 - Kepler CRM</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Asegurar que Select2 funcione con el diseño empresarial
            $('.form-select, .form-control-select').select2({
                width: '100%',
                theme: 'bootstrap-5'
            });

            // FIX: SB Admin 2 a veces bloquea el collapse de Bootstrap 5
            // Este código asegura que al hacer clic se dispare el evento nativo de BS5
            $('.nav-item .nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('data-bs-target');
                var myCollapse = document.querySelector(target);
                var bsCollapse = bootstrap.Collapse.getOrCreateInstance(myCollapse);
                bsCollapse.toggle();
            });
            
            // Mantener el estado de "active" en el menú según la URL
            var currentUrl = window.location.pathname.split('/').pop();
            $('.collapse-item').each(function() {
                if ($(this).attr('href') === currentUrl) {
                    $(this).addClass('active');
                    $(this).closest('.collapse').addClass('show');
                    $(this).closest('.nav-item').find('.nav-link').removeClass('collapsed');
                }
            });
        });
    </script>
</body>
</html>