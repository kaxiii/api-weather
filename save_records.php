<?php

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

// save_records.php
header('Content-Type: application/json');

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Ruta absoluta al directorio data
$dataDir = __DIR__ . '/data';
$filePath = $dataDir . '/records.json';

// Crear directorio si no existe
if (!file_exists($dataDir)) {
    if (!mkdir($dataDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio data']);
        exit;
    }
}

// Obtener datos JSON del cuerpo de la solicitud
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if ($data === null) {
    echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
    exit;
}

// Función para sanitizar registros
function sanitizeRecord($record) {
    return [
        'value' => isset($record['value']) && is_numeric($record['value']) ? floatval($record['value']) : null,
        'location' => isset($record['location']) ? 
            preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_,]/u', '', $record['location']) : '',
        'date' => isset($record['date']) && strtotime($record['date']) ? 
            date('Y-m-d H:i:s', strtotime($record['date'])) : ''
    ];
}

// Sanitizar todos los campos
$sanitizedData = [
    'max_temp' => sanitizeRecord($data['max_temp'] ?? []),
    'min_temp' => sanitizeRecord($data['min_temp'] ?? []),
    'max_uv' => sanitizeRecord($data['max_uv'] ?? []),
    'max_wind' => sanitizeRecord($data['max_wind'] ?? []),
    'max_radiation' => sanitizeRecord($data['max_radiation'] ?? []),
    'max_rain' => sanitizeRecord($data['max_rain'] ?? []),
    'max_snow' => sanitizeRecord($data['max_snow'] ?? [])
];

// Intentar escribir el archivo
try {
    $bytesWritten = file_put_contents($filePath, json_encode($sanitizedData, JSON_PRETTY_PRINT));
    
    if ($bytesWritten === false) {
        throw new Exception('Error al escribir en el archivo');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Registrar el error en el log del servidor
    error_log('Error al guardar records: ' . $e->getMessage());
    
    // Enviar respuesta de error
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar los datos',
        'details' => $e->getMessage()
    ]);
}
?>