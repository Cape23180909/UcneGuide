<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: Login.php?error=no_autenticado');
    exit();
}

// Validar que todos los campos requeridos están presentes
$requiredFields = ['asignaturaId', 'docenteId', 'comentario'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        header('Location: DescripcionAsignaturas.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&error=campos_requeridos');
        exit();
    }
}

// Preparar datos para enviar a la API
$data = [
    'asignaturaId' => $_POST['asignaturaId'],
    'docenteId' => $_POST['docenteId'],
    'usuarioId' => $_SESSION['usuario']['usuarioId'],
    'comentario' => $_POST['comentario'],
    'fechaComentario' => date('Y-m-d\TH:i:s') // Formato ISO 8601
];

// Enviar a la API (ejemplo con cURL)
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    header('Location: ConsultaComentarios.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&success=comentario_guardado');
} else {
    header('Location: ConsultaComentarios.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&error=error_api');
}
exit();
?>