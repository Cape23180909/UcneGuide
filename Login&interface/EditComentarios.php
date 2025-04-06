<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['authToken'])) {
    header('Location: Login.php');
    exit();
}

// Obtener datos del usuario
$carreraId = $_SESSION['usuario']['carreraId'] ?? null;
$usuarioId = $_SESSION['usuario_id'] ?? null;

if (is_null($carreraId)) {
    die("Error: No se ha identificado la carrera del usuario.");
}

// Configuración de la API
$apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api";
$endpoints = [
    'asignaturas' => "$apiBaseUrl/Asignaturas",
    'docentes' => "$apiBaseUrl/Docentes",
    'comentarios' => "$apiBaseUrl/Comentarios"
];

// Función para obtener datos de la API
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . ($_SESSION['authToken'] ?? '')
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("Error en $url: " . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Error HTTP $httpCode en $url");
        return null;
    }

    return json_decode($response, true);
}

// Obtener ID del comentario
$comentarioId = $_GET['id'] ?? null;
if (!$comentarioId) {
    die("Error: Falta el ID del comentario.");
}

// Cargar el comentario
$comentario = obtenerDatosAPI($endpoints['comentarios'] . "/$comentarioId");
if (!$comentario) {
    die("Error: No se pudo obtener el comentario.");
}

// Obtener datos necesarios
$asignaturas = obtenerDatosAPI($endpoints['asignaturas']) ?? [];
$docentes = obtenerDatosAPI($endpoints['docentes']) ?? [];

// Filtrar asignaturas por carrera
$asignaturasFiltradas = array_filter($asignaturas, fn($a) => $a['carreraId'] == $carreraId);

// Indexar docentes
$docentesIndexados = [];
foreach ($docentes as $docente) {
    $docentesIndexados[$docente['docenteId']] = $docente;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Comentario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CreateComentarios.css">
</head>
<body>
<div class="navbar">
    <button onclick="window.location.href='ConsultaComentarios.php'" class="logo-button">
        <img src="/Imagenes/UCNE.jpg" alt="Logo">
    </button>
</div>

<div class="container">
    <div class="form-header">
        <i class="bi bi-pencil-square"></i> Editar Comentario
    </div>
    <div class="form-body">
        <form action="actualizar_comentario.php" method="post">
            <input type="hidden" name="comentarioId" value="<?= htmlspecialchars($comentario['comentarioId']) ?>">
            <div class="form-group">
                <label>Fecha:</label>
                <div class="date-field"><?= date('d/m/Y', strtotime($comentario['fechaComentario'])) ?></div>
                <input type="hidden" name="fechaComentario" value="<?= $comentario['fechaComentario'] ?>">
            </div>

            <div class="form-group">
                <label for="asignaturaId">Asignatura:</label>
                <select id="asignaturaId" name="asignaturaId" class="form-control" required>
                    <option value="">Seleccione una asignatura</option>
                    <?php foreach ($asignaturasFiltradas as $asignatura): 
                        $docenteId = $asignatura['docenteId'] ?? null;
                        $docenteNombre = $docentesIndexados[$docenteId]['nombre'] ?? 'Docente no asignado';
                        $docenteEmail = $docentesIndexados[$docenteId]['email'] ?? '';
                        $selected = $asignatura['asignaturaId'] == $comentario['asignaturaId'] ? 'selected' : '';
                    ?>
                        <option value="<?= $asignatura['asignaturaId'] ?>"
                            data-docente-id="<?= $docenteId ?>"
                            data-docente-nombre="<?= htmlspecialchars($docenteNombre) ?>"
                            data-docente-email="<?= htmlspecialchars($docenteEmail) ?>"
                            <?= $selected ?>>
                            <?= htmlspecialchars($asignatura['nombreAsignatura']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Docente:</label>
                <div id="docenteInfo" class="docente-info-box">
                    <div class="teacher-details">
                        <span id="teacherName">
                            <?= $docentesIndexados[$comentario['docenteId']]['nombre'] ?? 'No asignado' ?>
                        </span><br>
                        <span id="teacherEmail" class="teacher-email">
                            <?= $docentesIndexados[$comentario['docenteId']]['email'] ?? '' ?>
                        </span>
                    </div>
                </div>
                <input type="hidden" id="docenteId" name="docenteId" value="<?= $comentario['docenteId'] ?>">
            </div>

            <div class="form-group">
                <label for="comentario">Comentario:</label>
                <textarea id="comentario" name="comentario" class="form-control" rows="5" required><?= htmlspecialchars($comentario['comentario']) ?></textarea>
            </div>

            <input type="hidden" name="usuarioId" value="<?= $usuarioId ?>">

            <div class="form-group mt-4">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-save-fill"></i> Actualizar Comentario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const asignaturaSelect = document.getElementById('asignaturaId');
    const docenteIdInput = document.getElementById('docenteId');
    const teacherName = document.getElementById('teacherName');
    const teacherEmail = document.getElementById('teacherEmail');

    asignaturaSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const nombre = selected.dataset.docenteNombre;
        const email = selected.dataset.docenteEmail;
        const docenteId = selected.dataset.docenteId;

        teacherName.textContent = nombre || 'No hay docente asignado';
        teacherEmail.textContent = email || '';
        docenteIdInput.value = docenteId || '';
    });
});
</script>
</body>
</html>