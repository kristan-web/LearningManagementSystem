# Module 12: Parent/Guardian Portal

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
View-only access to grades, attendance, announcements, and messaging with teachers.

**Keep in mind:**
- Must support guardians with multiple children — a single login should switch between wards.

---

## Data Model (relevant slice of the master ERD)

```mermaid
erDiagram
    GUARDIAN_PROFILE ||--o{ STUDENT_GUARDIAN : links
    STUDENT_PROFILE ||--o{ STUDENT_GUARDIAN : links

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

    STUDENT_PROFILE {
        uuid id PK
        uuid user_id FK
        string lrn
        date birth_date
        int grade_level
    }
```

---

## Module Flow

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

---

## Cross-Cutting Concerns That Apply Here

- **Data privacy:** Student data (especially minors) requires strict compliance (e.g., Philippine Data Privacy Act / DPA 2012, or GDPR/FERPA equivalents if international). Encrypt sensitive fields, log all access to guidance/counseling records.
- **Accessibility:** Screen-reader support and low-bandwidth modes matter for equitable access.

---

## Build Phase

**Phase 4 — Engagement** (see the master plan's Suggested Build Phases for the full sequence).

---

## Implementation Notes

This module is mostly a read-only composite view over Grading (7), Attendance (4), Communication (8), and Reports (13) — avoid duplicating their data, query through them.

---

## Implementation Status

*(verified against the codebase, Sept 2026 — see `modules/README.md` for the project-wide table)*

**Done:**
- **Fixed (Oct 2026):** `Guardian.php` now exists in `app/Models/` (`guardian_id`, `user_id`, `full_name`, `relationship`, `contact_number`, `user()` belongsTo, `students()` hasMany via `students.guardian_id`) — closes the "no Eloquent model" gap previously noted here. See `docs/spec/batch-a-model-fixes.md` (decision M12-03) and `tests/Feature/TrackGuardianModelTest.php`.

**Not started:**
- No guardian portal routes, controller, or views of any kind.
- No multi-ward (one guardian, multiple children) switching UI.
- The `student_guardians` many-to-many table referenced in Module 1 remains unused by design — `Guardian::students()` uses the single `students.guardian_id` FK instead (documented as a deferred decision in `docs/spec/batch-a-model-fixes.md`).

---

## Related Modules

- [Module 1: User & Role Management](./01-user-role-management.md)
- [Module 7: Grading & Report Cards](./07-grading-report-cards.md)
- [Module 4: Attendance](./04-attendance.md)
- [Module 8: Communication & Announcements](./08-communication-announcements.md)
- [Module 13: Reports & Analytics](./13-reports-analytics.md)
