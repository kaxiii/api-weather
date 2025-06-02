<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clima Detallado</title>
    <link rel="stylesheet" href="styles.css">
    <script src="js/records.js" defer></script>
</head>
<body>
    <h1>Clima Detallado</h1>

    <form id="buscador">
        <input type="text" id="ciudad" placeholder="Escribe una ciudad..." required>
        <button type="submit">Buscar</button>
    </form>

    <div class="tarjetas" id="contenedor-tarjetas">
        <div class="card loading">Cargando información meteorológica...</div>
    </div>

    <!-- Sección de Records -->
    <div id="records-container" class="card">
        <h3>Récords Absolutos</h3>
        <div class="records-grid">
            <div class="record-card">
                <h4>🌡️ Temp. Máxima</h4>
                <div class="record-value">-- °C</div>
                <div class="record-details">Ubicación: --<br>Fecha: --</div>
            </div>
            <div class="record-card">
                <h4>❄️ Temp. Mínima</h4>
                <div class="record-value">-- °C</div>
                <div class="record-details">Ubicación: --<br>Fecha: --</div>
            </div>
            <div class="record-card">
                <h4>☀️ Índice UV</h4>
                <div class="record-value">--</div>
                <div class="record-details">Ubicación: --<br>Fecha: --</div>
            </div>
            <div class="record-card">
                <h4>🌪️ Viento</h4>
                <div class="record-value">-- km/h</div>
                <div class="record-details">Ubicación: --<br>Fecha: --</div>
            </div>
            <div class="record-card">
                <h4>☢️ Radiación Solar</h4>
                <div class="record-value">-- W/m²</div>
                <div class="record-details">Ubicación: --<br>Fecha: --</div>
            </div>
        </div>
    </div>

    <script>
        // Función para cargar los records iniciales
        async function loadInitialRecords() {
            try {
                const response = await fetch('data/records.json');
                if (response.ok) {
                    const records = await response.json();
                    updateRecordsDisplay(records);
                }
            } catch (error) {
                console.error("Error loading records:", error);
            }
        }

        // Función para actualizar la visualización de records
        function updateRecordsDisplay(records) {
            if (!records) return;

            const updateRecordCard = (selector, value, location, date) => {
                const element = document.querySelector(selector);
                if (element) {
                    element.querySelector('.record-value').textContent = value;
                    element.querySelector('.record-details').innerHTML = 
                        `Ubicación: ${location}<br>Fecha: ${date}`;
                }
            };

            updateRecordCard(
                '.records-grid > div:nth-child(1)',
                `${records.max_temp.value}°C`,
                records.max_temp.location,
                records.max_temp.date
            );

            updateRecordCard(
                '.records-grid > div:nth-child(2)',
                `${records.min_temp.value}°C`,
                records.min_temp.location,
                records.min_temp.date
            );

            updateRecordCard(
                '.records-grid > div:nth-child(3)',
                records.max_uv.value,
                records.max_uv.location,
                records.max_uv.date
            );

            updateRecordCard(
                '.records-grid > div:nth-child(4)',
                `${records.max_wind.value} km/h`,
                records.max_wind.location,
                records.max_wind.date
            );

            updateRecordCard(
                '.records-grid > div:nth-child(5)',
                `${records.max_radiation.value} W/m²`,
                records.max_radiation.location,
                records.max_radiation.date
            );
        }

        // Cargar records al iniciar
        document.addEventListener('DOMContentLoaded', loadInitialRecords);

        // Resto del código existente...
        document.getElementById("buscador").addEventListener("submit", function(e) {
            e.preventDefault();
            const ciudad = document.getElementById("ciudad").value.trim();
            if (ciudad) {
                fetchTarjetas(ciudad);
            }
        });

        async function fetchTarjetas(ciudad) {
            const contenedor = document.getElementById("contenedor-tarjetas");
            contenedor.innerHTML = '<div class="card loading">Buscando datos para: ' + ciudad + '</div>';

            try {
                // Obtener datos meteorológicos
                const [climaRes, humedadRes, vientoRes, uvRes] = await Promise.all([
                    fetch(`cards/clima.php`, { method: 'POST', body: new URLSearchParams({ ciudad }) }),
                    fetch(`cards/humedad.php`, { method: 'POST', body: new URLSearchParams({ ciudad }) }),
                    fetch(`cards/viento.php`, { method: 'POST', body: new URLSearchParams({ ciudad }) }),
                    fetch(`cards/uv.php`, { method: 'POST', body: new URLSearchParams({ ciudad }) })
                ]);

                const [climaHtml, humedadHtml, vientoHtml, uvHtml] = await Promise.all([
                    climaRes.text(),
                    humedadRes.text(),
                    vientoRes.text(),
                    uvRes.text()
                ]);

                // Extraer valores numéricos de los datos
                const tempMatch = climaHtml.match(/class='big-value'>([\d.]+)°C/);
                const uvMatch = uvHtml.match(/class='big-value'.*?>([\d.]+)</);
                const vientoMatch = vientoHtml.match(/class='big-value'>([\d.]+) km\/h/);
                const radiacionMatch = uvHtml.match(/Radiación solar: <strong>([\d.]+) W\/m²/);

                if (tempMatch && uvMatch && vientoMatch && radiacionMatch) {
                    const weatherData = {
                        temperature: parseFloat(tempMatch[1]),
                        uv: parseFloat(uvMatch[1]),
                        wind: parseFloat(vientoMatch[1]),
                        radiation: parseFloat(radiacionMatch[1])
                    };
                    
                    // Verificar records
                    if (typeof recordKeeper !== 'undefined') {
                        await recordKeeper.checkForRecords(ciudad, weatherData);
                        // Recargar los records después de actualizarlos
                        await loadInitialRecords();
                    }
                }

                // Mostrar las tarjetas
                contenedor.innerHTML = '';
                const tarjetas = ['ubicacion', 'clima', 'humedad', 'viento', 'uv'];
                const htmls = [null, climaHtml, humedadHtml, vientoHtml, uvHtml];

                // Obtener ubicación (puede ser más lento)
                const ubicacionRes = await fetch(`cards/ubicacion.php`, { 
                    method: 'POST', 
                    body: new URLSearchParams({ ciudad }) 
                });
                const ubicacionHtml = await ubicacionRes.text();
                htmls[0] = ubicacionHtml;

                // Mostrar todas las tarjetas
                tarjetas.forEach((nombre, index) => {
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.innerHTML = htmls[index];
                    contenedor.appendChild(card);
                });

            } catch (error) {
                console.error("Error fetching data:", error);
                contenedor.innerHTML = '<div class="card error">Error al cargar los datos meteorológicos</div>';
            }
        }

        // Al iniciar, mostrar ubicación actual
        navigator.geolocation?.getCurrentPosition(async pos => {
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
                const data = await res.json();
                const ciudad = data.address.city || data.address.town || data.address.village || data.address.county;
                if (ciudad) fetchTarjetas(ciudad);
            } catch {
                fetchTarjetas("Madrid");
            }
        }, (error) => {
            console.error("Error getting location:", error);
            fetchTarjetas("Madrid");
        });
    </script>
</body>
</html>