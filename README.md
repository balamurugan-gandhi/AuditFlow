# AuditFlow - Audit Firm Management System

A comprehensive web application designed for audit firms to manage clients, employees, tax filings, and internal workflows.

## 🚀 Features

### Core Modules
*   **Authentication & Authorization:** Secure login with role-based access control (Admin, Manager, Employee, Client).
*   **Client Management:** Complete database of clients with tax details (PAN, GST, TIN) and filing cycles.
*   **Employee Management:** Manage staff profiles, roles, and assignments.
*   **File/Case Tracking:** Workflow system to track audit cases from "Pending" to "Completed".
*   **Work Logs:** Time tracking system for billable hours and employee productivity.
*   **Billing & Invoicing:** Automated invoice generation and payment tracking.
*   **Document Management:** Secure storage for client documents and case files.
*   **Dashboard:** Real-time overview of pending tasks, revenue, and active cases.

## 🛠️ Technology Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.3) |
| **Frontend** | Vue 3 (Composition API) + Vite |
| **UI Framework** | PrimeVue + TailwindCSS |
| **Database** | MySQL 8.0 |
| **Caching/Queue** | Redis |
| **Infrastructure** | Docker & Docker Compose |

## 📋 Prerequisites

Ensure you have the following installed on your local machine:
*   [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ensure it is running)
*   [Git](https://git-scm.com/downloads)

## ⚡ Quick Start Guide

Follow these steps to get the application running locally.

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd AuditFlow
```

### 2. Environment Setup

**Backend Configuration:**
```bash
cd backend
cp .env.example .env
# Open .env and ensure DB_HOST is set to 'db' and REDIS_HOST to 'redis'
cd ..
```

**Frontend Configuration:**
```bash
cd frontend
cp .env.example .env  # If applicable, otherwise create one if needed
cd ..
```

### 3. Start the Application
Run the following command to build and start the Docker containers:

```bash
docker-compose up -d
```
*Wait for a few minutes for the containers to initialize.*

### 4. Install Dependencies
**Backend Dependencies (Important):**
This project runs `composer` inside the container to optimize performance on Windows.

```bash
docker exec auditflow-app composer install
docker exec auditflow-app php artisan key:generate
docker exec auditflow-app php artisan migrate --seed
docker exec auditflow-app php artisan storage:link
```

**Frontend Dependencies:**
The frontend dependencies are managed within the docker container or can be installed locally if developing outside docker.
```bash
# If you want to install node modules locally for IDE support:
cd frontend
npm install
```

### 5. Create Admin User
To access the system, create an initial admin user:

```bash
docker exec -it auditflow-app php artisan tinker
```
Then paste the following PHP code:
```php
$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@auditflow.com',
    'password' => bcrypt('password123'),
]);
$user->assignRole('admin');
exit;
```

### 6. Access the Application
*   **Frontend (App):** [http://localhost:5174](http://localhost:5174)
*   **Backend (API):** [http://localhost:8080](http://localhost:8080)
*   **phpMyAdmin (DB):** [http://localhost:8081](http://localhost:8081)

## � Data Backup & Restoration

To transfer your project to another system, simply copying the files is not enough because database and storage data live inside Docker volumes. Follow these steps to migrate properly.

### Exporting Data (On Old System)
Run these commands in your project root to save your data into files:

**1. Backup Database**
```bash
docker exec auditflow-db mysqldump -u auditflow -psecret --no-tablespaces auditflow > backup_db.sql
```

**2. Backup Uploaded Files**
```bash
docker cp auditflow-app:/var/www/html/storage ./storage_backup
```
*Now copy your entire project folder (including `backup_db.sql` and `storage_backup`) to the new machine.*

### Importing Data (On New System)
After setting up the project and running `docker-compose up -d` on the new machine:

**1. Restore Database**
```powershell
# For PowerShell users:
Get-Content backup_db.sql | docker exec -i auditflow-db mysql -u auditflow -psecret auditflow

# For Git Bash / Linux users:
docker exec auditflow-db mysqldump -u auditflow -psecret --no-tablespaces auditflow > backup_db.sql
```

**2. Restore Uploaded Files**
```bash
docker cp ./storage_backup/. auditflow-app:/var/www/html/storage
```

**3. Reset Permissions**
```bash
docker exec -u 0 auditflow-app chown -R dev:dev /var/www/html/storage
```

## �🐛 Troubleshooting

### "vendor directory not found" or Class not found errors
If you see errors related to missing classes or vendor files in the backend:
1.  Ensure the `backend_vendor` volume is correctly uncommented in `docker-compose.yml`.
2.  Run the composer install command manually:
    ```bash
    docker exec auditflow-app composer install
    ```

### Slow Performance on Windows
Ensure that you are **not** syncing the `vendor/` directory from your host machine. The `docker-compose.yml` should have the following volume configuration for the `app` service:
```yaml
volumes:
  - ./backend:/var/www/html
  - backend_vendor:/var/www/html/vendor  # This enables high-speed internal storage
```

### Database Connection Refused
If the backend cannot connect to the database:
1.  Check if the `db` container is running: `docker ps`
2.  Ensure `.env` in `backend/` has `DB_HOST=db`.

## 🧪 Running Tests

**Backend:**
```bash
docker exec auditflow-app php artisan test
```
**Storage Symlink for windows:**
```bash
mklink /D C:\wamp64\www\AuditFlow\backend\public\storage C:\wamp64\www\AuditFlow\backend\storage\app\public
```

## 📜 License
Proprietary software. All rights reserved.
