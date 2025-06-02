<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clima Detallado</title>
    <link rel="stylesheet" href="styles.css">
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

    <script>
        document.getElementById("buscador").addEventListener("submit", function(e) {
            e.preventDefault();
            const ciudad = document.getElementById("ciudad").value.trim();
            if (ciudad) {
                fetchTarjetas(ciudad);
            }
        });

        function fetchTarjetas(ciudad) {
            const contenedor = document.getElementById("contenedor-tarjetas");
            contenedor.innerHTML = '<div class="card loading">Buscando datos para: ' + ciudad + '</div>';

            const tarjetas = ['ubicacion', 'clima', 'humedad', 'viento', 'uv'];

            // Crear tarjetas vacías con IDs predefinidos
            tarjetas.forEach(nombre => {
                const card = document.createElement('div');
                card.className = 'card loading';
                card.id = `card-${nombre}`;
                card.innerHTML = `Cargando ${nombre}...`;
                contenedor.appendChild(card);
            });

            // Luego hacer fetch y llenar cada una por ID
            tarjetas.forEach(nombre => {
                const formData = new FormData();
                formData.append('ciudad', ciudad);

                fetch(`cards/${nombre}.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    const target = document.getElementById(`card-${nombre}`);
                    if (target) {
                        target.innerHTML = html;
                        target.classList.remove('loading');
                    }
                })
                .catch(() => {
                    const target = document.getElementById(`card-${nombre}`);
                    if (target) {
                        target.innerHTML = `<p>Error al cargar ${nombre}</p>`;
                        target.classList.remove('loading');
                    }
                });
            });
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
        });
    </script>
</body>
</html>