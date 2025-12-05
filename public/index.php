<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByaHero - Bus Tracker (Passenger View)</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .container {
            display: flex;
            height: calc(100vh - 80px);
        }
        
        #map {
            flex: 1;
            height: 100%;
        }
        
        .sidebar {
            width: 300px;
            background: white;
            padding: 1.5rem;
            overflow-y: auto;
            box-shadow: -2px 0 4px rgba(0,0,0,0.1);
        }
        
        .filter-section {
            margin-bottom: 1.5rem;
        }
        
        .filter-section h3 {
            font-size: 1rem;
            margin-bottom: 0.75rem;
            color: #333;
        }
        
        .filter-section label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .filter-section select,
        .filter-section input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .legend {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1.5rem;
        }
        
        .legend h3 {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            color: #333;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid rgba(0,0,0,0.2);
        }
        
        .status-available { background: #10b981; }
        .status-on_stop { background: #f59e0b; }
        .status-full { background: #ef4444; }
        .status-unavailable { background: #6b7280; }
        
        .bus-list {
            margin-top: 1.5rem;
        }
        
        .bus-list h3 {
            font-size: 1rem;
            margin-bottom: 0.75rem;
            color: #333;
        }
        
        .bus-item {
            background: #f9f9f9;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
            border-left: 4px solid #667eea;
        }
        
        .bus-item strong {
            display: block;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .bus-item small {
            color: #666;
            font-size: 0.8rem;
        }
        
        .last-update {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.8rem;
            color: #999;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                max-height: 300px;
            }
            
            #map {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ByaHero: Prototype V4</h1>
    </div>
    
    <div class="container">
        <div id="map"></div>
        
        <div class="sidebar">
            <div class="filter-section">
                <h3>Filter by Route</h3>
                <select id="routeFilter">
                    <option value="">All Routes</option>
                </select>
            </div>
            
            <div class="legend">
                <h3>Bus Status</h3>
                <div class="legend-item">
                    <div class="legend-color status-available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color status-on_stop"></div>
                    <span>On Stop</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color status-full"></div>
                    <span>Full</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color status-unavailable"></div>
                    <span>Unavailable</span>
                </div>
            </div>
            
            <div class="bus-list">
                <h3>Active Buses (<span id="busCount">0</span>)</h3>
                <div id="busList"></div>
            </div>
            
            <div class="last-update">
                Last updated: <span id="lastUpdate">Never</span>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize map centered on a default location (Manila, Philippines)
        const map = L.map('map').setView([14.5995, 120.9842], 13);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Store bus markers
        const busMarkers = {};
        let selectedRoute = '';
        
        // Bus status colors
        const statusColors = {
            'available': '#10b981',
            'on_stop': '#f59e0b',
            'full': '#ef4444',
            'unavailable': '#6b7280'
        };
        
        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        /**
         * Create custom bus icon
         */
        function createBusIcon(status) {
            const color = statusColors[status] || '#6b7280';
            return L.divIcon({
                html: `<div style="background: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 16px;">🚌</div>`,
                className: 'bus-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
        }
        
        /**
         * Fetch and update bus locations
         */
        function updateBuses() {
            fetch('/api.php?action=get_buses')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.buses) {
                        updateMap(data.buses);
                        updateBusList(data.buses);
                        updateRouteFilter(data.buses);
                        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    }
                })
                .catch(error => {
                    console.error('Error fetching buses:', error);
                });
        }
        
        /**
         * Update map markers
         */
        function updateMap(buses) {
            // Filter buses by selected route
            const filteredBuses = buses.filter(bus => {
                if (!selectedRoute || selectedRoute === '') return true;
                return bus.route === selectedRoute;
            });
            
            // Remove markers for buses no longer in the list
            Object.keys(busMarkers).forEach(busId => {
                if (!filteredBuses.find(b => b.id == busId)) {
                    map.removeLayer(busMarkers[busId]);
                    delete busMarkers[busId];
                }
            });
            
            // Update or create markers for each bus
            filteredBuses.forEach(bus => {
                if (bus.lat && bus.lng) {
                    const position = [bus.lat, bus.lng];
                    
                    if (busMarkers[bus.id]) {
                        // Update existing marker
                        busMarkers[bus.id].setLatLng(position);
                        busMarkers[bus.id].setIcon(createBusIcon(bus.status));
                        busMarkers[bus.id].setPopupContent(createPopupContent(bus));
                    } else {
                        // Create new marker
                        const marker = L.marker(position, {
                            icon: createBusIcon(bus.status)
                        }).addTo(map);
                        
                        marker.bindPopup(createPopupContent(bus));
                        busMarkers[bus.id] = marker;
                    }
                }
            });
            
            // Auto-fit map to show all buses if this is the first load
            if (Object.keys(busMarkers).length > 0 && !window.mapInitialized) {
                const group = L.featureGroup(Object.values(busMarkers));
                map.fitBounds(group.getBounds().pad(0.1));
                window.mapInitialized = true;
            }
        }
        
        /**
         * Create popup content for bus marker
         */
        function createPopupContent(bus) {
            return `
                <div style="min-width: 150px;">
                    <strong style="font-size: 1.1em;">${escapeHtml(bus.code)}</strong><br>
                    <strong>Route:</strong> ${escapeHtml(bus.route || 'Not set')}<br>
                    <strong>Status:</strong> ${escapeHtml(bus.status)}<br>
                    <strong>Seats:</strong> ${bus.seats_available}/${bus.seats_total} available<br>
                    <small style="color: #666;">Updated: ${new Date(bus.updated_at).toLocaleTimeString()}</small>
                </div>
            `;
        }
        
        /**
         * Update bus list in sidebar
         */
        function updateBusList(buses) {
            const busList = document.getElementById('busList');
            const busCount = document.getElementById('busCount');
            
            // Filter buses by selected route
            const filteredBuses = buses.filter(bus => {
                if (!selectedRoute || selectedRoute === '') return true;
                return bus.route === selectedRoute;
            });
            
            busCount.textContent = filteredBuses.filter(b => b.lat && b.lng).length;
            
            if (filteredBuses.length === 0) {
                busList.innerHTML = '<p style="color: #999; font-size: 0.9rem;">No buses available</p>';
                return;
            }
            
            busList.innerHTML = filteredBuses
                .filter(bus => bus.lat && bus.lng)
                .map(bus => `
                    <div class="bus-item">
                        <strong>${escapeHtml(bus.code)}</strong>
                        <small>Route: ${escapeHtml(bus.route || 'Not set')}</small><br>
                        <small>Seats: ${bus.seats_available}/${bus.seats_total} | ${escapeHtml(bus.status)}</small>
                    </div>
                `).join('');
        }
        
        /**
         * Update route filter dropdown
         */
        function updateRouteFilter(buses) {
            const routeFilter = document.getElementById('routeFilter');
            const currentRoutes = [...new Set(buses.map(b => b.route).filter(r => r))];
            
            // Keep current selection if it exists
            const currentValue = routeFilter.value;
            
            routeFilter.innerHTML = '<option value="">All Routes</option>';
            currentRoutes.forEach(route => {
                const option = document.createElement('option');
                option.value = route;
                option.textContent = route;
                if (route === currentValue) {
                    option.selected = true;
                }
                routeFilter.appendChild(option);
            });
        }
        
        // Route filter change handler
        document.getElementById('routeFilter').addEventListener('change', (e) => {
            selectedRoute = e.target.value;
            updateBuses();
        });
        
        // Initial load and periodic updates
        updateBuses();
        setInterval(updateBuses, 3000); // Update every 3 seconds
    </script>
</body>
</html>
