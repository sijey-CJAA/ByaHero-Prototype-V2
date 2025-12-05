<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByaHero - Conductor Dashboard</title>
    
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
            padding: 2rem;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .content {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.95rem;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .status-indicator {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .status-inactive {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .info-box {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1.5rem;
        }
        
        .info-box h3 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.85rem;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item strong {
            color: #1f2937;
        }
        
        .info-item span {
            color: #6b7280;
        }
        
        #trackingSection {
            display: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚌 Conductor Dashboard</h1>
            <p>Manage your bus and share location with passengers</p>
        </div>
        
        <div class="content">
            <div id="alertBox"></div>
            
            <!-- Bus Selection Section -->
            <div id="setupSection">
                <div class="status-indicator status-inactive">
                    ⚠️ Please select your bus and configure route to start tracking
                </div>
                
                <div class="form-group">
                    <label for="busSelect">Select Your Bus</label>
                    <select id="busSelect">
                        <option value="">-- Select Bus --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="routeInput">Route Name</label>
                    <input type="text" id="routeInput" placeholder="e.g., Route 1, North Line, etc.">
                    <small>Enter the route name for this trip</small>
                </div>
                
                <div class="button-group">
                    <button class="btn btn-primary" id="startBtn" onclick="startTracking()">
                        Start Tracking
                    </button>
                </div>
            </div>
            
            <!-- Active Tracking Section -->
            <div id="trackingSection">
                <div class="status-indicator status-active">
                    ✅ Location tracking is active
                </div>
                
                <div class="form-group">
                    <label for="statusSelect">Bus Status</label>
                    <select id="statusSelect" onchange="updateStatus()">
                        <option value="available">Available</option>
                        <option value="on_stop">On Stop</option>
                        <option value="full">Full</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="seatsInput">Seats Available</label>
                    <input type="number" id="seatsInput" min="0" max="40" value="40" onchange="updateSeats()">
                    <small>Update available seats count</small>
                </div>
                
                <div class="info-box">
                    <h3>Current Location Info</h3>
                    <div class="info-item">
                        <strong>Bus Code:</strong>
                        <span id="currentBusCode">-</span>
                    </div>
                    <div class="info-item">
                        <strong>Route:</strong>
                        <span id="currentRoute">-</span>
                    </div>
                    <div class="info-item">
                        <strong>Latitude:</strong>
                        <span id="currentLat">-</span>
                    </div>
                    <div class="info-item">
                        <strong>Longitude:</strong>
                        <span id="currentLng">-</span>
                    </div>
                    <div class="info-item">
                        <strong>Last Update:</strong>
                        <span id="lastUpdate">-</span>
                    </div>
                </div>
                
                <div class="button-group">
                    <button class="btn btn-danger" onclick="stopTracking()">
                        Stop Tracking
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let trackingInterval = null;
        let currentBus = null;
        let currentPosition = null;
        
        /**
         * Load available buses on page load
         */
        function loadBuses() {
            fetch('/api.php?action=get_buses')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.buses) {
                        const select = document.getElementById('busSelect');
                        data.buses.forEach(bus => {
                            const option = document.createElement('option');
                            option.value = bus.id;
                            option.textContent = `${bus.code} (${bus.seats_total} seats)`;
                            option.dataset.code = bus.code;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    showAlert('Error loading buses: ' + error.message, 'error');
                });
        }
        
        /**
         * Start location tracking
         */
        function startTracking() {
            const busId = document.getElementById('busSelect').value;
            const route = document.getElementById('routeInput').value.trim();
            
            if (!busId) {
                showAlert('Please select a bus', 'error');
                return;
            }
            
            if (!route) {
                showAlert('Please enter a route name', 'error');
                return;
            }
            
            // Check if geolocation is supported
            if (!navigator.geolocation) {
                showAlert('Geolocation is not supported by your browser', 'error');
                return;
            }
            
            const select = document.getElementById('busSelect');
            const selectedOption = select.options[select.selectedIndex];
            
            currentBus = {
                id: busId,
                code: selectedOption.dataset.code,
                route: route
            };
            
            // Update UI
            document.getElementById('currentBusCode').textContent = currentBus.code;
            document.getElementById('currentRoute').textContent = currentBus.route;
            document.getElementById('setupSection').style.display = 'none';
            document.getElementById('trackingSection').style.display = 'block';
            
            // Start tracking location
            trackingInterval = setInterval(updateLocation, 3000);
            updateLocation(); // Initial update
            
            showAlert('Tracking started successfully!', 'success');
        }
        
        /**
         * Stop location tracking
         * Notify server so passenger view won't continue to show this bus
         */
        function stopTracking() {
            if (trackingInterval) {
                clearInterval(trackingInterval);
                trackingInterval = null;
            }
            
            // If we have a current bus, inform server to stop tracking (clear location + set unavailable)
            if (currentBus && currentBus.id) {
                fetch('/api.php?action=stop_tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ bus_id: currentBus.id })
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        console.error('Failed to stop tracking:', result.error);
                        showAlert('Failed to notify server when stopping tracking', 'error');
                    } else {
                        showAlert('Stopped tracking and notified server.', 'success');
                    }
                })
                .catch(error => {
                    console.error('Error stopping tracking:', error);
                    showAlert('Error notifying server when stopping tracking', 'error');
                });
            } else {
                // No active bus, just show confirmation
                showAlert('Tracking stopped', 'success');
            }
            
            currentBus = null;
            currentPosition = null;
            
            // Reset UI
            document.getElementById('setupSection').style.display = 'block';
            document.getElementById('trackingSection').style.display = 'none';
            document.getElementById('routeInput').value = '';
        }
        
        /**
         * Update bus location
         */
        function updateLocation() {
            navigator.geolocation.getCurrentPosition(
                position => {
                    currentPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Update UI
                    document.getElementById('currentLat').textContent = currentPosition.lat.toFixed(6);
                    document.getElementById('currentLng').textContent = currentPosition.lng.toFixed(6);
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    
                    // Send to server
                    const data = {
                        bus_id: currentBus.id,
                        lat: currentPosition.lat,
                        lng: currentPosition.lng,
                        route: currentBus.route,
                        seats_available: parseInt(document.getElementById('seatsInput').value),
                        status: document.getElementById('statusSelect').value
                    };
                    
                    fetch('/api.php?action=update_location', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (!result.success) {
                            console.error('Failed to update location:', result.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating location:', error);
                    });
                },
                error => {
                    console.error('Geolocation error:', error);
                    showAlert('Unable to get location. Please check permissions.', 'error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }
        
        /**
         * Update bus status
         */
        function updateStatus() {
            if (currentBus && currentPosition) {
                updateLocation();
            }
        }
        
        /**
         * Update seats available
         */
        function updateSeats() {
            if (currentBus && currentPosition) {
                updateLocation();
            }
        }
        
        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        /**
         * Show alert message
         */
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            
            alertBox.innerHTML = `<div class="alert ${alertClass}">${escapeHtml(message)}</div>`;
            
            setTimeout(() => {
                alertBox.innerHTML = '';
            }, 5000);
        }
        
        // Load buses on page load
        loadBuses();
    </script>
</body>
</html>