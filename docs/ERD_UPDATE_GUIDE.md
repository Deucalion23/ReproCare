# ReproCare ERD Draw.io Update Guide

This guide provides step-by-step instructions on how to update your **`erd123 (4).drawio`** file in Draw.io (diagrams.net) to incorporate the 5 newly added database tables and prepare for the later-to-do (deferred) functions.

---

## 🗺️ High-Level Structural Recommendations

Currently, your ERD has 4 horizontal modules. Rather than squeezing the new tables into these existing clinical spaces, we recommend **creating a 5th Module** and **adjusting one existing module**.

Here is how to structure them:

### 1. Module 1: USER + AREA + ADMIN + BHW WORKFLOW
* **Action**: Keep the name as is (or rename to **USER ACCOUNTS & WORKFLOW CONFIGURATION**).
* **Tables to Add here**: 
  - **`emergency_contacts`**: Placed directly next to the `users` table.
  - *Why*: It is a direct attribute extension of patient user profiles.

### 2. 🆕 New Module 5: ADMINISTRATIVE SURVEILLANCE & LOGISTICS
* **Name**: **ADMINISTRATIVE SURVEILLANCE & LOGISTICS MODULE**
* **Positioning**: Place it **vertically below Module 1** to save horizontal canvas space.
  - **Coordinates**: `x = -810`, `y = 1500` (since Module 1 ends at `y = 1350`).
  - **Size**: `width = 1300`, `height = 1000` (perfectly aligned with Module 1).
* **Tables to place here**:
  - **`supply_requests`** (New function - Logistics)
  - **`maternal_deaths`** (New function - Mortality Surveillance)
  - **`maternal_morbidities`** (New function - Near-Miss Surveillance)
  - **`activity_logs`** (New function - System Audit)
  - **`sms_logs`** (Deferred function - SMS Logger table to be created later)

---

## 🛠️ Step-by-Step Table Additions & Connectors

Follow these steps to add the tables and connect them with the correct Crow's Foot relationships in Draw.io:

### Step 1: Add `emergency_contacts` Table (in Module 1)
1. Draw a new table shape next to `users`.
2. Add columns:
   - `id` (**PK**, bigint)
   - `user_id` (**FK**, bigint)
   - `name` (varchar)
   - `relationship` (varchar)
   - `contact_number` (varchar)
   - `address` (varchar, Nullable)
   - `is_primary` (boolean)
3. **Relationship Line**: 
   - Draw a connector from `users` (Parent) to `emergency_contacts` (Child).
   - **Line Label**: `"registers"`
   - **Notation**: 
     - On `users` side: Double lines (`||` - Exactly One / Required).
     - On `emergency_contacts` side: Zero-or-Many Crow's foot (`o{` - Optional).

---

### Step 2: Draw the 5th Module Container (below Module 1)
1. Copy the background rectangle from Module 1.
2. Paste it below Module 1 (`x = -810`, `y = 1500`, `width = 1300`, `height = 1000`).
3. Add a header text label: **"MODULE 5: ADMINISTRATIVE SURVEILLANCE & LOGISTICS"**.

---

### Step 3: Add `supply_requests` Table (in Module 5)
1. Draw the table structure.
2. Add columns:
   - `id` (**PK**, bigint)
   - `requested_by_id` (**FK**, bigint, referencing `users.id`)
   - `approved_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `supply_category` (enum)
   - `supply_name` (varchar)
   - `quantity_requested` (int)
   - `unit` (varchar, Nullable)
   - `urgency` (enum)
   - `status` (enum)
   - `cho_notes` (text, Nullable)
   - `expected_delivery_date` (date, Nullable)
3. **Relationship Lines**:
   - Line 1: `users` (Module 1) to `supply_requests.requested_by_id`.
     - **Label**: `"requests"`
     - **Notation**: `users` side is `||` (Required), `supply_requests` side is `o{` (Optional).
   - Line 2: `users` (Module 1) to `supply_requests.approved_by_id`.
     - **Label**: `"approves"`
     - **Notation**: `users` side is `o|` (Optional), `supply_requests` side is `o{` (Optional).

---

### Step 4: Add `maternal_deaths` Table (in Module 5)
1. Draw the table structure.
2. Add columns:
   - `id` (**PK**, bigint)
   - `user_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `walk_in_patient_id` (**FK**, bigint, Nullable, referencing `walk_in_patients.id`)
   - `pregnancy_id` (**FK**, bigint, Nullable, referencing `pregnancies.id`)
   - `recorded_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `reviewed_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `purok_id` (**FK**, bigint, Nullable, referencing `puroks.id`)
   - `death_date` (date)
   - `place_of_death` (enum)
   - `cause_category` (enum)
   - `death_timing` (enum)
   - `audit_status` (enum)
   - `audit_notes` (text, Nullable)
3. **Relationship Lines**:
   - Line 1: `users` to `maternal_deaths.user_id`.
     - **Label**: `"death_of_registered"`
     - **Notation**: `users` side is `o|` (Optional), `maternal_deaths` side is `o|` (Optional).
   - Line 2: `pregnancies` (Module 2) to `maternal_deaths.pregnancy_id`.
     - **Label**: `"associated_pregnancy"`
     - **Notation**: `pregnancies` side is `o|` (Optional), `maternal_deaths` side is `o|` (Optional).
   - Line 3: `walk_in_patients` to `maternal_deaths.walk_in_patient_id`.
     - **Label**: `"death_of_walk_in"`
     - **Notation**: `walk_in_patients` side is `o|` (Optional), `maternal_deaths` side is `o|` (Optional).

---

### Step 5: Add `maternal_morbidities` Table (in Module 5)
1. Draw the table structure.
2. Add columns:
   - `id` (**PK**, bigint)
   - `user_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `walk_in_patient_id` (**FK**, bigint, Nullable, referencing `walk_in_patients.id`)
   - `pregnancy_id` (**FK**, bigint, Nullable, referencing `pregnancies.id`)
   - `recorded_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `reviewed_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `purok_id` (**FK**, bigint, Nullable, referencing `puroks.id`)
   - `complication_type` (enum)
   - `place_of_event` (enum)
   - `outcome` (enum)
   - `maternal_death_id` (**FK**, bigint, Nullable, referencing `maternal_deaths.id`)
3. **Relationship Lines**:
   - Line 1: `maternal_deaths.id` to `maternal_morbidities.maternal_death_id`.
     - **Label**: `"linked_death"`
     - **Notation**: `maternal_deaths` side is `o|` (Optional), `maternal_morbidities` side is `o|` (Optional).
   - Connect others (e.g. `users`, `walk_in_patients`, `pregnancies`) using standard 1-to-many optional lines.

---

### Step 6: Add `activity_logs` Table (in Module 5)
1. Draw the table structure.
2. Add columns:
   - `id` (**PK**, bigint)
   - `user_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `user_role` (varchar)
   - `user_name` (varchar)
   - `action` (enum)
   - `model_type` (varchar, Nullable)
   - `model_id` (bigint, Nullable)
   - `description` (varchar)
   - `ip_address` (varchar)
3. **Relationship Line**:
   - Connect `users` (Module 1) to `activity_logs.user_id`.
   - **Label**: `"performed_by"`
   - **Notation**: `users` side is `o|` (Optional - in case user is soft deleted/removed), `activity_logs` side is `o{` (Optional).

---

### Step 7: Add `sms_logs` (Deferred SMS Feature Table - Prepare for Later)
1. Place a table shape labeled **`sms_logs`** in Module 5 (shaded in a lighter color or dashed border to denote "Deferred / Future Phase").
2. Add columns:
   - `id` (**PK**, bigint)
   - `sent_by_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `recipient_user_id` (**FK**, bigint, Nullable, referencing `users.id`)
   - `phone_number` (varchar)
   - `message` (text)
   - `status` (enum: `sent`, `failed`, `pending`)
   - `sent_at` (timestamp, Nullable)
3. **Relationship Lines**:
   - Connect `users` (sender) to `sms_logs.sent_by_id`.
   - Connect `users` (recipient) to `sms_logs.recipient_user_id`.
   - Draw both lines as **dashed connector lines** to represent future implementations.

---

## 🎨 Styling & Color Palette Match

To ensure your ERD matches your current capstone design aesthetics, style the new Module 5 components in Draw.io as follows:
- **Module Background**: Use a light gray/blue container border (e.g., `#ECEFF1` or HSL H200 S10 L95) with rounded corners.
- **Table Headers**: Use a dark slate or navy blue fill (e.g., `#263238` or `#1A237E`) with white text for headers to match corporate database themes.
- **Dashed Borders**: For the `sms_logs` table (deferred feature), use a dashed border structure with a light orange or yellow background highlight (e.g., `#FFF3E0`) to indicate that it is a planned phase-2 implementation.

---

## 📝 Changes to EXISTING Tables (Column Additions)

Beyond adding new tables, you also need to **update columns in tables that already exist** in your current modules.

### Module 1: `users` Table — Add New Columns

The `users` table in Module 1 needs these new columns added at the bottom of its column list:

| New Column | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `rhu_assignment` | varchar(255) | YES | Which RHU this staff member belongs to (for midwife, bhw, bhw_president). |
| `cho_office` | varchar(255) | YES | CHO office name/location (for CHO role users). |
| `registered_by_rhu_id` | bigint (FK) | YES | Which RHU admin registered this user. References `users(id)`. |
| `registered_by_cho_id` | bigint (FK) | YES | Which CHO registered this RHU admin. References `users(id)`. |

Also update the **role enum** row from:
```
enum('midwife','bhw','bhw_president','user')
```
to:
```
enum('cho','rhu','midwife','bhw','bhw_president','user')
```

> **Draw.io Action**: Find the `users` table shape in Module 1, double-click the table body, and add the 4 new rows at the bottom. Update the `role` column to show the expanded enum.

---

### Module 2: `pregnancies` Table — Add Facility Delivery Fields

The `pregnancies` table in Module 2 needs these new columns added:

| New Column | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `facility_delivery_place` | enum | YES | Where the delivery happened: `home`, `barangay_health_station`, `rhu_birth_center`, `city_hospital`, `provincial_hospital`, `private_hospital`, `other`. |
| `delivery_date` | date | YES | Actual delivery date. |
| `delivery_time` | time | YES | Actual delivery time. |
| `delivery_attendant` | varchar(255) | YES | Who attended: midwife, doctor, nurse, hilot, self, other. |
| `delivery_notes` | text | YES | Additional delivery notes. |

> **Draw.io Action**: Find the `pregnancies` table in Module 2, double-click, and add these 5 rows after the `ended_at` column.

---

## 🔗 Cross-Module Connector Lines

Several new relationships **cross between modules**. In Draw.io, these are long connector lines that span from one module rectangle to another. Here is a complete list:

### From Module 5 → Module 1 (USER + AREA + ADMIN)
| Child Table (Module 5) | FK Column | Parent Table (Module 1) | Label |
| :--- | :--- | :--- | :--- |
| `supply_requests` | `requested_by_id` | `users` | "requests" |
| `supply_requests` | `approved_by_id` | `users` | "approves" |
| `maternal_deaths` | `user_id` | `users` | "death_of_registered" |
| `maternal_deaths` | `recorded_by_id` | `users` | "recorded_by" |
| `maternal_deaths` | `reviewed_by_id` | `users` | "reviewed_by" |
| `maternal_deaths` | `purok_id` | `puroks` | "occurred_in" |
| `maternal_morbidities` | `user_id` | `users` | "patient" |
| `maternal_morbidities` | `recorded_by_id` | `users` | "recorded_by" |
| `maternal_morbidities` | `reviewed_by_id` | `users` | "reviewed_by" |
| `maternal_morbidities` | `purok_id` | `puroks` | "occurred_in" |
| `activity_logs` | `user_id` | `users` | "performed_by" |

### From Module 5 → Module 2 (MATERNAL & REPRODUCTIVE HEALTH CORE)
| Child Table (Module 5) | FK Column | Parent Table (Module 2) | Label |
| :--- | :--- | :--- | :--- |
| `maternal_deaths` | `pregnancy_id` | `pregnancies` | "associated_pregnancy" |
| `maternal_deaths` | `walk_in_patient_id` | `walk_in_patients` | "death_of_walk_in" |
| `maternal_morbidities` | `pregnancy_id` | `pregnancies` | "associated_pregnancy" |
| `maternal_morbidities` | `walk_in_patient_id` | `walk_in_patients` | "walk_in_complication" |

### Within Module 5 (Internal)
| Child Table | FK Column | Parent Table | Label |
| :--- | :--- | :--- | :--- |
| `maternal_morbidities` | `maternal_death_id` | `maternal_deaths` | "linked_death" |

> **Draw.io Tip**: For cross-module lines, use **curved connectors** (`Style > Connection > Curved`) and route them around the module edges to keep the diagram clean. You can also right-click a line and choose **"Edit Style"** to set `exitX`, `exitY`, `entryX`, `entryY` for precise anchor points.

---

## 🏗️ Module Renaming Summary

| Current Module Name | Recommended New Name | Reason |
| :--- | :--- | :--- |
| **USER + AREA + ADMIN + BHW WORKFLOW** | **USER ACCOUNTS, AREA & WORKFLOW CONFIGURATION** | Cleaner; "ADMIN" is now ambiguous with CHO/RHU roles. |
| **MATERNAL & REPRODUCTIVE HEALTH CORE** | *(Keep as is)* | Still accurate — pregnancies, health records, checkups, referrals, walk-ins. |
| **MATERNAL CARE TARGET MODULE** | *(Keep as is)* | Still accurate — maternal target clients and their sub-tables. |
| **CHILD HEALTH + COMMUNITY SYSTEM** | *(Keep as is)* | Still accurate — child records, child target clients, forum, learning, messages. |
| *(New)* | **ADMINISTRATIVE SURVEILLANCE & LOGISTICS** | Houses the new admin-facing tables. |

---

## 📐 Final Canvas Layout Map

Here is the recommended spatial arrangement of all 5 modules on the Draw.io canvas:

```
┌─────────────────────────────────┐  ┌──────────────────────────────────────┐  ┌─────────────────────────────────────┐
│                                 │  │                                      │  │                                     │
│  MODULE 1                       │  │  MODULE 2                            │  │  MODULE 4                           │
│  USER ACCOUNTS, AREA &          │  │  MATERNAL & REPRODUCTIVE             │  │  CHILD HEALTH +                     │
│  WORKFLOW CONFIGURATION         │  │  HEALTH CORE                         │  │  COMMUNITY SYSTEM                   │
│                                 │  │                                      │  │                                     │
│  ┌──────────┐ ┌───────────────┐ │  │  ┌────────────┐ ┌───────────────┐   │  │  ┌─────────────┐ ┌────────────────┐ │
│  │ users    │ │ emergency_    │ │  │  │ pregnancies│ │ health_records│   │  │  │ child_      │ │ forum_posts   │ │
│  │ (updated)│ │ contacts (NEW)│ │  │  │ (updated) │ │               │   │  │  │ records     │ │               │ │
│  └──────────┘ └───────────────┘ │  │  └────────────┘ └───────────────┘   │  │  └─────────────┘ └────────────────┘ │
│  ┌──────────┐ ┌───────────────┐ │  │  ┌────────────┐ ┌───────────────┐   │  │  ┌─────────────┐ ┌────────────────┐ │
│  │ puroks   │ │ bhw_          │ │  │  │ checkups  │ │ walk_in_      │   │  │  │ learning_   │ │ messages      │ │
│  │          │ │ assignments   │ │  │  │           │ │ patients      │   │  │  │ materials   │ │               │ │
│  └──────────┘ └───────────────┘ │  │  └────────────┘ └───────────────┘   │  │  └─────────────┘ └────────────────┘ │
│  ...                            │  │  ...                                │  │  ...                                │
└─────────────────────────────────┘  └──────────────────────────────────────┘  └─────────────────────────────────────┘
                                     ┌──────────────────────────────────────┐
                                     │                                      │
                                     │  MODULE 3                            │
                                     │  MATERNAL CARE TARGET MODULE         │
                                     │                                      │
                                     │  ┌───────────────────────────────┐   │
                                     │  │ maternal_care_target_clients │   │
                                     │  └───────────────────────────────┘   │
                                     │  ...                                │
                                     └──────────────────────────────────────┘

┌─────────────────────────────────┐
│                                 │
│  MODULE 5 (NEW)                 │
│  ADMINISTRATIVE SURVEILLANCE    │
│  & LOGISTICS                    │
│                                 │
│  ┌──────────────┐ ┌───────────┐ │
│  │ supply_      │ │ activity_ │ │
│  │ requests     │ │ logs      │ │
│  │ (NEW)        │ │ (NEW)     │ │
│  └──────────────┘ └───────────┘ │
│  ┌──────────────┐ ┌───────────┐ │
│  │ maternal_    │ │ maternal_ │ │
│  │ deaths       │ │ morbid-   │ │
│  │ (NEW)        │ │ ities(NEW)│ │
│  └──────────────┘ └───────────┘ │
│  ┌──────────────┐               │
│  │ sms_logs     │               │
│  │ (DEFERRED)   │               │
│  └──────────────┘               │
└─────────────────────────────────┘
```

---

## ✅ Final Checklist

Use this checklist to verify you have completed all changes:

### New Tables Added
- [ ] `emergency_contacts` — placed in Module 1 next to `users`
- [ ] `supply_requests` — placed in Module 5
- [ ] `activity_logs` — placed in Module 5
- [ ] `maternal_deaths` — placed in Module 5
- [ ] `maternal_morbidities` — placed in Module 5
- [ ] `sms_logs` (dashed/deferred) — placed in Module 5

### Existing Tables Updated
- [ ] `users` — added `rhu_assignment`, `cho_office`, `registered_by_rhu_id`, `registered_by_cho_id` columns; updated `role` enum to include `cho` and `rhu`
- [ ] `pregnancies` — added `facility_delivery_place`, `delivery_date`, `delivery_time`, `delivery_attendant`, `delivery_notes` columns

### Module Changes
- [ ] Module 1 renamed to **USER ACCOUNTS, AREA & WORKFLOW CONFIGURATION**
- [ ] Module 5 container created with label **ADMINISTRATIVE SURVEILLANCE & LOGISTICS**

### Relationship Connectors
- [ ] `users` → `emergency_contacts` (1-to-Many, Optional)
- [ ] `users` → `supply_requests.requested_by_id` (1-to-Many, Required)
- [ ] `users` → `supply_requests.approved_by_id` (1-to-Many, Optional)
- [ ] `users` → `maternal_deaths.user_id` (1-to-Many, Optional)
- [ ] `users` → `maternal_deaths.recorded_by_id` (1-to-Many, Optional)
- [ ] `users` → `maternal_deaths.reviewed_by_id` (1-to-Many, Optional)
- [ ] `pregnancies` → `maternal_deaths.pregnancy_id` (1-to-1, Optional)
- [ ] `walk_in_patients` → `maternal_deaths.walk_in_patient_id` (1-to-Many, Optional)
- [ ] `puroks` → `maternal_deaths.purok_id` (1-to-Many, Optional)
- [ ] `users` → `maternal_morbidities.user_id` (1-to-Many, Optional)
- [ ] `pregnancies` → `maternal_morbidities.pregnancy_id` (1-to-Many, Optional)
- [ ] `walk_in_patients` → `maternal_morbidities.walk_in_patient_id` (1-to-Many, Optional)
- [ ] `maternal_deaths` → `maternal_morbidities.maternal_death_id` (1-to-1, Optional)
- [ ] `users` → `activity_logs.user_id` (1-to-Many, Optional)

### Labels & Notation
- [ ] All connector lines have relationship labels (e.g., "registers", "requests", "linked_death")
- [ ] All connectors use correct Crow's Foot notation with optionality markers
- [ ] Deferred table (`sms_logs`) uses dashed borders
