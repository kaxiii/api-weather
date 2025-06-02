<?php
// save_records.php
header('Content-Type: application/json');

// Asegurar que el directorio 'data' existe
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Función para sanitizar y validar un registro
function sanitizeRecord($record) {
    // Si el valor es -Infinity, lo convertimos a null para el almacenamiento
    $value = ($record['value'] === -INF || $record['value'] === -Infinity) ? null : $record['value'];
    
    return [
        'value' => is_numeric($value) ? floatval($value) : null,
        'location' => isset($record['location']) ? 
            preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_,]/u', '', $record['location']) : '',
        'date' => isset($record['date']) && strtotime($record['date']) ? 
            date('Y-m-d H:i:s', strtotime($record['date'])) : ''
    ];
}

// Campos a sanitizar
$fields = ['max_temp', 'min_temp', 'max_uv', 'max_wind', 'max_radiation', 'max_rain', 'max_snow'];
$sanitizedData = [];

foreach ($fields as $field) {
    if (isset($data[$field])) {
        $sanitizedData[$field] = sanitizeRecord($data[$field]);
    }
}

// Guardar archivo
file_put_contents('data/records.json', json_encode($sanitizedData, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
?>