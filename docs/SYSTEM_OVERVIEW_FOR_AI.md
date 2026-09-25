# ReproCare — Full System Overview (for AI context)

> **Last reviewed: 2026-09-19.** Current-workspace overview for developers and AI context.
> Checked against routes, controllers, models, services, migrations, shared views, browser scripts,
> and console schedules. `php artisan route:list --except-vendor --json` loaded successfully.
> This describes the source implementation; database migration state, external providers, browser
> behavior, and the running Windows scheduler were not verified in this documentation review.
> Section 15 summarizes changes since the previous overview. Paths are relative to `reprocare/`.

Quick navigation: [change summary](#15-changes-since-the-previous-overview),
[workflow details](#17-cross-role-workflows-and-lifecycle-details),
[automation](#19-menstrual-tracking-and-scheduled-automation),
[developer file map](#16-key-files-map-for-developersai).

## 1. What this system is

**ReproCare** = Maternal & Reproductive Health Management System for **San Carlos City, Philippines**.
It digitizes maternal/reproductive health workflows: patient registration, field assessments,
prenatal appointments, referrals, pregnancy outcomes, postpartum/newborn monitoring, menstrual
tracking, and FHSIS/MNCHN reporting through six user roles. The current registration catchment is
**RHU 1's 16 barangays in San Carlos City, Pangasinan**, supplied by `Barangay::catchmentNames('RHU 1')`.
CHO screens provide cross-record administrative oversight; their city-wide labels should not be
read as confirmation that all city RHUs are deployed. Registered patients and unlinked walk-in
field profiles are both supported.

## 2. Tech stack

- **Backend:** Laravel 12, PHP 8.2+, Blade templates, Eloquent ORM. Session authentication through
  `AuthController`, the `web` guard, and the `User` provider (`config/auth.php`).
- **Frontend:** Bootstrap 5.3 + Bootstrap Icons, custom ReproCare design system (Plus Jakarta Sans + Inter
  fonts), Chart.js, Leaflet GIS, Vite 7, and Tailwind CSS 4 tooling. Shared CSS includes
  `resources/css/{app,design-tokens,theme,app-components}.css`; entry points are configured in
  `vite.config.js`. Appearance combines CHO-managed brand settings and local light/dark preferences.
- **PWA:** `manifest.webmanifest`, service worker (`/sw.js`), IndexedDB outbox (`js/pwa-outbox.js`)
  for offline-first field use by BHWs.
- **Database:** MySQL (see `reprocare.sql`, `backups/`, Database Backup UI per CHO/RHU).
- **Integration services:** Movider/TextBee SMS; rule-based analytics with optional local Ollama or
  online Groq summaries. `twilio/sdk` is installed, but `SmsService` dispatches through Movider/TextBee.
- **Automation:** Laravel console scheduler (`routes/console.php`) for alerts, due messages,
  heartbeat, and activity-log retention. See section 19.
- **Guards/middleware on every role group:** `web`, `absolute.logout`, `auth`, `role:<role>`, `prevent-back`.
  `/dashboard` redirects by role. Legacy `/login` → `/auth/login`, `/register` → `/auth/register` (301).

## 3. The 6 roles (hierarchy top → bottom)

| # | Role value | Label | Prefix | What they are |
|---|-----------|-------|--------|---------------|
| 1 | `cho` | CHO Admin | `/cho` | City Health Office — city-wide super admin |
| 2 | `rhu` | RHU (Rural Health Unit 1) | `/rhu` | Facility admin — verifies accounts, manages midwives/BHW presidents/BHWs |
| 3 | `midwife` | Midwife | `/midwife` | Clinical reviewer — patients, checkups, referrals, risk alerts, reports |
| 4 | `bhw_president` | BHW President | `/bhw-president` | Barangay coordinator — manages BHWs, reviews submissions, tasks, analytics |
| 5 | `bhw` | BHW | `/bhw` | Barangay Health Worker — field data entry (women, checkups, pregnancies, reports) |
| 6 | `user` | Patient/Client (woman) | `/user` | Pregnant woman / patient self-service portal (no admin sidebar) |

**All six roles authenticate from `users`.** `Midwife`, `Bhw`, `BhwPresident`, `Woman`, and `Patient`
are role-scoped model wrappers over that same table, not separate login stores. Some comments and
older schema documents still describe separate tables. `role`, `status`, creator/assignment fields,
and relationships drive access and ownership. New self-registrations are `pending`; normal active
accounts are `approved`. Login blocks pending, suspended, inactive, archived, and other non-approved
states. Route groups enforce role access; individual actions also contain their own checks.

## 4. Entry flow (landing → inside the system)

1. **Landing page** `/` and `/reprocare` → `resources/views/landing.blade.php` (HTTP 200).
2. **Register** `/auth/register` → `auth/register.blade.php` → creates a `user`-role account with
   `status = pending`. Requires front/back ID image data and a primary emergency contact; supports
   structured address, purok resolution, and optional latitude/longitude/address label.
3. **Login** `/auth/login` → `auth/login.blade.php` → `AuthController@login`. Pending accounts are blocked.
4. **Verification queue (RHU):** `/rhu/pending-patients` (`RhuController@pendingPatients`,
   `rhu/pending-patients/index.blade.php`) lists pending women with search + pagination;
   **Approve** (`rhu.approve-patient`) activates the account, **Reject** (`rhu.reject-patient`, with reason)
   saves the reason, attempts notification/SMS, and soft-deletes the account. The queue now suggests
   possible duplicate accounts/field profiles; RHU can link a walk-in profile or dismiss a match.
   Linking preserves identities/history and does not itself approve the pending account. Direct
   RHU registration creates an already-approved account. Sidebar shows a pending-count badge.
5. **Post-login landing:** `/dashboard` → role dashboard (`cho/rhu/midwife/bhw/bhw-president/user.dashboard`).
6. **Logout:** POST `/logout` → dedicated logout confirmation modal (all roles, `.js-logout-form`).
7. **Password recovery:** `/auth/forgot-password` and `/auth/reset-password/{token}` use Laravel's
   password broker. Login, registration, and password recovery POST routes are throttled.

## 5. Global shell (all staff roles)

- `layouts/app.blade.php` (shared styles and global JS) → per-role layout
  (`cho/layout`, `rhu/layout`, `midwife/layout`, `bhw/layout`, `bhw-president/layout`) →
  shared `includes/sidebar.blade.php` (role-aware menu) + `includes/navigation.blade.php` (top navbar:
  brand, mobile hamburger `sidebarToggleBtn`, dark-mode toggle, notification bell, avatar dropdown with
  **My Profile → role Settings page**, System Settings shortcut for CHO, Log Out).
- **Sidebar:** shared role-aware navigation with a floating collapse control, icon-only collapsed
  state, and mobile toggle. Layout/style details live in the shared partial and CSS.
  Patient (`user`) portal has NO sidebar: full-width `women-shell` + mobile bottom dock
  (Home/Pregnancy/Cycle/Checkups/Care Chat).
- **Profile consolidation:** own-profile `profile.show` redirects to role Settings; staff
  `profile.edit` does likewise. Shared update/emergency-contact/image handlers remain active.
  Patient profile editing still renders `user/profile/edit`, and `profile.view` remains a separate
  route for viewing another profile.
- **Appearance:** CHO Settings saves primary/secondary/accent colors and the default mode in
  `settings` under `appearance.theme`. `AppearanceTheme`, `config/appearance.php`,
  `includes/appearance-head.blade.php`, and `public/js/appearance.js` apply validated palettes and
  contrast-aware tokens. A saved revision applies on next page load; local mode uses `rc_theme`
  and `rc_appearance_revision`, with `data-theme`, `data-bs-theme`, and the `dark` class synchronized.

## 6. CHO Admin pages (`/cho`, `ChoController`) — city-wide super admin

- `/cho/dashboard` (`cho.dashboard`, `cho/dashboard.blade.php`) — city-wide stats summary.
- `/cho/analytics` (`cho.analytics`, `cho/analytics.blade.php` + POST `analytics.chat`) — analytics &
  interventions incl. AI chat.
- `/cho/gis` + `/cho/gis/data` (`cho.gis.*`, `GisController`) — maternal Risk Heat Map.
- `/cho/users`, `/create`, `POST /`, `/{id}`, `/{id}/approve|reject|deactivate|activate`
  (`cho.users.*`, `cho/users/`) — User Management (approve/reject/deactivate/activate accounts).
- `/cho/supply-requests`, `/{id}`, `/{id}/approve|decline` (`cho.supply-requests.*`) — supply approvals.
- `/cho/maternal-deaths`, `/{id}`, `/{id}/audit` (`cho.maternal-deaths.*`) — maternal death audit.
- `/cho/pregnancies`, `/{id}` (`cho.pregnancies.*`) — city-wide pregnancy monitoring.
- `/cho/immunization` (`cho.immunization.index`) — immunization records.
- `/cho/reports` + `/export/csv|pdf` (`cho.reports.*`, `cho/reports/pdf.blade.php`) — city reports.
- `/cho/staff`, `/cho/staff/midwives`, `/cho/staff/bhws` (`cho.staff.*`) — staff directory (read-only).
- `/cho/handover`, `POST /recovery-key|execute` (`cho.handover.*`, `ChoHandoverController`) — super-admin
  role handover & succession with recovery key.
- `/cho/database` + `/export|import|download|delete` (`cho.database.*`, `DatabaseBackupController`) —
  database backup/restore.
- `/cho/sms` (`cho.sms.index`, read-only) — SMS log view.
- `/cho/archived` + `POST /{type}/{id}/restore` (`cho.archived.*`, `ArchivedRecordController`) — archives hub.
- `/cho/logs` (`cho.logs.index`, `cho/logs/index.blade.php`) — activity logs.
- `/cho/settings` + `PUT` (`cho.settings.*`, `cho/settings.blade.php`) — city-wide settings incl.
  **My Profile**, city-wide appearance, clinical thresholds, office profile, maintenance, SMS gateway.

## 7. RHU Admin pages (`/rhu`, `RhuController`) — facility admin

- `/rhu/dashboard` (`rhu.dashboard`, `rhu/dashboard.blade.php`).
- `/rhu/patients/create` + `POST /rhu/patients` (`rhu.patients.create/store`) — Register Woman.
- `/rhu/pending-patients` + `/{id}/approve|reject` (`rhu.pending-patients`, `rhu.approve/reject-patient`,
  `rhu/pending-patients/index.blade.php`) — **Account Verification queue** (search, approve, reject w/ reason).
- `POST /rhu/pending-patients/{id}/link|dismiss` (`rhu.link-duplicate`, `rhu.dismiss-duplicate`) —
  link a field profile or record a dismissed duplicate suggestion.
- `/rhu/midwives` CRUD (`rhu.midwives.*`, `rhu/midwives/`) — Midwife Management.
- `/rhu/bhw-presidents` CRUD + `POST /promote` (`rhu.bhw-presidents.*`) — BHW President management.
- `/rhu/bhws` CRUD + `/{id}/archive|activate` (`rhu.bhws.*`) — BHW account management.
- `/rhu/staff-transitions` + `POST /execute` (`rhu.staff-transitions.*`, `StaffTransitionController`) —
  replace/transfer staff with audit + reassignment.
- `/rhu/supply-requests` (`rhu.supply-requests.*`) — list/create/view/delete facility supply requests;
  approval is handled by CHO.
- `/rhu/maternal-deaths` CRUD (`rhu.maternal-deaths.*`) — maternal death records.
- `/rhu/morbidities` CRUD (`rhu.morbidities.*`) — maternal morbidity/complication records,
  presented in the near-miss module.
- `/rhu/bhw-reports`, `/{id}`, `/{id}/print|approve|reject`, `DELETE` (`rhu.bhw-reports.*`,
  `rhu/bhw-reports/`) — BHW Monthly Reports approval incl. pregnancies sub-view.
- `/rhu/reports`, `/details/{id}`, `/{id}`, `/export/csv|pdf` (`rhu.reports.*`, `rhu/reports/`) —
  FHSIS/MNCHN reports.
- `/rhu/gis` + `/data` (`rhu.gis.*`) — facility risk heat map.
- `/rhu/database` + export/import/download/delete (`rhu.database.*`, `rhu/database/index.blade.php`).
- `/rhu/logs` (`rhu.logs.index`) — activity logs.
- `/rhu/settings` + `PUT` (`rhu.settings.*`, `rhu/settings.blade.php`) — facility settings incl.
  **My Profile** tab + Facility Profile, staff security, president lock, escalation alerts, report templates.

## 8. Midwife pages (`/midwife`, `MidwifeController` + clinical controllers) — clinical reviewer

- `/midwife/dashboard` (`midwife.dashboard`, `midwife/dashboard.blade.php`).
- `/midwife/patients`, `/patients/{id}` (`midwife.patients`, `patient-details` — **view only**).
- `/midwife/pregnancies`, `/active`, `/create`, `POST`, `/{id}`, `/{id}/edit`, `PUT`, `DELETE`,
  `/{id}/submit`, `/{id}/reopen` (`midwife.pregnancies.*`, `PregnancyController`) — clinical management,
  submission, and reason-required reopening of locked delivery history.
- `/midwife/pregnant-patients`, `/{id}/history` — pregnant patient list + history.
- `/midwife/risk-alerts` (`midwife.risk-alerts`, `midwife/risk-alerts/index.blade.php`) — risk inbox.
- `/midwife/checkups` full CRUD + `/{id}/complete|miss|schedule|cancel` (`midwife.checkups.*`,
  `midwife/checkups/`, `CheckupController`).
- `/midwife/health-records` full CRUD + archived/accept/archive/patient/complete/incomplete/download
  (`midwife.health-records.*`, `HealthRecordController`).
- `/midwife/menstruation` CRUD + patient (`midwife.menstruation.*`, `midwife/menstruation/`).
- `/midwife/postpartum`, `/mother/{id}`, `POST /immunization/{id}` (`midwife.postpartum.*`,
  `PostpartumController`) — postpartum & newborn review.
- `/midwife/child-checkups/{childId}...` (`midwife.child-checkups.*`, `ChildCheckupController`).
- `/midwife/maternal-care-target-clients` list/create/edit/update + print (`midwife.maternal-care-target-clients.*`,
  `MaternalCareTargetClientController`) — **Maternal Client List (TCL)**.
- `/midwife/child-care-target-clients` list/create/edit/update + print (`midwife.child-care-target-clients.*`,
  `ChildCareTargetClientController`) — **Childcare Client List**.
- `/midwife/referrals`, `/{id}`, `/{id}/review|convert|decline` (`midwife.referrals.*`) — checkup referrals.
- `/midwife/walk-in-patients`, `/{id}`, `/{id}/activate` (GET+POST) (`midwife.walk-in-patients.*`,
  `midwife/walk-in-patients/`) — unlinked-profile review + portal account activation.
- `/midwife/reports`, `/export/csv|pdf`, `/{id}/details`, `/{id}` (`midwife.reports.*`, `midwife/reports/`).
- `/midwife/learning` CRUD (`midwife.learning.*`, `LearningController@adminIndex...`).
- `/midwife/forum-admin` full CRUD + bulk-delete + restore (`midwife.forum.admin.*`, `ForumController`).
- `/midwife/messages` inbox/compose/thread (`midwife.messages.*`, `MessageController`, `messages/` views).
- `/midwife/sms` + send/broadcast (`midwife.sms.*`, `SmsController`, `midwife/sms/index.blade.php`).
- `/midwife/notifications` list/create/show + read/delete (`midwife.notifications.*`): effective
  create/store routes use `NotificationController`; inbox/show/read/delete use `MidwifeController`.
- `/midwife/admin-dashboard` (`midwife.admin.dashboard`, `AdminDashboardController`) — analytics.
- `/midwife/settings` + `PUT` (`midwife.settings.*`, `midwife/settings.blade.php`) — clinical identity,
  workflow prefs, security & audit.

## 9. BHW President pages (`/bhw-president`, `BhwPresidentController` + `TaskController`) — coordinator

- `/bhw-president/dashboard` (`bhw-president.dashboard`).
- `/bhw-president/bhws` list/create/details + `/{id}/assign-purok|archive|inactive|activate`, `DELETE`
  (`bhw-president.bhws.*`, `bhw-president/bhws*.blade.php`) — All BHWs management.
- `/bhw-president/tasks` CRUD + `/{id}/complete|progress` (`bhw-president.tasks.*`, `TaskController`,
  `bhw-president/tasks/`) — task assignment to BHWs.
- `/bhw-president/health-records` + `/{id}/pass|review|approve|reject`
  (`bhw-president.health-records.*`, `HealthRecordController@bhwPresident*`) — review queue to midwife.
- `/bhw-president/pregnancies` + `/{id}/review|approve|reject` (`bhw-president.pregnancies.*`,
  `PregnancyController@bhwPresident*`, `bhw-president/pregnancies/review.blade.php`).
- `/bhw-president/analytics` (`bhw-president.analytics`, `analytics.blade.php`) — health analytics.
- `/bhw-president/coverage` (`bhw-president.coverage`, `coverage.blade.php`) — coverage report.
- `/bhw-president/high-risk` (`bhw-president.high-risk`, `high-risk.blade.php`) — high-risk cases.
- `/bhw-president/reports`, `/{id}`, `DELETE`, `/{id}/approve|reject` (`bhw-president.reports.*`,
  `reports.blade.php`, `report-show*.blade.php`) — monthly reports moderation.
- `/bhw-president/messages` (`bhw-president.messages.*`).
- `/bhw-president/settings` + `PUT` (`bhw-president.settings.*`, `settings.blade.php`).
- President creation/promotion and related staffing operations use `BhwPresidentAssignmentService`
  to check for an existing approved president in the same normalized/overlapping barangay.

## 10. BHW pages (`/bhw`, `BhwController` + clinical controllers) — field worker

- `/bhw/dashboard` (`bhw.dashboard`, `bhw/dashboard.blade.php`).
- `/bhw/patients`, `/create`, `POST`, `/{id}`, `/{id}/menstruation`,
  `/{id}/menstruation/report|export`, and `/bhw/schedules`
  (`bhw.patients*`, `bhw/patients*.blade.php`, `bhw/women/create.blade.php`) — Women registry (register,
  details, menstruation history + report + export).
- `/bhw/checkups`, `/archived`, `/create/{userId}`, `POST` (`bhw.checkups.*`, `bhw/checkups/`) — view-only
  + create; completed auto-archive.
- `/bhw/health-records`, `/create`, `POST`, `/{id}/submit-to-president`, `/archive/{id}`
  (`bhw.health-records.*`, `bhw/health-records/`) — create/submit/archive; returned-record corrections
  are handled by the separate workflow resubmission endpoint.
- `/bhw/pregnancies` (`bhw.pregnancies.index`, `bhw/pregnancies/index.blade.php`) — list.
- `/bhw/postpartum` + mother/newborn/visit/immunize (`bhw.postpartum.*`, `PostpartumController`).
- `/bhw/walk-in-patients` full CRUD + `/{id}/convert` (GET+POST), `/trash`, `POST /{id}/restore`
  (`bhw.walk-in-patients.*`). Field profiles can later be linked to a portal account.
- `/bhw/learning`, `/{id}` (`bhw.learning.*`, `LearningController@bhwIndex/bhwShow`, read-only).
- Forum uses shared `/forum` routes; BHW-specific templates are selected by the controller.
- `/bhw/reports` list/create/show/delete + `/{id}/print|submit-to-president`
  (`bhw.reports.*`, `BhwController`).
- `/bhw/referrals`, `/create`, `POST`, `/{id}` (`bhw.referrals.*`, `bhw/referrals/`).
- `/bhw/tasks`, `/{id}/start|complete` (`bhw.tasks.*`) — My Tasks (advance only).
- `/bhw/messages`, `/bhw/sms` + send/broadcast, `/bhw/notifications` (`bhw.notifications.index`,
  `bhw/notifications.blade.php`).
- `/bhw/settings` + `PUT` (`bhw.settings.*`, `bhw/settings.blade.php`).
- `/workflow/revision-queue` displays drafts/returned health records and reports; dedicated
  resubmission endpoints handle corrections. See section 17 for cross-role workflows.

## 11. Patient (woman) pages (`/user`, `UserController`) — self-service, no sidebar

- `/user/dashboard` (`user.dashboard`, `user/dashboard.blade.php`) — home with mobile bottom dock.
- `/user/pregnancies`, `/create`, `POST`, `/{id}` (`user.pregnancies.*`, `user/pregnancies/`) — my pregnancies.
- `/user/menstruation`, `/calendar`, `/statistics`, `/report`, `/create`, `POST`, `DELETE {id}`
  (`user.menstruation.*`, `user/menstruation/`: index/calendar/statistics/report/create) + Cycle JSON API
  (`user.cycles.*`, `user.cycle.status`: list/create/delete, prediction, calendar, status).
- `/user/checkups` (`user.checkups`, `user/checkups.blade.php`) — view only.
- `/user/health-records` + `/create` + `POST` (`user.health-records*`, `user/health-records*.blade.php`).
- `/user/notifications` (`user.notifications`, `user/notifications.blade.php`).
- `/user/messages` (`user.messages.*`, Care Chat; unread dot in dock).
- `/user/settings` (`user.settings`, `user/settings.blade.php` — posts to `profile.update`).
- Personal clinical CSV export at `GET /profile/download`; password-confirmed account soft deletion
  and sign-out at `DELETE /profile/account`. Clinical rows are retained. These routes are in the
  shared authenticated profile group, although the settings feature is patient-facing.
- Shared: `/forum/*` (community), `/learning/*` (articles/videos/links/files/training/week guides/quizzes),
  `/profile/*` (patient edit view; shared update handlers; own-profile show redirects to settings).

## 12. Shared modules (all authenticated users unless noted)

- **Forum** `/forum` (`forum.*`, `ForumController`, `forum/` views + `forum/admin/`): list/create/show/
  edit/delete + comments + likes. Midwife has separate `/midwife/forum-admin` moderation.
- **Learning** `/learning` (`learning.*`, `LearningController`, `learning/` views): library home, articles,
  videos, links, files, HCW training, week-by-week pregnancy guides (`week-guide`), video detail + quiz
  (`quiz`, `quiz-result`, youtube partials). Midwife = full CRUD admin; BHW = read-only.
- **Messages** `/{role}/messages`, for `midwife`, `bhw`, `bhw-president`, and `user`
  (`MessageController`, `messages/` views):
  inbox/contact search, compose/reply, polled thread updates and read state, trash/restore, and
  unread badges. Patients can contact BHWs, presidents, or midwives; patient-to-patient messaging
  is excluded. Sending accepts `client_uuid` for retry/double-click deduplication. New threads can
  trigger an SMS nudge; replies skip that nudge. Deleting a root soft-deletes its replies as well.
- **SMS** (`SmsController`, `SmsService`, `SmsLog`): midwife/RHU-admin send to registered or walk-in
  patients and broadcast (controller enforces midwife/RHU-only sending); BHW has no SMS access.
  CHO has read-only logs. Templates support English/Tagalog. Provider
  selection is `SMS_PROVIDER` (`movider` default or `textbee`); Movider has an explicit mock mode.
- **Notifications:** role inboxes + JSON API `/api/notifications/*` (`NotificationController`:
  unread-count, recent, mark-read, mark-all-read, delete) polled by the navbar bell.
- **GIS:** `/cho/gis`, `/rhu/gis` + `/data` JSON (`GisController`) — per-purok aggregates for patients,
  high BP, anemia, missed checkups, and active pregnancies. Uses stored purok coordinates or a
  deterministic fallback labelled approximate. Newly stored patient coordinates do not replace
  this aggregation with an individual-patient location map.
- **PWA/offline:** manifest + `sw.js` + `pwa-outbox.js` + `api/device/session-check` (`DeviceController`).
- **Content zoom dock:** fixed A−/A+/reset control, 80%–150% clamp, `Ctrl + +/-/0` shortcuts.
- **Theme:** CHO-managed palettes/default mode plus local light/dark controls (section 5).

## 13. Key end-to-end workflows

1. **Account verification:** self-registration (`pending`) → RHU checks identity and duplicate
   suggestions → approve or reject with reason. RHU's direct registration starts approved.
2. **Health record chain:** BHW creates (`recorded_by_bhw`) → submits
   (`submitted_to_bhw_president`) → president approves (`bhw_president_approved`) and/or uses the
   separate pass action (`submitted_to_midwife`) → midwife accepts (`accepted_by_midwife`).
   The approve and pass handlers write different states; do not treat approval alone as passing.
   Midwife-created records can start accepted. Patient self-reports start in the president queue.
3. **Checkup referral chain:** BHW creates referral → midwife reviews → convert to checkup / decline.
4. **Monthly reports chain:** BHW creates report → submits to president → president approves/rejects →
   RHU reviews BHW reports (`rhu.bhw-reports`) → approve or return for revision. Legacy stored
   names such as `submitted_to_midwife` and `approved_by_midwife` remain in this RHU-owned flow.
   Reporting screens also query underlying clinical records; report approval is not a universal
   visibility gate for all dashboards/reports.
5. **Pregnancy lifecycle:** midwife creates/manages pregnancies; BHW registration can create a draft
   pregnancy, while a patient self-report creates a pregnancy and linked assessment in
   `submitted_to_bhw_president` and notifies the midwife, president, and assigned BHW.
   BHW field staff can also file a dedicated pregnancy report (`bhw.pregnancies.create/store`):
   active-pregnancy duplicates are refused with a pointer to the existing record, and all
   approved midwives (in-app + SMS with the reporter's contact) plus the barangay president
   are alerted. Delivery countdown (`alerts:run --deliveries`, daily 07:30) notifies enrolled
   patients (portal + SMS), walk-ins (SMS), and the responsible BHW at 14/7/3/1 days and when
   overdue — once per milestone via `delivery` notification event keys. President review writes approval or `needs_revision`.
   Delivery transitions and historical locking are described in section 17. BHW pregnancy routes
   expose a list, not a general pregnancy create/edit API.
6. **Supply chain:** RHU creates supply request → CHO approves/declines (`cho.supply-requests`).
7. **Task chain:** president creates/assigns task → BHW starts → completes; president tracks progress.
8. **Maternal death audit:** RHU records death/related morbidity → CHO reviews and audits
   (`/cho/maternal-deaths/{id}/audit`). CHO has no maternal-death creation route.
9. **Staff lifecycle:** RHU/CHO create accounts → approve/deactivate/activate; replace/transfer via
   staff-transitions (RHU) or CHO handover with recovery key (CHO super-admin succession).
10. **Patient self-care loop:** woman tracks cycles (calendar/statistics/report/prediction) + pregnancies,
    views checkups/records, uses Care Chat, reads forum/learning, acknowledges alerts, and exports
    personal records. Prediction and alert behavior are detailed in sections 18–19.

## 14. Data model (Eloquent models in `app/Models/`)

- **People:** `User` (all portal accounts w/ role+status), role-scoped `Midwife`, `Bhw`, `BhwPresident`, `Woman`,
  `Patient`, `WalkInPatient`, `EmergencyContact`, `Barangay`, `Purok`.
- **Clinical:** `Pregnancy`, `Checkup`, `CheckupReferral`, `HealthRecord`, `MenstruationDaily`,
  `MenstruationRecordSymptom`, `Cycle`, `PostpartumVisit`, `Newborn` (+`NewbornImmunization`),
  `Maternal*` suite (Screening, Assessment, PrenatalVisit, PostpartumCare, Supplement, Vaccination,
  Morbidity, Death), `Child*` suite (Record, Checkup, Assessment, NutritionTracking, FeedingMilestone,
  Supplement, Vaccination, ManagementOutcome), `ChildCareTargetClient`, `MaternalCareTargetClient`.
- **Ops:** `Task`, `BhwAssignment` (model retained; no current assignment route group),
  `BhwMonthlyReport`, `SupplyRequest`, `StaffTransition`, `ChoHandover`, `PreventiveIntervention`,
  `PatientTransfer`, `EmergencyAlert`, `DuplicateReview`.
- **Comms/content:** `Message`, `SmsLog`, `Notification`, `ForumPost`, `ForumComment`, `ForumLike`,
  `LearningMaterial`.
- **System:** `Setting` (key-value incl. `rhu.station_name`, thresholds, `appearance.theme`),
  `ActivityLog`, `SyncLog` (offline replay claims).
- **Important relationships:** clinical records can refer to `user_id` or `walk_in_patient_id`;
  `pregnancy_id` ties assessments/checkups/TCL entries to a specific pregnancy. Walk-in identity
  links use `user_id`/`converted_to_user_id`; preserving that link differs from rewriting every
  historical clinical foreign key. Newborns use `mother_id` and may link to a pregnancy.
- **Recent schema additions:** September 16–17 migrations add revision metadata, transfers,
  emergencies, delivery/locking, duplicate reviews, sync logs, and offboarding support;
  September 18 adds notification lifecycle/scheduled-message fields and Critical alert risk;
  September 19 adds message client UUIDs, user location, pregnancy gravida/para, cycle/like
  uniqueness and an SMS phone index. Migration files establish intended schema, not proof that
  every deployment has applied them.

## 15. Changes since the previous overview

This is a comparison with the previous document, not a commit-by-commit release history.

| Area | Current implementation / documentation correction |
|---|---|
| Identity and scope | Shared `users` authentication for every role; registration uses RHU 1's 16-barangay catchment. |
| Registration | Duplicate suggestions, link/dismiss review, structured address and optional location fields, password recovery. |
| Review workflows | Reviewer reasons, `needs_revision`, revision counts/timestamps, BHW correction queue and resubmission. |
| Patient continuity | Transfer requests reassign BHW/location while preserving clinical history. |
| Emergencies | Immediate referral and responder notifications/SMS bypass the ordinary review chain. |
| Delivery | Outcome recording creates postpartum schedules and eligible newborn/immunization records; historical pregnancy locking and audited reopening. |
| Staff lifecycle | Offboarding blocker checks, handover/transfer workflows, president assignment conflict checks, session/cache revocation. |
| Alerts and automation | Episode-based alerts, patient acknowledgement, resolved state, SMS attempt caps, stale-appointment suppression, scheduled-message delivery and diagnostics. |
| Menstrual tracker | Observed start-to-start interval prediction, confidence/regularity rules, historical windows and deduplicated reminders. |
| Messaging | Polled updates/read state, retry UUIDs, trash/restore and new-thread SMS nudges. |
| Offline support | Limited BHW outbox coverage, replay UUID claims, newer-record warnings and device session checks. |
| Analytics | Filtered CHO operational analytics and review queue; rules/Ollama/Groq assistant providers with rules fallback. |
| Appearance | CHO-managed brand palettes/default mode, revision-aware local preferences, shared design tokens. |
| Patient tools | Clinical CSV export and password-confirmed account soft deletion. |
| Retention/integrity | Activity-log pruning schedule, message/cycle/like uniqueness support and SMS lookup indexing. |

Earlier profile-to-settings consolidation and the shared staff sidebar remain part of the current
shell. Old file line counts, presumed separate staff tables, and unrouted page claims have been
removed from this overview.

## 16. Key files map for developers/AI

- Routes: `routes/web.php` (role, shared, JSON, and `/workflow` routes), `routes/console.php`
  (scheduled commands), `bootstrap/app.php` (routing and middleware aliases; health endpoint `/up`).
- Role page logic: `ChoController`, `RhuController`, `MidwifeController`, `BhwController`,
  `BhwPresidentController`, `UserController` (role dashboards + settings + domain actions).
- Clinical logic: `PregnancyController`, `CheckupController`, `HealthRecordController`,
  `MenstruationController`, `PostpartumController`, `ChildCheckupController`,
  `MaternalCareTargetClientController`, `ChildCareTargetClientController`.
- Cross-cutting: `AuthController`, `ProfileController`, `MessageController`, `SmsController`,
  `ForumController`, `LearningController`, `NotificationController`, `GisController`,
  `DatabaseBackupController`, `ArchivedRecordController`, `TaskController`, `BhwAssignmentController`,
  `ChoHandoverController`, `StaffTransitionController`, `DeviceController`, `AdminDashboardController`,
  `WorkflowController`. `BhwAssignmentController` is present but currently unrouted.
- Workflows: `app/Services/WorkflowService.php`, `BhwPresidentAssignmentService.php`,
  `SessionRevocationService.php`, and `app/Http/Middleware/HandleSyncRequests.php`.
- Clinical/automation: `MaternalRiskService`, `RiskAnalysisService`, `SmartNotificationService`,
  `SmsService`, `CyclePredictionService`, `PeriodNotificationService`; commands in
  `app/Console/Commands/` and `app/Jobs/SendScheduledMessages.php`.
- Analytics: `MaternalAnalyticsService`, `AIInsightService`, `CloudAnalyticsContext`,
  `GroqAnalyticsService`, `app/Http/Requests/AnalyticsRequest.php`, `config/services.php`.
- Shell: `layouts/app.blade.php` (design system + global JS), `includes/sidebar.blade.php`,
  `includes/navigation.blade.php`, `includes/portal-theme.blade.php`, `includes/women-navigation.blade.php`,
  `includes/appearance-head.blade.php`, `public/js/appearance.js`, `config/appearance.php`.
- Offline: `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html`, `public/js/pwa-outbox.js`.
- Data: `app/Models/`, `database/migrations/`; consult current models/migrations before relying on
  historical SQL dumps, ERDs, or model comments.

## 17. Cross-role workflows and lifecycle details

Implemented by `WorkflowController` and `WorkflowService` under `/workflow`:

| Endpoint | Behavior |
|---|---|
| `GET /workflow/revision-queue` | BHW queue of draft/returned health records and returned monthly reports. |
| `POST /workflow/health-records/{id}/resubmit` | BHW corrects allowed vitals/notes and resubmits an owned returned record. |
| `POST /workflow/reports/{id}/resubmit` | Resubmits an owned returned monthly report; this handler does not edit report content. |
| `POST /workflow/health-records/{id}/send-back` | Midwife supplies a reason and returns a record for revision. |
| `GET/POST /workflow/transfers` | Transfer ledger and requests for a registered or walk-in patient. |
| `POST /workflow/transfers/{id}/approve` or `/reject` | President/RHU/midwife review; approved requests update BHW/purok/barangay assignment. |
| `POST /workflow/emergency` | Creates an emergency alert and urgent referral; alerts approved midwives/RHU admins and attempts applicable SMS. |
| `POST /workflow/emergency/{id}/acknowledge` | Midwife/RHU/CHO/president acknowledgement, with an audit event. |
| `POST /workflow/pregnancies/{pregnancyId}/delivery` | Records delivery and invokes the postpartum transition. |

**Revision data:** rejection reason, reviewer, rejection time, revision count, and resubmission
time are retained. Returned health records re-enter `submitted_to_bhw_president`; reports re-enter
`submitted_to_president`. The service also supports pregnancy revisions, but there is currently
no dedicated pregnancy-resubmit route or pregnancy tab in `WorkflowController::revisionQueue()`.

**Transfer data:** `PatientTransfer` records old/new assignments and review history. Approval
updates the existing patient rather than creating another clinical identity. A pending request
for the same patient blocks another pending request.

**Delivery:** stores outcome/delivery metadata and `ended_at`, updates/creates applicable maternal
TCL outcome data, and schedules checkups at birth date +1 day, +1 week, and +6 weeks. Registered
mothers receive placeholder postpartum visits at weeks 0/1/6; registered live births also get a
linked newborn and seeded immunizations. Walk-in pregnancies can receive scheduled checkups, but
the newborn and postpartum-visit creation branches require a registered `user_id`.
`postpartum_transitioned_at` prevents repeating schedule creation; `is_locked` blocks ordinary
pregnancy update/archive. Midwife `POST /midwife/pregnancies/{id}/reopen` requires a reason and
unlocks for correction without clearing the recorded outcome. New pregnancies are separate
records, with gravida/parity suggestions drawn from the latest ended pregnancy/TCL.

**Staff continuity:** `WorkflowService::guardOffboarding()` checks assigned patients/actionable
work in the staff lifecycle handlers that invoke it. RHU staff transitions reassign work;
CHO succession uses its recovery-key handover flow. Session revocation and PWA cache-version
changes support removing old access. These are application workflows, not a universal model-level
constraint on every possible delete/update.

**Access implementation:** revision routes explicitly restrict BHW/midwife roles. Transfer review
and emergency acknowledgement check roles inside the controller. Transfer listing/request,
emergency creation, and delivery routes currently have shared authentication middleware without
an additional action-level role/ownership restriction in `WorkflowController`; their intended
staff use should not be confused with a fully role-restricted API.

## 18. Risk assessment, notifications, and SMS acknowledgement

There are distinct assessment and reporting paths:

- `MaternalRiskService::assess()` calculates a deterministic Low/Medium/High result from supplied
  age, BP, BMI, lifestyle, health conditions, and obstetric history. Clinical forms support
  automatic assessment and, in staff flows, manual risk values.
- `RiskAnalysisService::evaluate()` evaluates stored patient history/care gaps and can produce
  Low/Medium/High/Critical, update the latest assessment, record preventive interventions,
  create a follow-up when no future scheduled checkup exists, and notify the patient. It respects
  the latest record's manual assessment. This operation has write/notification side effects.
- `RiskAnalysisService::prioritize()` and CHO analytics read stored facts for review ordering.
  The analytics assistant does not itself update clinical risk, create appointments, or send SMS.

`SmartNotificationService` and `Notification` track an alert episode/fingerprint, reminder state,
patient `read_at`, and resolution. Repeated evaluations retain an existing active alert; patient
acknowledgement stops its reminders, while staff reading a separate copy does not acknowledge it
for the patient. Low risk resolves the episode. Appointment alerts refer to an exact checkup and
schedule so completed/cancelled/rescheduled/deleted sources do not continue stale reminders.

Automated SMS attempts are capped per patient and alert type per day, including pending/failed
attempts. Opt-out and missing numbers are respected. Registered-patient SMS links to the portal
notification page for acknowledgement. Staff patient details show **Unseen / Seen / Resolved**,
acknowledgement time, and latest SMS result. A logged SMS `sent` means gateway acceptance, not a
handset read receipt; `MOCK_` entries are simulated. Walk-in SMS has no portal acknowledgement,
and no inbound SMS/read-receipt webhook is implemented.

## 19. Menstrual tracking and scheduled automation

`CyclePredictionService` uses up to six observed **start-to-start intervals**. No records give no
prediction; one record uses a low-confidence 28-day fallback. At least three observed intervals
are needed to assess regularity. The current standard-deviation heuristic classifies up to
3 days as regular, up to 7 as somewhat irregular, and above 7 as irregular. Irregular estimates
use shortest/longest historical intervals and widen subsequent windows. Fertile-window/ovulation
estimates are hidden unless history is sufficient and regular. These are calendar estimates;
confidence labels are not clinically validated accuracy percentages.

Cycle save/delete/restore changes recalculate stored intervals. Period reminders are deduplicated,
exclude unapproved patients and pregnancies not ended, and run three days before, one day before,
and at the estimated window start.

`routes/console.php` schedules the following using `config('app.timezone')` (Asia/Manila):

| Time | Command/task |
|---|---|
| Daily 07:00 | `alerts:run --appointments` — tomorrow's appointment reminders. |
| Daily 08:00 | `alerts:run --checkups` — mark overdue prior-day checkups missed. |
| Daily 08:30 | `alerts:run --risk` — missed-checkup risk evaluation. |
| Daily 08:40 | `alerts:run --review` — elevated-risk/unresolved-alert review. |
| Daily 08:45 | `alerts:run --reminders` — refresh unread risk reminders. |
| Daily 09:00 | `alerts:run --periods` — estimated-period notifications. |
| Every minute | `messages:process-scheduled` — deliver due scheduled messages. |
| Every minute | Cache heartbeat `automation.scheduler_heartbeat`. |
| First of month, 03:00 | `activity-logs:prune --days=90`. |

Commands use overlap locks. Due-message processing is synchronous through the command/job path;
it does not require a separate queue worker for this schedule. An external scheduler must invoke
`php artisan schedule:run` every minute (or use `schedule:work` for local development). Source
schedule definitions do not prove the Windows task is running.

## 20. CHO analytics and optional AI

`ChoController` → `MaternalAnalyticsService` supplies filtered counts, monthly trends, area summaries,
and a paginated pregnancy review queue. Dates apply to historical registrations/deaths/complications;
open-pregnancy/follow-up counts describe the current snapshot. The open-record definition excludes
ended/delivered/outcome-recorded pregnancies and known maternal-death links. Past EDD alone does
not close a pregnancy. Walk-in pregnancies are included in this analytics flow.

The review order uses recorded emergencies, the more serious stored pregnancy/latest linked
assessment risk, linked missed/overdue appointments, and EDD. Unknown risk stays unassessed.
These are operational counts and review priorities, not mortality-rate forecasts.

`POST /cho/analytics/chat` uses session/CSRF authentication and `throttle:10,1`:

- `ANALYTICS_AI_PROVIDER=rules` (default): deterministic answers from the selected report.
- `ollama`: optional local-model summaries; only loopback endpoints/local model metadata accepted.
  The report context excludes patient queue rows and identifiers; the typed question is included.
- `groq`: optional online summaries via `GroqAnalyticsService`. `CloudAnalyticsContext` sends grouped
  counts, area aliases and a supported fixed topic, rather than raw typed questions or patient
  rows. Counts below five are grouped (including zero); matching answers are cached for five minutes.
- Provider failures return a labelled rules answer. Rendering the analytics page does not itself
  call a language model. Legacy Gemini configuration is not the analytics provider selection.

Provider configuration lives in `config/services.php`. Setup references:
[local/rules analytics](ANALYTICS_AI_SETUP.md) and [Groq analytics](GROQ_ANALYTICS_SETUP.md).

## 21. Offline fieldwork and current boundaries

The PWA uses app-shell/runtime caches, an offline fallback, and an IndexedDB outbox. Offline
submission coverage is limited to BHW health records, walk-in creation, checkup creation,
postpartum newborn/visit creation, and referrals. Forms with file uploads require connectivity.
It is not a complete offline replica of every module.

Queued payloads include `sync_uuid` and `client_timestamp`. On covered routes, `sync` middleware
uses `SyncLog` to recognize repeated UUIDs and returns `synced`/`deduped` JSON plus an optional
warning if newer health records exist. The service worker flushes on reconnect/background sync;
422 responses are removed and reported as needing review, while network/server failures stay
queued. Device session checks run when connected and can wipe cached/outbox data after revoked
access or a PWA cache-version change. Offline devices only learn about server revocation once
they reconnect.

**Other route/schema distinctions to preserve in future changes:**

- `BhwAssignmentController`, assignment views, and `midwife/menstrual-cycle-dashboard.blade.php`
  exist, but have no corresponding current route group/page route.
- BHW forum templates use shared `/forum` routing; there is no `/bhw/forum` route group.
- Midwife notification create/store are declared twice; the later `NotificationController`
  declarations are the effective registered routes.
- Several endpoints named `export/pdf` return printable Blade views. Do not assume every such
  route produces a server-generated PDF attachment; CSV exports are separate responses.
- Monthly-report database field/status names still refer to midwives even where RHU owns the
  final review screen. Pregnancy revision support in the service is broader than the exposed UI.
- CHO analytics defines open pregnancies independently of `Pregnancy::active()`. The model's
  active scope/accessor requires no `ended_at` and EDD today or later; its completed/status logic
  also treats past EDD as completed. Lists and duplicate-active-pregnancy checks using that scope
  can therefore differ from the analytics queue, which retains overdue records until an outcome
  or other closure evidence is recorded.

## 22. Local verification and maintenance references

Run commands from `CapstoneProject/reprocare` using the configured PHP/XAMPP environment.
Application entry: `php artisan serve`; frontend development/build: `npm run dev` / `npm run build`.

Read-only inventory/diagnostics:

```powershell
php artisan route:list --except-vendor
php artisan schedule:list
php artisan automation:status
php artisan analytics:ai-check
```

Existing regression coverage includes `CyclePredictionTest`, `AlertLifecycleTest`,
`ScheduledAutomationTest`, `MaternalAnalyticsTest`, `GroqAnalyticsTest`, `AppearanceSettingsTest`,
`PatientProfileDisplayTest`, and `WalkInSmsAndMessagingTest` under `tests/Feature/`.
The automation/analytics test infrastructure uses isolated in-memory SQLite schemas; the historical
MySQL migration chain is not interchangeable with a full SQLite migration rebuild.

For implementation changes, run the relevant test filter, for example:

```powershell
php artisan test --filter=CyclePredictionTest
php artisan test --filter=MaternalAnalyticsTest
```

See [tracker and automation details](TRACKER_AND_AUTOMATION.md) for alert diagnostics and
`alerts:run --all --dry-run` semantics. That command exercises the connected database in a rollback
transaction and fakes gateway HTTP; it is more than a read-only status check. Unflagged alert
commands perform real processing. Historical verification notes in other documents are snapshots,
not current test results: for example, `ExampleTest` now expects the landing page's HTTP 200.

**Verification for this overview update:** checked current source and successfully loaded the
Laravel route inventory. Automated tests, live SMS/AI calls, migrations, and browser checks were
not run as part of this documentation-only update.
