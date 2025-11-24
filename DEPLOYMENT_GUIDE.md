# AuditFlow Deployment Guide

## Prerequisites

- Docker & Docker Compose
- Git
- Minimum 2GB RAM
- Ports available: 8080 (Nginx), 3307 (MySQL), 6380 (Redis), 5174 (Frontend)

---

## Quick Start (Development)

### 1. Clone the Repository
```bash
git clone <repository-url>
cd AuditFlow
```

### 2. Configure Environment
```bash
cd backend
cp .env.example .env
```

Edit `.env` and configure:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=auditflow
DB_USERNAME=auditflow
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Start Docker Containers
```bash
cd ..
docker-compose up -d
```

This will start:
- **app** - Laravel backend (PHP-FPM)
- **nginx** - Web server (port 8080)
- **db** - MySQL database (port 3307)
- **redis** - Redis cache (port 6380)
- **worker** - Queue worker
- **scheduler** - Task scheduler
- **frontend** - Vue.js dev server (port 5174)

### 4. Install Dependencies

**Backend:**
```bash
docker-compose exec app composer install
```

**Frontend:**
```bash
docker-compose run --rm frontend npm install
```

### 5. Run Migrations & Seeders
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

This creates:
- Database tables
- Default roles (admin, manager, employee, client)
- Permissions

### 6. Generate Application Key
```bash
docker-compose exec app php artisan key:generate
```

### 7. Create Storage Link
```bash
docker-compose exec app php artisan storage:link
```

### 8. Create Admin User
```bash
docker-compose exec app php artisan tinker
```

In Tinker:
```php
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@auditflow.com',
    'password' => bcrypt('password')
]);
$user->assignRole('admin');
exit
```

### 9. Access the Application

- **Frontend:** http://localhost:5174
- **Backend API:** http://localhost:8080/api
- **Login:** admin@auditflow.com / password

---

## Production Deployment

### 1. Environment Configuration

Update `.env` for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

REDIS_HOST=your-redis-host

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 2. Build Frontend for Production
```bash
cd frontend
npm run build
```

### 3. Configure Nginx

Update `nginx/conf.d/app.conf` to serve built frontend:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/public;

    # Serve Vue.js built files
    location / {
        try_files $uri $uri/ /index.html;
        root /var/www/frontend/dist;
    }

    # API endpoints
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. SSL Configuration (Recommended)

Use Let's Encrypt with Certbot:
```bash
docker-compose exec nginx certbot --nginx -d yourdomain.com
```

### 5. Optimize Laravel
```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app composer install --optimize-autoloader --no-dev
```

### 6. Set Up Cron (for Scheduler)

On the host machine, add to crontab:
```bash
* * * * * cd /path/to/AuditFlow && docker-compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

Or use the scheduler container (already configured in docker-compose.yml).

### 7. Configure Queue Worker

The queue worker is already running via docker-compose. Monitor it:
```bash
docker-compose logs -f worker
```

### 8. Database Backups

Set up automated backups:
```bash
# Backup script
docker-compose exec db mysqldump -u auditflow -p auditflow > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T db mysql -u auditflow -p auditflow < backup_20240101.sql
```

---

## Monitoring & Maintenance

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f worker
docker-compose logs -f nginx
```

### Restart Services
```bash
# All services
docker-compose restart

# Specific service
docker-compose restart app
docker-compose restart worker
```

### Clear Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Run Tests
```bash
# Backend
docker-compose exec app php artisan test

# Frontend
docker-compose run --rm frontend npm test
```

### Update Application
```bash
git pull origin main
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec app php artisan config:cache
docker-compose restart app worker
```

---

## Troubleshooting

### Permission Issues
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Failed
- Check if MySQL container is running: `docker-compose ps`
- Verify credentials in `.env`
- Check database logs: `docker-compose logs db`

### Queue Jobs Not Processing
- Check worker logs: `docker-compose logs worker`
- Restart worker: `docker-compose restart worker`
- Verify Redis connection: `docker-compose exec app php artisan queue:work --once`

### Frontend Not Loading
- Check if frontend container is running: `docker-compose ps`
- Rebuild frontend: `docker-compose run --rm frontend npm run build`
- Check Nginx logs: `docker-compose logs nginx`

### Storage Files Not Accessible
```bash
docker-compose exec app php artisan storage:link
```

---

## Security Checklist

- [ ] Change default database passwords
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure SSL/TLS certificates
- [ ] Set up firewall rules
- [ ] Enable CORS only for trusted domains
- [ ] Regular security updates
- [ ] Implement rate limiting
- [ ] Set up monitoring and alerts
- [ ] Regular database backups
- [ ] Secure file upload validation

---

## Performance Optimization

### Enable OPcache (Production)
Add to `backend/Dockerfile`:
```dockerfile
RUN docker-php-ext-install opcache
```

### Database Indexing
Ensure proper indexes on frequently queried columns.

### Redis Caching
Already configured for sessions, cache, and queues.

### CDN for Static Assets
Serve frontend assets via CDN in production.

---

## Scaling

### Horizontal Scaling
- Use load balancer for multiple app containers
- Separate database server
- Redis cluster for high availability
- Multiple queue workers

### Vertical Scaling
- Increase container resources in docker-compose.yml
- Optimize MySQL configuration
- Tune PHP-FPM worker processes

---

## Support

For issues and questions:
- Check logs: `docker-compose logs`
- Review API documentation: `API_DOCUMENTATION.md`
- Run tests to verify functionality
