<?php
$codigoAsignatura = $_GET['codigo'] ?? '';
$apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas";
$apiDocentesUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Docentes";

// Función para obtener datos de la API
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?: [];
}

// Función para obtener el nombre del docente
function obtenerNombreDocente($docenteId, $apiDocentesUrl) {
    if (empty($docenteId)) return null;
    
    $docenteUrl = $apiDocentesUrl . "/" . $docenteId;
    $docenteData = obtenerDatosAPI($docenteUrl);
    return $docenteData['nombre'] ?? null;
}

// Obtener datos de asignatura
$asignaturas = obtenerDatosAPI($apiBaseUrl);
$detalleAsignatura = [];

foreach ($asignaturas as $asignatura) {
    if ($asignatura['codigoAsignatura'] === $codigoAsignatura) {
        $detalleAsignatura = $asignatura;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Asignatura</title>
    <link rel="stylesheet" href="DescripcionAsignaturas.css">
    <link rel="Icon" href="/Imagenes/UCNE.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="navbar">
        <a href="Menu.php" class="logo-button">
            <img src="/Imagenes/UCNE.jpg" alt="Logo" class="logo">
        </a>
        <span class="title">Detalles de la asignatura</span>
    </div>

    <div class="container">
        <div class="section">
            <h2><?= htmlspecialchars($detalleAsignatura['nombreAsignatura'] ?? "Asignatura no encontrada") ?></h2>
            <p><strong>Código:</strong> <?= htmlspecialchars($detalleAsignatura['codigoAsignatura'] ?? "N/A") ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($detalleAsignatura['descripcionAsignatura'] ?? "Sin descripción")) ?></p>
        </div>

        <div class="section">
            <h3>Docente:</h3>
            <p><?= htmlspecialchars(obtenerNombreDocente($detalleAsignatura['docenteId'] ?? null, $apiDocentesUrl) ?? "No asignado") ?></p>
        </div>

        <div class="section comentarios">
            <h3>Agregar Comentario</h3>
            
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success">¡Comentario enviado correctamente!</div>
            <?php endif; ?>
            
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger">Error al enviar el comentario. Inténtalo de nuevo.</div>
            <?php endif; ?>
            
            <form action="guardar_comentario.php" method="post">
                <input type="hidden" name="asignaturaId" value="<?= htmlspecialchars($detalleAsignatura['asignaturaId'] ?? '') ?>">
                <input type="hidden" name="docenteId" value="<?= htmlspecialchars($detalleAsignatura['docenteId'] ?? '') ?>">
                <input type="hidden" name="codigoAsignatura" value="<?= htmlspecialchars($detalleAsignatura['codigoAsignatura'] ?? '') ?>">
                <input type="hidden" name="nombreAsignatura" value="<?= htmlspecialchars($detalleAsignatura['nombreAsignatura'] ?? '') ?>">
                <input type="hidden" name="usuarioId" value="1"> <!-- Cambiar por el ID del usuario logueado -->
                <textarea name="comentario" placeholder="Escribe tu comentario aquí..." required></textarea>
                <button type="submit">Publicar comentario</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="ConsultaComentarios.php" class="btn-ver-comentarios">Ver todos los comentarios</a>
            </div>
        </div>
    </div>
</body>
</html>