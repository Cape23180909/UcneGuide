<?php
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios";
$apiDocentesUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Docentes";
$apiAsignaturasUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas";
$comentarios = [];
$error = null;

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
    if (empty($docenteId)) return "No asignado";
    
    $docenteData = obtenerDatosAPI($apiDocentesUrl . "/" . urlencode($docenteId));
    return $docenteData['nombre'] ?? "No asignado";
}

// Función para obtener el nombre de la asignatura
function obtenerNombreAsignatura($asignaturaId, $apiAsignaturasUrl) {
    if (empty($asignaturaId)) return "No asignada";
    
    $asignaturaData = obtenerDatosAPI($apiAsignaturasUrl . "/" . urlencode($asignaturaId));
    return $asignaturaData['nombreAsignatura'] ?? "No asignada";
}

// Obtener comentarios
try {
    $comentarios = obtenerDatosAPI($apiUrl);
    if (!is_array($comentarios)) throw new Exception("Formato de datos incorrecto");
} catch (Exception $e) {
    error_log("Error API: " . $e->getMessage());
    $error = "No se pudieron cargar los comentarios.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Comentarios</title>
    <link rel="stylesheet" href="ConsultaComentarios.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="navbar">
        <button onclick="window.location.href='Menu.php'" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </button>
    </div>

    <div class="container">
        <header>Consulta de Comentarios</header>
        <a href="/Cotizacion/Create" class="button-Create ">
                <span class="bi bi-plus-square mt-3"></span> Crear
            </a>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Comentario guardado exitosamente!</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($comentarios)): ?>
            <div class="alert alert-warning">No hay comentarios registrados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th>Asignatura</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comentarios as $comentario): ?>
                            <tr>
                                <td><?= htmlspecialchars($comentario['fechaComentario'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(obtenerNombreDocente($comentario['docenteId'] ?? null, $apiDocentesUrl)) ?></td>
                                <td><?= htmlspecialchars(obtenerNombreAsignatura($comentario['asignaturaId'] ?? null, $apiAsignaturasUrl)) ?></td>
                                <td><?= htmlspecialchars($comentario['comentario'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>