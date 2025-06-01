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

// Obtener datos del clima desde Open-Meteo
$weather_url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=temperature_2m,apparent_temperature,weathercode";
$weather = json_decode(file_get_contents($weather_url), true);

if (!isset($weather['current'])) {
    echo "<p>Error al obtener datos del clima.</p>";
    exit;
}

$clima = $weather['current'];
$temp = $clima['temperature_2m'];
$sensacion = $clima['apparent_temperature'];
$code = $clima['weathercode'];

// Obtener ícono
function obtenerIconoClima($code) {
    $map = [
        0 => '01d', 1 => '02d', 2 => '03d', 3 => '04d',
        45 => '50d', 48 => '50d', 51 => '09d', 53 => '09d',
        55 => '09d', 61 => '10d', 63 => '10d', 65 => '10d',
        80 => '09d', 81 => '09d', 82 => '09d'
    ];
    $icon = $map[$code] ?? '01d';
    return "https://openweathermap.org/img/wn/{$icon}@2x.png";
}

$icono = obtenerIconoClima($code);

// Obtener descripción textual
function obtenerDescripcionClima($code) {
    $descripciones = [
        0 => 'Despejado',
        1 => 'Mayormente despejado',
        2 => 'Parcialmente nublado',
        3 => 'Nublado',
        45 => 'Niebla',
        48 => 'Niebla con escarcha',
        51 => 'Llovizna ligera',
        53 => 'Llovizna moderada',
        55 => 'Llovizna intensa',
        61 => 'Lluvia ligera',
        63 => 'Lluvia moderada',
        65 => 'Lluvia intensa',
        80 => 'Chubascos ligeros',
        81 => 'Chubascos moderados',
        82 => 'Chubascos intensos'
    ];
    return $descripciones[$code] ?? 'Condición desconocida';
}

$descripcion = obtenerDescripcionClima($code);

// Mostrar datos
echo "<img src='$icono' class='weather-icon' alt='Clima'>";
echo "<div><strong style='font-size: 1.2em;'>$descripcion</strong></div>";
echo "<div class='big-value'>{$temp}°C</div>";
echo "<div style='font-size: 1em;'>Sensación: {$sensacion}°C</div>";
