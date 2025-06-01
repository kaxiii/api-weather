<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clima Actual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
        }
        .card img {
            width: 80px;
            height: 80px;
        }
        .location {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .temp {
            font-size: 2em;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card" id="card1">
            <div>Obteniendo datos...</div>
        </div>
        <div class="card" id="card2" style="display: none;">
            <div>Obteniendo detalles...</div>
        </div>
    </div>

    <script>
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // Obtener nombre del lugar
                let nombreLugar = '';
                try {
                    const geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
                    const geoData = await geoRes.json();
                    nombreLugar = geoData.address.city || geoData.address.town || geoData.address.village || geoData.address.county || "Ubicación desconocida";
                } catch (e) {
                    nombreLugar = "Ubicación desconocida";
                }

                // Obtener datos climáticos
                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weathercode,windspeed_10m,relative_humidity_2m,uv_index`)
                    .then(response => response.json())
                    .then(data => {
                        const clima = data.current;

                        const temperatura = clima.temperature_2m;
                        const viento = clima.windspeed_10m;
                        const humedad = clima.relative_humidity_2m;
                        const uv = clima.uv_index;
                        const codigoClima = clima.weathercode;

                        const descripcion = obtenerDescripcionClima(codigoClima);
                        const icono = obtenerIconoClima(codigoClima);

                        // Primera tarjeta (general)
                        document.getElementById('card1').innerHTML = `
                            <div class="location">${nombreLugar}</div>
                            <img src="${icono}" alt="Clima">
                            <div class="temp">${temperatura}°C</div>
                            <div>${descripcion}</div>
                            <div>Viento: ${viento} km/h</div>
                        `;

                        // Segunda tarjeta (detalles)
                        document.getElementById('card2').style.display = 'block';
                        document.getElementById('card2').innerHTML = `
                            <h3>Detalles</h3>
                            <p><strong>Humedad:</strong> ${humedad}%</p>
                            <p><strong>Índice UV:</strong> ${uv}</p>
                            <p><strong>Viento:</strong> ${viento} km/h</p>
                        `;
                    })
                    .catch(error => {
                        document.getElementById('card1').innerText = 'Error al obtener el clima.';
                        document.getElementById('card2').innerText = '';
                    });
            }, function() {
                document.getElementById('card1').innerText = 'No se pudo obtener tu ubicación.';
            });
        } else {
            document.getElementById('card1').innerText = 'Tu navegador no soporta geolocalización.';
        }

        function obtenerDescripcionClima(code) {
            const descripciones = {
                0: 'Despejado',
                1: 'Mayormente despejado',
                2: 'Parcialmente nublado',
                3: 'Nublado',
                45: 'Niebla',
                48: 'Niebla con escarcha',
                51: 'Llovizna ligera',
                53: 'Llovizna moderada',
                55: 'Llovizna intensa',
                61: 'Lluvia ligera',
                63: 'Lluvia moderada',
                65: 'Lluvia fuerte',
                80: 'Chubascos ligeros',
                81: 'Chubascos moderados',
                82: 'Chubascos intensos'
            };
            return descripciones[code] || 'Condición desconocida';
        }

        function obtenerIconoClima(code) {
            const base = "https://openweathermap.org/img/wn/";
            const iconMap = {
                0: "01d", 1: "02d", 2: "03d", 3: "04d",
                45: "50d", 48: "50d",
                51: "09d", 53: "09d", 55: "09d",
                61: "10d", 63: "10d", 65: "10d",
                80: "09d", 81: "09d", 82: "09d"
            };
            return `${base}${iconMap[code] || "01d"}@2x.png`;
        }
    </script>
</body>
</html>
