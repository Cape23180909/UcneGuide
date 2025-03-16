<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargando...</title>
    <link rel="stylesheet" href="Index.css">
</head>
<body>
    <div class="loading-container">
        <div class="loading-circle">
        <img src="/Imagenes/guia-turistico 3.png" alt="Cargando">
        </div>
        <p class="loading-text">Cargando...</p>
    </div>
</body>
</html>

<?php
$segundos_espera = 10; // Ajusta el tiempo de carga aquí
header("refresh:$segundos_espera; url=Login.php"); // Redireccionar a la pagina de Login.php
?>