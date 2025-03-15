<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="menu.css">
</head>
<body>
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
                        <i class="fas fa-user-edit"></i>
                        <span>Editar perfil</span>
                    </div>
                    <a href="Login.php" class="menu-item-dropdown">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Menú principal -->
    <nav class="menu-container">
        <div class="menu-item">
            <img src="/Imagenes/facultades.png" alt="Facultades" class="menu-icon">
            <span>Facultades</span>
        </div>
        
        <div class="menu-item">
            <img src="/Imagenes/Subgerencias.png" alt="Sugerencias" class="menu-icon">
            <span>Sugerencias</span>
        </div>
        
        <div class="menu-item">
            <img src="/Imagenes/Maestros.png" alt="Maestros" class="menu-icon">
            <span>Maestros</span>
        </div>
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