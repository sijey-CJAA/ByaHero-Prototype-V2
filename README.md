# ByaHero-Prototype-V2

A minimal PHP-based real-time bus tracking system MVP inspired by TODARescue. This prototype allows passengers to view real-time bus availability and conductors to update bus status without requiring authentication.

## Features

### Passenger Features
- View real-time list of buses on selected routes
- See bus code, status, and seat availability
- Auto-refreshing display (every 5 seconds)
- Clean, responsive UI

### Conductor Features
- Select operating bus from available fleet
- Update bus status (available, unavailable, on_stop, full)
- Update available seat count
- Simple form-based interface

### Technical Features
- RESTful JSON API endpoints
- SQLite database for data persistence
- PDO with prepared statements for security
- Input validation and error handling
- Real-time updates using polling

## Project Structure

```
ByaHero-Prototype-V2/
├── public/                 # Web-accessible files
│   ├── index.php          # Passenger-facing UI
│   ├── conductor.php      # Conductor-facing UI
│   └── api/               # API endpoints
│       ├── get_buses.php      # Get buses list (filterable by route)
│       ├── get_routes.php     # Get available routes
│       └── update_status.php  # Update bus status/seats
├── src/                   # PHP classes and helpers
│   └── Database.php       # SQLite database helper class
├── scripts/               # Utility scripts
│   └── init_db.php       # Database initialization script
├── data/                  # Database storage (created on init)
│   └── buses.sqlite      # SQLite database file
└── README.md             # This file
```

## Requirements

- PHP 7.4 or higher (tested with PHP 8.3)
- SQLite3 PDO extension (usually included with PHP)
- No external dependencies required

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/sijey-CJAA/ByaHero-Prototype-V2.git
cd ByaHero-Prototype-V2
```

### 2. Initialize the Database

Run the database initialization script to create the SQLite database and seed it with initial bus data:

```bash
php scripts/init_db.php
```

This will:
- Create the `data/` directory if it doesn't exist
- Create a new `buses.sqlite` database
- Create the `buses` table with appropriate schema
- Insert three seed buses (BUS001, BUS002, BUS003) on Route 1

### 3. Start the PHP Development Server

```bash
php -S localhost:8000 -t public
```

The server will start on `http://localhost:8000`

### 4. Access the Application

- **Passenger View**: http://localhost:8000/index.php
- **Conductor Panel**: http://localhost:8000/conductor.php

## API Endpoints

### GET /api/get_routes.php
Returns list of available routes.

**Response:**
```json
{
  "success": true,
  "routes": ["Route 1", "Route 2"]
}
```

### GET /api/get_buses.php?route=Route%201
Returns list of buses, optionally filtered by route.

**Query Parameters:**
- `route` (optional): Filter buses by route name

**Response:**
```json
{
  "success": true,
  "buses": [
    {
      "id": 1,
      "code": "BUS001",
      "route": "Route 1",
      "seats_total": 40,
      "seats_available": 35,
      "status": "available",
      "last_update": 1733319600,
      "last_update_formatted": "2024-12-04 14:00:00"
    }
  ],
  "count": 1
}
```

### POST /api/update_status.php
Updates bus status and/or seat availability.

**Request Body:**
```json
{
  "code": "BUS001",
  "status": "on_stop",
  "seats_available": 30
}
```

**Allowed Status Values:**
- `available` - Bus is running and accepting passengers
- `unavailable` - Bus is not in service
- `on_stop` - Bus is currently at a stop
- `full` - Bus has no available seats

**Validation Rules:**
- `seats_available` must be >= 0 and <= `seats_total`
- `status` must be one of the allowed values
- Bus code must exist in database

**Response:**
```json
{
  "success": true,
  "message": "Bus updated successfully",
  "bus": {
    "id": 1,
    "code": "BUS001",
    "route": "Route 1",
    "seats_total": 40,
    "seats_available": 30,
    "status": "on_stop",
    "last_update": 1733319660,
    "last_update_formatted": "2024-12-04 14:01:00"
  }
}
```

## Database Schema

### Table: `buses`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | Unique bus identifier |
| code | TEXT | UNIQUE NOT NULL | Bus code (e.g., BUS001) |
| route | TEXT | NOT NULL | Route name |
| seats_total | INTEGER | NOT NULL | Total seat capacity |
| seats_available | INTEGER | NOT NULL | Currently available seats |
| status | TEXT | NOT NULL | Current status |
| last_update | INTEGER | NOT NULL | UNIX timestamp of last update |

## Usage Examples

### As a Passenger
1. Open http://localhost:8000/index.php
2. Select a route from the dropdown
3. View real-time bus information
4. The page auto-refreshes every 5 seconds

### As a Conductor
1. Open http://localhost:8000/conductor.php
2. Select your bus from the dropdown
3. Update the bus status using radio buttons
4. Enter available seat count
5. Click "Update Bus Status"
6. The system validates and updates the information

## Development Notes

### Security Considerations
- No authentication implemented (prototype only)
- Input validation on all API endpoints
- Prepared statements used to prevent SQL injection
- CORS headers enabled for API endpoints

### Error Handling
- All API endpoints return JSON responses
- Appropriate HTTP status codes (400, 404, 500)
- User-friendly error messages in UI

### Browser Compatibility
- Uses vanilla JavaScript (no frameworks)
- Fetch API for AJAX requests
- CSS Grid and Flexbox for layout
- Works in all modern browsers

## Testing the Prototype

### Manual Testing Steps

1. **Initialize Database**
   ```bash
   php scripts/init_db.php
   ```

2. **Start Server**
   ```bash
   php -S localhost:8000 -t public
   ```

3. **Test Passenger View**
   - Open http://localhost:8000/index.php
   - Verify buses are displayed
   - Check auto-refresh is working

4. **Test Conductor Panel**
   - Open http://localhost:8000/conductor.php
   - Select a bus (e.g., BUS001)
   - Change status to "on_stop"
   - Update seats to 25
   - Submit the form

5. **Verify Real-Time Updates**
   - Keep passenger view open
   - Update bus status from conductor panel
   - Watch passenger view update within 5 seconds

### API Testing with curl

```bash
# Get all buses
curl http://localhost:8000/api/get_buses.php

# Get buses for specific route
curl http://localhost:8000/api/get_buses.php?route=Route%201

# Get routes
curl http://localhost:8000/api/get_routes.php

# Update bus status
curl -X POST http://localhost:8000/api/update_status.php \
  -H "Content-Type: application/json" \
  -d '{"code":"BUS001","status":"full","seats_available":0}'
```

## Troubleshooting

### Database Issues
If you encounter database errors:
```bash
# Reinitialize the database
rm -f data/buses.sqlite
php scripts/init_db.php
```

### Permission Issues
Ensure the `data/` directory is writable:
```bash
chmod 755 data/
chmod 644 data/buses.sqlite
```

### Port Already in Use
If port 8000 is busy, use a different port:
```bash
php -S localhost:8080 -t public
```

## Next Steps & Recommended Enhancements

### Phase 2 - Authentication & Authorization
- [ ] Add user authentication for conductors
- [ ] Implement role-based access control (admin, conductor, passenger)
- [ ] Add login/logout functionality
- [ ] Session management

### Phase 3 - Real-Time Features
- [ ] Implement WebSocket connections for true real-time updates
- [ ] Add push notifications for passengers
- [ ] GPS integration for live bus location tracking
- [ ] Display buses on an interactive map

### Phase 4 - Enhanced Features
- [ ] Passenger registration and profiles
- [ ] Bus booking/reservation system
- [ ] Historical tracking and analytics
- [ ] Route planning and optimization
- [ ] ETA calculations based on location

### Phase 5 - Mobile Support
- [ ] Progressive Web App (PWA) implementation
- [ ] Native mobile apps (iOS/Android)
- [ ] Offline mode support
- [ ] Push notifications for mobile

### Phase 6 - Operations & Management
- [ ] Admin dashboard for fleet management
- [ ] Report generation and analytics
- [ ] Multi-route and multi-city support
- [ ] Integration with payment systems
- [ ] Driver/conductor management

### Technical Improvements
- [ ] Migrate to MySQL/PostgreSQL for production
- [ ] Add caching layer (Redis)
- [ ] Implement proper logging system
- [ ] Add automated testing (PHPUnit)
- [ ] API rate limiting and throttling
- [ ] Comprehensive error tracking (Sentry)
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Docker containerization
- [ ] CI/CD pipeline setup

## Contributing

This is a prototype project. For production use, please implement proper security measures, authentication, and testing.

## License

See LICENSE file for details.

## Contact

For questions or suggestions, please open an issue on GitHub.