<?php
$codigoAsignatura = $_GET['codigo'] ?? '';
$apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas";

// Función para obtener datos de la API
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true) ?: [];
}

// Obtener todas las asignaturas y buscar la específica
$asignaturas = obtenerDatosAPI($apiBaseUrl);
$detalleAsignatura = [];

foreach ($asignaturas as $asignatura) {
    if ($asignatura['codigoAsignatura'] === $codigoAsignatura) {
        $detalleAsignatura = $asignatura;
        break;
    }
}

// Obtener docente y comentarios si hay datos disponibles
$docentes = [];
$comentarios = [];

if (!empty($detalleAsignatura)) {
    $docenteId = $detalleAsignatura['docenteId'] ?? '';

    if (!empty($docenteId)) {
        $docenteData = obtenerDatosAPI("$apiBaseUrl/Docente?DocenteId=" . urlencode($docenteId));
        if (!empty($docenteData) && is_array($docenteData)) {
            $docentes[] = $docenteData;
        }
    }

    $comentarios = obtenerDatosAPI("$apiBaseUrl/Comentarios?CodigoAsignatura=" . urlencode($codigoAsignatura));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Asignatura</title>
    <link rel="stylesheet" href="DescripcionAsignaturas.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="Menu.php" class="logo">
                <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
            </a>
            <h2><?= htmlspecialchars($detalleAsignatura['nombreAsignatura'] ?? "Asignatura no encontrada") ?></h2>
        </header>

        <section class="detalle-asignatura">
            <h3>Detalles de la asignatura:</h3>
            <p><strong>Código:</strong> <?= htmlspecialchars($detalleAsignatura['codigoAsignatura'] ?? "N/A") ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($detalleAsignatura['descripcionAsignatura'] ?? "Sin descripción")) ?></p>
        </section>

        <section class="docentes">
            <h3>Docente:</h3>
            <?php if (!empty($docentes)): ?>
                <ul>
                    <li><?= htmlspecialchars($docentes[0]['nombre'] ?? "Docente desconocido") ?></li>
                </ul>
            <?php else: ?>
                <p>No hay docentes registrados.</p>
            <?php endif; ?>
        </section>

        <section class="comentarios">
            <h3>Comentarios:</h3>
            <form action="guardar_comentario.php" method="post">
                <input type="hidden" name="codigoAsignatura" value="<?= htmlspecialchars($codigoAsignatura) ?>">
                <label for="usuario">Nombre de usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
                <label for="comentario">Tu comentario:</label>
                <textarea id="comentario" name="comentario" required></textarea>
                <button type="submit">Comentar</button>
            </form>

            <?php if (!empty($comentarios)): ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario-card">
                        <p><strong>@<?= htmlspecialchars($comentario['usuario'] ?? "Anónimo") ?></strong> → <?= htmlspecialchars($comentario['docente'] ?? "Sin docente") ?></p>
                        <p><?= htmlspecialchars($comentario['contenido'] ?? "Sin contenido") ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comentarios disponibles.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
