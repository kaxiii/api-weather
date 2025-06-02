// js/records.js
class RecordKeeper {
    constructor() {
        this.recordsFile = 'data/records.json';
        this.records = {
            max_temp: { value: -Infinity, location: '', date: '' },
            min_temp: { value: Infinity, location: '', date: '' },
            max_uv: { value: -Infinity, location: '', date: '' },
            max_wind: { value: -Infinity, location: '', date: '' },
            max_radiation: { value: -Infinity, location: '', date: '' }
        };
        this.init();
    }

    async init() {
        await this.loadRecords();
        this.setupNotificationSystem();
    }

    async loadRecords() {
        try {
            const response = await fetch(this.recordsFile);
            if (response.ok) {
                const data = await response.json();
                // Merge con valores por defecto
                this.records = {
                    ...this.records,
                    ...data
                };
            }
        } catch (error) {
            console.error("Error loading records:", error);
        }
    }

    async saveRecords() {
        try {
            const response = await fetch('save_records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.records)
            });
            return response.ok;
        } catch (error) {
            console.error("Error saving records:", error);
            return false;
        }
    }

    setupNotificationSystem() {
        const notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = message;
        
        const container = document.getElementById('notification-container');
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }

    async checkForRecords(location, weatherData) {
        const now = new Date();
        const dateStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
        let updated = false;

        // Verificar cada record
        if (weatherData.temperature > this.records.max_temp.value) {
            const oldValue = this.records.max_temp.value;
            this.records.max_temp = {
                value: weatherData.temperature,
                location: location,
                date: dateStr
            };
            this.showNotification(
                `🔥 <strong>Nuevo record de temperatura máxima!</strong><br>
                ${weatherData.temperature}°C en ${location}<br>
                <small>Anterior: ${oldValue}°C</small>`,
                'record'
            );
            updated = true;
        }
        
        if (weatherData.temperature < this.records.min_temp.value) {
            const oldValue = this.records.min_temp.value;
            this.records.min_temp = {
                value: weatherData.temperature,
                location: location,
                date: dateStr
            };
            this.showNotification(
                `❄️ <strong>Nuevo record de temperatura mínima!</strong><br>
                ${weatherData.temperature}°C en ${location}<br>
                <small>Anterior: ${oldValue}°C</small>`,
                'record'
            );
            updated = true;
        }
        
        if (weatherData.uv > this.records.max_uv.value) {
            const oldValue = this.records.max_uv.value;
            this.records.max_uv = {
                value: weatherData.uv,
                location: location,
                date: dateStr
            };
            this.showNotification(
                `☀️ <strong>Nuevo record de UV!</strong><br>
                Índice ${weatherData.uv} en ${location}<br>
                <small>Anterior: ${oldValue}</small>`,
                'record'
            );
            updated = true;
        }
        
        if (weatherData.wind > this.records.max_wind.value) {
            const oldValue = this.records.max_wind.value;
            this.records.max_wind = {
                value: weatherData.wind,
                location: location,
                date: dateStr
            };
            this.showNotification(
                `🌪️ <strong>Nuevo record de viento!</strong><br>
                ${weatherData.wind} km/h en ${location}<br>
                <small>Anterior: ${oldValue} km/h</small>`,
                'record'
            );
            updated = true;
        }
        
        if (weatherData.radiation > this.records.max_radiation.value) {
            const oldValue = this.records.max_radiation.value;
            this.records.max_radiation = {
                value: weatherData.radiation,
                location: location,
                date: dateStr
            };
            this.showNotification(
                `☢️ <strong>Nuevo record de radiación solar!</strong><br>
                ${weatherData.radiation} W/m² en ${location}<br>
                <small>Anterior: ${oldValue} W/m²</small>`,
                'record'
            );
            updated = true;
        }
        
        if (updated) {
            await this.saveRecords();
            this.displayCurrentRecords();
        }
    }

    displayCurrentRecords() {
        const recordsContainer = document.getElementById('records-container');
        if (!recordsContainer) return;

        recordsContainer.innerHTML = `
            <h3>Récords Absolutos</h3>
            <div class="records-grid">
                <div class="record-card">
                    <h4>🌡️ Temp. Máxima</h4>
                    <div class="record-value">${this.records.max_temp.value}°C</div>
                    <div class="record-details">${this.records.max_temp.location}<br>${this.records.max_temp.date}</div>
                </div>
                <div class="record-card">
                    <h4>❄️ Temp. Mínima</h4>
                    <div class="record-value">${this.records.min_temp.value}°C</div>
                    <div class="record-details">${this.records.min_temp.location}<br>${this.records.min_temp.date}</div>
                </div>
                <div class="record-card">
                    <h4>☀️ Índice UV</h4>
                    <div class="record-value">${this.records.max_uv.value}</div>
                    <div class="record-details">${this.records.max_uv.location}<br>${this.records.max_uv.date}</div>
                </div>
                <div class="record-card">
                    <h4>🌪️ Viento</h4>
                    <div class="record-value">${this.records.max_wind.value} km/h</div>
                    <div class="record-details">${this.records.max_wind.location}<br>${this.records.max_wind.date}</div>
                </div>
                <div class="record-card">
                    <h4>☢️ Radiación Solar</h4>
                    <div class="record-value">${this.records.max_radiation.value} W/m²</div>
                    <div class="record-details">${this.records.max_radiation.location}<br>${this.records.max_radiation.date}</div>
                </div>
            </div>
        `;
    }
}

// Crear instancia global
const recordKeeper = new RecordKeeper();

// Mostrar records al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // Crear contenedor para mostrar los records
    const recordsContainer = document.createElement('div');
    recordsContainer.id = 'records-container';
    recordsContainer.className = 'card';
    document.querySelector('body').appendChild(recordsContainer);
    
    // Mostrar records después de cargarlos
    setTimeout(() => {
        recordKeeper.displayCurrentRecords();
    }, 500);
});