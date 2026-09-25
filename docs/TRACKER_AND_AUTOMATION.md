# Menstrual tracker and automated alerts

## Prediction behavior

- Predictions use up to six recent **start-to-start intervals**, derived from recorded dates. Editing, deleting, restoring, or backdating a record recalculates the stored lengths too.
- No records means no prediction. A single record uses a **28-day fallback, low confidence**. At least three observed intervals (normally four period records) are needed to assess consistency.
- The current heuristic uses the standard deviation of interval lengths: up to 3 days = regular, up to 7 = somewhat irregular, over 7 = irregular. The confidence labels describe this heuristic, not clinically validated accuracy percentages.
- Irregular periods can be recorded normally. The next-period window uses the shortest and longest observed intervals; subsequent windows widen cumulatively. Actual dates can fall outside these historical bounds.
- The calendar and prediction API hide fertile-window and ovulation dates unless there is sufficient, regular history. Calendar estimates do not confirm ovulation or provide reliable contraception.
- Period reminders run at 09:00 using calendar dates, rather than fractional time differences. They run 3 days before, 1 day before, and at the start of the estimated window. Repeat execution does not duplicate them. Unapproved accounts and pregnancies not marked ended are excluded from period reminders.

## Risk notification and SMS lifecycle

1. A Medium/High/Critical concern creates one patient notification with an episode identifier and a fingerprint of the risk level/reason.
2. While unread and active, the same in-app alert is retained and its reminder timestamp refreshed at most once per calendar day. It does not accumulate duplicate rows. Automated SMS attempts are capped per patient and alert type per day, including failed/pending attempts to avoid repeating a request that may already have reached the gateway.
3. Opening a notification card or selecting **Mark All Read** acknowledges it. Merely loading the notification list does not silently acknowledge unseen pages. The first read timestamp is preserved when reopening a card.
4. Acknowledging the alert stops its risk SMS and daily reminders across subsequent days and repeat evaluations. A staff member reading their own copy does not acknowledge it for the patient.
5. Risk clearing to Low resolves the episode and stops reminders. A later, distinct concern can generate a new alert, still subject to the daily creation limit. A changed concern detected after today's alert is deferred until the next day's evaluation.
6. Appointment/missed-checkup messages reference the exact checkup and schedule. Completed, cancelled, rescheduled, deleted, or acknowledged source alerts cannot keep sending stale SMS. New appointments have their own acknowledgement state.

BHW and midwife patient details display **Unseen / Seen / Resolved**, the acknowledgement time, and the latest SMS outcome. Staff notification copies link to the patient's receipt. Existing legacy risk read states and soft-deleted alert history are preserved.

### What “seen” means for SMS

Ordinary SMS does not supply a phone read receipt. `sent` in SMS logs means accepted by the configured gateway, not confirmed handset delivery or reading. `MOCK_` logs explicitly represent simulation. Automated registered-patient messages include the ReproCare notification-page URL; the patient signs in and opens the relevant alert to acknowledge it. This URL must be reachable from the patient's phone (`APP_URL`), not just from the server.

No inbound SMS reply/read-receipt webhook is implemented. Walk-in patients without a portal account have no portal read receipt; completing/cancelling/rescheduling their checkup stops its automated reminders. SMS opt-out and missing phone numbers are respected.

## Schedule (Asia/Manila)

| Task | Time |
|---|---|
| Tomorrow's appointment reminders | Daily 07:00 |
| Delivery countdown reminders (14/7/3/1 days + overdue) | Daily 07:30 |
| Mark prior-day overdue checkups missed | Daily 08:00 |
| Missed-checkup risk evaluation | Daily 08:30 |
| Review elevated-risk patients and unresolved alerts | Daily 08:40 |
| Refresh unread risk reminders | Daily 08:45 |
| Estimated-period reminders | Daily 09:00 |
| Deliver due scheduled messages | Every minute |
| Scheduler heartbeat | Every minute |

Jobs use overlap locks. Scheduled messages are processed synchronously by the scheduler command, so this path does not require a separate queue worker. Message delivery and notification creation are transactional and retry-safe. Existing immediate messages default to sent and are not re-delivered.

The Windows task `ReproCareScheduler` invokes `php artisan schedule:run` every minute. It was disabled during inspection and has been enabled with an explicit project working directory. The server, database, network, and SMS gateway must remain available at the scheduled times; Laravel does not replay a missed daily time automatically. Existing Windows battery/sleep conditions still apply.

## Diagnostics and verification

Run from the `reprocare` project directory:

```powershell
php artisan automation:status
php artisan schedule:list
php artisan alerts:run --all --dry-run
php artisan test --filter='CyclePredictionTest|AlertLifecycleTest|ScheduledAutomationTest'
```

`automation:status` only reads configuration/aggregate status and does not disclose credentials or send SMS. The heartbeat should advance every minute.

`--dry-run` executes alert processing against the connected database inside a rollback transaction and fakes all HTTP gateway requests. It is intended for verification, not actual delivery. Commands without this flag perform their real actions.

Regression tests use an isolated in-memory SQLite schema plus the new migrations, with outbound HTTP faked. The historical migration chain includes MySQL-specific consolidation SQL, so these tests deliberately avoid rebuilding it on SQLite. The local MySQL migration and rollback-only command run provide a separate schema/integration check.

The existing `tests/Feature/ExampleTest.php` expects `/` to redirect (302); the current landing page returns 200. That unrelated assertion fails in the full suite. Handset delivery still requires a controlled real-device test; no real SMS was sent by the verification runs.
