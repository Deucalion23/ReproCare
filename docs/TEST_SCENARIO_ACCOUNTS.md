# Fictional test accounts

Run on a development/test database after migrations:

```bash
php artisan db:seed --class=TestScenarioAccountsSeeder
```

This is an **opt-in** seeder, separate from `DatabaseSeeder`. It creates exactly 100 accounts on a clean run. Every email is `reprocare-test-NNN@example.test` (001–100); the shared password is `TestAccount123!`. Names are distinct pseudo-random first/last-name pairs, stable across runs. No actual mailboxes or real patient information are used. Running it again does not reset existing account edits or duplicate scenario records.

| Account numbers | Role or patient situation |
| --- | --- |
| 001 | CHO |
| 002–003 | RHU staff |
| 004–006 | Midwives |
| 007–009 | BHW presidents |
| 010–012 | BHWs |
| 013–020 | Registration / account states: pending, rejected, suspended, unverified email, incomplete profile, approved |
| 021–028 | Regular menstrual cycles |
| 029–036 | Irregular menstrual cycles |
| 037–044 | Early pregnancy |
| 045–052 | Second-trimester pregnancy |
| 053–060 | High-risk third-trimester pregnancy and urgent/emergency referrals |
| 061–068 | Pregnancy awaiting BHW-president review |
| 069–076 | Rejected pregnancy awaiting correction |
| 077–084 | Postpartum, newborn and immunizations (including a recorded dose) |
| 085–092 | Postpartum and newborn danger signs requiring follow-up |
| 093–100 | Routine referrals in pending/reviewed/scheduled/declined states |

The approved patient groups also include varied vitals/workflow records, appointment states (scheduled, completed, missed, rescheduled, cancelled), emergency contacts, and read/unread notifications. Postpartum groups include child records and growth checkups. Staff accounts have tasks, supply requests, and monthly reports at different workflow stages. A forum post with a comment and like, and a two-message conversation, are included for interaction testing.

For example, sign in as `reprocare-test-053@example.test` to inspect high-risk care, or `reprocare-test-021@example.test` to inspect a regular menstrual history. The registration-state accounts intentionally cannot all reach the patient dashboard until their status/verification/profile is completed.
