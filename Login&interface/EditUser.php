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
                        <label for="password">Nueva Contraseña (Si vas a actualizar debes colocar una nueva)</label>
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
        // Obtener elementos
        const nombre = document.getElementById("nombre");
        const email = document.getElementById("email");
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm-password");
        const carreraId = document.getElementById("carreraId");
        const facultadId = document.getElementById("facultadId");
        
        // Validar nombre
        if (nombre.value.trim() === "") {
            mostrarError(nombre, "El nombre es obligatorio");
            return false;
        } else {
            quitarError(nombre);
        }
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            mostrarError(email, "Ingrese un email válido");
            return false;
        } else {
            quitarError(email);
        }
        
        // Validar contraseñas
        if (password.value !== confirmPassword.value) {
            mostrarError(password, "Las contraseñas no coinciden");
            mostrarError(confirmPassword, "Las contraseñas no coinciden");
            return false;
        } else {
            quitarError(password);
            quitarError(confirmPassword);
        }
        
        if (password.value.length > 0 && password.value.length < 8) {
            mostrarError(password, "La contraseña debe tener al menos 8 caracteres");
            return false;
        } else if (password.value.length > 0) {
            quitarError(password);
        }
        
        // Validar selects
        if (carreraId.value === "") {
            mostrarError(carreraId, "Seleccione una carrera");
            return false;
        } else {
            quitarError(carreraId);
        }
        
        if (facultadId.value === "") {
            mostrarError(facultadId, "Seleccione una facultad");
            return false;
        } else {
            quitarError(facultadId);
        }
        
        return true;
    }
    
    function mostrarError(elemento, mensaje) {
        // Remover cualquier mensaje de error existente
        quitarError(elemento);
        
        // Crear elemento de error
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.textContent = mensaje;
        errorDiv.style.color = "#ff4444";
        errorDiv.style.fontSize = "0.8em";
        errorDiv.style.marginTop = "5px";
        
        // Insertar después del elemento
        elemento.parentNode.appendChild(errorDiv);
        
        // Resaltar el campo con error
        elemento.style.borderColor = "#ff4444";
    }
    
    function quitarError(elemento) {
        // Quitar borde rojo
        elemento.style.borderColor = "";
        
        // Eliminar mensaje de error si existe
        const errorDiv = elemento.parentNode.querySelector(".error-message");
        if (errorDiv) {
            elemento.parentNode.removeChild(errorDiv);
        }
    }
    
    // Validación en tiempo real para algunos campos
    document.getElementById("email").addEventListener("blur", function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(this.value)) {
            mostrarError(this, "Ingrese un email válido");
        } else {
            quitarError(this);
        }
    });
    
    document.getElementById("confirm-password").addEventListener("input", function() {
        const password = document.getElementById("password").value;
        if (this.value !== password) {
            mostrarError(this, "Las contraseñas no coinciden");
        } else {
            quitarError(this);
            quitarError(document.getElementById("password"));
        }
    });
</script>
</body>
</html>