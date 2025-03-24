<?php
// Obtener datos de la API de comentarios
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios";
$comentarios = [];

try {
    $response = file_get_contents($apiUrl);
    if ($response === false) {
        throw new Exception("Error al obtener comentarios.");
    }
    $comentarios = json_decode($response, true);
} catch (Exception $e) {
    error_log("Error API: " . $e->getMessage());
    die("<div class='alert alert-danger'>No se pudieron cargar los comentarios.</div>");
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
</head>
<body>

    <!-- Barra de navegación con el logo a la izquierda -->
    <div class="navbar">
    <a href="Menu.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
    </a>
    <button onclick="window.location.href='componente-x.php'" class="logo-button">
        <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
    </button>
</div>

    <div class="container">
        <header>Consulta de Comentarios</header>

        <?php if (!empty($comentarios)): ?>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Comentario ID</th>
                            <th>Fecha</th>
                            <th>Docente ID</th>
                            <th>Asignatura ID</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comentarios as $comentario): ?>
                            <tr>
                                <td><?= htmlspecialchars($comentario['comentarioId'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($comentario['fecha'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($comentario['docenteId'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($comentario['asignaturaId'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($comentario['comentario'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No hay comentarios registrados.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>