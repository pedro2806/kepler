<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small fw-bold">
                    <?php echo $_SESSION['full_name'] ?? 'Usuario Kepler'; ?>
                </span>
                <img class="img-profile rounded-circle" 
                    src="/incidencias/img/undraw_profile.svg" 
                    alt="Perfil"
                    style="width: 32px; height: 32px;">
            </a>
            
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="perfil.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Mi Perfil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModalN">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Salir
                </a>
            </div>
        </li>
    </ul>

    <div class="modal fade" id="logoutModalN" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-left-danger shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-danger" id="modalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Cerrar sesión
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <h5>¿Estás seguro de que deseas salir del sistema <strong>Kepler</strong>?</h5>
                    <p class="text-muted small">Asegúrate de haber guardado tus cambios en el registro de actividades.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-danger px-4" href="logout.php">Confirmar Salida</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para validar y enviar cambio de contraseña mediante AJAX
        function validarContrasenas() {
            var password = $('#nuevapass').val();
            var confirmPassword = $('#confirmapass').val();

            if (password === "" || confirmPassword === "") {
                Swal.fire("Error", "Los campos no pueden estar vacíos", "error");
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire("Atención", "Las contraseñas no coinciden", "warning");
            } else {
                ejecutarCambioPassword();
            }
        }

        function ejecutarCambioPassword() {
            const password = $('#nuevapass').val();
            const noEmpleado = getCookie("noEmpleado");
            const accion = "CambioPassword";

            $.ajax({
                url: 'acciones_contrasena.php',
                method: 'POST',
                dataType: 'json',
                data: { accion, password, noEmpleado },
                success: function(response) {
                    Swal.fire({
                        title: "¡Éxito!",
                        text: "Tu contraseña ha sido actualizada correctamente",
                        icon: "success",
                        timer: 2000
                    }).then(() => {
                        $('#staticBackdrop').modal('hide');
                        $('#nuevapass, #confirmapass').val('');
                    });
                },
                error: function() {
                    Swal.fire("Error", "No se pudo procesar el cambio. Intenta más tarde.", "error");
                }
            });
        }

        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
            return null;
        }
    </script>
</nav>