<?php
$ciudad = $_POST['ciudad'] ?? '';

// Crear contexto HTTP con User-Agent
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

// Obtener clima: UV actual, UV máximo diario y radiación solar
$weather_url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=uv_index,shortwave_radiation&daily=uv_index_max&timezone=auto";
$weather = json_decode(file_get_contents($weather_url), true);

if (!isset($weather['current']['uv_index']) || !isset($weather['daily']['uv_index_max'][0]) || !isset($weather['current']['shortwave_radiation'])) {
    echo "<p>Error al obtener datos UV.</p>";
    exit;
}

$uv = $weather['current']['uv_index'];
$uv_max = $weather['daily']['uv_index_max'][0];
$radiacion = $weather['current']['shortwave_radiation'];

// Definir nivel y color según el UV
function getNivelUV($uv) {
    if ($uv <= 2) return ['Bajo', 'green', 'Seguro para estar al sol. Protección mínima.'];
    if ($uv <= 5) return ['Moderado', 'yellow', 'Busca sombra en horas pico. Usa protector solar.'];
    if ($uv <= 7) return ['Alto', 'orange', 'Usa sombrero, gafas y protector solar.'];
    if ($uv <= 10) return ['Muy alto', 'red', 'Evita el sol directo. Usa protección extra.'];
    return ['Extremo', 'purple', 'Evita exposición total al sol. Peligro inmediato.'];
}

[$nivel, $color, $tip] = getNivelUV($uv);

// Mostrar tarjeta UV con color y tooltip
echo "<img src='https://img.icons8.com/ios-filled/100/000000/sun.png' class='icon' alt='UV'>";
echo "<h3>Índice UV</h3>";
echo "<div class='big-value' style='color: $color;' title='$tip'>{$uv}</div>";
echo "<div style='margin-top: 5px; font-size: 1.1em;' title='$tip'>Nivel: <strong>$nivel</strong></div>";

echo "<div style='margin-top: 10px; font-size: 0.95em;'>
        Máx. del día: <strong>{$uv_max}</strong><br>
        Radiación solar: <strong>{$radiacion} W/m²</strong>
      </div>";
