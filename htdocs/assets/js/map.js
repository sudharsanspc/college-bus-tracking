// Shared map utilities
class BusMap {
    constructor(mapElementId, centerLat, centerLng) {
        this.map = null;
        this.markers = {};
        this.mapElementId = mapElementId;
        this.centerLat = centerLat;
        this.centerLng = centerLng;
        this.initMap();
    }
    
    initMap() {
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }
        
        this.map = L.map(this.mapElementId).setView([this.centerLat, this.centerLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
    }
    
    addMarker(lat, lng, title, popupContent = null) {
        const marker = L.marker([lat, lng]).addTo(this.map);
        
        if (title) {
            marker.bindTooltip(title);
        }
        
        if (popupContent) {
            marker.bindPopup(popupContent);
        }
        
        return marker;
    }
    
    addBusMarker(busNumber, lat, lng) {
        const busIcon = L.divIcon({
            className: 'bus-marker',
            html: `<div style="background: #ff9800; color: white; padding: 5px 10px; border-radius: 20px; font-weight: bold; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                   🚌 Bus ${busNumber}
                   </div>`,
            iconSize: [80, 30],
            popupAnchor: [0, -15]
        });
        
        const marker = L.marker([lat, lng], { icon: busIcon }).addTo(this.map);
        this.markers[busNumber] = marker;
        return marker;
    }
    
    updateBusMarker(busNumber, lat, lng) {
        if (this.markers[busNumber]) {
            this.markers[busNumber].setLatLng([lat, lng]);
        } else {
            this.addBusMarker(busNumber, lat, lng);
        }
    }
    
    removeBusMarker(busNumber) {
        if (this.markers[busNumber]) {
            this.map.removeLayer(this.markers[busNumber]);
            delete this.markers[busNumber];
        }
    }
    
    centerOnLocation(lat, lng, zoom = 15) {
        this.map.setView([lat, lng], zoom);
    }
    
    centerOnBus(busNumber) {
        if (this.markers[busNumber]) {
            const position = this.markers[busNumber].getLatLng();
            this.centerOnLocation(position.lat, position.lng);
            this.markers[busNumber].openPopup();
        }
    }
    
    addRoute(points, color = '#ff9800') {
        const routeLine = L.polyline(points, {
            color: color,
            weight: 3,
            opacity: 0.7,
            dashArray: '5, 10'
        }).addTo(this.map);
        
        return routeLine;
    }
    
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const dLat = this.toRad(lat2 - lat1);
        const dLng = this.toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
    
    toRad(degrees) {
        return degrees * Math.PI / 180;
    }
    
    getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    },
                    (error) => {
                        reject(error);
                    }
                );
            } else {
                reject(new Error('Geolocation not supported'));
            }
        });
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusMap;
}