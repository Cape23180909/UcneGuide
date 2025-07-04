<?php
// URL de la API
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Asignaturas?CarreraId=7";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Ejecutar y obtener respuesta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decodificar JSON y manejar posibles errores
$asignaturas = json_decode($response, true);
if ($httpCode !== 200 || !is_array($asignaturas)) {
    $asignaturas = [];
}

$carreraIdBuscado = 7; // ID específico para Sistemas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaturas Psicologia</title>
    <link rel="stylesheet" href="AsignaturasPsicologia.css">
</head>
<body>
    <div class="container">
        <header>
        <a href="Facultades.php">
            <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
        </a>
        Psicologia
        </header>
        <div class="materias">
            <?php if (!empty($asignaturas)): 
                $mostradas = 0; // Contador de materias mostradas
                ?>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <?php if (isset($asignatura['carreraId']) && $asignatura['carreraId'] == $carreraIdBuscado): ?>
                        <div class="materia">
                            <strong><?php echo htmlspecialchars($asignatura['codigoAsignatura'] ?? 'N/A'); ?></strong> 
                            <?php echo htmlspecialchars($asignatura['nombreAsignatura'] ?? 'Sin nombre'); ?>
                        </div>
                        <?php $mostradas++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if ($mostradas === 0): ?>
                    <p>No se encontraron materias para esta carrera.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No se encontraron materias disponibles.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>