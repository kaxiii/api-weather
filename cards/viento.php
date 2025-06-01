<?php
$ciudad = $_POST['ciudad'] ?? '';

// Crear contexto HTTP con User-Agent para Nominatim
$options = [
    'http' => [
        'header' => "User-Agent: clima-app/1.0\r\n"
    ]
];
$context = stream_context_create($options);

// Obtener coordenadas
$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ciudad);
$response = file_get_contents($url, false, $context);
$geo = json_decode($response, true);

if (!$geo || count($geo) === 0) {
    echo "<p>Error de ubicación.</p>";
    exit;
}

$lat = $geo[0]['lat'];
$lon = $geo[0]['lon'];

// Obtener datos del viento
$weather_url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=windspeed_10m,winddirection_10m";
$weather = json_decode(file_get_contents($weather_url), true);

if (!isset($weather['current']['windspeed_10m']) || !isset($weather['current']['winddirection_10m'])) {
    echo "<p>Error al obtener datos del viento.</p>";
    exit;
}

$viento = $weather['current']['windspeed_10m'];
$direccion = $weather['current']['winddirection_10m']; // En grados (0° = norte)

// Calcular dirección cardinal (opcional, para texto)
function getDireccionCardinal($grados) {
    $puntos = ['N', 'NE', 'E', 'SE', 'S', 'SO', 'O', 'NO', 'N'];
    return $puntos[round($grados / 45)];
}

$cardinal = getDireccionCardinal($direccion);

// Mostrar tarjeta
echo "<img src='https://img.icons8.com/ios-filled/100/000000/windy-weather.png' class='icon' alt='Viento'>";
echo "<h3>Viento</h3>";
echo "<div class='big-value'>{$viento} km/h</div>";

echo "<div style='margin-top:10px; font-size:1.1em;'>Dirección: $direccion ($cardinal)</div>";

// Rosa de los vientos con rotación CSS
echo "<div style='margin-top:10px;'>
        <img src='https://img.icons8.com/ios-filled/100/000000/compass.png'
            alt='Rosa de los vientos'
            style='width:60px; height:60px; transform: rotate(<?php echo $direccion; ?>deg); transform-origin: center center; display: block; margin: 0 auto;'>
        <div style='font-size:0.9em; margin-top:5px;'>↖ viento desde</div>
    </div>";
