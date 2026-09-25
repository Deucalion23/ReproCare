# ReproCare Newly Added Database Tables

This document lists the five (5) newly added database tables that were created during the feature expansion phase. These tables support the split portal architecture (Clinical BHW/Midwife and Admin RHU/CHO portals) and clinical audit tools.

---

## Summary of New Tables

| Table Name | Migration File | Primary Purpose |
| :--- | :--- | :--- |
| **`emergency_contacts`** | `2026_05_23_000002_create_emergency_contacts_table.php` | Tracks primary and secondary emergency contact details for patients. |
| **`supply_requests`** | `2026_05_23_000003_create_supply_requests_table.php` | Manages supply requests submitted by RHU Admins to the City Health Office (CHO). |
| **`activity_logs`** | `2026_05_23_000004_create_activity_logs_table.php` | Logs auditable events performed by staff (BHW, Midwife, RHU, CHO) for accountability. |
| **`maternal_deaths`** | `2026_05_23_000005_create_maternal_deaths_table.php` | Tracks maternal mortalities, cause of death, timing, and CHO case reviews. |
| **`maternal_morbidities`** | `2026_05_23_000006_create_maternal_morbidities_table.php` | Tracks non-fatal severe complications (near-misses) during pregnancy and delivery. |

---

## 1. `emergency_contacts`

* **Purpose**: Tracks patient emergency contact persons. A patient can register up to two contacts (one designated as primary).
* **Relationships**: 
  - `user_id` &rarr; `users(id)` (1-to-Many; cascades on delete).

### Schema Structure:
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20) unsigned` | NO (PK) | Primary Key (auto increment). |
| **`user_id`** | `bigint(20) unsigned` | NO (FK) | Reference to the patient in the `users` table. |
| **`name`** | `varchar(255)` | NO | Name of the contact person. |
| **`relationship`** | `varchar(255)` | NO | Relationship to patient (e.g. Spouse, Parent, Friend). |
| **`contact_number`** | `varchar(255)` | NO | Phone number of the contact. |
| **`address`** | `varchar(255)` | YES | Address of the contact. |
| **`is_primary`** | `tinyint(1)` | NO | True (1) if this is the primary contact; False (0) for secondary. |
| **`created_at`** | `timestamp` | YES | Creation timestamp. |
| **`updated_at`** | `timestamp` | YES | Update timestamp. |

---

## 2. `supply_requests`

* **Purpose**: Tracks requests for vitamins, vaccines, medicines, and kits submitted by RHUs to the City Health Office.
* **Relationships**:
  - `requested_by_id` &rarr; `users(id)` (RHU Admin user).
  - `approved_by_id` &rarr; `users(id)` (CHO user; nullable until approved).

### Schema Structure:
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20) unsigned` | NO (PK) | Primary Key. |
| **`requested_by_id`** | `bigint(20) unsigned` | NO (FK) | RHU admin who created the request. |
| **`approved_by_id`** | `bigint(20) unsigned` | YES (FK) | CHO admin who approved the request (null on delete). |
| **`supply_category`** | `enum` | NO | Categories: `vitamins`, `vaccines`, `birthing_kits`, `medicines`, `equipment`, `ppe`, `other`. |
| **`supply_name`** | `varchar(255)` | NO | Specific name of the item. |
| **`quantity_requested`** | `int(11)` | NO | Quantity requested. |
| **`unit`** | `varchar(255)` | YES | Unit of measurement (e.g., pieces, boxes, vials). |
| **`urgency`** | `enum('routine','urgent','emergency')` | NO | Request priority level. |
| **`reason`** | `text` | YES | Justification for the request. |
| **`status`** | `enum` | NO | Workflow: `draft`, `submitted`, `under_review`, `approved`, `declined`, `delivered`. |
| **`cho_notes`** | `text` | YES | Feedback notes from the CHO reviewer. |
| **`expected_delivery_date`** | `date` | YES | Expected delivery target. |
| **`submitted_at`** | `timestamp` | YES | Date submitted. |
| **`reviewed_at`** | `timestamp` | YES | Date reviewed. |
| **`approved_at`** | `timestamp` | YES | Date approved. |
| **`delivered_at`** | `timestamp` | YES | Date delivered. |
| **`created_at`** | `timestamp` | YES | Creation timestamp. |
| **`updated_at`** | `timestamp` | YES | Update timestamp. |

---

## 3. `activity_logs`

* **Purpose**: Provides audit trails of administrative actions. Audits BHWs, BHW Presidents, Midwives, RHUs, and CHOs (excluding patients).
* **Relationships**:
  - `user_id` &rarr; `users(id)` (nullable, set null on user delete).

### Schema Structure:
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20) unsigned` | NO (PK) | Primary Key. |
| **`user_id`** | `bigint(20) unsigned` | YES (FK) | Reference to the user who performed the action. |
| **`user_role`** | `varchar(255)` | YES | Snapshot of the user role at the time of log creation. |
| **`user_name`** | `varchar(255)` | YES | Snapshot of user name (retains history if user is deleted). |
| **`action`** | `enum` | NO | Actions: `login`, `logout`, `create`, `update`, `archive`, `restore`, `approve`, `reject`, `submit`, `print`, `export`, `view_sensitive`, `send_message`, `request_supply`, `delete`, `other`. |
| **`model_type`** | `varchar(255)` | YES | The modified Eloquent model (e.g. `HealthRecord`, `Pregnancy`). |
| **`model_id`** | `bigint(20) unsigned` | YES | The primary key ID of the modified model instance. |
| **`description`** | `varchar(255)` | NO | Human-readable explanation of the log. |
| **`ip_address`** | `varchar(45)` | YES | IP address of the device. |
| **`user_agent`** | `text` | YES | Browser user agent details. |
| **`created_at`** | `timestamp` | YES | Log timestamp (corresponds to action time). |
| **`updated_at`** | `timestamp` | YES | Update timestamp. |

---

## 4. `maternal_deaths`

* **Purpose**: Tracks maternal death surveillance events for clinical auditing.
* **Relationships**:
  - `user_id` &rarr; `users(id)` (if registered patient).
  - `walk_in_patient_id` &rarr; `walk_in_patients(id)` (if unregistered patient).
  - `pregnancy_id` &rarr; `pregnancies(id)`.
  - `recorded_by_id` &rarr; `users(id)` (staff recorder).
  - `reviewed_by_id` &rarr; `users(id)` (CHO auditor).
  - `purok_id` &rarr; `puroks(id)`.

### Schema Structure:
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20) unsigned` | NO (PK) | Primary Key. |
| **`user_id`** | `bigint(20) unsigned` | YES (FK) | Reference to registered user (if applicable). |
| **`walk_in_patient_id`** | `bigint(20) unsigned` | YES (FK) | Reference to walk-in patient (if applicable). |
| **`pregnancy_id`** | `bigint(20) unsigned` | YES (FK) | Reference to the associated pregnancy record. |
| **`recorded_by_id`** | `bigint(20) unsigned` | YES (FK) | RHU admin/staff who recorded the event. |
| **`reviewed_by_id`** | `bigint(20) unsigned` | YES (FK) | CHO admin who audited the case. |
| **`purok_id`** | `bigint(20) unsigned` | YES (FK) | Reference to geographical location of death. |
| **`barangay`** | `varchar(255)` | YES | Barangay text backup. |
| **`death_date`** | `date` | NO | Date of death. |
| **`death_time`** | `time` | YES | Time of death. |
| **`age_at_death`** | `int(11)` | YES | Age at death (years). |
| **`place_of_death`** | `enum` | NO | Places: `home`, `barangay_health_station`, `rhu`, `city_hospital`, `provincial_hospital`, `private_hospital`, `in_transit`, `other`. |
| **`cause_of_death`** | `varchar(255)` | YES | Clinical cause details (free text). |
| **`cause_category`** | `enum` | YES | Categories: `hemorrhage`, `hypertension_eclampsia`, `sepsis`, `obstructed_labor`, `unsafe_abortion`, `embolism`, `other_direct`, `indirect_cause`, `unknown`. |
| **`death_timing`** | `enum` | YES | Timing: `during_pregnancy`, `during_delivery`, `within_24_hours_postpartum`, `within_7_days_postpartum`, `within_42_days_postpartum`, `unknown`. |
| **`notes`** | `text` | YES | Case remarks. |
| **`audit_status`** | `enum` | NO | Audit state: `pending`, `under_review`, `reviewed`, `closed`. |
| **`audit_notes`** | `text` | YES | Final audit logs entered by CHO. |
| **`reviewed_at`** | `timestamp` | YES | Audit completion date. |
| **`created_at`** | `timestamp` | YES | Creation timestamp. |
| **`updated_at`** | `timestamp` | YES | Update timestamp. |

---

## 5. `maternal_morbidities`

* **Purpose**: Logs non-fatal maternal near-miss events for clinical quality control.
* **Relationships**:
  - `user_id` &rarr; `users(id)`.
  - `walk_in_patient_id` &rarr; `walk_in_patients(id)`.
  - `pregnancy_id` &rarr; `pregnancies(id)`.
  - `recorded_by_id` &rarr; `users(id)` (staff recorder).
  - `reviewed_by_id` &rarr; `users(id)` (CHO reviewer).
  - `purok_id` &rarr; `puroks(id)`.
  - `maternal_death_id` &rarr; `maternal_deaths(id)` (links morbidity to death if patient eventually died).

### Schema Structure:
| Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20) unsigned` | NO (PK) | Primary Key. |
| **`user_id`** | `bigint(20) unsigned` | YES (FK) | Reference to registered user. |
| **`walk_in_patient_id`** | `bigint(20) unsigned` | YES (FK) | Reference to walk-in patient. |
| **`pregnancy_id`** | `bigint(20) unsigned` | YES (FK) | Reference to pregnancy record. |
| **`recorded_by_id`** | `bigint(20) unsigned` | YES (FK) | Reference to staff recorder. |
| **`reviewed_by_id`** | `bigint(20) unsigned` | YES (FK) | Reference to CHO reviewer. |
| **`purok_id`** | `bigint(20) unsigned` | YES (FK) | Purok location of the event. |
| **`barangay`** | `varchar(255)` | YES | Barangay name. |
| **`event_date`** | `date` | NO | Date of complication occurrence. |
| **`event_time`** | `time` | YES | Time of occurrence. |
| **`complication_type`** | `enum` | NO | Complications: `severe_hemorrhage`, `eclampsia`, `severe_preeclampsia`, `sepsis`, `ruptured_uterus`, `severe_anemia`, `obstructed_labor`, `placenta_previa`, `placental_abruption`, `other`. |
| **`place_of_event`** | `enum` | NO | Places: `home`, `barangay_health_station`, `rhu`, `city_hospital`, `provincial_hospital`, `private_hospital`, `in_transit`, `other`. |
| **`outcome`** | `enum` | NO | Outcomes: `survived_no_intervention`, `survived_with_intervention`, `transferred_to_higher_facility`, `died`. |
| **`maternal_death_id`** | `bigint(20) unsigned` | YES (FK) | Reference link to death record if outcome is `died`. |
| **`description`** | `text` | YES | Detailed explanation of event. |
| **`interventions_done`** | `text` | YES | Clinical interventions performed. |
| **`notes`** | `text` | YES | Additional case notes. |
| **`review_status`** | `enum` | NO | Review state: `pending`, `under_review`, `reviewed`, `closed`. |
| **`review_notes`** | `text` | YES | CHO review feedback comments. |
| **`reviewed_at`** | `timestamp` | YES | Review completion date. |
| **`created_at`** | `timestamp` | YES | Creation timestamp. |
| **`updated_at`** | `timestamp` | YES | Update timestamp. |
