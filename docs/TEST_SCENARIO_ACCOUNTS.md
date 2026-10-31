# Fictional test accounts

Run on a development/test database after migrations:

```bash
php artisan db:seed --class=TestScenarioAccountsSeeder
```

This is separate from `DatabaseSeeder`. On the deployed Render service, `docker/entrypoint.sh` runs it at startup when `SEED_TEST_SCENARIOS=true` and the test-account count is not 62. Set `SEED_TEST_SCENARIOS=false` to disable this deployment behavior. It creates exactly 62 accounts on a clean run: 50 women and 12 supporting staff accounts. Every email is `reprocare-test-NNN@example.test` (001–062); the shared password is `TestAccount123!`. Names are distinct pseudo-random first/last-name pairs, stable across runs, and each first name starts with `Test`. Test profiles have no patient phone number, are opted out of SMS, and use non-deliverable `.test` email addresses. Emergency contacts use a non-routable placeholder number. Running it again does not reset existing account edits or duplicate scenario records.

| Account numbers | Role or patient situation |
| --- | --- |
| 001 | CHO |
| 002–003 | RHU staff |
| 004–006 | Midwives |
| 007–009 | BHW presidents |
| 010–012 | BHWs |
| 013–017 | Registration / account states: pending, rejected, suspended, unverified email, incomplete profile |
| 018–022 | Regular menstrual cycles |
| 023–027 | Irregular menstrual cycles |
| 028–032 | Early pregnancy |
| 033–037 | Second-trimester pregnancy |
| 038–042 | High-risk third-trimester pregnancy and urgent/emergency referrals |
| 043–046 | Pregnancy awaiting BHW-president review |
| 047–050 | Rejected pregnancy awaiting correction |
| 051–054 | Postpartum, newborn and immunizations (including a recorded dose) |
| 055–058 | Postpartum and newborn danger signs requiring follow-up |
| 059–062 | Routine referrals in pending/reviewed/scheduled/declined states |

The approved patient groups also include varied vitals/workflow records, appointment states (scheduled, completed, missed, rescheduled, cancelled), emergency contacts, and read/unread notifications. Postpartum groups include child records and growth checkups. Staff accounts have tasks, supply requests, and monthly reports at different workflow stages. A forum post with a comment and like, and a two-message conversation, are included for interaction testing.

For example, sign in as `reprocare-test-038@example.test` to inspect high-risk care, or `reprocare-test-018@example.test` to inspect a regular menstrual history. The registration-state accounts intentionally cannot all reach the patient dashboard until their status/verification/profile is completed.
