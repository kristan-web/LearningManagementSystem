# Senior High School LMS — Planning Document

A blueprint for building a Learning Management System tailored to Senior High School (Grades 11–12, Tracks/Strands system as used under the Philippine K-12 curriculum, but adaptable to any SHS setup).

> **Per-module detail:** Each module in Section 1 now also has its own standalone file under
> [`modules/`](./modules/) with the module's function, the relevant slice of the ERD, its flowchart,
> applicable cross-cutting concerns, and implementation notes — a self-contained brief you can hand
> to a developer or an AI coding assistant without them needing the whole plan. See
> [`modules/README.md`](./modules/README.md) for the full index.

---

## 0. Tech Stack

**Backend:** Laravel (PHP) — REST/JSON API, queued jobs for notifications and report generation, Eloquent ORM, policies/gates for the RBAC model in Module 1.1.
**Frontend:** Vue.js (SPA) — consumes the Laravel API, component-based UI per role dashboard (Admin, Teacher, Student, Guardian, Guidance).
**Database:** relational (MySQL/PostgreSQL), matching the ERD in Section 4.

> **⚠️ Stack correction (Sept 2026):** the Vue.js SPA described above was never
> adopted. `package.json` has no Vue dependency; every screen is a
> server-rendered Laravel Blade view. The actual frontend stack is **Blade +
> Alpine.js (interactivity) + ApexCharts (charts)**. `routes/api.php` is
> empty — there is no JSON API in active use; auth and all data flows are
> session-based (see `WebAuthController`). This plan's ERD, module scope, and
> business rules are still the accurate target design; only the "Laravel +
> Vue SPA + REST API" framing in this section and in modules 01–15 is
> outdated. See [`modules/16-student-dashboard.md`](./16-student-dashboard.md)
> for the first module written against the real stack, and
> [`modules/README.md`](./README.md) for a per-module implementation
> status table.

### Strict rule: scalability and readability

> **Every piece of this system — backend and frontend — must be built to be scalable and
> readable. This is non-negotiable and applies to all modules, all code, and all future
> contributions.**

In practice, this means:

- **Readable first.** Code should be understandable by a new developer without a walkthrough: clear naming, small functions/methods, no cleverness for its own sake, consistent formatting (PSR-12 for PHP, an enforced ESLint/Prettier config for Vue/JS).
- **Scalable by default.** No hardcoded business rules that belong in data (e.g., grading weights, curriculum maps — see Sections 1.2 and 1.7). Design for multiple schools/years/sections from day one, even if only one is live at launch.
- **Thin controllers, fat services.** Laravel controllers should only orchestrate; business logic belongs in Service classes / Actions, validation in Form Requests, and query complexity in Eloquent scopes or dedicated Query classes — not in controllers or Blade/Vue templates.
- **Modular by domain.** Group code by the modules defined in Section 1 (e.g., `Enrollment`, `Grading`, `Attendance`) rather than dumping everything into generic `Http/Controllers` and `Models` folders as the app grows.
- **Typed and validated.** Use PHP type hints/return types and Form Request validation on every endpoint; use TypeScript or at least strict prop typing in Vue components where practical.
- **Tested.** Core business logic (grading computation, curriculum rules, rollover) needs unit/feature tests — these are exactly the areas most likely to silently break as the system scales.
- **Consistent API contract.** A single, predictable JSON response/error shape between Laravel and Vue, versioned (`/api/v1/...`) so the frontend and backend can evolve independently.

### Laravel project file structure (what you just generated)

A quick orientation to a fresh Laravel install, and where this project's module-specific code should live as it grows:

```
your-laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Thin — receive request, call a Service/Action, return a response
│   │   ├── Middleware/      # Auth, role checks, tenant scoping, etc.
│   │   └── Requests/        # Form Request classes — all validation lives here, not in controllers
│   ├── Models/               # Eloquent models — one per ERD entity (User, Enrollment, Grade...)
│   ├── Policies/              # RBAC — "can this user do X to this model" (ties into Module 1.1)
│   ├── Services/              # Business logic per module (e.g. GradingService, RolloverService)
│   ├── Providers/             # Service container bindings, event/listener registration
│   └── Console/                # Artisan commands (e.g. school-year rollover as a command)
├── bootstrap/                   # Framework bootstrap files — rarely touched directly
├── config/                       # All config as PHP arrays — env-driven, never hardcode secrets here
├── database/
│   ├── migrations/               # Schema as version-controlled code — mirrors the ERD in Section 4
│   ├── factories/                # Test data generators (great for seeding demo Sections/Students)
│   └── seeders/                  # Populate Roles, Tracks/Strands, demo School Year, etc.
├── resources/
│   ├── js/                        # Vue.js SPA lives here (components/, pages or views/, router, store)
│   └── views/                     # Blade templates — minimal if Vue handles the SPA, maybe just app.blade.php
├── routes/
│   ├── api.php                     # All endpoints the Vue SPA calls — this is the Laravel/Vue contract
│   └── web.php                     # Usually just the SPA entry point route
├── storage/                          # Logs, cached files, and uploaded content (learning materials, etc.)
├── tests/
│   ├── Feature/                       # End-to-end HTTP tests per module
│   └── Unit/                          # Isolated logic tests (e.g. grading formula edge cases)
└── .env                                 # Environment config — DB credentials, app key, mail/SMS drivers
```

**Key takeaway for this project:** as modules in Section 1 are implemented, group `Models`,
`Services`, `Requests`, and `Policies` by module/domain subfolder (e.g.
`app/Services/Grading/GradingService.php`, `app/Services/Attendance/AttendanceService.php`)
rather than letting `app/Models` and `app/Http/Controllers` become flat, hundred-file dumping
grounds — this is what "scalable and readable" means concretely as the codebase grows past a
handful of modules.

---

## 1. Core Modules

### 1.1 User & Role Management
📄 *Full module context: [`modules/01-user-role-management.md`](./modules/01-user-role-management.md)*

**Function:** Central identity system for Admins, Registrars, Teachers/Advisers, Students, Parents/Guardians, and Guidance Counselors. Handles authentication, role-based access control (RBAC), and profile data.

**Keep in mind:**
- One student may have multiple guardians; one guardian may have multiple children in the system.
- Teachers can be "subject teachers" and/or "advisers" (homeroom) — these are different permission scopes.
- Support account status: active, suspended, graduated, transferred-out.
- Plan for bulk import (CSV) at start-of-year enrollment.

### 1.2 Enrollment & Academic Structure
📄 *Full module context: [`modules/02-enrollment-academic-structure.md`](./modules/02-enrollment-academic-structure.md)*

**Function:** Manages Tracks (Academic, TVL, Sports, Arts & Design), Strands (STEM, ABM, HUMSS, GAS, etc.), Grade Levels (11/12), Sections, and Semesters (SHS runs on 2 semesters/year, not quarters).

**Keep in mind:**
- A student's subjects depend on their Track/Strand + Semester — build this as a rules-driven curriculum map, not hardcoded.
- Handle strand transfers mid-year (rare but happens).
- Track "specialized subjects" vs "core subjects" vs "applied subjects" since SHS curricula separate these.

### 1.3 Class & Scheduling
📄 *Full module context: [`modules/03-class-scheduling.md`](./modules/03-class-scheduling.md)*

**Function:** Assigns subjects to sections, teachers to subjects, and generates timetables. Handles room/resource allocation for lab-based strands (STEM, TVL).

**Keep in mind:**
- Conflict detection (teacher double-booked, room double-booked).
- Half-day/shifting schedules (common in public SHS with limited classrooms).
- Immersion/OJT scheduling for TVL and work-immersion requirements — this is a block schedule outside normal classes.

### 1.4 Attendance
📄 *Full module context: [`modules/04-attendance.md`](./modules/04-attendance.md)*

**Function:** Daily/per-subject attendance logging, absence/tardy tracking, and automated alerts to guardians.

**Keep in mind:**
- SHS often tracks attendance per subject period, not just once a day.
- Needs an audit trail (who logged/edited an entry and when) — this feeds into official DepEd forms.
- Excessive absence triggers should notify Guidance/Adviser automatically.

### 1.5 Content & Learning Materials
📄 *Full module context: [`modules/05-content-learning-materials.md`](./modules/05-content-learning-materials.md)*

**Function:** Upload/organize modules, videos, slides, and self-learning kits per subject; versioning of materials.

**Keep in mind:**
- Support offline-friendly formats (many SHS students have limited connectivity) — downloadable PDFs, low-bandwidth video links.
- Materials should be scoped by subject + strand + semester, not a flat file dump.
- Access control: draft vs published content.

### 1.6 Assignments, Quizzes & Assessments
📄 *Full module context: [`modules/06-assignments-quizzes-assessments.md`](./modules/06-assignments-quizzes-assessments.md)*

**Function:** Create/distribute tasks, auto-graded quizzes (MCQ, T/F), manually-graded essays/performance tasks, rubric-based scoring, submission tracking, plagiarism/late-submission flags.

**Keep in mind:**
- Philippine DepEd grading uses **Written Work, Performance Tasks, and Quarterly/Semestral Exams** with weighted percentages that differ by subject type (Academic Track vs TVL) — the grading engine must support configurable weight templates.
- Support rubric grading for performance-based/portfolio tasks (common in TVL and Arts & Design strands).

### 1.7 Grading & Report Cards
📄 *Full module context: [`modules/07-grading-report-cards.md`](./modules/07-grading-report-cards.md)*

**Function:** Computes final grades using weighted components, generates report cards (equivalent to DepEd SF9/Form 138), computes GPA, and generates official transcripts.

**Keep in mind:**
- Grade computation formulas must be configurable per subject/strand — don't hardcode a single formula.
- Historical grade locking (once a grading period closes, grades shouldn't be silently editable — require an override/audit log).
- Support grade appeals/correction workflow with approval trail.

### 1.8 Communication & Announcements
📄 *Full module context: [`modules/08-communication-announcements.md`](./modules/08-communication-announcements.md)*

**Function:** School-wide, section-wide, and subject-specific announcements; direct messaging between teachers/students/guardians; emergency broadcast (e.g., class suspension).

**Keep in mind:**
- Guardians should get a simplified/read-only channel, not full access to internal teacher discussions.
- Push/SMS/email fallback matters — not everyone checks the portal daily.

### 1.9 Calendar & Events
📄 *Full module context: [`modules/09-calendar-events.md`](./modules/09-calendar-events.md)*

**Function:** Academic calendar, exam schedules, holidays, deadlines, school events, immersion schedules.

**Keep in mind:**
- Should sync per-role (a student sees their own section's events; a teacher sees all sections they handle).

### 1.10 Guidance & Counseling
📄 *Full module context: [`modules/10-guidance-counseling.md`](./modules/10-guidance-counseling.md)*

**Function:** Tracks behavioral records, counseling session logs, career guidance notes (especially relevant since SHS is meant to prepare students for college/work/entrepreneurship).

**Keep in mind:**
- Highest sensitivity data in the system — restrict to Guidance role + Admin only, with strict audit logging.
- Should never be visible to regular subject teachers by default.

### 1.11 Library / Resource Management (optional but common)
📄 *Full module context: [`modules/11-library-resource-management.md`](./modules/11-library-resource-management.md)*

**Function:** Digital or physical library catalog, borrowing records, e-book/resource links tied to subjects.

### 1.12 Parent/Guardian Portal
📄 *Full module context: [`modules/12-parent-guardian-portal.md`](./modules/12-parent-guardian-portal.md)*

**Function:** View-only access to grades, attendance, announcements, and messaging with teachers.

**Keep in mind:**
- Must support guardians with multiple children — a single login should switch between wards.

### 1.13 Reports & Analytics
📄 *Full module context: [`modules/13-reports-analytics.md`](./modules/13-reports-analytics.md)*

**Function:** Dashboards for at-risk students (low grades/attendance), class performance analytics, DepEd-compliant exportable reports.

**Keep in mind:**
- Needs role-scoped dashboards (Admin sees school-wide, Teacher sees their own classes, Guardian sees their child only).

### 1.14 Notifications & Alerts
📄 *Full module context: [`modules/14-notifications-alerts.md`](./modules/14-notifications-alerts.md)*

**Function:** Centralized notification engine (in-app, email, SMS) for deadlines, grades posted, low attendance, announcements.

### 1.15 System Administration
📄 *Full module context: [`modules/15-system-administration.md`](./modules/15-system-administration.md)*

**Function:** School year setup/rollover, curriculum configuration, backup/audit logs, permission management.

**Keep in mind:**
- **School year rollover** is one of the trickiest features — promoting Grade 11 → Grade 12, archiving old sections, carrying forward only the right data (not duplicating). Design this as a first-class workflow, not an afterthought.

---

## 2. Cross-Cutting Considerations

- **Data privacy:** Student data (especially minors) requires strict compliance (e.g., Philippine Data Privacy Act / DPA 2012, or GDPR/FERPA equivalents if international). Encrypt sensitive fields, log all access to guidance/counseling records.
- **Multi-tenancy:** If this LMS will serve multiple schools, isolate data per school (tenant_id) from day one — retrofitting multi-tenancy later is painful.
- **Offline resilience:** Design APIs to tolerate spotty connections (queue submissions, retry uploads).
- **Scalability of grading rules:** Different strands/tracks have different weight formulas — model this as data (a "Grading Template" table), not code.
- **Auditability:** Grades, attendance, and guidance records all need "who changed what, when" trails — build this in from the start, it's expensive to bolt on later.
- **Accessibility:** Screen-reader support and low-bandwidth modes matter for equitable access.
- **Localization:** Support Filipino/English bilingual UI if targeting Philippine public schools.

---

## 3. Suggested Build Phases

1. **Phase 1 — Foundation:** User/Role management, Academic structure (Tracks/Strands/Sections/Subjects), Enrollment.
2. **Phase 2 — Daily Operations:** Scheduling, Attendance, Content/Materials.
3. **Phase 3 — Academics Core:** Assignments/Quizzes, Grading engine, Report Cards.
4. **Phase 4 — Engagement:** Communication, Notifications, Calendar, Parent Portal.
5. **Phase 5 — Oversight:** Guidance module, Reports/Analytics, Library.
6. **Phase 6 — Ops Hardening:** School year rollover, audit logs, backups, multi-tenancy (if needed).

---

## 4. Entity-Relationship Diagram (ERD)

```mermaid
erDiagram
    SCHOOL ||--o{ SCHOOL_YEAR : has
    SCHOOL_YEAR ||--o{ SEMESTER : has
    SCHOOL ||--o{ USER : employs_enrolls

    USER ||--o{ ROLE_ASSIGNMENT : has
    ROLE_ASSIGNMENT }o--|| ROLE : refers_to

    USER ||--o| STUDENT_PROFILE : extends
    USER ||--o| TEACHER_PROFILE : extends
    USER ||--o| GUARDIAN_PROFILE : extends

    GUARDIAN_PROFILE ||--o{ STUDENT_GUARDIAN : links
    STUDENT_PROFILE ||--o{ STUDENT_GUARDIAN : links

    TRACK ||--o{ STRAND : contains
    STRAND ||--o{ CURRICULUM_SUBJECT : defines
    SUBJECT ||--o{ CURRICULUM_SUBJECT : used_in
    SEMESTER ||--o{ CURRICULUM_SUBJECT : offered_in

    SECTION ||--o{ ENROLLMENT : contains
    STUDENT_PROFILE ||--o{ ENROLLMENT : has
    STRAND ||--o{ SECTION : grouped_by
    SCHOOL_YEAR ||--o{ SECTION : belongs_to

    SECTION ||--o{ CLASS_SCHEDULE : has
    SUBJECT ||--o{ CLASS_SCHEDULE : scheduled_as
    TEACHER_PROFILE ||--o{ CLASS_SCHEDULE : teaches
    ROOM ||--o{ CLASS_SCHEDULE : hosts

    CLASS_SCHEDULE ||--o{ ATTENDANCE_RECORD : generates
    STUDENT_PROFILE ||--o{ ATTENDANCE_RECORD : has

    CLASS_SCHEDULE ||--o{ ASSIGNMENT : has
    ASSIGNMENT ||--o{ SUBMISSION : receives
    STUDENT_PROFILE ||--o{ SUBMISSION : submits

    CLASS_SCHEDULE ||--o{ QUIZ : has
    QUIZ ||--o{ QUIZ_QUESTION : contains
    QUIZ ||--o{ QUIZ_ATTEMPT : has
    STUDENT_PROFILE ||--o{ QUIZ_ATTEMPT : takes

    GRADING_TEMPLATE ||--o{ SUBJECT : applied_to
    SUBMISSION ||--o| GRADE_COMPONENT : contributes_to
    QUIZ_ATTEMPT ||--o| GRADE_COMPONENT : contributes_to
    GRADE_COMPONENT }o--|| ENROLLMENT : belongs_to
    ENROLLMENT ||--o{ FINAL_GRADE : computes_to

    CLASS_SCHEDULE ||--o{ LEARNING_MATERIAL : has

    USER ||--o{ ANNOUNCEMENT : posts
    SECTION ||--o{ ANNOUNCEMENT : targeted_to

    USER ||--o{ MESSAGE : sends
    USER ||--o{ MESSAGE : receives

    STUDENT_PROFILE ||--o{ GUIDANCE_RECORD : has
    USER ||--o{ GUIDANCE_RECORD : logged_by

    USER ||--o{ NOTIFICATION : receives
    USER ||--o{ AUDIT_LOG : triggers

    SCHOOL {
        uuid id PK
        string name
        string address
    }
    SCHOOL_YEAR {
        uuid id PK
        uuid school_id FK
        string label
        date start_date
        date end_date
    }
    SEMESTER {
        uuid id PK
        uuid school_year_id FK
        int semester_number
        date start_date
        date end_date
    }
    USER {
        uuid id PK
        string full_name
        string email
        string password_hash
        string status
    }
    ROLE {
        uuid id PK
        string name
    }
    ROLE_ASSIGNMENT {
        uuid id PK
        uuid user_id FK
        uuid role_id FK
        uuid school_id FK
    }
    STUDENT_PROFILE {
        uuid id PK
        uuid user_id FK
        string lrn
        date birth_date
        int grade_level
    }
    TEACHER_PROFILE {
        uuid id PK
        uuid user_id FK
        string employee_id
        string specialization
    }
    GUARDIAN_PROFILE {
        uuid id PK
        uuid user_id FK
        string relationship
        string contact_number
    }
    STUDENT_GUARDIAN {
        uuid id PK
        uuid student_id FK
        uuid guardian_id FK
        boolean is_primary
    }
    TRACK {
        uuid id PK
        string name
    }
    STRAND {
        uuid id PK
        uuid track_id FK
        string name
    }
    SUBJECT {
        uuid id PK
        string name
        string subject_type
    }
    CURRICULUM_SUBJECT {
        uuid id PK
        uuid strand_id FK
        uuid subject_id FK
        uuid semester_id FK
        boolean is_core
    }
    SECTION {
        uuid id PK
        uuid strand_id FK
        uuid school_year_id FK
        string name
        int grade_level
        uuid adviser_id FK
    }
    ENROLLMENT {
        uuid id PK
        uuid student_id FK
        uuid section_id FK
        uuid semester_id FK
        string status
    }
    ROOM {
        uuid id PK
        string name
        string type
    }
    CLASS_SCHEDULE {
        uuid id PK
        uuid section_id FK
        uuid subject_id FK
        uuid teacher_id FK
        uuid room_id FK
        string day_of_week
        time start_time
        time end_time
    }
    ATTENDANCE_RECORD {
        uuid id PK
        uuid class_schedule_id FK
        uuid student_id FK
        date attendance_date
        string status
        uuid logged_by FK
    }
    ASSIGNMENT {
        uuid id PK
        uuid class_schedule_id FK
        string title
        text instructions
        datetime due_date
        int max_score
    }
    SUBMISSION {
        uuid id PK
        uuid assignment_id FK
        uuid student_id FK
        datetime submitted_at
        string file_url
        float score
        string status
    }
    QUIZ {
        uuid id PK
        uuid class_schedule_id FK
        string title
        int time_limit_minutes
    }
    QUIZ_QUESTION {
        uuid id PK
        uuid quiz_id FK
        text question_text
        string question_type
        json options
        string correct_answer
    }
    QUIZ_ATTEMPT {
        uuid id PK
        uuid quiz_id FK
        uuid student_id FK
        float score
        datetime started_at
        datetime submitted_at
    }
    GRADING_TEMPLATE {
        uuid id PK
        string name
        float written_work_weight
        float performance_task_weight
        float exam_weight
    }
    GRADE_COMPONENT {
        uuid id PK
        uuid enrollment_id FK
        string component_type
        float raw_score
        float max_score
    }
    FINAL_GRADE {
        uuid id PK
        uuid enrollment_id FK
        float final_rating
        string remarks
    }
    LEARNING_MATERIAL {
        uuid id PK
        uuid class_schedule_id FK
        string title
        string file_url
        string status
    }
    ANNOUNCEMENT {
        uuid id PK
        uuid posted_by FK
        uuid section_id FK
        string title
        text body
        datetime posted_at
    }
    MESSAGE {
        uuid id PK
        uuid sender_id FK
        uuid receiver_id FK
        text body
        datetime sent_at
    }
    GUIDANCE_RECORD {
        uuid id PK
        uuid student_id FK
        uuid logged_by FK
        string category
        text notes
        datetime created_at
    }
    NOTIFICATION {
        uuid id PK
        uuid user_id FK
        string type
        string message
        boolean is_read
    }
    AUDIT_LOG {
        uuid id PK
        uuid user_id FK
        string action
        string entity_type
        uuid entity_id
        datetime timestamp
    }
```

---

## 5. Notes on the ERD

- **CURRICULUM_SUBJECT** is the key rules table: it defines which subjects belong to which strand, in which semester — this drives auto-scheduling and grading templates.
- **GRADE_COMPONENT** is deliberately generic (component_type: "written_work" | "performance_task" | "exam") so the grading engine can sum/weight them per **GRADING_TEMPLATE** without hardcoding formulas.
- **AUDIT_LOG** is generic/polymorphic (entity_type + entity_id) so it can track changes across grades, attendance, and guidance records from one table.
- **STUDENT_GUARDIAN** is a join table because guardianship is many-to-many (blended families, multiple wards).
- Consider adding a **NOTIFICATION_PREFERENCE** table later if you want per-user control over email/SMS/in-app channels.

---

## 6. Module Flowcharts

Each diagram below shows the core operational flow of that module. All are Mermaid `flowchart` diagrams.

### 6.1 User & Role Management

```mermaid
flowchart TD
    A[Admin creates account or bulk-imports CSV] --> B{Role type?}
    B -->|Student| C[Create Student Profile]
    B -->|Teacher| D[Create Teacher Profile]
    B -->|Guardian| E[Create Guardian Profile + Link to Student]
    C --> F[Assign default role permissions]
    D --> F
    E --> F
    F --> G[Send account credentials/invite]
    G --> H[User logs in first time]
    H --> I{Password reset required?}
    I -->|Yes| J[Force password change]
    I -->|No| K[Access role-based dashboard]
    J --> K
```

### 6.2 Enrollment & Academic Structure

```mermaid
flowchart TD
    A[New school year opens] --> B[Admin configures Tracks/Strands/Semesters]
    B --> C[Define Curriculum Subjects per Strand/Semester]
    C --> D[Student applies/registers for SHS]
    D --> E{Track/Strand selected?}
    E -->|Yes| F[Validate prerequisites e.g. Grade 10 completion]
    E -->|No| G[Guidance counsels student on strand choice]
    G --> E
    F --> H[Assign student to Section]
    H --> I[Create Enrollment record for Semester]
    I --> J[Auto-populate subjects from Curriculum map]
    J --> K[Enrollment confirmed]
```

### 6.3 Class & Scheduling

```mermaid
flowchart TD
    A[Admin/Registrar opens scheduling tool] --> B[Select Section]
    B --> C[Assign Subjects to Section]
    C --> D[Assign Teacher per Subject]
    D --> E[Assign Room/Time slot]
    E --> F{Conflict detected?}
    F -->|Teacher double-booked| G[Reject & suggest alternate slot]
    F -->|Room double-booked| G
    F -->|No conflict| H[Save Class Schedule]
    G --> E
    H --> I[Publish timetable to students & teachers]
    I --> J[Sync to Calendar module]
```

### 6.4 Attendance

```mermaid
flowchart TD
    A[Class period starts] --> B[Teacher opens attendance sheet]
    B --> C[Mark each student: Present/Late/Absent/Excused]
    C --> D[Submit attendance record]
    D --> E[Log entry in Audit Log]
    E --> F{Student flagged for excessive absences?}
    F -->|Yes| G[Auto-notify Adviser & Guidance]
    F -->|No| H[Update attendance summary]
    G --> H
    H --> I[Guardian portal reflects updated attendance]
```

### 6.5 Content & Learning Materials

```mermaid
flowchart TD
    A[Teacher uploads material] --> B[Tag by Subject/Strand/Semester]
    B --> C{Status?}
    C -->|Draft| D[Visible only to teacher]
    C -->|Published| E[Visible to enrolled students]
    D --> F[Teacher reviews/edits]
    F --> C
    E --> G[Student accesses/downloads material]
    G --> H[System logs access for analytics]
```

### 6.6 Assignments, Quizzes & Assessments

```mermaid
flowchart TD
    A[Teacher creates Assignment or Quiz] --> B[Set due date, max score, rubric]
    B --> C[Publish to Class Schedule]
    C --> D[Student views task]
    D --> E{Task type?}
    E -->|Assignment| F[Student uploads submission]
    E -->|Quiz| G[Student takes timed quiz]
    F --> H{Late?}
    H -->|Yes| I[Flag as late, apply penalty rule]
    H -->|No| J[Mark on-time]
    G --> K[Auto-grade objective items]
    I --> L[Teacher manually grades/reviews]
    J --> L
    K --> M[Score sent to Grade Component]
    L --> M
```

### 6.7 Grading & Report Cards

```mermaid
flowchart TD
    A[Grade Components collected: Written Work, Performance Tasks, Exams] --> B[Apply Grading Template weights]
    B --> C[Compute weighted average per subject]
    C --> D{Grading period closed?}
    D -->|No| E[Grades remain editable]
    D -->|Yes| F[Lock grades, require override approval to edit]
    E --> C
    F --> G[Generate Final Grade per Enrollment]
    G --> H[Compile Report Card / Form 138 equivalent]
    H --> I[Publish to Student & Guardian portal]
    I --> J{Grade appeal filed?}
    J -->|Yes| K[Route to Teacher/Admin approval workflow]
    J -->|No| L[Grade finalized]
    K --> F
```

### 6.8 Communication & Announcements

```mermaid
flowchart TD
    A[User composes announcement or message] --> B{Audience scope?}
    B -->|School-wide| C[Admin publishes to all users]
    B -->|Section-wide| D[Teacher/Adviser publishes to section]
    B -->|Direct message| E[Send to specific user]
    C --> F[Trigger Notification]
    D --> F
    E --> F
    F --> G{Channel preference?}
    G -->|In-app only| H[Deliver in-app]
    G -->|Email/SMS fallback| I[Deliver via email/SMS + in-app]
```

### 6.9 Calendar & Events

```mermaid
flowchart TD
    A[Admin/Teacher creates event: exam, holiday, immersion, deadline] --> B[Set date/time & audience scope]
    B --> C{Scope?}
    C -->|School-wide| D[Add to master academic calendar]
    C -->|Section-specific| E[Add to section calendar]
    C -->|Subject-specific| F[Add to class schedule calendar]
    D --> G[Sync to all user dashboards per role]
    E --> G
    F --> G
    G --> H[Trigger reminder notifications as date approaches]
```

### 6.10 Guidance & Counseling

```mermaid
flowchart TD
    A[Referral trigger: low grades, poor attendance, teacher/self-referral] --> B[Guidance Counselor opens Guidance Record]
    B --> C[Log session notes & category]
    C --> D{Sensitive/restricted case?}
    D -->|Yes| E[Restrict visibility to Guidance + Admin only]
    D -->|No| F[Optional summary shared with Adviser]
    E --> G[Schedule follow-up session]
    F --> G
    G --> H[Track case status: open/monitoring/closed]
    H --> I[Audit log records every access]
```

### 6.11 Library / Resource Management

```mermaid
flowchart TD
    A[Librarian/Admin adds resource to catalog] --> B[Tag by subject/strand/type]
    B --> C{Resource type?}
    C -->|Physical| D[Track copies & shelf location]
    C -->|Digital| E[Attach file/link]
    D --> F[Student requests borrow]
    F --> G{Copy available?}
    G -->|Yes| H[Issue & set due date]
    G -->|No| I[Add to waitlist]
    H --> J[Track return / overdue status]
    E --> K[Student accesses directly online]
```

### 6.12 Parent/Guardian Portal

```mermaid
flowchart TD
    A[Guardian logs in] --> B{Multiple wards linked?}
    B -->|Yes| C[Select which student to view]
    B -->|No| D[Load single student dashboard]
    C --> D
    D --> E[View grades, attendance, announcements]
    E --> F{Wants to message teacher?}
    F -->|Yes| G[Send message via Communication module]
    F -->|No| H[Browse/close portal]
    G --> H
```

### 6.13 Reports & Analytics

```mermaid
flowchart TD
    A[User opens Analytics Dashboard] --> B{Role?}
    B -->|Admin| C[View school-wide performance & attendance trends]
    B -->|Teacher| D[View own class/section analytics]
    B -->|Guardian| E[View own child's performance only]
    C --> F[Identify at-risk students: low grades/attendance]
    D --> F
    F --> G[Auto-flag & notify Adviser/Guidance]
    G --> H[Export DepEd-compliant reports]
```

### 6.14 Notifications & Alerts

```mermaid
flowchart TD
    A[Trigger event occurs: grade posted, deadline near, low attendance, announcement] --> B[Notification Engine receives event]
    B --> C[Determine target user(s) by role/relationship]
    C --> D{User channel preference}
    D -->|In-app| E[Push in-app notification]
    D -->|Email| F[Send email]
    D -->|SMS| G[Send SMS]
    E --> H[Mark as read/unread, log in Notification table]
    F --> H
    G --> H
```

### 6.15 System Administration

```mermaid
flowchart TD
    A[Admin initiates School Year Rollover] --> B[Archive current School Year data]
    B --> C[Promote Grade 11 students to Grade 12]
    C --> D{Graduating Grade 12?}
    D -->|Yes| E[Mark as Graduated, generate final transcript]
    D -->|No| F[Re-enroll into next Section/Semester]
    E --> G[Deactivate active enrollment]
    F --> H[Carry forward only required records]
    G --> I[New School Year structure created]
    H --> I
    I --> J[Reset/reassign teacher & section assignments]
```

---

## 6.16 Current Implementation Status (Sept 2026)

*Verified directly against the codebase — controllers, models, migrations, routes, views, and
tests. See [`modules/README.md`](./README.md) for the full per-module table with file-level
detail; summary:*

| Status | Modules |
|---|---|
| ✅ Done | 16 (Student Dashboard) |
| 🚧 In progress (real backend/models exist, UI or scope incomplete) | 1, 2, 3, 5, 6, 8, 9, 13, 14 |
| ⏳ Not started (plan/migration only) | 4, 7, 10, 11, 12, 15 |

Notable findings from this pass:
- The Student Dashboard (Module 16) is the only module with a fully wired controller → service →
  view → test chain, and it accidentally created the first real Eloquent models for Modules 6, 8,
  and 9 (`Assignment`, `Quiz`, `QuizAttempt`, `Submission`, `Announcement`, `ScheduleEvent`) as a
  side effect — those tables existed in migrations but had zero models before.
- **Update:** Module 9 (Calendar & Events) has since grown its own controller → service → view →
  test chain (`CalendarController`, `CalendarEventService`, `StoreCalendarEventRequest`, a full
  FullCalendar UI, passing tests), and repaid the favor by adding `forSectionCalendar()` scopes
  to `Assignment`/`Quiz` (Module 6) plus a `quizzes.due_date` column, so assignment/quiz deadlines
  now render on the calendar. **Update (Oct 2026):** no longer student-only — `CalendarController`
  now branches by role, with `feedForAdmin()` (every school-wide event) and `feedForTeacher()`
  (personal events + assignment/quiz due dates across every section the teacher teaches, via new
  `forTeacherCalendar()` scopes) added alongside the original `feedFor()` for students. Real
  `admin/calendar` and `teacher/calendar` views exist. Dedicated test coverage for the teacher path
  is still thin.
- **Update:** Module 5 (Content & Learning Materials) also now has a full backend: `LearningMaterial`
  model with `forTeacher()`/`visibleToSection()` scopes, `TeacherMaterialController` (upload/edit/
  delete, owner-scoped), `StudentMaterialController` (Published-only, section-scoped, grouped by
  subject), and `MaterialDownloadController` sharing one `authorizeAccess()` gate for both a forced
  download (`show()`) and an inline browser preview (`preview()`, PDFs/video render without
  downloading). Files stay on the non-public `local` disk so drafts never get a guessable URL.
  `tests/Feature/TeacherMaterialTest.php` + `tests/Feature/StudentMaterialTest.php` pass. Real gaps
  against this module's original spec: no file versioning (edits overwrite in place), no
  offline-friendly-format handling, and no access-analytics logging.
- **Update (Oct 2026):** both live bugs noted in the original pass are now fixed — `Track.php`
  (Module 2) exists so `Strand::track()` resolves, and `Guardian.php` (Module 12) exists with
  `user()`/`students()` relations. Neither fix adds UI: Module 12 still has no portal routes,
  controller, or views at all.
- **Update (Oct 2026):** Module 3 (Class & Scheduling) grew a real `AdminScheduleController`
  (CRUD + section/teacher/room conflict detection), a real `Room` model, and teacher/student
  timetable views — no longer just the bare `Schedule` model. 3 `AdminScheduleTest` cases still
  fail (conflict-message precedence when two conflict types overlap; a 404-before-403 ordering
  issue on a bad schedule ID) — documented as known gaps in `modules/03-class-scheduling.md`,
  not yet fixed.
- **Update (Oct 2026):** Module 6 (Assignments, Quizzes & Assessments) — quizzes now have a full
  teacher/student UI (`TeacherQuizController` with CSV question import, `StudentQuizController`
  with take/resume/auto-score), matching what the assignments loop already had. Both Module 6
  (assignments) and Module 8 (announcements) gained a new, previously-undocumented threaded
  discussion/comments capability (`AssignmentComment`, `AnnouncementComment` — one level of
  replies, author/owner moderation), each with its own feature test.
- **Update (Oct 2026):** Module 8 (Communication & Announcements) gained a real posting UI —
  `AnnouncementController` (role-scoped CRUD, thumbnail + multi-file attachments) — closing the
  "no posting UI" gap from the original pass. Direct messaging (`Message` model) remains
  unbuilt.
- Self-service registration/password-reset (originally under Module 1) was removed from the
  codebase — account provisioning is now Admin-only.
- `routes/api.php` is empty; despite Section 0 describing a REST/JSON API, no module currently
  uses one — everything is session-based Blade.

---

## 7. Next Steps Checklist

- [ ] Finalize grading formula rules per DepEd/institution requirements
- [ ] Decide on multi-tenancy (single school vs SaaS for many schools)
- [x] Choose tech stack — **Laravel + Vue.js** (see Section 0); still decide DB engine and hosting.
      *Correction: implementation actually settled on Laravel + Blade + Alpine.js + ApexCharts,
      not Vue — see the Section 0 stack-correction note and §6.16 above.*
- [ ] Design wireframes for Student, Teacher, Guardian, Admin dashboards
- [ ] Set up role-based access control matrix (who can see/edit what)
- [ ] Plan data migration/import strategy for existing student records
- [ ] Define school year rollover process in detail before writing code
