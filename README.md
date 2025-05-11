
# Service Reservation System API
## Introduction
This is a RESTful API for a Service Reservation System, built using Laravel. It allows users to register, browse services (consultations, coaching, etc.), and reserve them online. Admins can manage services and reservations.

## Entity Relationship Diagram (ERD)
ERD

## Features
- JWT Authentication using Laravel Sanctum
- Role-baed Access (User vs Admin)
- Service Browsing & Reservation
- User Reservation History & Cancelation
- Admin-Only Controls
  - Manage Services (Add, Edit, Delete)
  - View and Update All Reservations
  - Export Reservations as CSV
- Middleware-Baed Access Control
  
## API Endpoints
### Authentication API

| HTTP Verbs | Endpoint | Action |
| --- | --- | --- |
POST | ```/api/register``` |	Register a new user|
POST |	```/api/login``` |	Login and receive token|
POST |	```/api/logout``` |	Logout the user|
GET   |  ```/api/user/``` | Get authenticated user info|

### Services API

| HTTP Verbs | Endpoint | Action | Access |
| --- | --- | --- | --- | 
GET | ```/api/services``` | List all services | Public for User & Admin |
GET | ```/api/services/{id}``` | Get details of a single service | Public for User & Admin |
POST | ```/api/servies``` | Add a new service | Admin only | 
PUT | ```/api/services/{id{``` | Update an existing service | Admin only | 
DELETE | ```api/services/{id}``` | Delete a service | Admin only |

### Services API

| HTTP Verbs | Endpoint | Action | 
| --- | --- | --- | 
POST | ```/api/reservations``` | Make a reservation |  
GET | ```/api/reservations``` | List user's reservations |
PUT | ```/api/reservations/{id}``` | Update reservation date/time |
DELETE | ```/api/reservations/{id}``` | Cancel reservation | 
PUT | ```/api/reservations/confirm/{id}``` | Confirm reservation |

<p>⚠️ Confirmation & update/cancel only allowed if conditions pass custom middleware.</p>

### Admin Reservation Actions
| HTTP Verbs | Endpoint | Action | 
| --- | --- | --- | 
GET | ```/api/reservations/all```  | View all reservations | 
GET | ```/api/reservations/{id}``` | Get specific reservation |  
PUT | ```/api/reservations/status/{id}``` | Update reservation status |
PATCH | ```/api/reservations/{id}/cancel``` | Admin cancelation of reservation| 
GET | ```/api/reservations/export/csv``` | Export all reservations to CSV|

## Technologies Used
- Laravel 12
- Laravel Sanctum
- MySQl
- Eloquent ORM
- Custom Middleware
- Laravel Resource Routing
- CSV Export

## Setup Instructions
1. Clone the repository:
   ```
   git clone
   cd service-reservation
   ```
2. Install dependencies:
```
composer install
```
3. Configure ```.env``` file and set up your database credentials.
4. Run database migrations
```
php artisan migrate
```
5. Run tests to make sure everything is working well.
```
php artisan test
```
6. Serve the API:
```
php artisan serve
```

## 🔒 Authentication Details
<p>After login, you'll receive a token.</p>
<p>Include it in the <b>Authorization</b> header like:</p>

```
Authorization: Bearer your-token
```

## Future Enhancements
- Integrate login with third-party like Google and Facebook
- Integrate with payment gateway to handle paid reservations
- Add pagination and filters to service list 
- Implement email notifications for reservation confirmations and updates
- Build a dashboard for service providers to manage their own services
