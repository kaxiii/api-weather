<?php
$ciudad = $_POST['ciudad'] ?? '';

// Crear contexto HTTP con User-Agent para Nominatim
$options = [
    'http' => [
        'header' => "User-Agent: clima-app/1.0\r\n"
    ]
];
$context = stream_context_create($options);

// Obtener coordenadas desde Nominatim
$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ciudad);
$response = file_get_contents($url, false, $context);
$geo = json_decode($response, true);

if (!$geo || count($geo) === 0) {
    echo "<p>Error de ubicación.</p>";
    exit;
}

$lat = $geo[0]['lat'];
$lon = $geo[0]['lon'];

// Obtener índice UV desde Open-Meteo
$weather_url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=uv_index";
$weather = json_decode(file_get_contents($weather_url), true);

if (!isset($weather['current']['uv_index'])) {
    echo "<p>Error al obtener el índice UV.</p>";
    exit;
}

$uv = $weather['current']['uv_index'];

// Mostrar tarjeta
echo "<img src='https://img.icons8.com/ios-filled/100/000000/sun.png' class='icon' alt='UV'>";
echo "<h3>Índice UV</h3>";
echo "<div class='big-value'>{$uv}</div>";
