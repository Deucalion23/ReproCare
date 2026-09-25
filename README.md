# ReproCare: Maternal and Reproductive Health Tracking and Learning System

ReproCare is a comprehensive web-based health information system designed for community-based maternal and reproductive health tracking. The system supports three user roles: **Midwives (Administrators)**, **Barangay Health Workers (BHWs)**, and **Patients**, enabling efficient health record management, pregnancy tracking, fertility monitoring, and health education delivery.

## Features

- **User Management**: Role-based access control for Midwives, BHWs, and Patients
- **Health Records**: Vital signs tracking (BP, weight, temperature, heart rate) with risk assessment
- **Pregnancy Tracking**: Gestational age calculation, EDD estimation, high-risk pregnancy flagging
- **Fertility & Cycle Tracking**: Menstrual cycle logging, ovulation prediction, fertility window identification
- **Checkup Scheduling**: Appointment management with midwife assignment
- **BHW Monthly Reports**: Comprehensive health record reporting with filtering and print capabilities
- **Learning System**: Health education content for patients
- **Database Backup & Restore**: Built-in tools for exporting and importing database for easy system transfer

## System Requirements

- **PHP**: 8.2 or higher
- **Database**: SQLite (default) or MySQL 5.7+/MariaDB 10.3+
- **Web Server**: Apache/Nginx with mod_rewrite enabled
- **Composer**: 2.0 or higher
- **Node.js**: 18.0 or higher (for asset building)

## Quick Installation

### Option 1: One-Command Setup (Recommended)

```bash
composer setup
```

This command will:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

### Option 2: Manual Step-by-Step Setup

See [INSTALLATION.md](INSTALLATION.md) for detailed manual installation instructions.

## Default Login Credentials

After running seeders, use these credentials to log in:

| Role | Email | Password |
|------|-------|----------|
| Midwife | midwife@reprocare.com | midwife123 |
| Patient | ana@reprocare.com | password |
| Patient | grace@reprocare.com | password |

## Project Structure

```
reprocare/
├── app/                    # Application logic (Controllers, Models)
├── bootstrap/              # Framework bootstrap
├── config/                 # Configuration files
├── database/               # Migrations and seeders
├── docs/                   # Documentation (ERD, DFD, Framework)
├── public/                 # Public assets (CSS, JS, images)
├── resources/              # Views (Blade templates)
├── routes/                 # Route definitions
├── storage/                # Logs and cached files
└── tests/                  # Unit and feature tests
```

## Documentation

- **[ERD.md](docs/ERD.md)**: Entity Relationship Diagram
- **[ReproCare_Conceptual_Framework.md](docs/ReproCare_Conceptual_Framework.md)**: Conceptual Framework
- **[ReproCare_Use_Case.md](docs/ReproCare_Use_Case.md)**: Use Case Diagram
- **[ReproCare_DFD.md](docs/ReproCare_DFD.md)**: Data Flow Diagrams
- **[ReproCare_Data_Dictionary.md](docs/ReproCare_Data_Dictionary.md)**: Data Dictionary
- **[INSTALLATION.md](INSTALLATION.md)**: Detailed Installation Guide
- **[INSTALLATION_QUICK.md](INSTALLATION_QUICK.md)**: Quick Reference
- **[SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)**: Setup Checklist
- **[TRANSFER_GUIDE.md](TRANSFER_GUIDE.md)**: Transfer Instructions
- **[DATABASE_TRANSFER.md](DATABASE_TRANSFER.md)**: Database Export/Import Guide
- **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)**: Complete Project Overview

## Performance Optimization

The system includes built-in performance optimizations:

- **Query Caching**: Dashboard stats cached for 5 minutes
- **Eager Loading**: All relationships loaded in single queries
- **Database Indexes**: Optimized for common query patterns
- **Column Selection**: Only needed columns fetched

### Apply Optimizations

Run the optimization script:
```bash
optimize_performance.bat
```

Or manually:
```bash
php artisan migrate                    # Add database indexes
php artisan cache:clear                # Clear old cache
php artisan config:cache               # Cache config
php artisan route:cache                # Cache routes
php artisan view:cache                 # Cache views
composer dump-autoload --optimize      # Optimize autoloader
```

**Expected improvements**:
- Dashboard: 85% faster
- Patient Details: 90% faster  
- Memory usage: 50% reduction

For details, see [OPTIMIZE_PERFORMANCE.md](OPTIMIZE_PERFORMANCE.md)

## Database Migration

To migrate an existing database to a new installation:

1. Export current database:
   ```bash
   php artisan db:seed --class=DatabaseSeeder
   ```

2. Copy the database file (if using SQLite):
   - Source: `database/database.sqlite`
   - Destination: New PC `database/database.sqlite`

3. For MySQL/MariaDB, export SQL dump and import on new system.

## Transfer to Another PC

See [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md) for complete transfer instructions.

## Support

CHO Analytics includes free local decision support, pregnancy priorities, and maternal-death trends by month and barangay. See [Analytics and optional local AI setup](docs/ANALYTICS_AI_SETUP.md) for usage, data definitions, and Ollama instructions.

For online AI on a computer with limited memory, use the [Groq setup guide](docs/GROQ_ANALYTICS_SETUP.md). The integration includes an API-key check, a synthetic connection test, grouped outbound statistics, and a local fallback when Groq is unavailable.

For issues or questions, please refer to the documentation in the `docs/` directory.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
