# LMS Modules — Documentation Index

This folder contains one Markdown doc per module of the Senior High School LMS.
Each doc covers: system overview, function, data model (ERD slice), file map,
cross-cutting concerns, build phase, and — where the module has real code —
an **Implementation Status** section with concrete file paths.

> **Stack correction:** the master plan (§0) and modules 01–15 describe a
> planned Laravel + Vue.js SPA stack. That was never adopted. The actual
> codebase (`package.json`, `resources/views/**/*.blade.php`) is
> server-rendered **Laravel Blade + Alpine.js + ApexCharts**, with no Vue
> dependency at all. Module 16 was written against the real stack; modules
> 01–15 now carry a correction note near their "Tech stack" line pointing
> here.

The statuses below were verified directly against the codebase (controllers,
models, migrations, routes, views, tests) — not inferred from the docs.

---

## Module Index

| # | Module | Doc | Status | Notes |
|---|--------|-----|--------|-------|
| 01 | User & Role Management | [01-user-role-management.md](./01-user-role-management.md) | 🚧 In progress | Login, forced password change (tested), admin user CRUD, and self-service Teacher/Student profile editing (`ProfileController`, tested, 8/8 passing) all real. Self-service registration/reset (account creation) was removed from the codebase — profile *editing* is not the same as account creation and remains Admin-provisioned. No Policies/Gates yet; `Guardian` model missing. |
| 02 | Enrollment & Academic Structure | [02-enrollment-academic-structure.md](./02-enrollment-academic-structure.md) | 🚧 In progress | `EnrollmentController` + `SubjectController` are full working admin CRUD. `Strand::track()` references a nonexistent `Track` model (bug). Student-facing enrollment view is a stub. |
| 03 | Class & Scheduling | [03-class-scheduling.md](./03-class-scheduling.md) | 🚧 In progress | `Schedule` model only (built as Module 16 plumbing). No controller/UI, no conflict detection, no `Room` model. |
| 04 | Attendance | [04-attendance.md](./04-attendance.md) | ⏳ Not started | Table exists via migration only. No model, controller, or wired view. |
| 05 | Content & Learning Materials | [05-content-learning-materials.md](./05-content-learning-materials.md) | 🚧 In progress | `LearningMaterial` model, `TeacherMaterialController` (full CRUD), `StudentMaterialController`, and `MaterialDownloadController` (download + inline preview, shared access gate) all real and tested (part of the 42/42 passing suite). No versioning, no offline-format handling, no access-analytics logging. |
| 06 | Assignments, Quizzes & Assessments | [06-assignments-quizzes-assessments.md](./06-assignments-quizzes-assessments.md) | 🚧 In progress | `Assignment`, `Quiz`, `QuizAttempt`, `Submission` models exist with real scopes (Module 16 + Module 9 dependencies), tested. **Assignments loop done** (Sept 29, 2026): teacher CRUD → student submit → teacher grade → student score (`TeacherAssignmentController`, `StudentAssignmentController`, `AssignmentLoopTest` 5/5). Quizzes have no teacher/student UI yet. |
| 07 | Grading & Report Cards | [07-grading-report-cards.md](./07-grading-report-cards.md) | ⏳ Not started | Tables exist via migrations only. No models/controllers/UI. |
| 08 | Communication & Announcements | [08-communication-announcements.md](./08-communication-announcements.md) | 🚧 In progress | `Announcement` model + `visibleToSection()` scope real and tested (Module 16 dependency). No posting UI; no `Message` model; messaging views are stubs. |
| 09 | Calendar & Events | [09-calendar-events.md](./09-calendar-events.md) | 🚧 In progress | Real `CalendarController` + `CalendarEventService` + `StoreCalendarEventRequest`, wired to `/calendar` with a full FullCalendar UI and 9 passing tests. Student-only end to end — no admin/teacher event creation/viewing yet. |
| 10 | Guidance & Counseling | [10-guidance-counseling.md](./10-guidance-counseling.md) | ⏳ Not started | No code beyond the plan. |
| 11 | Library / Resource Management | [11-library-resource-management.md](./11-library-resource-management.md) | ⏳ Not started | No code beyond the plan. |
| 12 | Parent / Guardian Portal | [12-parent-guardian-portal.md](./12-parent-guardian-portal.md) | ⏳ Not started | `guardians` table exists (with `students.guardian_id` FK) but no `Guardian` Eloquent model and no portal UI. |
| 13 | Reports & Analytics | [13-reports-analytics.md](./13-reports-analytics.md) | 🚧 In progress | `ReportController` + `/admin/reports` is a real, working audit-log dashboard (filters, stats, top users) — furthest along of the ⏳-adjacent modules. |
| 14 | Notifications & Alerts | [14-notifications-alerts.md](./14-notifications-alerts.md) | 🚧 In progress | `Notification` model + `NotificationController` (list/mark-read/mark-all-read) exist and are wired into the layout. Not yet a generic event-driven engine — no other module dispatches into it. |
| 15 | System Administration | [15-system-administration.md](./15-system-administration.md) | ⏳ Not started | `admin/school-year/index.blade.php` is an unwired stub. No rollover logic. |
| 16 | Student Dashboard | [16-student-dashboard.md](./16-student-dashboard.md) | ✅ Done | Controller, service, and 6 supporting models are real, wired to `/student/`, and covered by `tests/Feature/StudentDashboardTest.php` (4/4 passing). Written against the actual schema, not the master ERD. |

**Legend:** ✅ Done · 🚧 In progress (real backend/model code exists, UI or full scope incomplete) · ⏳ Not started (plan only, or migration-only) · ⚠️ Blocked

---

## Cross-cutting concerns (referenced by every module)

These apply project-wide. Most are still aspirational (see per-module
Implementation Status sections for what's actually enforced today):

| Concern | Real status | Notes |
|---|---|---|
| **Data privacy** | Not implemented | No field-level encryption; no access logging on guidance-type records (Module 10 doesn't exist yet) |
| **Multi-tenancy** | Not implemented | No `tenant_id` anywhere; single-school assumption throughout |
| **Auditability** | 🚧 Partial | Audit-log data backs Module 13's `/admin/reports` dashboard (real, working) |
| **Mass assignment** | ✅ Followed | Models use `$fillable` consistently |
| **Response envelope** | N/A | No JSON API in active use (`routes/api.php` is empty — all flows are session-based Blade, see Module 1) |
| **Service layer** | 🚧 Partial | `StudentDashboardService` (Module 16) and `CalendarEventService` (Module 9) are the clearest examples so far; most controllers still hold their own query logic |
| **Form Requests** | 🚧 Partial | Used in some controllers (e.g. `EnrollmentController`, `StoreCalendarEventRequest`), not yet universal |
| **RBAC (Policies)** | Not implemented | Controllers use ad hoc `abort_unless(in_array($request->user()->role, [...]))` checks; no Laravel Policies/Gates yet |

---

## Standard module doc structure

Each module doc under `01`–`15` generally covers: System Overview, Function
("Keep in mind" notes), Data Model (ERD slice), File Map, Cross-Cutting
Concerns, Build Phase, and — added during the Sept 2026 documentation pass —
an **Implementation Status** section. Module 16 additionally has precise
Definitions, Testing, and Implementation Notes sections since it documents
already-shipped code rather than planned scope.
