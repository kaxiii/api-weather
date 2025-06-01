<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clima Detallado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e8f0f8;
            padding: 20px;
        }

        h1 {
            text-align: center;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        input[type="text"] {
            padding: 10px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color: #0077cc;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .tarjetas {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            width: 280px;
            text-align: center;
        }

        .card img.weather-icon {
            width: 100px;
            height: 100px;
        }

        .card iframe {
            border: none;
            width: 100%;
            height: 200px;
            border-radius: 8px;
        }

        .location {
            font-weight: bold;
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .big-value {
            font-size: 2.2em;
            font-weight: bold;
            margin: 10px 0;
        }

        .card h3 {
            margin-bottom: 10px;
        }

        .icon {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Clima Detallado</h1>
    <form id="buscador">
        <input type="text" id="ciudad" placeholder="Escribe una ciudad..." required>
        <button type="submit">Buscar</button>
    </form>

    <div class="tarjetas">
        <div class="card" id="card1">Esperando datos...</div>
        <div class="card" id="card2"></div>
        <div class="card" id="card3"></div>
        <div class="card" id="card4"></div>
        <div class="card" id="card5"></div>
    </div>

    <script>
        document.getElementById("buscador").addEventListener("submit", function(e) {
            e.preventDefault();
            const ciudad = document.getElementById("ciudad").value.trim();
            if (ciudad) {
                buscarCiudad(ciudad);
            }
        });

        async function buscarCiudad(nombreCiudad) {
            resetearTarjetas("Cargando datos...");
            try {
                const geoRes = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(nombreCiudad)}`);
                const resultados = await geoRes.json();
                if (resultados.length === 0) {
                    resetearTarjetas("Ciudad no encontrada.");
                    return;
                }

                const lat = resultados[0].lat;
                const lon = resultados[0].lon;
                const nombre = resultados[0].display_name;

                mostrarDatosClima(nombre, lat, lon);
            } catch (err) {
                resetearTarjetas("Error al buscar ciudad.");
            }
        }

        async function mostrarDatosClima(nombreLugar, lat, lon) {
            document.getElementById('card1').innerHTML = `
                <div class="location">${nombreLugar}</div>
                <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=${lon-0.01},${lat-0.01},${lon+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lon}"></iframe>
            `;

            try {
                const climaRes = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,apparent_temperature,weathercode,relative_humidity_2m,windspeed_10m,uv_index`);
                const data = await climaRes.json();
                const clima = data.current;

                const temp = clima.temperature_2m;
                const sensacion = clima.apparent_temperature;
                const code = clima.weathercode;
                const icono = obtenerIconoClima(code);
                const descripcion = obtenerDescripcionClima(code);
                const humedad = clima.relative_humidity_2m;
                const viento = clima.windspeed_10m;
                const uv = clima.uv_index;

                document.getElementById('card2').innerHTML = `
                    <img src="${icono}" class="weather-icon" alt="Clima">
                    <div><strong style="font-size: 1.2em;">${descripcion}</strong></div>
                    <div class="big-value">${temp}°C</div>
                    <div style="font-size: 1em;">Sensación: ${sensacion}°C</div>
                `;

                document.getElementById('card3').innerHTML = `
                    <img src="https://img.icons8.com/ios-filled/100/000000/hygrometer.png" alt="Humedad" class="icon">
                    <h3>Humedad</h3>
                    <div class="big-value">${humedad}%</div>
                `;

                document.getElementById('card4').innerHTML = `
                    <img src="https://img.icons8.com/ios-filled/100/000000/windy-weather.png" alt="Viento" class="icon">
                    <h3>Viento</h3>
                    <div class="big-value">${viento} km/h</div>
                `;

                document.getElementById('card5').innerHTML = `
                    <img src="https://img.icons8.com/ios-filled/100/000000/sun.png" alt="UV" class="icon">
                    <h3>Índice UV</h3>
                    <div class="big-value">${uv}</div>
                `;
            } catch {
                resetearTarjetas("Error al cargar clima.");
            }
        }

        function resetearTarjetas(mensaje) {
            document.getElementById('card1').innerHTML = mensaje;
            for (let i = 2; i <= 5; i++) {
                document.getElementById(`card${i}`).innerHTML = "";
            }
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
                65: 'Lluvia intensa',
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

        // Mostrar ubicación actual al cargar
        navigator.geolocation?.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            mostrarDatosClima("Tu ubicación", latitude, longitude);
        });
    </script>
</body>
</html>
