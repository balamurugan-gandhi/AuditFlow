# AuditFlow - Audit Firm Management System

A comprehensive web application for managing audit firm operations including client management, employee management, file/case tracking, work logs, document management, billing, and notifications.

## Features

### Core Modules
- **Authentication & Authorization** - Role-based access control (Admin, Manager, Employee, Client)
- **Client Management** - Complete CRUD for client information
- **Employee Management** - User management with role assignment
- **File/Case Management** - Track audit files and cases with status workflow
- **Work Logs** - Time tracking for billable hours
- **Document Management** - Upload and manage case-related documents
- **Billing & Invoicing** - Generate invoices and track payments
- **Notifications** - Real-time notifications for important events
- **Scheduler & Queues** - Automated tasks and background job processing

### Technology Stack

**Backend:**
- Laravel 11 (PHP 8.3)
- MySQL 8.0
- Redis
- Laravel Sanctum (API Authentication)
- Spatie Laravel Permission (RBAC)

**Frontend:**
- Vue 3 (Composition API)
- Vite
- Pinia (State Management)
- Vue Router
- PrimeVue (UI Components)
- Axios

**Infrastructure:**
- Docker & Docker Compose
- Nginx
- PHP-FPM

## Quick Start

### Prerequisites
- Docker & Docker Compose installed
- Ports 8080, 3307, 6380, 5174 available

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd AuditFlow
```

2. **Configure environment**
```bash
cd backend
cp .env.example .env
cd ..
```

3. **Start containers**
```bash
docker-compose up -d
```

4. **Install dependencies**
```bash
# Backend
docker-compose exec app composer install

# Frontend
docker-compose run --rm frontend npm install
```

5. **Run migrations and seeders**
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

6. **Create admin user**
```bash
docker-compose exec app php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@auditflow.com',
    'password' => bcrypt('password')
]);
$user->assignRole('admin');
exit
```

7. **Access the application**
- Frontend: http://localhost:5174
- Backend API: http://localhost:8080/api
- Login: admin@auditflow.com / password

## Documentation

- **[API Documentation](API_DOCUMENTATION.md)** - Complete API reference
- **[Deployment Guide](DEPLOYMENT_GUIDE.md)** - Production deployment instructions

## Project Structure

```
AuditFlow/
├── backend/                 # Laravel backend
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Notifications/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   └── tests/
├── frontend/               # Vue.js frontend
│   ├── src/
│   │   ├── api/
│   │   ├── components/
│   │   ├── router/
│   │   ├── stores/
│   │   └── views/
│   └── tests/
├── nginx/                  # Nginx configuration
└── docker-compose.yml
```

## Testing

### Backend Tests
```bash
docker-compose exec app php artisan test
```

### Frontend Tests
```bash
docker-compose run --rm frontend npm test
```

## Key Features Detail

### Client Management
- Add/edit/delete clients
- Store business details, contact information
- Track PAN, GST, TIN numbers
- Define filing cycles

### File/Case Management
- Create files for different service types
- Assign files to employees
- Track file status (pending, in_progress, completed, on_hold)
- Link files to clients

### Work Logs
- Log billable hours per file
- Track work descriptions and dates
- Associate logs with specific employees

### Document Management
- Upload documents to files
- Support for multiple file types
- Secure storage with access control

### Billing System
- Generate invoices for clients
- Track invoice status (unpaid, partial, paid, overdue)
- Record payments with multiple payment methods
- Automatic status updates based on payments

### Notifications
- Invoice generation notifications
- Payment received notifications
- Database-backed notification system
- Unread notification count

### Automated Tasks
- Daily check for overdue invoices
- Queue-based job processing
- Scheduled task execution

## Default Roles & Permissions

- **Admin** - Full system access
- **Manager** - Manage employees, files, and clients
- **Employee** - View assigned files, log work, upload documents
- **Client** - View own files and invoices (future feature)

## Security Features

- API authentication via Laravel Sanctum
- Role-based access control
- Password hashing
- CSRF protection
- Input validation
- SQL injection prevention

## License

Proprietary - All rights reserved

## Support

For issues and questions, please refer to the documentation or contact the development team.
