<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Asignatura</title>
    <link rel="stylesheet" href="DescripcionAsignaturas.css">
</head>
<body>
    <!-- Barra de navegación -->
    <div class="navbar">
        <a href="Menu.php">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo" class="logo">
        </a>
        <span class="title">Detalles de la asignatura</span>
    </div>

    <div class="container">
        <?php
        $codigoAsignatura = $_GET['codigo'] ?? '';
        $apiBaseUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas";
        $apiDocentesUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Docentes"; // URL de la API de docentes

        // Función para obtener datos de la API
        function obtenerDatosAPI($url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true) ?: [];
        }

        // Función para obtener el nombre del docente desde la API
        function obtenerNombreDocente($docenteId, $apiDocentesUrl) {
            if (empty($docenteId)) {
                return null; // Si no hay ID, devuelve null
            }

            // Hacer la solicitud a la API de docentes
            $docenteUrl = $apiDocentesUrl . "/" . $docenteId;
            $docenteData = obtenerDatosAPI($docenteUrl);

            // Verificar si se obtuvo el nombre del docente
            return $docenteData['nombre'] ?? null;
        }

        // Obtener todas las asignaturas
        $asignaturas = obtenerDatosAPI($apiBaseUrl);
        $detalleAsignatura = [];

        // Buscar la asignatura específica por su código
        foreach ($asignaturas as $asignatura) {
            if ($asignatura['codigoAsignatura'] === $codigoAsignatura) {
                $detalleAsignatura = $asignatura;
                break;
            }
        }
        ?>

        <!-- Detalles de la asignatura -->
        <div class="section">
            <h2><?= htmlspecialchars($detalleAsignatura['nombreAsignatura'] ?? "Asignatura no encontrada") ?></h2>
            <p><strong>Código:</strong> <?= htmlspecialchars($detalleAsignatura['codigoAsignatura'] ?? "N/A") ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($detalleAsignatura['descripcionAsignatura'] ?? "Sin descripción")) ?></p>
        </div>

        <!-- Docentes -->
        <div class="section">
            <h3>Docente:</h3>
            <p><?= htmlspecialchars(obtenerNombreDocente($detalleAsignatura['docenteId'] ?? null, $apiDocentesUrl) ?? "No asignado") ?></p>
        </div>

        <!-- Comentarios -->
        <div class="section comentarios">
            <h3>Comentarios</h3>
            <form action="guardar_comentario.php" method="post">
                <input type="hidden" name="codigoAsignatura" value="<?= htmlspecialchars($codigoAsignatura) ?>">
                <textarea name="comentario" placeholder="Escribe tu comentario" required></textarea>
                <button type="submit">Comentar</button>
            </form>
        </div>
    </div>
</body>
</html>