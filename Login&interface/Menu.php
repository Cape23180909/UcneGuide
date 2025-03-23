<?php
session_start();
if (!isset($_SESSION['authToken'])) {
    header('Location: Login.php');
    exit();
}

$usuario_id = $_SESSION['usuario']['usuarioId'] ?? null;
$nombreUsuario = $_SESSION['usuario']['nombre'] ?? "Usuario";
$carreraId = $_SESSION['usuario']['carreraId'] ?? null;

// Función para obtener el enlace según la carrera
function obtenerEnlaceAsignaturas($carreraId) {
    switch ($carreraId) {
        case 1: return 'MateriasSistemas.php';
        case 2: return 'AsignaturasCivil.php';
        case 3: return 'AsignaturasGeomatica.php';
        case 4: return 'AsignaturasAdminEmpresa.php';
        case 5: return 'AsignaturasMatematicas.php';
        case 6: return 'AsignaturasMedicina.php';
        case 7: return 'AsignaturasPsicologia.php';
        case 8: return 'AsignaturasEnfermeria.php';
        case 9: return 'AsignaturasOdontologia.php';
        case 10: return 'AsignaturasMercadeo.php';
        case 11: return 'AsignaturasContabilidad.php';
        case 12: return 'AsignaturasArquitectura.php';
        case 13: return 'AsignaturasTurismo.php';
        case 14: return 'AsignaturasDerecho.php';
        default: 
            // Podemos redirigir o manejar el error según necesidades
            return 'carrera_no_registrada.php'; // Nueva página para carreras no identificadas
    }
}

// Verificamos que tenga carrera asignada
if(is_null($carreraId)) {
    header('Location: error.php?codigo=sin_carrera');
    exit();
}

$enlaceAsignaturas = obtenerEnlaceAsignaturas($carreraId);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="menu.css">
</head>
    <!-- Encabezado -->
    <header class="menu-header">
        <div class="header-content">
            <a href="Menu.php" class="logo-button">
                <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="header-logo">
            </a>
            <h1>Menú Principal</h1>
            
            <div class="user-container" onclick="toggleMenu()">
                <div class="user-icon"></div>
                <div class="dropdown-menu">
    <div class="menu-item-dropdown">
        <a href="EditUser.php?id=<?= urlencode($usuario_id) ?>" class="menu-item-dropdown">
            <i class="fas fa-user-edit"></i>
            Actualizar Usuario
        </a>
    </div>
    <div class="menu-item-dropdown">
        <a href="Logout.php" class="menu-item-dropdown">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar sesión</span>
        </a>
    </div>
</div>

        </div>
        </div>
    </header>

    <!-- Menú principal -->
    <nav class="menu-container">
        <a href="<?php echo $enlaceAsignaturas; ?>" class="menu-link">
            <div class="menu-item">
                <img src="/Imagenes/Asignaturas.png" alt="Facultades" class="menu-icon">
                <span>Asignaturas</span>
            </div>
        </a>

        <div class="menu-item">
            <img src="/Imagenes/Subgerencias.png" alt="Sugerencias" class="menu-icon">
            <span>Comentarios</span>
        </div>
        
        <nav class="menu container">
        <div class="menu-item">
        <a href="Docentes.php" class="menu-link">
            <img src="/Imagenes/Maestros.png" alt="Docentes" class="menu-icon">
            <span>Docentes</span>
        </div>
</a>
    </nav>
</nav>

    <script>
        function toggleMenu() {
            const menu = document.querySelector('.dropdown-menu');
            menu.classList.toggle('show');
        }

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            const menu = document.querySelector('.dropdown-menu');
            if (!e.target.closest('.user-container')) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>