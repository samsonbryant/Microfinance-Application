# Installation Guide - Microfinance Management System

This guide will walk you through the complete installation and setup process for the Microfinance Management System (MMS).

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 16.x or higher
- **NPM**: 8.x or higher
- **Database**: SQLite (included) or MySQL 8.0+ / PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum, 1GB recommended
- **Storage**: 2GB free space

### Recommended Requirements
- **PHP**: 8.3
- **Memory**: 2GB RAM
- **Storage**: 10GB free space
- **Database**: MySQL 8.0+ or PostgreSQL 13+

## 🚀 Step-by-Step Installation

### Step 1: Download and Extract

1. Download the project files
2. Extract to your web server directory
3. Navigate to the project directory:
   ```bash
   cd microfinance-laravel
   ```

### Step 2: Install Dependencies

#### Install PHP Dependencies
```bash
composer install --optimize-autoloader
```

#### Install Node.js Dependencies
```bash
npm install
```

### Step 3: Environment Configuration

1. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

2. Generate application key:
   ```bash
   php artisan key:generate
   ```

3. Configure your `.env` file with the following settings:

```env
# Application
APP_NAME="Microfinance MMS"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database (SQLite - Default)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# OR MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=microfinance_mms
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# OR PostgreSQL
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=microfinance_mms
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Twilio SMS Configuration
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# Queue Configuration
QUEUE_CONNECTION=database

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
```

### Step 4: Database Setup

#### For SQLite (Default)
```bash
# Create the database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

#### For MySQL
```bash
# Create database
mysql -u root -p
CREATE DATABASE microfinance_mms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

#### For PostgreSQL
```bash
# Create database
createdb microfinance_mms

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

### Step 5: Build Assets

#### For Development
```bash
npm run dev
```

#### For Production
```bash
npm run build
```

### Step 6: Set Permissions

#### Linux/Unix Systems
```bash
# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Windows Systems
Ensure the web server has read/write access to:
- `storage/` directory
- `bootstrap/cache/` directory

### Step 7: Configure Web Server

#### Apache Configuration
Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/microfinance-laravel/public
    
    <Directory /path/to/microfinance-laravel/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/microfinance_error.log
    CustomLog ${APACHE_LOG_DIR}/microfinance_access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/microfinance-laravel/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 8: SSL Certificate (Recommended)

#### Using Let's Encrypt
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d your-domain.com
```

### Step 9: Production Optimization

#### Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Queue Workers (Background Jobs)
```bash
# Start queue worker
php artisan queue:work --daemon

# Or use Supervisor for production
sudo nano /etc/supervisor/conf.d/microfinance-worker.conf
```

Supervisor configuration:
```ini
[program:microfinance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/microfinance-laravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/microfinance-laravel/storage/logs/worker.log
stopwaitsecs=3600
```

#### Scheduled Tasks (Cron Jobs)
Add to your crontab:
```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path/to/microfinance-laravel && php artisan schedule:run >> /dev/null 2>&1
```

### Step 10: Final Verification

1. **Test the installation**:
   - Visit `http://your-domain.com`
   - Login with admin credentials
   - Check all major features

2. **Verify PWA functionality**:
   - Open in mobile browser
   - Test offline capabilities
   - Check service worker registration

3. **Test notifications**:
   - Configure Twilio settings
   - Test SMS notifications
   - Test email notifications

## 🔧 Post-Installation Configuration

### 1. Configure Twilio SMS
1. Sign up for Twilio account
2. Get your Account SID and Auth Token
3. Purchase a phone number
4. Update `.env` file with Twilio credentials

### 2. Configure Email Settings
1. Set up SMTP server (Gmail, SendGrid, etc.)
2. Update mail configuration in `.env`
3. Test email functionality

### 3. Set Up Backup
```bash
# Create backup script
nano backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
PROJECT_DIR="/path/to/microfinance-laravel"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
sqlite3 $PROJECT_DIR/database/database.sqlite ".backup $BACKUP_DIR/database_$DATE.sqlite"

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $PROJECT_DIR storage/ public/uploads/

# Clean old backups (keep last 7 days)
find $BACKUP_DIR -name "*.sqlite" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### 4. Monitor System Health
```bash
# Check logs
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:monitor

# Check scheduled tasks
php artisan schedule:list
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Database Connection Issues
- Check database credentials in `.env`
- Ensure database server is running
- Verify database exists

#### 3. Asset Loading Issues
```bash
npm run build
php artisan view:clear
php artisan cache:clear
```

#### 4. Queue Not Processing
```bash
php artisan queue:restart
php artisan queue:work
```

#### 5. PWA Not Working
- Check service worker registration
- Verify HTTPS is enabled
- Clear browser cache

### Log Files
- Application logs: `storage/logs/laravel.log`
- Queue logs: `storage/logs/worker.log`
- Web server logs: Check your web server configuration

## 📞 Support

If you encounter issues during installation:

1. Check the logs for error messages
2. Verify all requirements are met
3. Ensure proper permissions are set
4. Contact support with detailed error information

## 🔄 Updates

To update the system:

1. Backup your database and files
2. Pull latest changes
3. Run `composer install`
4. Run `npm install && npm run build`
5. Run `php artisan migrate`
6. Clear caches: `php artisan cache:clear`

---

**Installation completed successfully! 🎉**

Your Microfinance Management System is now ready to use.
