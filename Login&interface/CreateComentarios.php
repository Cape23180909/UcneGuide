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
    'docentes' => "$apiBaseUrl/Docentes"
];

// Función para obtener datos de la API con manejo de errores
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . ($_SESSION['authToken'] ?? '')
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log("Error en la petición a $url: " . curl_error($ch));
        curl_close($ch);
        return null;
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("API respondió con código $httpCode para $url");
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decodificando JSON: " . json_last_error_msg());
        return null;
    }
    
    return is_array($data) ? $data : null;
}

// Obtener datos
$asignaturas = obtenerDatosAPI($endpoints['asignaturas']) ?? [];
$docentes = obtenerDatosAPI($endpoints['docentes']) ?? [];

// Filtrar asignaturas por carrera
$asignaturasFiltradas = array_values(array_filter($asignaturas, function($a) use ($carreraId) {
    return isset($a['carreraId']) && $a['carreraId'] == $carreraId;
}));

// Indexar docentes por ID para fácil acceso
$docentesIndexados = [];
foreach ($docentes as $docente) {
    if (isset($docente['docenteId'])) {
        $docentesIndexados[$docente['docenteId']] = $docente;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Comentario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="CreateComentarios.css">
    <link rel="Icon" href="/Imagenes/guia-turistico 3.png">
</head>
<body>
    <div class="navbar">
        <button onclick="window.location.href='ConsultaComentarios.php'" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </button>
    </div>
    <div class="container">
        <div class="form-header">
            <i class="bi bi-chat-square-text-fill"></i> Crear Comentario
        </div>
        <div class="form-body">
            <form action="guardar_comentario.php" method="post">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <div class="date-field"><?= date('d/m/Y') ?></div>
                    <input type="hidden" name="fechaComentario" value="<?= date('Y-m-d\TH:i:s') ?>">
                </div>
                
                <div class="form-group">
                    <label for="asignaturaId">Asignatura:</label>
                    <select id="asignaturaId" name="asignaturaId" class="form-control" required>
                        <option value="">Seleccione una asignatura</option>
                        <?php foreach ($asignaturasFiltradas as $asignatura): 
                            $docenteId = $asignatura['docenteId'] ?? null;
                            $docenteNombre = isset($docentesIndexados[$docenteId]['nombre']) 
                                ? $docentesIndexados[$docenteId]['nombre'] 
                                : 'Docente no asignado';
                        ?>
                            <option value="<?= $asignatura['asignaturaId'] ?>" 
                                    data-docente-id="<?= $docenteId ?>"
                                    data-docente-nombre="<?= htmlspecialchars($docenteNombre) ?>"
                                    data-docente-email="<?= isset($docentesIndexados[$docenteId]['email']) 
                                        ? htmlspecialchars($docentesIndexados[$docenteId]['email']) 
                                        : '' ?>">
                                <?= htmlspecialchars($asignatura['nombreAsignatura']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="docenteInfo" class="form-label">Docente:</label>
                    <div id="docenteInfo" class="docente-info-box">
                        <div id="teacherAvatar" class="teacher-avatar">
                        </div>
                        <div class="teacher-details">
                            <span id="teacherName" class="">Seleccione una asignatura para que se coloque el docente</span>
                            <span id="teacherEmail" class="teacher-email"></span>
                        </div>
                    </div>
                    <input type="hidden" id="docenteId" name="docenteId" value="">
                </div>
                
                <div class="form-group">
                    <label for="comentario">Comentario:</label>
                    <textarea id="comentario" name="comentario" class="form-control" required 
                        placeholder="Escribe tu comentario aquí..." rows="5"></textarea>
                </div>
                
                <input type="hidden" name="usuarioId" value="<?= $_SESSION['usuario_id'] ?? 1 ?>">
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-save-fill"></i> Guardar Comentario
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturaSelect = document.getElementById('asignaturaId');
            const docenteIdInput = document.getElementById('docenteId');
            const teacherName = document.getElementById('teacherName');
            const teacherEmail = document.getElementById('teacherEmail');
            
            // Actualizar información del docente cuando cambia la asignatura
            asignaturaSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const docenteId = selectedOption.dataset.docenteId;
                const docenteNombre = selectedOption.dataset.docenteNombre;
                const docenteEmail = selectedOption.dataset.docenteEmail;
                
                if (docenteId) {
                    teacherName.textContent = docenteNombre;
                    teacherEmail.textContent = docenteEmail;
                    docenteIdInput.value = docenteId;
                    
                    // Cambiar clases para resaltar la información
                    teacherName.parentElement.parentElement.classList.remove('no-teacher');
                    teacherName.parentElement.parentElement.classList.add('has-teacher');
                } else {
                    teacherName.textContent = 'No hay docente asignado';
                    teacherEmail.textContent = '';
                    docenteIdInput.value = '';
                    teacherName.parentElement.parentElement.classList.remove('has-teacher');
                    teacherName.parentElement.parentElement.classList.add('no-teacher');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>