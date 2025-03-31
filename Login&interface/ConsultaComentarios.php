<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: Login.php?error=no_autenticado');
    exit();
}

// Obtener el ID del usuario logueado
$usuarioId = $_SESSION['usuario']['usuarioId'];

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
    
    if (curl_errno($ch)) {
        throw new Exception('Error en la conexión: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("La API respondió con código $httpCode");
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error decodificando JSON: ' . json_last_error_msg());
    }
    
    return $data;
}

// Función para obtener el nombre del docente
function obtenerNombreDocente($docenteId, $apiDocentesUrl) {
    if (empty($docenteId)) return "No asignado";
    
    try {
        $docenteData = obtenerDatosAPI($apiDocentesUrl . "/" . urlencode($docenteId));
        return $docenteData['nombre'] ?? "No asignado";
    } catch (Exception $e) {
        error_log("Error obteniendo docente: " . $e->getMessage());
        return "Error al cargar";
    }
}

// Función para obtener el nombre de la asignatura
function obtenerNombreAsignatura($asignaturaId, $apiAsignaturasUrl) {
    if (empty($asignaturaId)) return "No asignada";
    
    try {
        $asignaturaData = obtenerDatosAPI($apiAsignaturasUrl . "/" . urlencode($asignaturaId));
        return $asignaturaData['nombreAsignatura'] ?? "No asignada";
    } catch (Exception $e) {
        error_log("Error obteniendo asignatura: " . $e->getMessage());
        return "Error al cargar";
    }
}

// Obtener y filtrar comentarios
try {
    // Obtener todos los comentarios
    $todosComentarios = obtenerDatosAPI($apiUrl);
    
    // Filtrar solo los comentarios del usuario logueado
    $comentarios = array_filter($todosComentarios, function($comentario) use ($usuarioId) {
        return isset($comentario['usuarioId']) && $comentario['usuarioId'] == $usuarioId;
    });
    
    // Reindexar el array
    $comentarios = array_values($comentarios);
    
    // Obtener nombres de docentes y asignaturas para cada comentario
    foreach ($comentarios as &$comentario) {
        $comentario['nombreDocente'] = obtenerNombreDocente($comentario['docenteId'] ?? null, $apiDocentesUrl);
        $comentario['nombreAsignatura'] = obtenerNombreAsignatura($comentario['asignaturaId'] ?? null, $apiAsignaturasUrl);
    }
    unset($comentario); // Romper la referencia
    
} catch (Exception $e) {
    error_log("Error al cargar comentarios: " . $e->getMessage());
    $error = "No se pudieron cargar los comentarios. Por favor intenta más tarde.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Comentarios</title>
    <link rel="stylesheet" href="ConsultaComentarios.css">
    <link rel="Icon" href="/Imagenes/guia-turistico 3.png">
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
        <a href="CreateComentarios.php" class="button-Create ">
                <span class="fas fa-plus-square"></span> Crear
            </a>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Comentario guardado exitosamente!</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($comentarios)): ?>
          
        <?php else: ?>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th>Asignatura</th>
                            <th>Comentario</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comentarios as $comentario): ?>
                            <tr>
                                <td><?= htmlspecialchars(isset($comentario['fechaComentario']) ? date('d/m/20y', strtotime($comentario['fechaComentario'])) : 'N/A') ?></td>
                                <td><?= htmlspecialchars(obtenerNombreDocente($comentario['docenteId'] ?? null, $apiDocentesUrl)) ?></td>
                                <td><?= htmlspecialchars(obtenerNombreAsignatura($comentario['asignaturaId'] ?? null, $apiAsignaturasUrl)) ?></td>
                                <td><?= htmlspecialchars($comentario['comentario'] ?? 'N/A') ?></td>
                                <td><a href='EditComentarios.php?id=<?= htmlspecialchars($comentario['comentarioId']) ?>' class='btn btn-primary'>Editar</a></td>
                                <td><a href='DeleteComentarios.php?id=<?= htmlspecialchars($comentario['comentarioId']) ?>' class='btn btn-danger'>Eliminar</a></td>
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