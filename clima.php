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
    </style>
</head>
<body>
    <h1>Clima Detallado</h1>

    <form id="buscador">
        <input type="text" id="ciudad" placeholder="Escribe una ciudad..." required>
        <button type="submit">Buscar</button>
    </form>

    <div class="tarjetas" id="contenedor-tarjetas">
        <div class="card">Cargando tarjetas...</div>
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
            contenedor.innerHTML = '';

            const tarjetas = ['ubicacion', 'clima', 'humedad', 'viento', 'uv'];

            // Crear tarjetas vacías con IDs predefinidos
            tarjetas.forEach(nombre => {
                const card = document.createElement('div');
                card.className = 'card';
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
                    if (target) target.innerHTML = html;
                })
                .catch(() => {
                    const target = document.getElementById(`card-${nombre}`);
                    if (target) target.innerHTML = `<p>Error al cargar ${nombre}</p>`;
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
