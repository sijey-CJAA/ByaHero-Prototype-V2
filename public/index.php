<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByaHero - Real-Time Bus Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .controls {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .bus-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .bus-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .bus-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .bus-card.available {
            border-left: 5px solid #4caf50;
        }
        
        .bus-card.unavailable {
            border-left: 5px solid #f44336;
            opacity: 0.7;
        }
        
        .bus-card.on_stop {
            border-left: 5px solid #ff9800;
        }
        
        .bus-card.full {
            border-left: 5px solid #9e9e9e;
        }
        
        .bus-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .bus-code {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        
        .bus-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
        }
        
        .status-available {
            background: #4caf50;
            color: white;
        }
        
        .status-unavailable {
            background: #f44336;
            color: white;
        }
        
        .status-on_stop {
            background: #ff9800;
            color: white;
        }
        
        .status-full {
            background: #9e9e9e;
            color: white;
        }
        
        .bus-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .seats-bar {
            width: 100%;
            height: 20px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .seats-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .last-update {
            font-size: 0.85em;
            color: #999;
            margin-top: 10px;
            text-align: right;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .auto-refresh {
            text-align: right;
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }
        
        .no-buses {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚌 ByaHero</h1>
            <p class="subtitle">Real-Time Bus Tracking System</p>
        </header>
        
        <div class="controls">
            <div class="form-group">
                <label for="routeSelect">Select Route:</label>
                <select id="routeSelect">
                    <option value="">Loading routes...</option>
                </select>
            </div>
            <div class="auto-refresh">🔄 Auto-refreshing every 5 seconds</div>
        </div>
        
        <div id="errorContainer"></div>
        
        <div class="bus-list">
            <div id="busContainer">
                <div class="loading">Loading bus information...</div>
            </div>
        </div>
    </div>

    <script>
        let selectedRoute = '';
        let refreshInterval = null;
        
        // Load routes on page load
        async function loadRoutes() {
            try {
                const response = await fetch('api/get_routes.php');
                const data = await response.json();
                
                if (data.success && data.routes.length > 0) {
                    const select = document.getElementById('routeSelect');
                    select.innerHTML = '<option value="">-- Select a route --</option>';
                    
                    data.routes.forEach(route => {
                        const option = document.createElement('option');
                        option.value = route;
                        option.textContent = route;
                        select.appendChild(option);
                    });
                    
                    // Auto-select first route
                    select.value = data.routes[0];
                    selectedRoute = data.routes[0];
                    loadBuses();
                }
            } catch (error) {
                showError('Failed to load routes: ' + error.message);
            }
        }
        
        // Load buses for selected route
        async function loadBuses() {
            if (!selectedRoute) {
                document.getElementById('busContainer').innerHTML = 
                    '<div class="no-buses">Please select a route to view buses</div>';
                return;
            }
            
            try {
                const response = await fetch(`api/get_buses.php?route=${encodeURIComponent(selectedRoute)}`);
                const data = await response.json();
                
                if (data.success) {
                    displayBuses(data.buses);
                    clearError();
                } else {
                    showError('Failed to load buses: ' + data.error);
                }
            } catch (error) {
                showError('Failed to load buses: ' + error.message);
            }
        }
        
        // Display buses in the UI
        function displayBuses(buses) {
            const container = document.getElementById('busContainer');
            
            if (buses.length === 0) {
                container.innerHTML = '<div class="no-buses">No buses found for this route</div>';
                return;
            }
            
            container.innerHTML = buses.map(bus => {
                const percentage = bus.seats_total > 0 ? (bus.seats_available / bus.seats_total * 100).toFixed(0) : 0;
                
                return `
                    <div class="bus-card ${bus.status}">
                        <div class="bus-header">
                            <div class="bus-code">${bus.code}</div>
                            <div class="bus-status status-${bus.status}">
                                ${bus.status.replace('_', ' ')}
                            </div>
                        </div>
                        <div class="bus-info">
                            <div class="info-item">
                                <span class="info-label">Route:</span>
                                <span class="info-value">${bus.route}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Available Seats:</span>
                                <span class="info-value">${bus.seats_available} / ${bus.seats_total}</span>
                            </div>
                        </div>
                        <div class="seats-bar">
                            <div class="seats-fill" style="width: ${percentage}%"></div>
                        </div>
                        <div class="last-update">
                            Last updated: ${bus.last_update_formatted}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Show error message
        function showError(message) {
            const container = document.getElementById('errorContainer');
            container.innerHTML = `<div class="error">⚠️ ${message}</div>`;
        }
        
        // Clear error message
        function clearError() {
            document.getElementById('errorContainer').innerHTML = '';
        }
        
        // Handle route selection change
        document.getElementById('routeSelect').addEventListener('change', (e) => {
            selectedRoute = e.target.value;
            loadBuses();
        });
        
        // Start auto-refresh
        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            refreshInterval = setInterval(() => {
                if (selectedRoute) {
                    loadBuses();
                }
            }, 5000); // Refresh every 5 seconds
        }
        
        // Initialize
        loadRoutes();
        startAutoRefresh();
    </script>
</body>
</html>
