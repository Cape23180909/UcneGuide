<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['authToken'])) {
    header('Location: Login.php');
    exit();
}

// Configuración de la API
$apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api";
$comentariosEndpoint = "$apiBaseUrl/Comentarios";
$asignaturasEndpoint = "$apiBaseUrl/Asignaturas";
$docentesEndpoint = "$apiBaseUrl/Docentes";

// Obtener ID del comentario a eliminar
$comentarioId = $_GET['id'] ?? null;
if (!$comentarioId) {
    die("<div class='alert alert-danger'>Error: No se ha especificado el comentario a eliminar.</div>");
}

// Función para obtener datos de la API
function obtenerDatosAPI($url, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($token) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        return null;
    }

    return json_decode($response, true);
}

// Obtener datos del comentario
$comentario = obtenerDatosAPI("$comentariosEndpoint/$comentarioId", $_SESSION['authToken']);
if (!$comentario) {
    die("<div class='alert alert-danger'>Error: No se pudo cargar el comentario para eliminación.</div>");
}

// Verificar permisos
if ($_SESSION['usuario']['usuarioId'] != $comentario['usuarioId']) {
    die("<div class='alert alert-danger'>Error: No tienes permiso para eliminar este comentario.</div>");
}

// Obtener datos adicionales
$asignatura = obtenerDatosAPI("$asignaturasEndpoint/{$comentario['asignaturaId']}", $_SESSION['authToken']);
$docente = obtenerDatosAPI("$docentesEndpoint/{$comentario['docenteId']}", $_SESSION['authToken']);

// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar'])) {
    // Configuración de cURL para eliminar el comentario
    $ch = curl_init("$comentariosEndpoint/$comentarioId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Usar DELETE para eliminar
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['authToken'],
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Obtener el código de estado HTTP
    curl_close($ch);

    // Verificar si la eliminación fue exitosa (código HTTP 200 o 204)
    if ($httpCode == 200 || $httpCode == 204) {
        header("Location: DescripcionAsignaturas.php?codigo=" . urlencode($comentario['codigoAsignatura']) . "&success=comentario_eliminado");
        exit();
    } else {
        // Mostrar detalles del error
        $error = "<div class='alert alert-danger'>Error al eliminar el comentario. Código: $httpCode</div>";
        $error .= "<div class='alert alert-info mt-2'>Respuesta del servidor: " . htmlspecialchars($response) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Comentario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="DeleteComentarios.css">
    <link rel="Icon" href="/Imagenes/guia-turistico 3.png">
</head>
<body>
    <div class="navbar">
        <button onclick="window.location.href='ConsultaComentarios.php'" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </button>
    </div>

    <div class="main-container">
        <div class="confirmation-container">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <h3>¿Estás seguro de que deseas eliminar este comentario?</h3>
                <p class="mb-0">Esta acción no se puede deshacer</p>
            </div>
            
            <div class="confirmation-body">
                <?php if (isset($error)): ?>
                    <?php echo $error; ?>
                <?php endif; ?>
                
                <div class="comment-details">
                    <div class="detail-item">
                        <span class="detail-label">Asignatura:</span>
                        <span><?php echo htmlspecialchars($asignatura['nombreAsignatura'] ?? 'No disponible'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Docente:</span>
                        <span><?php echo htmlspecialchars($docente['nombre'] ?? 'No disponible'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fecha:</span>
                        <span><?php echo date('d/m/Y', strtotime($comentario['fechaComentario'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Comentario:</span>
                    </div>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                    </div>
                </div>
                
                <form method="POST" action="DeleteComentarios.php?id=<?php echo $comentarioId; ?>">
                    <div class="action-buttons">
                        <button type="submit" name="confirmar" class="btn btn-confirm text-white">
                            <i class="bi bi-trash-fill me-2"></i>Confirmar Eliminación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>