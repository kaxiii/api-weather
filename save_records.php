<?php
// save_records.php
header('Content-Type: application/json');

// Asegurar que el directorio data existe
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar y sanitizar datos
function sanitizeRecord($record) {
    return [
        'value' => filter_var($record['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'location' => preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_,]/u', '', $record['location']),
        'date' => date('Y-m-d H:i:s', strtotime($record['date']))
    ];
}

$sanitizedData = [
    'max_temp' => sanitizeRecord($data['max_temp']),
    'min_temp' => sanitizeRecord($data['min_temp']),
    'max_uv' => sanitizeRecord($data['max_uv']),
    'max_wind' => sanitizeRecord($data['max_wind']),
    'max_radiation' => sanitizeRecord($data['max_radiation'])
];

file_put_contents('data/records.json', json_encode($sanitizedData, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
?>