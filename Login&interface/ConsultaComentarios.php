<?php
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios";
$comentarios = [];

try {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) throw new Exception("Error al obtener comentarios.");
    $comentarios = json_decode($response, true) ?: [];
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
        <a href="Menu.php" class="back-link">
        </a>
        <button onclick="window.location.href='Menu.php'" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </button>
    </div>

    <div class="container">
        <header>Consulta de Comentarios</header>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Comentario guardado exitosamente!</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
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
            <td><?= htmlspecialchars($comentario['nombre'] ?? $comentario['docenteId'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($comentario['nombreAsignatura'] ?? 'N/A') ?></td>
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