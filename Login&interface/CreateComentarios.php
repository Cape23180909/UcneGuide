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
    'asignaciones' => "$apiBaseUrl/AsignacionDocentes"
];

// Función mejorada para obtener datos de la API
function obtenerDatosAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
$asignaciones = obtenerDatosAPI($endpoints['asignaciones']) ?? [];

// Filtrar asignaturas por carrera
$asignaturasFiltradas = array_values(array_filter($asignaturas, function($a) use ($carreraId) {
    return isset($a['carreraId']) && $a['carreraId'] == $carreraId;
}));

// Construir relación completa de docentes por asignatura
$docentesPorAsignatura = [];
foreach ($asignaciones as $asignacion) {
    if (!isset($asignacion['asignaturaId']) || !isset($asignacion['docenteId'])) continue;
    
    $asigId = $asignacion['asignaturaId'];
    $docId = $asignacion['docenteId'];
    
    // Buscar docente completo
    $docente = current(array_filter($docentes, function($d) use ($docId) {
        return isset($d['docenteId']) && $d['docenteId'] == $docId;
    }));
    
    if ($docente) {
        if (!isset($docentesPorAsignatura[$asigId])) {
            $docentesPorAsignatura[$asigId] = [];
        }
        
        $docentesPorAsignatura[$asigId][] = [
            'id' => $docente['docenteId'],
            'nombre' => $docente['nombre'] ?? 'Docente no disponible',
            'email' => $docente['email'] ?? ''
        ];
    }
}

// Depuración crítica
error_log("Total asignaturas: " . count($asignaturas));
error_log("Asignaturas filtradas: " . count($asignaturasFiltradas));
error_log("Docentes por asignatura: " . json_encode(array_keys($docentesPorAsignatura)));
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
</head>
<body>
    <div class="navbar">
        <button onclick="window.location.href='Menu.php'" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </button>
        <span style="margin-left: 80px; font-size: 1.2em;">Crear Nuevo Comentario</span>
    </div>
    <div class="container">
        <div class="form-header">
            <i class="bi bi-chat-square-text-fill"></i> Nuevo Comentario
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
                        <?php foreach ($asignaturasFiltradas as $asignatura): ?>
                            <option value="<?= $asignatura['asignaturaId'] ?>"><?= htmlspecialchars($asignatura['nombreAsignatura']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                        <label for="docenteId" class="form-label">Docente:</label>
                        <select id="docenteId" name="docenteId" class="form-select" required disabled>
                            <option value="">Seleccione primero una asignatura</option>
                            <?php 
                            // Opción alternativa: precargar todos los docentes con data-attributes
                            foreach ($docentes as $docente): ?>
                                <option value="<?= htmlspecialchars($docente['docenteId']) ?>" 
                                        data-asignatura="<?= htmlspecialchars($docente['asignaturaId'] ?? '') ?>"
                                        style="display: none;">
                                    <?= htmlspecialchars($docente['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="teacherInfo" class="teacher-info" style="display: none;">
                            <div class="teacher-avatar" id="teacherAvatar"></div>
                            <div class="teacher-details">
                                <span id="teacherName" class="fw-bold"></span>
                                <span id="teacherEmail" class="teacher-email"></span>
                            </div>
                        </div>
                    </div>
                <div class="form-group">
                    <label for="comentario">Comentario:</label>
                    <textarea id="comentario" name="comentario" class="form-control" required placeholder="Escribe tu comentario aquí..."></textarea>
                </div>
                <input type="hidden" name="usuarioId" value="<?= $_SESSION['usuario_id'] ?? 1 ?>">
                <div class="form-group">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-save-fill"></i> Guardar Comentario
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Versión alternativa con precarga de docentes
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturaSelect = document.getElementById('asignaturaId');
            const docenteSelect = document.getElementById('docenteId');
            const teacherInfo = document.getElementById('teacherInfo');
            
            // Mostrar/ocultar docentes según asignatura seleccionada
            function actualizarDocentes() {
                const asignaturaId = asignaturaSelect.value;
                const options = docenteSelect.querySelectorAll('option');
                
                // Resetear
                docenteSelect.value = '';
                teacherInfo.style.display = 'none';
                docenteSelect.disabled = !asignaturaId;
                
                if (!asignaturaId) return;
                
                // Mostrar solo docentes de esta asignatura
                options.forEach(option => {
                    if (option.value === '') return; // Mantener opción por defecto
                    
                    if (option.dataset.asignatura === asignaturaId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                docenteSelect.disabled = false;
            }
            
            // Mostrar información del docente seleccionado
            function mostrarInfoDocente() {
                if (docenteSelect.value && docenteSelect.selectedIndex > 0) {
                    const selectedOption = docenteSelect.options[docenteSelect.selectedIndex];
                    teacherInfo.style.display = 'flex';
                } else {
                    teacherInfo.style.display = 'none';
                }
            }
            
            // Asignar eventos
            asignaturaSelect.addEventListener('change', actualizarDocentes);
            docenteSelect.addEventListener('change', mostrarInfoDocente);
            
            // Inicializar si hay una asignatura seleccionada
            if (asignaturaSelect.value) {
                actualizarDocentes();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>