<?php
$codigoAsignatura = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/AsignaturaDetalles?CodigoAsignatura=" . urlencode($codigoAsignatura);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$detalleAsignatura = json_decode($response, true);
if ($httpCode !== 200 || !is_array($detalleAsignatura)) {
    $detalleAsignatura = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Asignatura</title>
    <link rel="stylesheet" href="DescripcionAsignaturas.css">
</head>
<body>
    <div class="container">
        <header>
            <a href="Menu.php">
                <img src="/Imagenes/guia-turistico 3.png" alt="Logo">
            </a>
            Detalles de la Asignatura
        </header>

        <?php if (!empty($detalleAsignatura)): ?>
            <h3><?php echo htmlspecialchars($detalleAsignatura['nombreAsignatura']); ?></h3>
            <p><strong>Código:</strong> <?php echo htmlspecialchars($detalleAsignatura['codigoAsignatura']); ?></p>
            <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($detalleAsignatura['descripcionAsignatura'])); ?></p>
            <p><strong>Duración:</strong> <?php echo htmlspecialchars($detalleAsignatura['duracion']); ?> horas</p>
            <p><strong>Requisitos:</strong> <?php echo htmlspecialchars($detalleAsignatura['requisitos']); ?></p>
            <p><strong>Docente ID:</strong> <?php echo htmlspecialchars($detalleAsignatura['docenteId']); ?></p>
            <p><strong>Carrera ID:</strong> <?php echo htmlspecialchars($detalleAsignatura['carreraId']); ?></p>
        <?php else: ?>
            <p>No se encontraron detalles para esta asignatura.</p>
        <?php endif; ?>
    </div>
</body>
</html>