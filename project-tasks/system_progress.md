# LMS System Progress & Development Roadmap

This document provides a comprehensive overview of the current development status of the Senior High School Learning Management System, detailed feature maps, and a roadmap for upcoming development.

## 1. Status / Progress Tracker

| # | Module | Status | Core Implementation Status |
| :--- | :--- | :--- | :--- |
| **01** | **User & Role Management** | 🚧 In Progress | **Done:** Login, Forced Password Change, Admin User CRUD, Profile Editing, RBAC Policy implementation (Materials, Grades, Assignments). **Gap:** Remaining controllers (Classroom/Quiz), Bulk CSV import. |
| **02** | **Enrollment & Academic Structure** | 🚧 In Progress | **Done:** Admin CRUD for Enrollment & Subjects, Track/Strand models. **Gap:** Student-facing enrollment view, Strand transfer workflow. |
| **03** | **Class & Scheduling** | 🚧 In Progress | **Done:** Admin CRUD with conflict detection, Room model, Timetable views, RBAC Policy integration. **Gap:** OJT/Immersion block scheduling. |
| **04** | **Attendance** | ✅ Done | **Done:** Table migration, AttendanceRecord model, StudentAttendanceController, TeacherAttendanceController, Teacher attendance view, Student attendance view, Basic audit trail (logged_by/logged_at), Comprehensive audit event integration (AttendanceRecorded event), Excessive absence alerts (automated notifications for concerning patterns). |
| **05** | **Content & Learning Materials** | 🚧 In Progress | **Done:** Models, Teacher CRUD, Preview/Download, Section scoping, RBAC Policy. **Gap:** File versioning, Access analytics. |
| **06** | **Assignments & Quizzes** | 🚧 In Progress | **Done:** Full Loop (Create -> Submit -> Grade), CSV Quiz Import, Discussion Comments, Deadline enforcement, RBAC Policy. **Gap:** Rubrics, Plagiarism detection. |
| **07** | **Grading & Report Cards** | ⏳ Not Started | **Done:** Table migrations. **Gap:** Models, Grade Calculation Engine, Report Card Generation (Form 138). |
| **08** | **Communication & Announcements** | 🚧 In Progress | **Done:** Announcement CRUD, Attachments, Threaded Comments. **Gap:** Direct Messaging (DM), Real-time notifications. |
| **09** | **Calendar & Events** | 🚧 In Progress | **Done:** FullCalendar UI, Admin/Teacher/Student feed logic. **Gap:** Recurring events, Subject-specific event scoping. |
| **10** | **Guidance & Counseling** | ⏳ Not Started | **Gap:** No code. Requires high-sensitivity data handling and strict audit logging. |
| **11** | **Library Management** | ⏳ Not Started | **Gap:** No code. Optional/Lower priority. |
| **12** | **Parent / Guardian Portal** | 🚧 In Progress | **Done:** Guardian model, Dashboard Controller, Dashboard View. **Gap:** Multi-ward switching interface. |
| **13** | **Reports & Analytics** | 🚧 In Progress | **Done:** Audit Log Dashboard. **Gap:** Academic performance analytics, At-risk student flagging. |
| **14** | **Notifications & Alerts** | 🚧 In Progress | **Done:** Model/Controller (Read/Unread). **Gap:** Event-driven engine (Laravel Events), Email/SMS integration. |
| **15** | **System Administration** | 🚧 In Progress | **Done:** SchoolYear model, CRUD, Rollover UI/Logic. **Gap:** Automated student promotion/archiving process. |
| **16** | **Student Dashboard** | ✅ Done | **Done:** Full implementation (Controller, Service, Models, Views, Tests). |

---

## 2. Step-by-Step Roadmap

### Phase A: Foundational Stability & RBAC (Current)
1.  **RBAC Refactoring:** Move ad-hoc role checks in controllers to Laravel **Policies and Gates** (In progress - Material/Assignment/Grades done).
2.  **Fix Regressions:** Resolve the 3 failing tests in `AdminScheduleTest.php`.
3.  **Core UI Completeness:** Build the Admin UI for School Year management (Module 15) to allow creating new academic years (Done).

### Phase B: Essential Operations (High Priority)
1.  **Attendance Module (M04):** Implement the full attendance logging flow for teachers and views for students/guardians.
2.  **Grading Engine (M07):** Build the core logic to calculate weighted grades based on DepEd standards. This is the most complex remaining business logic.
3.  **Report Cards:** Generate exportable PDFs for SF9/Form 138.

### Phase C: Stakeholder Engagement
1.  **Guardian Portal (M12):** Build the dedicated dashboard for parents to view their children's progress.
2.  **Direct Messaging (M08):** Implement the `Message` model and UI for teacher-student-parent communication.
3.  **Notification Engine (M14):** Transition to an event-driven system where actions (grading, attendance) automatically trigger notifications.

### Phase D: Oversight & Hardening
1.  **Guidance Module (M10):** Implement sensitive record management with strict audit logging.
2.  **Analytics (M13):** Build "At-Risk" student dashboards using grading and attendance data.
3.  **School Year Rollover (M15):** Build the automated process for promoting students and archiving data.

---

## 3. Detailed Module/Features for Development

### Module 04: Attendance
*   **Model:** `AttendanceRecord` (uuid, schedule_id, student_id, date, status, logged_by).
*   **Features:** Batch attendance entry for teachers, Daily/Subject-based views for students, Auto-alerts for excessive absences.

### Module 07: Grading & Report Cards
*   **Models:** `GradingTemplate`, `GradeComponent`, `FinalGrade`.
*   **Features:** Configurable weightage (Written Work vs. Performance Task), Historical grade locking, Grade appeal workflow.

### Module 12: Parent Portal
*   **Features:** Ward selector (for multi-child guardians), Read-only view of M04 (Attendance) and M07 (Grades), Access to M08 (Messaging).

### Module 15: System Administration
*   **Features:** School Year CRUD, Semester management, The "Rollover" pipeline (Promote G11 -> G12, Graduate G12).

---

## 4. Immediate Next Steps Checklist

- [x] **M03:** Debug and fix `tests/Feature/AdminScheduleTest.php` failures.
- [x] **M15:** Create `AdminSchoolYearController` and views to manage school years.
- [x] **M04:** Generate `AttendanceRecord` model and migration (if missing) and start `TeacherAttendanceController`.
- [x] **M01:** Audit all controllers and replace manual role checks with `$this->authorize()` (Materials, Grades, Assignments done).
- [x] **M12:** Define routes for `/guardian/dashboard` and start the base view.
