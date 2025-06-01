<?php
$ciudad = $_POST['ciudad'] ?? '';

// Crear contexto con User-Agent
$options = [
    'http' => [
        'header' => "User-Agent: clima-app/1.0\r\n"
    ]
];
$context = stream_context_create($options);

// Obtener coordenadas desde Nominatim con encabezado válido
$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ciudad);
$response = file_get_contents($url, false, $context);

$geo = json_decode($response, true);
if (!$geo || count($geo) === 0) {
    echo "<p>Error de ubicación.</p>";
    exit;
}

$lat = $geo[0]['lat'];
$lon = $geo[0]['lon'];

$weather = json_decode(file_get_contents("https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=relative_humidity_2m"), true);
$humedad = $weather['current']['relative_humidity_2m'];

echo "<img src='https://img.icons8.com/ios-filled/100/000000/hygrometer.png' width='50'>";
echo "<h3>Humedad</h3>";
echo "<div style='font-size:2em;'>$humedad%</div>";
