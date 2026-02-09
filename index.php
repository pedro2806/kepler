<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CRM Kepler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f9;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .card-header {
            background-color: #1a2a3a; /* Azul empresarial */
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }
        .btn-primary {
            background-color: #1a2a3a;
            border: none;
            padding: 0.7rem;
        }
        .btn-primary:hover {
            background-color: #2c3e50;
        }
        .form-control:focus {
            border-color: #1a2a3a;
            box-shadow: 0 0 0 0.2rem rgba(26, 42, 58, 0.1);
        }
        .brand-name {
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card login-card">
        <div class="card-header">
            <h4 class="brand-name mb-0">Kepler CRM</h4>
            <small class="text-white-50">Gestión Empresarial</small>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 small text-center" role="alert">
                    <?php 
                        echo ($_GET['error'] == 'credenciales_invalidas') ? 'Usuario o contraseña incorrectos' : 'Cuenta deshabilitada';
                    ?>
                </div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label text-muted">Usuario o Correo</label>
                    <input type="text" name="username" class="form-control" id="username" placeholder="ej. admin" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted">Contraseña</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white border-0 text-center pb-4">
            <a href="#" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>

</body>
</html>