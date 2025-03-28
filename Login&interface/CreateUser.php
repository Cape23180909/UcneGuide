<?php
session_start(); // Debe ser lo PRIMERO en el archivo

// Procesar el formulario antes de cualquier HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación de contraseñas (también en servidor aunque esté en JS)
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header("Location: CreateUser.php");
        exit();
    }

    // Preparar datos
    $data = [
        'nombre' => $_POST['nombre'],
        'email' => $_POST['email'],
        'password' => $_POST['password'], // Deberías hashear esto si la API lo requiere
        'carreraId' => (int)$_POST['carreraId'],
        'facultadId' => (int)$_POST['facultadId']
    ];

    // Enviar a API
    $ch = curl_init("https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Usuarios");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Manejar respuesta
    if ($httpCode >= 200 && $httpCode < 300) { // Cualquier éxito 2xx
        $_SESSION['success'] = "¡Registro exitoso! Por favor inicia sesión";
        header("Location: Login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al registrar: " . $response;
        header("Location: CreateUser.php");
        exit();
    }
}

// Función para obtener datos (después del bloque POST)
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?: [];
}

// Obtener datos para el formulario
$carreras = obtenerDatosAPI("https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Carreras");
$facultades = obtenerDatosAPI("https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Facultades");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="CreateUser.css">
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
            <h2>Crear Cuenta</h2>
            <form action="CreateUser.php" method="POST" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <div class="input-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="input-group">
                        <label for="confirm-password">Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" name="confirm_password" required>
                    </div>

                    <!-- Nuevos campos requeridos por la API -->
                    <div class="input-group">
                        <label for="carreraId">Carrera</label>
                        <select id="carreraId" name="carreraId" required>
                            <option value="">Seleccione una carrera</option>
                            <?php foreach ($carreras as $carrera): ?>
                                <option value="<?= $carrera['carreraId'] ?>"><?= $carrera['nombreCarrera'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="facultadId">Facultad</label>
                        <select id="facultadId" name="facultadId" required>
                            <option value="">Seleccione una facultad</option>
                            <?php foreach ($facultades as $facultad): ?>
                                <option value="<?= $facultad['facultadId'] ?>"><?= $facultad['nombreFacultad'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="signup-btn">Registrarse</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarFormulario() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm-password").value;
            
            if (password !== confirmPassword) {
                alert("Las contraseñas no coinciden");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>