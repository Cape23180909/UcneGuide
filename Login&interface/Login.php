<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>
    <div class="top-bar"></div>
    <div class="container">
        <div class="header">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="logo">
            <div class="wave"></div>
        </div>

        <div class="login-box">
            <h2>Login</h2>
            <form action="Login.php" method="POST">
                <div class="input-group">
                    <input type="email" placeholder="Email" name="email" required>
                </div>
                <div class="input-group">
                    <input type="password" placeholder="Contraseña" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
            <a href="CreateUser.php" class="register-link">Crear cuenta</a>

            <!-- Mensajes de error -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
session_start();
if (isset($_SESSION['usuario'])) {
    $nombreUsuario = $_SESSION['usuario']['nombre'];
    $emailUsuario = $_SESSION['usuario']['email'];
    $usuario_id = $_SESSION['usuario']['usuarioId'];
    $carrera_id = $_SESSION['carrera']['carreraId'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: Login.php?error=" . urlencode("Todos los campos son obligatorios"));
        exit();
    }

    // URL de la API
    $apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Usuarios";

    // Configurar cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($response)) {
        header("Location: Login.php?error=" . urlencode("Usuario no registrado"));
        exit();
    }

    // Decodificar JSON
    $userData = json_decode($response, true);
    $user = null;

    foreach ($userData as $u) {
        if ($u['email'] === $email) {
            $user = $u;
            break;
        }
    }

    if (!$user) {
        header("Location: Login.php?error=" . urlencode("Usuario no encontrado"));
        exit();
    }

    // Validar contraseña
    if ($password !== $user['password']) {
        header("Location: Login.php?error=" . urlencode("Contraseña incorrecta"));
        exit();
    }

    // Iniciar sesión con la información del usuario
    $_SESSION['authToken'] = bin2hex(random_bytes(32));
    $_SESSION['usuario'] = [
        'usuarioId' => $user['usuarioId'],
        'nombre' => $user['nombre'],
        'email' => $user['email']
    ];

    header('Location: Menu.php');
    exit();
}
?>