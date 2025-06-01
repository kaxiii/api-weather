<?php
$ciudad = $_POST['ciudad'] ?? '';

// Crear contexto HTTP con User-Agent
$options = [
    'http' => [
        'header' => "User-Agent: clima-app/1.0\r\n"
    ]
];
$context = stream_context_create($options);

// Obtener coordenadas de la ciudad desde Nominatim
$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ciudad);
$response = file_get_contents($url, false, $context);
$geo = json_decode($response, true);

if (!$geo || count($geo) === 0) {
    echo "<p>Error de ubicación.</p>";
    exit;
}

$lat = $geo[0]['lat'];
$lon = $geo[0]['lon'];
$nombreCompleto = $geo[0]['display_name'];

// Mostrar nombre y mapa de la ubicación
echo "<div class='location'><strong>$nombreCompleto</strong></div>";
echo "<iframe src='https://www.openstreetmap.org/export/embed.html?bbox=" 
    . ($lon - 0.01) . "," . ($lat - 0.01) . "," . ($lon + 0.01) . "," . ($lat + 0.01) 
    . "&layer=mapnik&marker=$lat,$lon'></iframe>";
