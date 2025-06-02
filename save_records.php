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

// Validar y sanitizar datos (ejemplo básico)
$sanitizedData = [];
foreach ($data as $location => $records) {
    $sanitizedLocation = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_,]/u', '', $location);
    $sanitizedData[$sanitizedLocation] = [
        'max_temp' => filter_var($records['max_temp'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'min_temp' => filter_var($records['min_temp'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'max_uv' => filter_var($records['max_uv'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'max_wind' => filter_var($records['max_wind'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'max_radiation' => filter_var($records['max_radiation'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'updated' => date('Y-m-d\TH:i:s\Z', strtotime($records['updated']))
    ];
}

file_put_contents('data/records.json', json_encode($sanitizedData, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
?>