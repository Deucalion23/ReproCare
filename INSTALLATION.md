# ReproCare Installation Guide

Complete step-by-step installation instructions for transferring ReproCare to a new PC or fresh installation.

---

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Pre-Installation Checklist](#pre-installation-checklist)
3. [Step-by-Step Installation](#step-by-step-installation)
4. [Database Configuration](#database-configuration)
5. [Post-Installation Setup](#post-installation-setup)
6. [Verification Steps](#verification-steps)
7. [Troubleshooting](#troubleshooting)

---

## System Requirements

### Minimum Requirements

| Component | Requirement |
|-----------|-------------|
| PHP | 8.2 or higher |
| Web Server | Apache 2.4+ or Nginx 1.18+ |
| Database | SQLite 3.35+ or MySQL 5.7+ / MariaDB 10.3+ |
| Composer | 2.0 or higher |
| Node.js | 18.0 or higher (for development) |
| RAM | 4 GB minimum |
| Storage | 1 GB free space |

### Required PHP Extensions

- `php-mbstring`
- `php-xml`
- `php-curl`
- `php-sqlite3` (for SQLite)
- `php-mysql` (for MySQL)
- `php-openssl`
- `php-zip`
- `php-gd` or `php-imagick`

---

## Pre-Installation Checklist

Before starting installation, ensure you have:

- [ ] XAMPP/WAMP/MAMP installed (for Windows/Mac) OR Apache/Nginx + PHP installed
- [ ] Composer installed globally
- [ ] Node.js and npm installed
- [ ] Git installed (optional, for version control)
- [ ] Project files copied to new PC (via USB, cloud, or Git)

---

## Step-by-Step Installation

### Step 1: Copy Project Files

Copy the entire `reprocare` folder to your new PC:

```
Source: [Original PC] C:\xampp\htdocs\CapstoneProject\reprocare
Destination: [New PC] C:\xampp\htdocs\reprocare
```

Or if using a different path:
```
C:\laragon\www\reprocare
C:\wamp64\www\reprocare
/var/www/html/reprocare (Linux)
```

### Step 2: Install PHP Dependencies

Open terminal/command prompt in the project directory:

```bash
cd C:\xampp\htdocs\reprocare
composer install
```

**Note:** If you don't have `vendor/` folder, this command will download all dependencies. If `vendor/` exists, it will verify and update if needed.

### Step 3: Configure Environment File

Copy the example environment file:

```bash
copy .env.example .env
```

Or on Linux/Mac:
```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Step 4: Database Setup

#### Option A: SQLite (Default, Easiest)

1. Ensure SQLite extension is enabled in `php.ini`:
   ```ini
   extension=sqlite3
   extension=pdo_sqlite
   ```

2. Create SQLite database file:
   ```bash
   php -r "touch('database/database.sqlite');"
   ```

3. Run migrations:
   ```bash
   php artisan migrate --force
   ```

#### Option B: MySQL/MariaDB

1. Update `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=reprocare
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

2. Create database:
   ```sql
   CREATE DATABASE reprocare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Run migrations:
   ```bash
   php artisan migrate --force
   ```

### Step 5: Seed Initial Data

Run the database seeder to create default users:

```bash
php artisan db:seed --class=DatabaseSeeder
```

This creates:
- Midwife account: `midwife@reprocare.com` / `midwife123`
- Test patients: `ana@reprocare.com`, `grace@reprocare.com` / `password`

### Step 6: Install Frontend Dependencies

```bash
npm install
```

### Step 7: Build Frontend Assets

For production:
```bash
npm run build
```

For development (with hot reload):
```bash
npm run dev
```

---

## Database Configuration

### Importing Existing Database (If Transferring)

#### SQLite Transfer

1. Copy the database file from old PC:
   ```
   From: database/database.sqlite
   To:   database/database.sqlite
   ```

2. Ensure proper permissions:
   ```bash
   chmod 664 database/database.sqlite
   ```

#### MySQL Transfer

1. Export from old PC:
   ```bash
   mysqldump -u root -p reprocare > reprocare_backup.sql
   ```

2. Import on new PC:
   ```bash
   mysql -u root -p reprocare < reprocare_backup.sql
   ```

---

## Post-Installation Setup

### 1. Configure Web Server

#### For XAMPP (Apache)

The project should work out of the box. Access via:
```
http://localhost/reprocare/public
```

**Recommended:** Set up virtual host in `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/reprocare/public"
    ServerName reprocare.local
    <Directory "C:/xampp/htdocs/reprocare/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 reprocare.local
```

#### For Laragon

Right-click Laragon tray icon → "www" → "Quick create" → "Laravel"

### 2. Set Storage Permissions

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

On Windows (Command Prompt as Admin):
```cmd
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T
```

### 3. Clear Cache (If Needed)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Verification Steps

### 1. Test Application Access

Open browser and navigate to:
```
http://localhost/reprocare/public
```

Or if using virtual host:
```
http://reprocare.local
```

### 2. Verify Database Connection

```bash
php artisan tinker
>>> DB::connection()->getPdo();
// Should return PDO object without errors
>>> exit
```

### 3. Test Login

1. Go to login page
2. Try Midwife login:
   - Email: `midwife@reprocare.com`
   - Password: `midwife123`
3. Verify dashboard loads correctly

### 4. Run Health Check

```bash
php artisan about
```

This displays environment information and confirms proper setup.

---

## Troubleshooting

### Issue: " vendor/autoload.php not found"

**Solution:** Run `composer install`

### Issue: "Failed to open stream: Permission denied"

**Solution:** Check storage permissions:
```bash
chmod -R 775 storage/
```

### Issue: "SQLSTATE[HY000] [1049] Unknown database"

**Solution:** Create database first:
```bash
php -r "touch('database/database.sqlite');"  # For SQLite
# OR create MySQL database manually
```

### Issue: "No application encryption key"

**Solution:** Generate key:
```bash
php artisan key:generate
```

### Issue: CSS/JS not loading (404 on assets)

**Solution:**
1. Ensure `npm run build` completed successfully
2. Check `.env` APP_URL matches your actual URL
3. Run `php artisan storage:link`

### Issue: "Class not found" errors

**Solution:**
```bash
composer dump-autoload
```

### Issue: "Call to undefined function sqlite_open()"

**Solution:** Enable SQLite extension in `php.ini`:
```ini
extension=sqlite3
extension=pdo_sqlite
```

---

## Quick Reference Commands

| Command | Purpose |
|---------|---------|
| `composer install` | Install PHP dependencies |
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed` | Seed database with test data |
| `npm install` | Install Node.js dependencies |
| `npm run build` | Build production assets |
| `npm run dev` | Build with hot reload |
| `php artisan serve` | Start development server |
| `php artisan cache:clear` | Clear all caches |

---

## Next Steps

After successful installation:

1. Review the system documentation in `docs/` folder
2. Log in as Midwife and create BHW accounts
3. Register patient accounts
4. Begin recording health data

For questions or issues, check the README.md or other documentation files.

---

*End of Installation Guide*
