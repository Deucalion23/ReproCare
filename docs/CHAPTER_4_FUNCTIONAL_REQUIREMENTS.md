# CHAPTER 4

# PRESENTATION, ANALYSIS, AND INTERPRETATION OF SYSTEM FUNCTIONAL REQUIREMENTS

## 4.1 Introduction

This chapter presents the major functional requirements implemented in ReproCare, a web-based maternal and reproductive health tracking and learning system. The system supports six primary user roles: Patient, Barangay Health Worker (BHW), BHW President, Midwife, Rural Health Unit (RHU) Administrator, and City Health Office (CHO) Administrator. Each role is given only the functions needed for its responsibilities through role-based access control.

The functions described in this chapter are based on the implemented ReproCare modules, including patient registration, account verification, maternal-health monitoring, menstrual and pregnancy tracking, checkup management, reporting, analytics, learning materials, communication, and administrative management.

## 4.2 General Functional Requirements

The system provides the following functions to authorized users:

1. The system shall allow authorized users to sign in and sign out securely.
2. The system shall restrict pages and actions according to the user's assigned role.
3. The system shall allow users to view and update their profile information.
4. The system shall provide password-recovery functions.
5. The system shall provide in-system notifications for relevant health, workflow, and administrative events.
6. The system shall allow authenticated users to access health-learning materials.
7. The system shall allow authenticated users to participate in the community forum through posts, comments, and reactions.
8. The system shall keep activity logs for selected administrative and approval actions.
9. The system shall restrict inactive, unverified, incomplete, or unapproved patient accounts from protected patient functions.

## 4.3 Patient Functional Requirements

Patients are the primary recipients of maternal and reproductive health services in ReproCare. The patient module is designed to support self-monitoring, access to health information, and communication with community health personnel.

| No. | Functional Requirement |
|---|---|
| 1 | The patient shall be able to register an account and provide personal, address, barangay, and contact information. |
| 2 | The patient shall verify ownership of an email address before RHU account approval. |
| 3 | The patient shall complete the required profile information before accessing protected patient functions. |
| 4 | The patient shall be able to view a personal dashboard. |
| 5 | The patient shall be able to record, view, and delete menstrual records. |
| 6 | The patient shall be able to view menstrual calendars, cycle predictions, statistics, and reports. |
| 7 | The patient shall be able to record and view pregnancy information. |
| 8 | The patient shall be able to view checkups and personal health records. |
| 9 | The patient shall be able to receive and read notifications related to health records, checkups, and approval status. |
| 10 | The patient shall be able to send and receive messages from authorized health personnel. |
| 11 | The patient shall be able to manage emergency-contact information. |
| 12 | The patient shall be able to access learning articles, videos, files, weekly guides, and quizzes. |
| 13 | The patient shall be able to participate in the community forum. |
| 14 | The patient shall be able to download personal data and request account deactivation. |

## 4.4 Barangay Health Worker Functional Requirements

Barangay Health Workers are responsible for community-level data collection, patient support, and submission of field records.

| No. | Functional Requirement |
|---|---|
| 1 | The BHW shall be able to view a dashboard for assigned patients and barangay coverage. |
| 2 | The BHW shall be able to register patient accounts and walk-in patients. |
| 3 | The BHW shall be able to update walk-in patient demographic information. |
| 4 | The BHW shall be able to create and manage health records for assigned patients. |
| 5 | The BHW shall be able to record pregnancy-related information and submit field records for review. |
| 6 | The BHW shall be able to view assigned checkups, pregnancies, and patient information. |
| 7 | The BHW shall be able to prepare and submit monthly reports. |
| 8 | The BHW shall be able to revise and resubmit returned health records and reports. |
| 9 | The BHW shall be able to request patient transfers between barangays, puroks, or health workers. |
| 10 | The BHW shall be able to submit emergency cases for review by authorized health personnel. |
| 11 | The BHW shall be able to view assigned tasks and update task progress. |
| 12 | The BHW shall be able to access learning materials, notifications, and authorized communication features. |

## 4.5 BHW President Functional Requirements

The BHW President performs a supervisory role over BHW activities within the assigned coverage area.

| No. | Functional Requirement |
|---|---|
| 1 | The BHW President shall be able to view BHWs and patients within assigned barangay coverage. |
| 2 | The BHW President shall be able to review BHW health records before forwarding them to a midwife. |
| 3 | The BHW President shall be able to review, return, approve, or forward BHW monthly reports. |
| 4 | The BHW President shall be able to assign and monitor BHW tasks. |
| 5 | The BHW President shall be able to review patient-transfer requests. |
| 6 | The BHW President shall be able to monitor barangay-level pregnancy, health-record, and reporting information. |
| 7 | The BHW President shall be able to access notifications, reports, and coverage information. |

## 4.6 Midwife Functional Requirements

Midwives provide clinical review and care coordination for patients in their assigned areas.

| No. | Functional Requirement |
|---|---|
| 1 | The Midwife shall be able to view patients, pregnancies, checkups, and health records within assigned coverage areas. |
| 2 | The Midwife shall be able to review BHW-submitted health records and return records for correction. |
| 3 | The Midwife shall be able to review BHW monthly reports before forwarding them to the RHU. |
| 4 | The Midwife shall be able to schedule, complete, cancel, and reschedule checkups. |
| 5 | The Midwife shall be able to monitor high-risk pregnancies and risk alerts. |
| 6 | The Midwife shall be able to review referrals and convert valid referrals into checkups. |
| 7 | The Midwife shall be able to review walk-in patients and activate an appropriate portal account. |
| 8 | The Midwife shall be able to send notifications and SMS alerts when an SMS provider is configured. |
| 9 | The Midwife shall be able to view and export reports in CSV and PDF formats. |
| 10 | The Midwife shall be able to access maternal-health decision-support information. |

## 4.7 RHU Administrator Functional Requirements

The RHU Administrator manages patient verification, staff records, maternal-health administration, and RHU-level reporting.

| No. | Functional Requirement |
|---|---|
| 1 | The RHU Administrator shall be able to approve, reject, reactivate, and link pending patient registrations. |
| 2 | The RHU Administrator shall be able to review possible duplicate patient registrations. |
| 3 | The RHU Administrator shall be able to create, update, activate, archive, and manage Midwife, BHW, and BHW President accounts. |
| 4 | The RHU Administrator shall be able to manage staff transitions and coverage assignments. |
| 5 | The RHU Administrator shall be able to create and manage maternal-death and maternal-morbidity records. |
| 6 | The RHU Administrator shall be able to manage supply requests. |
| 7 | The RHU Administrator shall be able to review, approve, or reject BHW reports. |
| 8 | The RHU Administrator shall be able to manage learning materials. |
| 9 | The RHU Administrator shall be able to send individual and broadcast SMS alerts when an SMS provider is configured. |
| 10 | The RHU Administrator shall be able to view analytics, GIS maps, reports, and activity logs. |
| 11 | The RHU Administrator shall be able to export, import, download, and manage database backups. |
| 12 | The RHU Administrator shall be able to restore eligible archived records. |

## 4.8 CHO Administrator Functional Requirements

The CHO Administrator has city-wide monitoring and administrative responsibilities.

| No. | Functional Requirement |
|---|---|
| 1 | The CHO Administrator shall be able to view city-wide dashboards, pregnancy records, reports, and maternal-health analytics. |
| 2 | The CHO Administrator shall be able to manage user accounts by approving, rejecting, activating, or deactivating accounts. |
| 3 | The CHO Administrator shall be able to review supply requests. |
| 4 | The CHO Administrator shall be able to review maternal-death records and perform audit actions. |
| 5 | The CHO Administrator shall be able to view activity logs for accountability. |
| 6 | The CHO Administrator shall be able to access GIS-based maternal-risk maps. |
| 7 | The CHO Administrator shall be able to export city-wide reports in CSV and PDF formats. |
| 8 | The CHO Administrator shall be able to manage learning materials. |
| 9 | The CHO Administrator shall be able to view SMS delivery records. |
| 10 | The CHO Administrator shall be able to manage database backups and restore eligible archived records. |
| 11 | The CHO Administrator shall be able to perform administrator role handover and recovery-key setup. |

## 4.9 Summary

ReproCare implements role-specific functions to support community-based maternal and reproductive health monitoring. Patients can monitor menstrual and pregnancy information, access health education, communicate with health personnel, and receive notifications. BHWs collect and submit community health information, while BHW Presidents and Midwives review records and reports. RHU and CHO Administrators manage accounts, reports, maternal-health records, analytics, and system-level administrative functions. This role-based arrangement supports appropriate access, accountability, and coordinated health-service delivery.
