<?php
session_start();
require_once 'conn.php'; // Importamos la conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['username'] ?? '';
    $password_input = $_POST['password'] ?? '';

    if (!empty($user_input) && !empty($password_input)) {
        try {
            // Buscamos al usuario por username o email
            $stmt = $pdo->prepare("SELECT id, username, password, enabled, roles FROM fos_user WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$user_input, $user_input]);
            $user = $stmt->fetch();
            echo $password_input."\n";
            echo $user['password']."\n";
            echo $user;
            if ($password_input == $user['password']) {
                
                if (!$user['enabled']) {
                    header("Location: index.php?error=cuenta_desactivada");
                    exit;
                }

                // Configuración de sesión empresarial
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['roles']    = $user['roles']; // Recuerda que esto es un array serializado

                // Actualizar último login
                $update = $pdo->prepare("UPDATE fos_user SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);

                header("Location: dashboard.php");
                exit;

            } else {
                header("Location: index.php?username=$user_input&error=credenciales_invalidas&password=$password_input");
            }
        } catch (Exception $e) {
            die("Error en el servidor.");
        }
    }
}