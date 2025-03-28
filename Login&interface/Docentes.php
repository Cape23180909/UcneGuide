<?php
session_start();
session_regenerate_id(true); // Protege contra secuestro de sesión

// Verificar que el usuario está autenticado
if (!isset($_SESSION['authToken'])) {
    header('Location: Login.php');
    exit();
}

// Obtener carreraId del usuario autenticado
$carreraId = $_SESSION['usuario']['carreraId'] ?? null;

// Verificación de seguridad
if (is_null($carreraId)) {
    die("Error: No se ha identificado la carrera del usuario.");
}

// Obtener datos de la API de manera segura
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Docentes";
$maestros = [];

try {
    $response = file_get_contents($apiUrl);
    if ($response === false) {
        throw new Exception("Error al obtener datos de la API.");
    }
    $maestros = json_decode($response, true);
} catch (Exception $e) {
    error_log("Error en API: " . $e->getMessage());
    die("Error al cargar los docentes. Intente más tarde.");
}

// Filtrar docentes solo de la carrera del usuario
$maestrosFiltrados = array_filter($maestros, function ($docente) use ($carreraId) {
    return isset($docente['carreraId']) && $docente['carreraId'] == $carreraId;
});

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Docentes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Docentes.css">
    <link rel="Icon" href="/Imagenes/guia-turistico 3.png">
    <script>
        // Enviar los docentes filtrados al frontend
        const maestrosData = <?= json_encode(array_values($maestrosFiltrados)) ?>;
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="Menu.php" class="logo-button">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="header-logo">
        </a>
    </div>
    
    <div class="academic-container">
        <div class="header-section">
            <div class="icon-circle">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h1 class="main-title">Docentes</h1>
            
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Buscar maestros..." id="searchInput">
                <button class="filter-btn" onclick="filterTeachers()">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <div class="teacher-list" id="teacherContainer">
            <!-- Contenido dinámico -->
        </div>
    </div>

    <script>
    function renderTeachers(teachers) {
        const container = document.getElementById('teacherContainer');
        container.innerHTML = '';
        
        teachers.forEach(teacher => {
            const teacherCard = document.createElement('div');
            teacherCard.className = 'teacher-card';
            teacherCard.innerHTML = `
                <div class="teacher-info">
                    <div class="teacher-name">${teacher.nombre}</div>
                    <div class="teacher-details">
                        <span class="teacher-role">${teacher.rol || 'Docente'}</span>
                        <span class="teacher-email">${teacher.email || ''}</span>
                    </div>
                </div>
                <div class="action-arrow"></div>
            `;
            container.appendChild(teacherCard);
        });
    }

    function filterTeachers() {
        const term = document.getElementById('searchInput').value.toLowerCase();
        const filtered = maestrosData.filter(teacher => {
            return Object.values(teacher).some(value => 
                String(value).toLowerCase().includes(term)
            );
        });
        renderTeachers(filtered);
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderTeachers(maestrosData);
        document.getElementById('searchInput').addEventListener('input', filterTeachers);
    });
    </script>
</body>
</html>