<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facultades Universitarias</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Facultades.css">
</head>
<body>
    <div class="academic-container">
        <div class="header-section">
        <a href="Menu.php" class="logo-button">
                <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="header-logo">
            </a>
            <div class="icon-circle">
                <i class="fas fa-university"></i>
            </div>
            <div class="top-bar"></div>
            <h1 class="main-title">Facultades</h1>
        </div>

        <div class="faculties-list">
            <!-- Facultad de Ingeniería -->
            <div class="faculty-group">
                <div class="faculty-header">
                    <i class="fas fa-cogs"></i>
                    <h2 class="faculty-title">Facultad de Ingeniería</h2>
                </div>
                <div class="career-grid">
    <a href="MateriasSistemas.php" class="career-link">
        <button class="career-card">Ingeniería en Sistemas y Cómputos</button>
    </a>
    <a href="AsignaturasCivil.php" class="career-link civil-button">
        <button class="career-card">Ingeniería Civil</button>
    </a>
    <a href="MateriasGeomatica.php" class="career-link">
        <button class="career-card">Ingeniería Geomática</button>
    </a>
    <a href="MateriasArquitectura.php" class="career-link">
        <button class="career-card">Arquitectura</button>
    </a>
</div>
            </div>

            <!-- Facultad de Educación -->
            <div class="faculty-group">
                <div class="faculty-header">
                    <i class="fas fa-book-open"></i>
                    <h2 class="faculty-title">Facultad de Educación</h2>
                </div>
                <div class="career-grid">
                    <button class="career-card">Matemática Orientada a la Educación Secundaria</button>
                </div>
            </div>

            <!-- Ciencias de la Salud -->
            <div class="faculty-group">
                <div class="faculty-header">
                    <i class="fas fa-heartbeat"></i>
                    <h2 class="faculty-title">Ciencias de la Salud</h2>
                </div>
                <div class="career-grid">
                    <button class="career-card">Medicina</button>
                    <button class="career-card">Psicología</button>
                    <button class="career-card">Enfermería</button>
                    <button class="career-card">Odontología</button>
                </div>
            </div>

            <!-- Ciencias Económicas y Sociales -->
            <div class="faculty-group">
                <div class="faculty-header">
                    <i class="fas fa-chart-line"></i>
                    <h2 class="faculty-title">Ciencias Económicas y Sociales</h2>
                </div>
                <div class="career-grid">
                    <button class="career-card">Mercadeo</button>
                    <button class="career-card">Contabilidad</button>
                    <button class="career-card">Administración de Empresas</button>
                    <button class="career-card">Administración Turística y Hotelera</button>
                </div>
            </div>

            <!-- Ciencias Jurídicas -->
            <div class="faculty-group">
                <div class="faculty-header">
                    <i class="fas fa-balance-scale"></i>
                    <h2 class="faculty-title">Ciencias Jurídicas</h2>
                </div>
                <div class="career-grid">
                    <button class="career-card">Derecho</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>