<?php
// URL de la API
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Deshabilitar verificación SSL si es necesario

// Ejecutar y obtener respuesta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decodificar JSON y manejar posibles errores
$asignaturas = json_decode($response, true);
if ($httpCode !== 200 || !is_array($asignaturas)) {
    $asignaturas = []; // Si hay error, usar array vacío
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias Sistemas</title>
    <link rel="stylesheet" href="MateriasSistemas.css">
</head>
<body>
    <div class="container">
        <header>
        <a href="Menu.php">
    <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
</a>
            Ingeniería en Sistemas y Cómputos
            <!-- <button class="back-button">&#x21A9;</button> -->
        </header>
        <div class="materias">
            <?php if (!empty($asignaturas)): ?>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <div class="materia">
                        <strong><?php echo htmlspecialchars($asignatura['codigoAsignatura'] ?? 'N/A'); ?></strong> 
                        <?php echo htmlspecialchars($asignatura['nombreAsignatura'] ?? 'Sin nombre'); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No se encontraron materias disponibles.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>