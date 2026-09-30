# Module 8: Communication & Announcements

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
School-wide, section-wide, and subject-specific announcements; direct messaging between teachers/students/guardians; emergency broadcast (e.g., class suspension).

**Keep in mind:**
- Guardians should get a simplified/read-only channel, not full access to internal teacher discussions.
- Push/SMS/email fallback matters — not everyone checks the portal daily.

---

## Data Model (relevant slice of the master ERD)

```mermaid
erDiagram
    USER ||--o{ ANNOUNCEMENT : posts
    SECTION ||--o{ ANNOUNCEMENT : targeted_to
    USER ||--o{ MESSAGE : sends
    USER ||--o{ MESSAGE : receives

    USER {
        uuid id PK
        string full_name
        string email
        string password_hash
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

    SECTION {
        uuid id PK
        uuid strand_id FK
        uuid school_year_id FK
        string name
        int grade_level
        uuid adviser_id FK
    }
```

---

## Module Flow

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

---

## Cross-Cutting Concerns That Apply Here

- **Offline resilience:** Design APIs to tolerate spotty connections (queue submissions, retry uploads).
- **Localization:** Support Filipino/English bilingual UI if targeting Philippine public schools.

---

## Build Phase

**Phase 4 — Engagement** (see the master plan's Suggested Build Phases for the full sequence).

---

## Implementation Notes

Every announcement/message should raise a NOTIFICATION event (module 14) rather than pushing directly — keep delivery channels decoupled from content creation.

---

## Implementation Status

*(verified against the codebase, Sept 2026 — see `modules/README.md` for the project-wide table)*

**Done:**
- `Announcement` model with `visibleToSection()` scope (school-wide when `section_id IS NULL`, or scoped to a given section) — built as a Module 16 dependency and covered by `tests/Feature/StudentDashboardTest.php`. `shared/announcements/index.blade.php` renders a real, working list via `StudentDashboardController`.
- **`AnnouncementController`** now exists — full index/store/destroy CRUD at `/announcements`, with role-scoped visibility (students see their section's + school-wide announcements; teachers see their own posts, the sections they teach, and school-wide; admins see everything) and enforcement that a teacher can only post to a section they actually teach. Supports a thumbnail plus multiple file attachments via the new `AnnouncementAttachment` model, streamed back through private-disk download/thumbnail routes. Covered by `tests/Feature/AnnouncementTest.php`.
- **New capability — Announcement discussion/comments:** `AnnouncementComment` model (one level of threaded replies, same pattern as Module 6's assignment comments) plus `AnnouncementCommentController` (store/destroy). A comment can be deleted by its author, the announcement's poster, or an admin. Rendered via `partials/announcement-comment.blade.php` / `partials/announcement-discussion.blade.php`. Covered by `tests/Feature/AnnouncementDiscussionTest.php`.

**Not started:**
- No `Message` model and no direct-messaging feature — `shared/messages/index.blade.php`, `teacher/communication/index.blade.php`, and `student/communication/index.blade.php` are all unwired placeholder views.
- No broadcast/notification hookup when an announcement is posted (see Module 14 — not yet event-driven).

---

## Related Modules

- [Module 1: User & Role Management](./01-user-role-management.md)
- [Module 12: Parent/Guardian Portal](./12-parent-guardian-portal.md)
- [Module 14: Notifications & Alerts](./14-notifications-alerts.md)
