<?php
// Iniciar la sesión para mantener mensajes entre redirecciones
session_start();

// Configurar headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: DescripcionAsignaturas.php?codigo=" . ($_POST['codigoAsignatura'] ?? '') . "&error=1");
    exit();
}

// Recoger y sanitizar datos
$nombreAsignatura = filter_input(INPUT_POST, 'nombreAsignatura', FILTER_SANITIZE_STRING);
$docenteId = filter_input(INPUT_POST, 'docenteId', FILTER_SANITIZE_STRING);
$nombreDocente = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$comentarioTexto = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING);

// Validación básica
if (empty($comentarioTexto) || empty($nombreAsignatura)) {
    $_SESSION['error'] = "Datos incompletos";
    header("Location: DescripcionAsignaturas.php?error=1");
    exit();
}

// Preparar datos para la API
$data = [
    'comentarioId' => 0, // Puedes omitir esto si la API lo genera automáticamente
    'comentario' => $comentarioTexto,
    'docenteId' => $docenteId,
    'nombreAsignatura' => $nombreAsignatura, // Aquí se incluye el nombre de la asignatura
    'usuarioId' => 0, // Reemplaza con el ID del usuario si está disponible
    'fechaComentario' => date('Y-m-d\TH:i:s.v\Z') // Formato ISO 8601
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
    CURLOPT_TIMEOUT => 10 // Tiempo de espera de 10 segundos
]);

// Ejecutar y manejar respuesta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Verificar si hubo error en la conexión
if ($response === false) {
    $_SESSION['error'] = "Error de conexión: $error";
    header("Location: DescripcionAsignaturas.php?codigo=$codigoAsignatura&error=1");
    exit();
}

// Decodificar la respuesta JSON
$responseData = json_decode($response, true);

// Redirección con feedback
if ($httpCode === 201 || $httpCode === 200) {
    $_SESSION['success'] = "Comentario guardado exitosamente";
    header("Location: ConsultaComentarios.php?success=1");
} else {
    $errorMsg = $responseData['message'] ?? "Error desconocido al guardar el comentario";
    $_SESSION['error'] = "Error $httpCode: $errorMsg";
    header("Location: DescripcionAsignaturas.php?codigo=$codigoAsignatura&error=1");
}
exit();
?>