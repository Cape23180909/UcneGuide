<?php
session_start();

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: DescripcionAsignaturas.php?error=1");
    exit();
}

// Recoger y sanitizar datos
$asignaturaId = filter_input(INPUT_POST, 'asignaturaId', FILTER_SANITIZE_NUMBER_INT);
$docenteId = filter_input(INPUT_POST, 'docenteId', FILTER_SANITIZE_NUMBER_INT);
$codigoAsignatura = filter_input(INPUT_POST, 'codigoAsignatura', FILTER_SANITIZE_STRING);
$nombreAsignatura = filter_input(INPUT_POST, 'nombreAsignatura', FILTER_SANITIZE_STRING);
$comentarioTexto = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING);
$usuarioId = filter_input(INPUT_POST, 'usuarioId', FILTER_SANITIZE_NUMBER_INT) ?: 0;

// Validación básica
if (empty($comentarioTexto) || empty($asignaturaId) || empty($docenteId)) {
    $_SESSION['error'] = "Datos incompletos";
    header("Location: DescripcionAsignaturas.php?codigo=" . urlencode($codigoAsignatura) . "&error=1");
    exit();
}

// Preparar datos para la API
$data = [
    'comentarioId' => 0,  // La API debe generar el ID
    'comentario' => $comentarioTexto,
    'docenteId' => $docenteId,
    'asignaturaId' => $asignaturaId,
    'usuarioId' => $usuarioId,
    'fechaComentario' => date('Y-m-d\TH:i:s') // Formato ISO 8601 sin milisegundos
];

// Configurar cURL
$ch = curl_init("https://api-ucne-emfugwekcfefc3ef.eastus-01.azurewebsites.net/api/Comentarios");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);

// Ejecutar y manejar respuesta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    $_SESSION['error'] = "Error de conexión: $error";
    header("Location: DescripcionAsignaturas.php?codigo=" . urlencode($codigoAsignatura) . "&error=1");
    exit();
}

// Validar la respuesta HTTP
if ($httpCode === 201 || $httpCode === 200) {
    $_SESSION['success'] = "Comentario guardado exitosamente";
    header("Location: ConsultaComentarios.php?success=1");
} else {
    $responseData = json_decode($response, true);
    $errorMsg = $responseData['message'] ?? "Error desconocido al guardar el comentario";
    $_SESSION['error'] = "Error $httpCode: $errorMsg";
    header("Location: DescripcionAsignaturas.php?codigo=" . urlencode($codigoAsignatura) . "&error=1");
}
exit();
?>