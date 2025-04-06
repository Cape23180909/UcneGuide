<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: Login.php?error=no_autenticado');
    exit();
}

// Validar que todos los campos requeridos están presentes
$requiredFields = ['comentarioId', 'asignaturaId', 'docenteId', 'comentario'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        header('Location: ConsultaComentarios.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&error=campos_requeridos');
        exit();
    }
}

// Preparar datos para enviar a la API
$data = [
    'comentarioId' => $_POST['comentarioId'], // ID del comentario a actualizar
    'asignaturaId' => $_POST['asignaturaId'],
    'docenteId' => $_POST['docenteId'],
    'usuarioId' => $_SESSION['usuario']['usuarioId'],
    'comentario' => $_POST['comentario'],
    'fechaComentario' => date('Y-m-d\TH:i:s') // Actualizar la fecha también
];

// Enviar a la API usando PUT para actualizar
$apiUrl = "https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios/" . urlencode($_POST['comentarioId']);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "PUT", // Método HTTP PUT para actualizar
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    header('Location: ConsultaComentarios.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&success=comentario_actualizado');
} else {
    header('Location: DescripcionAsignaturas.php?codigo=' . urlencode($_POST['codigoAsignatura']) . '&error=error_api');
}
exit();
?>