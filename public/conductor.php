<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByaHero - Conductor Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
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
            color: #f5576c;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1.05em;
        }
        
        select, input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background: white;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #f5576c;
        }
        
        .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .radio-item input[type="radio"] {
            width: auto;
            cursor: pointer;
        }
        
        .radio-item label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
        
        .current-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .current-info h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .current-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .link {
            text-align: center;
            margin-top: 20px;
        }
        
        .link a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .link a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👨‍✈️ Conductor Panel</h1>
            <p class="subtitle">Update your bus status and seat availability</p>
        </header>
        
        <div id="messageContainer"></div>
        
        <div class="card">
            <form id="conductorForm">
                <div class="form-group">
                    <label for="busSelect">Select Your Bus:</label>
                    <select id="busSelect" required>
                        <option value="">Loading buses...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Bus Status:</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="statusAvailable" name="status" value="available" checked>
                            <label for="statusAvailable">Available</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="statusOnStop" name="status" value="on_stop">
                            <label for="statusOnStop">On Stop</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="statusFull" name="status" value="full">
                            <label for="statusFull">Full</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="statusUnavailable" name="status" value="unavailable">
                            <label for="statusUnavailable">Unavailable</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="seatsAvailable">Seats Available:</label>
                    <input type="number" id="seatsAvailable" name="seats_available" min="0" required>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Enter the number of currently available seats
                    </small>
                </div>
                
                <div id="currentInfo"></div>
                
                <button type="submit" id="submitBtn">Update Bus Status</button>
            </form>
        </div>
        
        <div class="link">
            <a href="index.php">← View Passenger Page</a>
        </div>
    </div>

    <script>
        let buses = [];
        let selectedBusCode = '';
        
        // Load all buses
        async function loadBuses() {
            try {
                const response = await fetch('api/get_buses.php');
                const data = await response.json();
                
                if (data.success) {
                    buses = data.buses;
                    populateBusSelect();
                } else {
                    showMessage('Failed to load buses: ' + data.error, 'error');
                }
            } catch (error) {
                showMessage('Failed to load buses: ' + error.message, 'error');
            }
        }
        
        // Populate bus select dropdown
        function populateBusSelect() {
            const select = document.getElementById('busSelect');
            
            if (buses.length === 0) {
                select.innerHTML = '<option value="">No buses available</option>';
                return;
            }
            
            select.innerHTML = '<option value="">-- Select your bus --</option>';
            buses.forEach(bus => {
                const option = document.createElement('option');
                option.value = bus.code;
                option.textContent = `${bus.code} - ${bus.route}`;
                option.dataset.bus = JSON.stringify(bus);
                select.appendChild(option);
            });
        }
        
        // Handle bus selection
        document.getElementById('busSelect').addEventListener('change', (e) => {
            selectedBusCode = e.target.value;
            
            if (selectedBusCode) {
                const option = e.target.selectedOptions[0];
                const bus = JSON.parse(option.dataset.bus);
                displayCurrentInfo(bus);
                
                // Pre-fill form with current values
                document.getElementById('seatsAvailable').value = bus.seats_available;
                document.getElementById('seatsAvailable').max = bus.seats_total;
                document.querySelector(`input[name="status"][value="${bus.status}"]`).checked = true;
            } else {
                document.getElementById('currentInfo').innerHTML = '';
            }
        });
        
        // Display current bus information
        function displayCurrentInfo(bus) {
            const container = document.getElementById('currentInfo');
            container.innerHTML = `
                <div class="current-info">
                    <h3>Current Bus Information</h3>
                    <p><strong>Code:</strong> ${bus.code}</p>
                    <p><strong>Route:</strong> ${bus.route}</p>
                    <p><strong>Status:</strong> ${bus.status.replace('_', ' ')}</p>
                    <p><strong>Seats:</strong> ${bus.seats_available} / ${bus.seats_total} available</p>
                    <p><strong>Last Updated:</strong> ${bus.last_update_formatted}</p>
                </div>
            `;
        }
        
        // Handle form submission
        document.getElementById('conductorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!selectedBusCode) {
                showMessage('Please select a bus', 'error');
                return;
            }
            
            const status = document.querySelector('input[name="status"]:checked').value;
            const seatsAvailable = parseInt(document.getElementById('seatsAvailable').value);
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            
            try {
                const response = await fetch('api/update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        code: selectedBusCode,
                        status: status,
                        seats_available: seatsAvailable
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('✓ Bus status updated successfully!', 'success');
                    
                    // Update the bus in our local array
                    const busIndex = buses.findIndex(b => b.code === selectedBusCode);
                    if (busIndex !== -1) {
                        buses[busIndex] = data.bus;
                        displayCurrentInfo(data.bus);
                    }
                } else {
                    showMessage('Failed to update: ' + data.error, 'error');
                }
            } catch (error) {
                showMessage('Failed to update: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Bus Status';
            }
        });
        
        // Show message
        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            container.innerHTML = `<div class="${type}">${message}</div>`;
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    container.innerHTML = '';
                }, 5000);
            }
        }
        
        // Initialize
        loadBuses();
    </script>
</body>
</html>
