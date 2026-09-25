# ReproCare System - Complete Analysis

This document contains the complete analysis of the ReproCare system including system context, current capabilities, user role permissions, and workflow processes.

---

## Table of Contents

1. [System Context](#system-context)
2. [Current System State](#current-system-state)
3. [User Roles](#user-roles)
4. [System Capabilities by Role](#system-capabilities-by-role)
5. [Role Restrictions and Limitations](#role-restrictions-and-limitations)
6. [Workflow Summaries](#workflow-summaries)
7. [System Modules](#system-modules)
8. [Key Features](#key-features)
9. [Statistics](#statistics)

---

## System Context

ReproCare is a comprehensive **maternal and child health management system** designed for barangay-level healthcare delivery in the Philippines. Built with the Laravel PHP framework, the system digitizes and automates the workflow of reproductive health services, replacing manual paper-based processes with a secure, web-based platform.

### Primary Purpose
- **Digitize maternal and child health records** at the barangay level
- **Streamline healthcare workflows** from patient registration to checkup completion
- **Enable real-time monitoring** of high-risk pregnancies and health indicators
- **Facilitate communication** between patients, Barangay Health Workers (BHWs), BHW Presidents, and Midwives
- **Provide analytics and reporting** for health officials to track coverage and outcomes

### Target Users
1. **Women/Patients** - Recipients of healthcare services who track their own health data
2. **Barangay Health Workers (BHWs)** - Frontline health workers who conduct home visits, record health data, and schedule checkups
3. **BHW Presidents** - Supervisors who manage BHWs, review health records, approve reports, and monitor coverage
4. **Midwives** - Healthcare professionals who conduct checkups, provide final approvals, manage the system, and oversee all operations

### Geographic Context
Designed for **Barangay Burgos Padlan, San Carlos City, Pangasinan, Philippines** - with support for multiple puroks (sub-villages) within the barangay structure.

### Technology Stack
- **Backend**: Laravel 11 (PHP Framework)
- **Database**: SQLite (with MySQL compatibility)
- **Frontend**: Blade Templates with TailwindCSS
- **Authentication**: Laravel's built-in authentication with role-based middleware
- **PDF Generation**: DomPDF for reports
- **File Storage**: Local file system for profile images and uploads

---

## Current System State

### Implementation Status
The ReproCare system is **fully functional** with all core modules implemented and operational. The system includes:

- **Complete user management** with 4 distinct roles and approval workflows
- **Health record management** with automatic risk assessment
- **Pregnancy tracking** with trimester monitoring and high-risk alerts
- **Checkup scheduling** with status tracking (scheduled, completed, missed, cancelled)
- **Menstrual cycle tracking** with predictions and calendar views
- **Learning materials** system with articles, videos, files, and week-by-week pregnancy guides
- **Forum** for community discussions with comments and likes
- **Messaging system** for direct communication between all roles
- **Monthly reporting** for BHWs with approval workflow
- **Analytics dashboards** for BHW Presidents and Midwives
- **Coverage analysis** by purok and barangay
- **High-risk pregnancy monitoring** with automated alerts
- **Child care tracking** including target clients, checkups, vaccinations, nutrition, and assessments
- **Walk-in patient management** for temporary records
- **Referral system** from BHWs to Midwives
- **Database backup/restore** functionality
- **Profile management** with image uploads

### Database Structure
The system uses a **consolidated users table** approach where all user types (Women, BHWs, BHW Presidents, Midwives) are stored in a single `users` table with role-based differentiation. Key tables include:

- **users** - All user accounts with role field (user, bhw, bhw_president, midwife)
- **pregnancies** - Pregnancy records with risk assessment
- **health_records** - Health measurements with workflow status
- **checkups** - Scheduled and completed checkups
- **cycles** - Menstrual cycle records with predictions
- **menstruation_dailies** - Daily symptom and mood tracking
- **learning_materials** - Educational content
- **forum_posts**, **forum_comments**, **forum_likes** - Community forum
- **messages** - Direct messaging system
- **notifications** - System alerts and notifications
- **bhw_monthly_reports** - Monthly BHW reports with approval workflow
- **tasks** - Task assignments for BHWs
- **bhw_assignments** - Purok assignments for BHWs
- **checkup_referrals** - Referrals from BHWs to Midwives
- **walk_in_patients** - Temporary patient records
- **maternal_care_target_clients** - Maternal care tracking
- **child_care_target_clients** - Child care tracking
- **child_checkups** - Child health checkups
- **child_records**, **child_vaccinations**, **child_supplements**, **child_nutrition_tracking**, **child_assessments** - Comprehensive child health data
- **puroks** - Geographic subdivisions within barangay

### Workflow Status
All multi-level approval workflows are implemented:
- **Patient Registration**: Women register → BHW reviews → BHW President approves → Midwife final approval
- **Health Records**: BHW creates → Submits to BHW President → President reviews/approves → Midwife accepts
- **Pregnancy Records**: Women declare → BHW records → Submits to BHW President → President reviews/approves → Midwife accepts
- **Monthly Reports**: BHW generates → Submits to BHW President → President reviews/approves → Midwife final approval
- **Referrals**: BHW creates referral → Midwife reviews → Midwife converts to checkup or declines

---

## DFD Process Hierarchy

### Level 0
```
0.0 ReproCare System (Automated)
```

### Level 1
```
1.0 User & Patient Management
2.0 Health Records & Checkups
3.0 BHW Operations
4.0 Supervision & Analytics
5.0 Communication & Learning
6.0 Cycle & Fertility Tracking
```

### Level 2 - Process 1.0 (User & Patient Management)
```
1.1 User Registration & Authentication
1.2 Patient Profile Management
1.3 Walk-in Patient Processing
1.4 Approval Workflow
```

### Level 2 - Process 2.0 (Health Records & Checkups)
```
2.1 Health Record Entry
2.2 Checkup Scheduling
2.3 Checkup Conduct
2.4 Referral Management
2.5 Pregnancy Tracking
2.6 Record Review & Approval
```

### Level 2 - Process 3.0 (BHW Operations)
```
3.1 BHW Assignment Management
3.2 Task Assignment
3.3 Task Tracking
3.4 Monthly Report Generation
```

### Level 2 - Process 4.0 (Supervision & Analytics)
```
4.1 Record Review & Approval
4.2 Health Analytics Generation
4.3 Coverage Analysis
4.4 High-Risk Case Monitoring
4.5 BHW Performance Tracking
4.6 Workflow Management
```

### Level 2 - Process 5.0 (Communication & Learning)
```
5.1 Direct Messaging
5.2 Forum Management
5.3 Notification System
5.4 Learning Materials Management
5.5 Content Delivery
```

### Level 2 - Process 6.0 (Cycle & Fertility Tracking)
```
6.1 Menstrual Cycle Entry
6.2 Daily Symptom Tracking
6.3 Fertility Log Entry
6.4 Cycle Analysis & Prediction
6.5 Report Generation
```

### Summary
- **Level 0:** 1 process
- **Level 1:** 6 processes
- **Level 2:** 30 sub-processes
- **Total:** 37 processes

---

## User Roles

| Role | Description | Color Theme | Authority Level |
|------|-------------|-------------|----------------|
| **Women (user)** | Patients/Clients who receive healthcare services | Orange (#fff3e0) | Level 1 (Data Entry Only) |
| **BHWs (bhw)** | Barangay Health Workers - frontline health workers | Green (#e8f5e9) | Level 2 (Data Entry & Review) |
| **BHW President (bhw_president)** | Supervisor of BHWs, manages operations | Purple (#f3e5f5) | Level 3 (Supervision & Approval) |
| **Midwife (midwife)** | Healthcare professional, final authority | Pink (#fce4ec) | Level 4 (System Administration) |

---

## System Capabilities by Role

### 1. WOMEN (Patients) - Role: `user`

#### Authentication & Profile
- Register new account
- Login/logout
- View and edit profile
- Update profile image
- View other user profiles

#### Dashboard
- View personal dashboard
- View notifications
- Check upcoming appointments

#### Menstruation & Cycle Tracking
- View menstruation records
- Add menstruation records (period start/end, flow intensity)
- View menstrual calendar
- View menstruation statistics
- Generate menstruation report
- Delete menstruation records
- View cycle predictions
- View cycle calendar
- Get cycle status (current phase, predictions)

#### Pregnancy Tracking
- View pregnancy records
- Create new pregnancy declaration

#### Health Records
- View own health records
- Create self-reported health records
- View health history

#### Checkups
- View scheduled checkups
- View checkup history

#### Communication
- Send messages to BHWs, BHW President, Midwife
- View message inbox
- View message threads
- Mark messages as read
- Delete messages

#### Learning Materials
- Browse learning materials (articles, videos, links, files)
- View content by pregnancy week
- Take quizzes on learning content
- View week-by-week pregnancy guides

#### Forum
- View forum posts
- Create forum posts
- Comment on posts
- Like posts
- Edit own posts
- Delete own posts

---

### 2. BHW (Barangay Health Worker) - Role: `bhw`

#### Authentication & Profile
- Login/logout
- View and edit profile
- Update profile image
- View settings

#### Dashboard
- View dashboard with statistics
- View notifications
- View schedules

#### Patient Management
- View all assigned patients
- Create new patient profiles
- View patient details
- View patient menstruation records
- Generate patient menstruation report
- Export patient menstruation data
- Review pending patients
- Approve patient registrations
- Reject patient registrations

#### Checkup Management
- View all checkups
- Create new checkups for patients
- Delete checkups

#### Health Records (Entry Only)
- View health records
- Create health records for patients
- Create health records for walk-in patients
- Submit health records to BHW President for review
- Archive health records

#### Pregnancy Tracking
- View all pregnancies
- View pregnancy details

#### Referral Management
- View all referrals
- Create new referrals to Midwife
- View referral details

#### Walk-in Patient Management
- View walk-in patients
- Create walk-in patient records
- View walk-in patient details
- Edit walk-in patient records
- Update walk-in patient records
- Delete walk-in patient records
- Convert walk-in patient to full user account

#### Monthly Reports
- View monthly reports
- Create new monthly report
- Store monthly report
- View report details
- Print monthly report
- Delete monthly report
- Submit report to BHW President

#### Learning Materials
- Browse learning materials
- View learning content

#### Communication
- Send messages to Women, BHW President, Midwife
- View message inbox
- View message threads
- Mark messages as read
- Delete messages

---

### 3. BHW PRESIDENT - Role: `bhw_president`

#### Authentication & Profile
- Login/logout
- View and edit profile
- Update profile image
- View settings

#### Dashboard
- View dashboard with statistics
- View pending patients

#### Patient Approval
- View pending patients
- Approve patient registrations
- Reject patient registrations

#### BHW Management
- View all BHWs
- Create new BHW accounts
- View BHW details
- Assign BHW to purok
- Archive BHW
- Mark BHW as inactive
- Activate BHW
- Delete BHW

#### BHW Assignments
- View all BHW assignments
- Create new assignment
- View assignment details
- Edit assignment
- Update assignment
- Delete assignment
- Toggle assignment status (active/inactive)

#### Task Management
- View all tasks
- Create new tasks
- View task details
- Edit tasks
- Update tasks
- Delete tasks
- Mark task as complete
- Mark task as in progress

#### Health Record Review
- View all health records
- Edit health records
- Update health records
- Pass health records to Midwife (after review)
- Review health records
- Approve health records
- Reject health records

#### Pregnancy Review
- View all pregnancies
- Review pregnancy records
- Approve pregnancy records
- Reject pregnancy records

#### Analytics
- View health analytics dashboard
- View statistics and trends

#### Coverage Analysis
- View coverage reports by purok
- View BHW coverage statistics

#### High-Risk Monitoring
- View high-risk cases
- Monitor high-risk pregnancies
- View risk alerts

#### Reports
- View all monthly reports from BHWs
- View report details
- Delete reports
- Approve monthly reports
- Reject monthly reports

#### Communication
- Send messages to Women, BHWs, Midwife
- View message inbox
- View message threads
- Mark messages as read
- Delete messages

---

### 4. MIDWIFE - Role: `midwife`

#### Authentication & Profile
- Login/logout
- View and edit profile
- Update profile image
- View settings

#### Dashboard
- View dashboard with statistics
- View admin dashboard
- View notifications
- Create notifications
- Store notifications
- View notification details
- Mark notifications as read
- Delete notifications

#### Patient Management
- View all patients
- View pending patients
- Create new patient profiles
- Store patient profiles
- Approve patient registrations
- Reject patient registrations
- View patient details

#### Checkup Management
- View all checkups
- Create checkups
- Store checkups
- View checkup details
- Edit checkups
- Update checkups
- Delete checkups
- Mark checkup as completed
- Mark checkup as missed
- Mark checkup as scheduled
- Mark checkup as cancelled

#### Health Records (Full Access)
- View all health records
- Create health records
- Store health records
- View health record details
- Edit health records
- Update health records
- Accept health records from BHW President
- Archive health records
- Delete health records
- View patient health records
- View complete patient records
- View incomplete patient records
- Download patient records

#### Pregnancy Management
- View all pregnancies
- View active pregnancies
- Create pregnancy records
- Store pregnancy records
- View pregnancy details
- Edit pregnancy records
- Update pregnancy records
- Delete pregnancy records
- Submit pregnancy to BHW President
- View pregnant patients
- View pregnancy history

#### Menstruation Management
- View all menstruation records
- Create menstruation records
- Store menstruation records
- View menstruation details
- Edit menstruation records
- Update menstruation records
- Delete menstruation records
- View patient menstruation records

#### Learning Materials (Admin)
- View all learning materials
- Create learning materials
- Store learning materials
- Edit learning materials
- Update learning materials
- Delete learning materials

#### Forum Administration
- View all forum posts (admin view)
- Create forum posts (admin)
- Store forum posts (admin)
- Bulk delete forum posts
- View forum post details (admin)
- Edit forum posts (admin)
- Update forum posts (admin)
- Delete forum posts (admin)
- Restore deleted posts

#### BHW President Management
- View all BHW Presidents
- Create BHW President accounts
- Store BHW President
- View BHW President details
- Edit BHW President
- Update BHW President
- Delete BHW President

#### Referral Management
- View all referrals
- View referral details
- Review referrals
- Convert referrals to checkups
- Decline referrals

#### Walk-in Patient Management
- View walk-in patients
- View walk-in patient details
- Edit walk-in patient records
- Update walk-in patient records
- Delete walk-in patient records
- Convert walk-in to user account
- Store converted user

#### BHW Reports
- View BHW monthly reports
- View BHW report details
- Print BHW reports
- Approve BHW reports
- Reject BHW reports

#### Maternal Care Target Clients
- View maternal care target clients
- Print maternal care target client list
- Create maternal care target client
- Store maternal care target client
- Edit maternal care target client
- Update maternal care target client

#### Child Care Target Clients
- View child care target clients
- Print child care target client list
- Create child care target client
- Store child care target client
- Edit child care target client
- Update child care target client

#### Child Checkups
- View child checkups
- Create child checkup
- Store child checkup
- View child checkup details
- Edit child checkup
- Update child checkup
- Delete child checkup

#### Reports
- View reports
- View report details
- Export reports to CSV
- Export reports to PDF

#### Database Management
- View database backups
- Export database
- Import database
- Download backup file
- Delete backup file

#### Communication
- Send messages to Women, BHWs, BHW President
- View message inbox
- View message threads
- Mark messages as read
- Delete messages

---

## Role Restrictions and Limitations

### 1. WOMEN (Patients) - What They CANNOT Do

**Data Management Restrictions:**
- Cannot view or edit other patients' health records
- Cannot create health records for other users
- Cannot edit or delete health records once created (read-only view)
- Cannot delete checkups (view only)
- Cannot approve or reject any records
- Cannot access administrative functions

**System Management Restrictions:**
- Cannot create or manage BHW accounts
- Cannot create or manage BHW President accounts
- Cannot create or manage Midwife accounts
- Cannot access analytics dashboards
- Cannot generate system-wide reports
- Cannot manage learning materials (view only)
- Cannot administer forum (can only participate as regular user)
- Cannot create system notifications
- Cannot perform database backup/restore

**Workflow Restrictions:**
- Cannot approve patient registrations
- Cannot review health records
- Cannot approve monthly reports
- Cannot manage tasks or assignments
- Cannot assign BHWs to puroks
- Cannot create or manage referrals

---

### 2. BHW (Barangay Health Worker) - What They CANNOT Do

**Data Management Restrictions:**
- Cannot edit or delete health records after submission (add-only access)
- Cannot edit or delete checkups scheduled by Midwives
- Cannot view health records created by other BHWs (only their own)
- Cannot edit pregnancy records (view only)
- Cannot edit menstruation records (view only for patients)
- Cannot delete patient accounts

**System Management Restrictions:**
- Cannot create or manage other BHW accounts
- Cannot create or manage BHW President accounts
- Cannot create or manage Midwife accounts
- Cannot access Midwife-level analytics
- Cannot manage learning materials (view only)
- Cannot administer forum (regular user access)
- Cannot create system notifications
- Cannot perform database backup/restore

**Approval Authority Restrictions:**
- Cannot approve patient registrations (can only review and recommend)
- Cannot approve health records (can only submit to President)
- Cannot approve pregnancy records
- Cannot approve monthly reports (can only submit to President)
- Cannot give final approval on any workflow

**Supervisory Restrictions:**
- Cannot manage other BHWs
- Cannot assign BHWs to puroks
- Cannot create or manage tasks
- Cannot access BHW President analytics
- Cannot view coverage reports
- Cannot monitor high-risk cases at supervisory level

**Midwife Functions:**
- Cannot conduct checkups (can only schedule)
- Cannot convert referrals to checkups
- Cannot access child care management
- Cannot manage maternal care target clients

---

### 3. BHW PRESIDENT - What They CANNOT Do

**Data Management Restrictions:**
- Cannot create health records (review and edit only)
- Cannot delete health records
- Cannot create pregnancy records (review and approve only)
- Cannot create checkups
- Cannot edit menstruation records

**System Management Restrictions:**
- Cannot create or manage Midwife accounts
- Cannot manage learning materials (view only)
- Cannot administer forum (regular user access)
- Cannot perform database backup/restore
- Cannot access Midwife admin dashboard

**Final Authority Restrictions:**
- Cannot give final approval on health records (passes to Midwife)
- Cannot give final approval on pregnancy records (passes to Midwife)
- Cannot give final approval on monthly reports (passes to Midwife)
- Cannot approve patient registrations as final authority (passes to Midwife)

**Midwife Functions:**
- Cannot conduct checkups
- Cannot convert referrals to checkups
- Cannot manage child care system
- Cannot manage maternal care target clients
- Cannot manage child care target clients

**BHW Management Limitations:**
- Cannot delete BHWs without proper archiving process
- Cannot reassign BHWs across barangays (must match purok barangay)

---

### 4. MIDWIFE - What They CANNOT Do

**Operational Restrictions:**
- Cannot create health records directly (accepts from BHW President)
- Cannot edit health records created by BHWs without proper workflow
- Cannot delete patient accounts with active records
- Cannot delete BHW accounts without proper process

**Workflow Restrictions:**
- Cannot bypass approval workflows (must follow BHW → President → Midwife chain)
- Cannot create patient registrations that skip BHW review
- Cannot edit BHW monthly reports (can only approve/reject)

**System Restrictions:**
- Cannot delete their own account (requires system admin)
- Cannot modify system-level configurations without proper access
- Cannot assign multiple BHW Presidents (single president per barangay)

**Data Integrity Restrictions:**
- Cannot modify historical health records after final approval
- Cannot delete checkup records that have been completed
- Cannot alter pregnancy outcome data after completion

---

## Shared Features (All Roles)

### Profile System
- View own profile
- Edit own profile
- Update profile image
- Remove profile image
- View other user profiles

### Notifications API
- Get unread notification count
- Get recent notifications
- Mark notification as read
- Mark all notifications as read
- Delete notification

### Forum (All Authenticated Users)
- View forum posts
- Create forum posts
- Store forum posts
- View post details
- Edit own posts
- Update own posts
- Delete own posts
- Comment on posts
- Like posts

### Learning Materials (All Authenticated Users)
- Browse learning materials
- View articles
- View links
- View files
- View week-by-week guides
- View learning content details
- Submit quizzes

---

## Workflow Summaries

### Patient Registration Workflow
```
Women → Register → BHW Review → BHW President Approve → Midwife Final Approval
```

### Health Record Workflow
```
BHW Creates → Submits to BHW President → President Reviews → President Approves → Midwife Accepts
```

### Pregnancy Workflow
```
Women Declares → BHW Records → Submits to BHW President → President Reviews → President Approves → Midwife Accepts
```

### Referral Workflow
```
BHW Creates Referral → Midwife Reviews → Midwife Converts to Checkup OR Declines
```

### Monthly Report Workflow
```
BHW Generates Report → Submits to BHW President → President Reviews → President Approves → Midwife Final Approval
```

---

## System Modules

| Module | Description | Primary Roles |
|--------|-------------|---------------|
| **User Management** | Registration, profiles, authentication | All roles |
| **Patient Management** | Patient records, registration | BHW, BHW President, Midwife |
| **Health Records** | Health measurements, assessments | BHW (entry), BHW President (review), Midwife (final) |
| **Checkups** | Appointments, visit records | BHW, Midwife |
| **Pregnancy Tracking** | Pregnancy records, trimesters | BHW, BHW President, Midwife |
| **Menstruation Tracking** | Period records, cycle analysis | Women, BHW, Midwife |
| **Cycle Tracking** | Cycle predictions, fertility | Women |
| **Referrals** | BHW to Midwife referrals | BHW, Midwife |
| **Walk-in Patients** | Temporary patient records | BHW, Midwife |
| **BHW Management** | BHW accounts, assignments | BHW President, Midwife |
| **Task Management** | Task assignment, tracking | BHW President |
| **BHW Assignments** | Purok assignments | BHW President |
| **Monthly Reports** | BHW monthly health reports | BHW, BHW President, Midwife |
| **Analytics** | Health statistics, trends | BHW President, Midwife |
| **Coverage Analysis** | Geographic coverage by purok | BHW President |
| **High-Risk Monitoring** | Risk alerts, monitoring | BHW President, Midwife |
| **Communication** | Messaging between users | All roles |
| **Forum** | Community discussions | All roles |
| **Learning Materials** | Educational content | Midwife (admin), All (view) |
| **Notifications** | System alerts, reminders | All roles |
| **Database Management** | Backup, restore | Midwife |
| **Maternal Care Target Clients** | Target client tracking | Midwife |
| **Child Care Target Clients** | Child health tracking | Midwife |
| **Child Checkups** | Child health visits | Midwife |

---

## Key Features

### Automation Features
- Automatic cycle predictions
- Automatic period predictions
- Automatic ovulation predictions
- Automatic risk assessment
- Automatic notification generation
- Automatic workflow routing

### Tracking Features
- Menstrual cycle tracking
- Fertility tracking
- Pregnancy tracking (trimesters, outcomes)
- High-risk case monitoring
- BHW performance tracking
- Task progress tracking
- Report status tracking

### Reporting Features
- Monthly health reports (BHW)
- Health analytics (BHW President)
- Coverage reports (BHW President)
- CSV/PDF export (Midwife)
- Menstruation reports (Women, BHW)
- Pregnancy history reports

### Communication Features
- Direct messaging (all roles)
- Forum with comments and likes
- System notifications
- Learning material delivery
- Week-by-week pregnancy guides

---

## Statistics

| Metric | Count |
|--------|-------|
| **User Roles** | 4 |
| **Main Modules** | 20+ |
| **Total Routes** | 150+ |
| **Controllers** | 19 |
| **Workflow Steps** | 3-5 levels per process |
| **Data Stores** | 8 |
| **DFD Processes** | 37 (1 Level 0, 6 Level 1, 30 Level 2) |

---

## External Entities (DFD Level 0)

| Entity | Role | Interactions |
|--------|------|--------------|
| **Women** | Patients/Clients | Registration, health updates, checkups, cycle tracking |
| **BHWs** | Barangay Health Workers | Data entry, referrals, task completion |
| **BHW President** | Supervisor | Approvals, assignments, analytics review |
| **Midwife** | Healthcare Professional | Checkups, assessments, learning content |

---

## Data Stores (DFD Level 1)

| Store | Name | Purpose |
|-------|------|---------|
| D1 | Users | User accounts and patient profiles |
| D2 | Health Records | Health measurements and assessments |
| D3 | Checkups | Appointment and visit records |
| D4 | Pregnancies | Pregnancy tracking data |
| D5 | Messages | Communications between users |
| D6 | Learning Materials | Educational content |
| D7 | Cycles & Menstruation | Period and fertility data |
| D8 | Analytics & Reports | Generated statistics and reports |

---

## Security & Access Control

| Feature | Implementation |
|---------|----------------|
| **Role-Based Access** | Middleware: `role:user`, `role:bhw`, `role:bhw_president`, `role:midwife` |
| **Authentication** | Login/logout with session management |
| **Prevent Back** | Prevents browser back navigation after logout |
| **Absolute Logout** | Ensures session termination |
| **Profile Isolation** | Users can only edit own profiles |
| **Workflow Gates** | Multi-level approval required for records |

---

## Comparison: Existing Manual vs. Proposed ReproCare

| Aspect | Existing Manual | Proposed ReproCare |
|--------|-----------------|-------------------|
| **Processes** | 4 main | 6 main (+2 for automation) |
| **Data Stores** | 4 | 8 (+messaging, cycles, analytics) |
| **Automation** | Manual paper forms | Digital workflows |
| **Communication** | Face-to-face only | In-app messaging + forum |
| **Tracking** | Basic health records | Full cycle + fertility tracking |
| **Reporting** | Hand-generated | Auto-generated analytics |
| **Referrals** | Paper-based | Digital with workflow |
| **Patient Records** | Physical files | Digital database |
| **BHW Supervision** | Manual coordination | Task assignment + tracking |
| **Analytics** | Manual calculation | Real-time dashboards |

---

## File References

- **DFD PlantUML Files**: `docs/dfd_level0_proposed.puml`, `docs/dfd_level1_proposed.puml`, `docs/dfd_level2_*.puml`
- **Database Schema**: `docs/DATABASE_SCHEMA.md`
- **ERD**: `docs/ERD_MERMAID_CLEAR.md`
- **Routes**: `routes/web.php`
- **Controllers**: `app/Http/Controllers/`
