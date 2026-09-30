# Module 3: Class & Scheduling

## System Overview

This file is one module out of a set of module-context files for the **Senior High School LMS**
— a Learning Management System for Grades 11–12 under the Philippine K-12 Tracks/Strands
curriculum (Academic, TVL, Sports, Arts & Design tracks; STEM, ABM, HUMSS, GAS, etc. strands),
adaptable to any SHS setup.

**Tech stack (as planned):** Laravel (backend/API) + Vue.js (frontend SPA). *Correction: the actual codebase never adopted Vue — it is server-rendered Laravel Blade + Alpine.js + ApexCharts (see `package.json`; no Vue dependency exists). See Module 16 and `modules/README.md` for real implementation status.* See **Section 0 — Tech Stack** in
[`senior-high-school-lms-plan.md`](../senior-high-school-lms-plan.md) for the full stack decision,
the strict scalability/readability rule that governs all code in this project, and an explanation
of the Laravel file structure.

**Full plan:** [`senior-high-school-lms-plan.md`](../senior-high-school-lms-plan.md) contains
the complete module list, the full ERD, all module flowcharts, cross-cutting considerations,
and build phases. This file extracts and expands only what's relevant to this one module so it
can be handed to a developer (or an AI coding assistant) as a self-contained brief.

---

## Function
Assigns subjects to sections, teachers to subjects, and generates timetables. Handles room/resource allocation for lab-based strands (STEM, TVL).

**Keep in mind:**
- Conflict detection (teacher double-booked, room double-booked).
- Half-day/shifting schedules (common in public SHS with limited classrooms).
- Immersion/OJT scheduling for TVL and work-immersion requirements — this is a block schedule outside normal classes.

---

## Data Model (relevant slice of the master ERD)

```mermaid
erDiagram
    SECTION ||--o{ CLASS_SCHEDULE : has
    SUBJECT ||--o{ CLASS_SCHEDULE : scheduled_as
    TEACHER_PROFILE ||--o{ CLASS_SCHEDULE : teaches
    ROOM ||--o{ CLASS_SCHEDULE : hosts

    SECTION {
        uuid id PK
        uuid strand_id FK
        uuid school_year_id FK
        string name
        int grade_level
        uuid adviser_id FK
    }

    SUBJECT {
        uuid id PK
        string name
        string subject_type
    }

    TEACHER_PROFILE {
        uuid id PK
        uuid user_id FK
        string employee_id
        string specialization
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
```

---

## Module Flow

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

---

## Cross-Cutting Concerns That Apply Here

- **Offline resilience:** Design APIs to tolerate spotty connections (queue submissions, retry uploads).
- **Auditability:** Grades, attendance, and guidance records all need "who changed what, when" trails — build this in from the start, it's expensive to bolt on later.

---

## Build Phase

**Phase 2 — Daily Operations** (see the master plan's Suggested Build Phases for the full sequence).

---

## Implementation Notes

CLASS_SCHEDULE is the pivot table nearly every operational module (Attendance, Materials, Assignments, Quizzes) hangs off of — get its constraints right early.

---

## Implementation Status

*(verified against the codebase, Sept 2026 — see `modules/README.md` for the project-wide table)*

**Done:**
- `Schedule` model (`schedules` table) with `section()`, `subject()`, `teacher()`, `assignments()`, `quizzes()` relations — built as supporting infrastructure for Module 16 (Student Dashboard), directly reusable for this module's own purpose.
- `Room` model (`room_id`, `room_name`, `building`, `capacity`, `schedules()` relation) — no longer raw-table-only.
- `AdminScheduleController`: full CRUD for `/admin/schedule` (index/store/update/destroy) — assigns section + subject + teacher + optional room to a day/time slot. Overlap-based **conflict detection** rejects a save if the section, the teacher, or the room is already booked for an overlapping time on the same day. Deleting a schedule period is blocked while it has linked assignments/quizzes. Wired to a real `admin/schedule/index.blade.php` view with a Room dropdown (rooms are managed inline through this form — there is still no standalone Room CRUD screen).
- `TeacherScheduleController` (`/teacher/schedule`) and `StudentScheduleController` (`/student/schedule`): real, day-of-week-ordered weekly timetable views, scoped to the logged-in teacher's own periods / the student's active-enrollment section respectively. Wired to real views (no longer stubs).
- Tests: `tests/Feature/AdminScheduleTest.php`, `tests/Feature/TeacherScheduleTest.php`, `tests/Feature/StudentScheduleTest.php`.

**Known test gaps (not yet fixed):**
- 3 of `AdminScheduleTest`'s cases currently fail:
  - Two conflict-message-precedence cases expect a "Room is already booked" message, but `detectConflict()` checks section-conflict before room-conflict, so when a test setup produces both, the section message wins instead — the save is still correctly blocked, just with a different message than the test asserts.
  - One authorization-ordering case: `PUT /admin/schedule/{id}` for a non-existent schedule ID hits Laravel route-model-binding (404) before the controller's own `abort_unless(...,403)` role check runs, so a non-admin with a bad ID gets 404 instead of the expected 403.

**Not started:**
- No immersion/OJT block-schedule support.

---

## Related Modules

- [Module 2: Enrollment & Academic Structure](./02-enrollment-academic-structure.md)
- [Module 4: Attendance](./04-attendance.md)
- [Module 9: Calendar & Events](./09-calendar-events.md)
