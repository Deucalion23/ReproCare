# ReproCare Project Summary

Complete overview of the ReproCare system files and documentation.

---

## Project Overview

**Name**: ReproCare: Maternal and Reproductive Health Tracking and Learning System
**Type**: Web-based Health Information System
**Framework**: Laravel 12 (PHP 8.2+)
**Database**: SQLite (default) or MySQL
**Frontend**: Blade Templates + Vite

---

## Directory Structure

```
reprocare/
│
├── app/                          # Application Logic
│   ├── Http/Controllers/         # Controllers (Admin, Bhw, Patient, User)
│   ├── Models/                   # Eloquent Models
│   ├── Providers/                # Service Providers
│   └── ...
│
├── bootstrap/                    # Framework Bootstrap
│
├── config/                       # Configuration Files
│
├── database/                     # Database Files
│   ├── database.sqlite          # SQLite Database (created after setup)
│   ├── factories/               # Model Factories
│   ├── migrations/              # Database Migrations (22 files)
│   └── seeders/                 # Database Seeders
│
├── docs/                         # Documentation
│   ├── ERD.md                    # Entity Relationship Diagram
│   ├── ReproCare_Conceptual_Framework.md
│   ├── ReproCare_Use_Case.md
│   ├── ReproCare_DFD.md          # Data Flow Diagrams
│   └── ReproCare_Data_Dictionary.md
│
├── public/                       # Public Assets
│   ├── build/                    # Compiled CSS/JS (after npm run build)
│   ├── images/                   # Image uploads
│   └── index.php                 # Entry point
│
├── resources/                    # Views & Assets
│   ├── views/                    # Blade Templates
│   │   ├── admin/               # Midwife views
│   │   ├── bhw/                 # BHW views
│   │   ├── patient/             # Patient views
│   │   └── welcome.blade.php    # Landing page
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript files
│
├── routes/                       # Route Definitions
│   └── web.php                  # Web routes
│
├── storage/                      # Storage
│   ├── app/                     # File uploads
│   ├── framework/               # Cache, sessions, views
│   └── logs/                    # Log files
│
├── tests/                        # Unit/Feature Tests
│
├── vendor/                       # Composer Dependencies (generated)
├── node_modules/                 # NPM Dependencies (generated)
│
├── .env                          # Environment Configuration (generated)
├── .env.example                  # Environment Template
├── .gitignore                    # Git Ignore Rules
├── .editorconfig                 # Editor Configuration
├── artisan                       # Laravel CLI
├── composer.json                 # PHP Dependencies
├── composer.lock                 # Locked Dependencies
├── database_export.bat           # Database Export Script
├── database_restore.bat          # Database Restore Script
├── INSTALLATION.md               # Detailed Installation Guide
├── INSTALLATION_QUICK.md         # Quick Installation Reference
├── PROJECT_SUMMARY.md            # This file
├── README.md                     # Project Overview
├── SETUP_CHECKLIST.md            # Setup Checklist
├── TRANSFER_GUIDE.md             # Transfer Instructions
└── vite.config.js               # Vite Configuration
```

---

## Database Schema

### Tables (8 Core Tables)

1. **users** - User accounts (Midwife, BHW, Patient)
2. **health_records** - Vital signs and health data
3. **checkups** - Appointment scheduling
4. **pregnancies** - Pregnancy tracking
5. **cycles** - Menstrual cycle tracking
6. **fertility_logs** - Fertility indicators
7. **menstruation_records** - Legacy cycle data
8. **bhw_monthly_reports** - BHW report metadata

### Migrations (22 Files)

- Laravel default: users, cache, jobs
- ReproCare specific: midwives, bhw, pregnancies, menstruation_records, checkups, health_records, learning_materials, forum_posts, forum_comments, forum_likes, notifications, cycles, fertility_logs, bhw_monthly_reports

### Seeders

- **DatabaseSeeder.php** - Creates default Midwife and Patient accounts
- **LearningMaterialSeeder.php** - Seeds health education content
- **MidwifeSeeder.php** - Additional midwife data

---

## Key Features

### User Roles

| Role | Capabilities |
|------|-------------|
| **Midwife (Admin)** | Manage users, record health data, schedule checkups, generate reports, view all data |
| **BHW** | Record health data for patients, generate monthly reports |
| **Patient** | View own records, track pregnancy, log cycles/fertility, view checkups, access learning |

### Modules

1. **User Management** - CRUD users, assign roles
2. **Health Records** - Record/view vital signs, risk assessment
3. **Pregnancy Tracking** - LMP, EDD, AOG calculation, high-risk flagging
4. **Fertility Tracking** - Cycle logging, BBT, ovulation prediction
5. **Checkup Scheduling** - Appointments, midwife assignment
6. **BHW Reports** - Monthly reports with filtering and printing
7. **Learning System** - Health education materials

---

## Documentation Files

| File | Purpose | Audience |
|------|---------|----------|
| **README.md** | Project overview, quick start | Everyone |
| **INSTALLATION.md** | Detailed setup instructions | Developers/IT |
| **INSTALLATION_QUICK.md** | Quick reference commands | Developers |
| **SETUP_CHECKLIST.md** | Step-by-step checklist | First-time installers |
| **TRANSFER_GUIDE.md** | Transfer methods | System administrators |
| **PROJECT_SUMMARY.md** | This file - complete overview | Everyone |
| **docs/ERD.md** | Entity Relationship Diagram | Developers, Thesis panel |
| **docs/ReproCare_Conceptual_Framework.md** | IPO Model | Thesis documentation |
| **docs/ReproCare_Use_Case.md** | Use cases | Thesis documentation |
| **docs/ReproCare_DFD.md** | Data Flow Diagrams | Thesis documentation |
| **docs/ReproCare_Data_Dictionary.md** | Database documentation | Developers, Thesis panel |

---

## Setup Commands

### One-Command Setup
```bash
composer setup
```

### Manual Setup (In Order)
```bash
# 1. Install PHP dependencies
composer install

# 2. Environment setup
copy .env.example .env
php artisan key:generate

# 3. Database setup
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder

# 4. Frontend setup
npm install
npm run build

# 5. Done! Start Apache and access:
# http://localhost/reprocare/public
```

---

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Midwife | midwife@reprocare.com | midwife123 |
| Patient 1 | ana@reprocare.com | password |
| Patient 2 | grace@reprocare.com | password |

---

## Transfer Methods

### Method 1: Full Copy (Easiest)
- Copy entire `reprocare/` folder
- Run `composer install` and `npm install` on new PC
- Database included if SQLite

### Method 2: Clean Install + DB Import
- Copy project without `vendor/` and `node_modules/`
- Run `composer install` and `npm install`
- Import database separately

### Method 3: Git (Recommended for Developers)
- Push to Git repository
- Clone on new PC
- Install dependencies

---

## System Requirements

- **PHP**: 8.2+
- **Extensions**: mbstring, xml, curl, sqlite3/pdo_sqlite, openssl, zip, gd
- **Web Server**: Apache/Nginx with mod_rewrite
- **Database**: SQLite 3.35+ or MySQL 5.7+/MariaDB 10.3+
- **Tools**: Composer 2.0+, Node.js 18+

---

## Quick Reference

| Task | Command |
|------|---------|
| Start development | `php artisan serve` + `npm run dev` |
| Reset database | `php artisan migrate:fresh --seed` |
| Clear cache | `php artisan cache:clear` |
| Export database | Run `database_export.bat` |
| Restore database | Run `database_restore.bat` |
| Build assets | `npm run build` |

---

## Support

For detailed instructions, see:
- **First time setup**: [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)
- **Transferring system**: [TRANSFER_GUIDE.md](TRANSFER_GUIDE.md)
- **Installation issues**: [INSTALLATION.md](INSTALLATION.md)
- **Quick commands**: [INSTALLATION_QUICK.md](INSTALLATION_QUICK.md)

---

## Project Statistics

- **Controllers**: 4 (Admin, Bhw, Patient, User)
- **Models**: 12+
- **Migrations**: 22
- **Views**: 80+ Blade templates
- **Routes**: 50+ defined routes
- **Documentation**: 7 comprehensive documents

---

*Project Summary Version 1.0*
*Last Updated: April 2026*
