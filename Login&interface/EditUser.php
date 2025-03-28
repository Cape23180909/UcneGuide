<?php
session_start();

// --- Inicializar variables ---
$error = null;
$success = null;

// --- Verificar autenticación ---
if (!isset($_SESSION['usuario']['usuarioId'])) {
    header("Location: Login.php");
    exit();
}

// --- Configuración API ---
$apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/";
$usuarioId = $_SESSION['usuario']['usuarioId'];

// --- Manejar mensajes de sesión ---
$error = $_SESSION['error'] ?? $error;
$success = $_SESSION['success'] ?? $success;
unset($_SESSION['error'], $_SESSION['success']);

// --- Función para llamadas API ---
function llamarAPI($url, $metodo = 'GET', $datos = null) {
    $ch = curl_init($url);
    $opciones = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ];
    
    if ($datos) {
        $opciones[CURLOPT_POSTFIELDS] = json_encode($datos);
    }
    
    curl_setopt_array($ch, $opciones);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// --- Obtener datos del usuario ---
$respuestaUsuario = llamarAPI($apiBaseUrl . "Usuarios/$usuarioId");
if ($respuestaUsuario['status'] !== 200) {
    die("Error al obtener usuario: " . ($respuestaUsuario['data']['message'] ?? ''));
}
$usuario = $respuestaUsuario['data'];

// --- Obtener datos para selects ---
$carreras = llamarAPI($apiBaseUrl . "Carreras")['data'] ?? [];
$facultades = llamarAPI($apiBaseUrl . "Facultades")['data'] ?? [];

// --- Procesar actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido");
    }

    // Validar contraseñas
    $password = $_POST['password'];
    if (!empty($password) && $password !== $_POST['confirm_password']) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: EditUser.php");
        exit();
    }

    // Preparar datos para actualización
   // --- Modificar el bloque de preparación del payload ---
$payload = [
    'usuarioId' => $usuarioId,
    'nombre' => $_POST['nombre'],
    'email' => $_POST['email'],
    'carreraId' => (int)$_POST['carreraId'],
    'facultadId' => (int)$_POST['facultadId']
];

// Ajustar el campo de contraseña según lo que requiera la API
if (!empty($password)) {
    // Si la API espera texto plano:
    $payload['password'] = $password; 
    
    // O si requiere confirmación de contraseña actual:
    // $payload = [
    //     'nuevaPassword' => $password,
    //     'confirmacionPassword' => $_POST['confirm_password'],
    //     'passwordActual' => $_POST['current_password'] // Agregar campo en el formulario
    // ];
}

    // Enviar actualización a la API
    $respuesta = llamarAPI(
        $apiBaseUrl . "Usuarios/$usuarioId",
        'PUT',
        $payload
    );

    // Manejar respuesta
    if ($respuesta['status'] >= 200 && $respuesta['status'] < 300) {
        // Actualizar TODOS los datos en sesión
        $_SESSION['usuario'] = [
            'usuarioId' => $usuarioId,
            'nombre' => $_POST['nombre'],
            'email' => $_POST['email'],
            'carreraId' => $_POST['carreraId'],
            'facultadId' => $_POST['facultadId']
        ];
        
        $_SESSION['success'] = "¡Datos actualizados correctamente!";
        header("Location: Menu.php"); // Redirigir al menú principal
        exit();
    } else {
        $_SESSION['error'] = "Error al actualizar: " . ($respuesta['data']['message'] ?? 'Código ' . $respuesta['status']);
        header("Location: EditUser.php");
        exit();
    }
}

// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Usuario</title>
    <link rel="stylesheet" href="EditUser.css">
    <link rel="Icon" href="/Imagenes/guia-turistico 3.png">
</head>
<body>
    <div class="top-bar"></div>

    <div class="container">
        <div class="signup-header">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="logo">
            <div class="wave"></div>
        </div>

        <div class="signup-box">
            <h2>Actualizar Usuario</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="EditUser.php" method="POST" onsubmit="return validarFormulario()">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-group">
                    <div class="input-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" 
                            value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                            value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="input-group">
                        <label for="confirm-password">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirm-password" name="confirm_password">
                    </div>

                    <div class="input-group">
                        <label for="carreraId">Carrera</label>
                        <select id="carreraId" name="carreraId" required>
                            <option value="">Seleccione una carrera</option>
                            <?php foreach ($carreras as $carrera): ?>
                                <option value="<?= $carrera['carreraId'] ?>" 
                                    <?= ($carrera['carreraId'] == ($usuario['carreraId'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($carrera['nombreCarrera']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="facultadId">Facultad</label>
                        <select id="facultadId" name="facultadId" required>
                            <option value="">Seleccione una facultad</option>
                            <?php foreach ($facultades as $facultad): ?>
                                <option value="<?= $facultad['facultadId'] ?>" 
                                    <?= ($facultad['facultadId'] == ($usuario['facultadId'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($facultad['nombreFacultad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
    <div class="button-container">
        <button type="submit" class="signup-btn">Actualizar Usuario</button>
        <a href="Menu.php" class="back-btn">Volver</a>
    </div>
</div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarFormulario() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;
            
            if (password !== confirmPassword) {
                alert("Las contraseñas no coinciden");
                return false;
            }
            
            if (password.length > 0 && password.length < 8) {
                alert("La contraseña debe tener al menos 8 caracteres");
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>