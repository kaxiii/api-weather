// js/records.js
class RecordKeeper {
    constructor() {
        this.recordsFile = 'data/records.json';
        this.records = {};
        this.currentLocation = null;
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
                this.records = await response.json();
            } else {
                this.records = {};
            }
        } catch (error) {
            console.error("Error loading records:", error);
            this.records = {};
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
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '20px';
        notificationContainer.style.right = '20px';
        notificationContainer.style.zIndex = '1000';
        document.body.appendChild(notificationContainer);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        const container = document.getElementById('notification-container');
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }

    async checkForRecords(location, weatherData) {
        this.currentLocation = location;
        
        if (!this.records[location]) {
            this.records[location] = {
                max_temp: weatherData.temperature,
                min_temp: weatherData.temperature,
                max_uv: weatherData.uv,
                max_wind: weatherData.wind,
                max_radiation: weatherData.radiation,
                updated: new Date().toISOString()
            };
            await this.saveRecords();
            this.showNotification(`¡Nuevos records establecidos para ${location}!`, 'success');
            return;
        }

        let updated = false;
        const locationRecords = this.records[location];
        
        // Verificar cada record
        if (weatherData.temperature > locationRecords.max_temp) {
            this.showNotification(`¡Nuevo record de temperatura máxima en ${location}: ${weatherData.temperature}°C (antes ${locationRecords.max_temp}°C)`, 'record');
            locationRecords.max_temp = weatherData.temperature;
            updated = true;
        }
        
        if (weatherData.temperature < locationRecords.min_temp) {
            this.showNotification(`¡Nuevo record de temperatura mínima en ${location}: ${weatherData.temperature}°C (antes ${locationRecords.min_temp}°C)`, 'record');
            locationRecords.min_temp = weatherData.temperature;
            updated = true;
        }
        
        if (weatherData.uv > locationRecords.max_uv) {
            this.showNotification(`¡Nuevo record de UV en ${location}: ${weatherData.uv} (antes ${locationRecords.max_uv})`, 'record');
            locationRecords.max_uv = weatherData.uv;
            updated = true;
        }
        
        if (weatherData.wind > locationRecords.max_wind) {
            this.showNotification(`¡Nuevo record de viento en ${location}: ${weatherData.wind} km/h (antes ${locationRecords.max_wind} km/h)`, 'record');
            locationRecords.max_wind = weatherData.wind;
            updated = true;
        }
        
        if (weatherData.radiation > locationRecords.max_radiation) {
            this.showNotification(`¡Nuevo record de radiación solar en ${location}: ${weatherData.radiation} W/m² (antes ${locationRecords.max_radiation} W/m²)`, 'record');
            locationRecords.max_radiation = weatherData.radiation;
            updated = true;
        }
        
        if (updated) {
            locationRecords.updated = new Date().toISOString();
            await this.saveRecords();
        }
    }
}

// Crear instancia global
const recordKeeper = new RecordKeeper();