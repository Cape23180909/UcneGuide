<?php
// Obtener datos de la API
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Docentes";
$maestros = @json_decode(file_get_contents($apiUrl), true) ?: [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Docentes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="Icon" href="/Imagenes/UCNE.jpg">
    <link rel="stylesheet" href="Docentes.css">
    <script>
        const maestrosData = <?= json_encode($maestros) ?>;
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="Menu.php" class="logo-button">
            <img src="/Imagenes/UCNE.jpg" alt="Logo" class="header-logo">
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