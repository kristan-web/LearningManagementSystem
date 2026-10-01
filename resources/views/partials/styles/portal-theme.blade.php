{{--
    Partial: Portal Theme (student + teacher)
    Puts the student and teacher pages on the admin navy palette (ink #16244f, brand #2f5fd0, gold #f4b301).
    Scoped to body.lms-portal (set by layouts.student and layouts.teacher), so the shared bento partial and
    components keep their own look elsewhere.
--}}
<style>
    /* Bento cards: same tokens as the admin dashboard cards */
    .lms-portal .bento-content {
        --card-bg: linear-gradient(160deg, #ffffff 0%, #f7f9fe 100%);
        --card-border: #e3e8f4;
        --card-shadow: 0 1px 2px rgb(22 36 79 / 0.04), 0 8px 24px -12px rgb(22 36 79 / 0.16);
        --card-text: #16244f;
        --card-label: #1e46a8;
        --card-muted: #737c95;
        --card-hover-border: #c9d6f5;
    }
    html.dark .lms-portal .bento-content {
        --card-bg: #0f1a38;
        --card-border: #24386f;
        --card-shadow: none;
        --card-text: #ffffff;
        --card-label: #a9bff0;
        --card-muted: #aab4cf;
        --card-hover-border: #3a52a0;
    }
    .lms-portal .bento-header { padding-top: 2rem; padding-bottom: 0.5rem; }
    .lms-portal .bento-card { position: relative; overflow: hidden; }
    .lms-portal .bento-card:hover { border-color: var(--card-hover-border); box-shadow: 0 1px 2px rgb(22 36 79 / 0.05), 0 14px 32px -14px rgb(22 36 79 / 0.28); }
    /* Full-width card (tables, forms): spans every grid column and does not lift on hover */
    .lms-portal .bento-card--full { grid-column: 1 / -1; }
    .lms-portal .bento-card--full:hover, .lms-portal .bento-card--static:hover { transform: none; }
    .lms-portal .bento-card__label { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
    .lms-portal .bento-card__title { margin-top: 0.25rem; font-family: var(--font-display); font-weight: 700; letter-spacing: -0.01em; }
    .lms-portal .bento-card__value { font-family: var(--font-display); letter-spacing: -0.02em; }
    .lms-portal .bento-card__thumb {
        background: linear-gradient(150deg, hsl(var(--thumb-hue, 225) 55% 52%), #16244f 85%);
        box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.2), 0 3px 8px -2px rgb(22 36 79 / 0.45);
    }
    /* Gold hairline on clickable cards, same accent as .btn-navy */
    .lms-portal a.bento-card::after {
        content: ""; position: absolute; inset: auto 30% 0; height: 2px; border-radius: 2px;
        background: linear-gradient(90deg, transparent, #f4b301, transparent);
        opacity: 0; transition: opacity .25s ease, inset .3s ease;
    }
    .lms-portal a.bento-card:hover::after { opacity: 1; inset: auto 10% 0; }

    /* Icon tile used in card headers and stat cards */
    .st-icon {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; color: #fff;
        background: linear-gradient(150deg, #3a52a0, #16244f 78%);
        box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.2), 0 3px 8px -2px rgb(22 36 79 / 0.45);
    }
    .st-icon svg { width: 1.25rem; height: 1.25rem; }

    /* Tables: separated row cards, same look as the admin user table */
    .st-table-wrap { width: 100%; overflow-x: auto; }
    .st-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; text-align: left; font-size: 0.875rem; color: #455072; }
    .st-table thead th {
        padding: 0.5rem 1rem 0.125rem; color: #737c95; white-space: nowrap;
        font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
    }
    .st-table tbody td {
        padding: 0.875rem 1rem; vertical-align: top; background: #fff;
        border-top: 1px solid #e3e8f4; border-bottom: 1px solid #e3e8f4;
        transition: background .2s, border-color .2s;
    }
    .st-table tbody td:first-child { border-left: 1px solid #e3e8f4; border-radius: 0.75rem 0 0 0.75rem; }
    .st-table tbody td:last-child { border-right: 1px solid #e3e8f4; border-radius: 0 0.75rem 0.75rem 0; }
    .st-table tbody td:only-child { border-radius: 0.75rem; }
    .st-table tbody tr:hover td { background: #f5f7fd; border-color: #c9d6f5; }
    .st-table .st-table__empty { padding: 2.5rem 1rem; text-align: center; color: #737c95; border-style: dashed; }
    .st-table tbody tr:hover .st-table__empty { background: #fff; border-color: #e3e8f4; }
    html.dark .st-table { color: #cbd5e1; }
    html.dark .st-table thead th { color: #94a3b8; }
    html.dark .st-table tbody td { background: rgb(15 23 42 / 0.45); border-color: #334155; }
    html.dark .st-table tbody tr:hover td { background: rgb(59 130 246 / 0.1); border-color: rgb(96 165 250 / 0.4); }
    html.dark .st-table tbody tr:hover .st-table__empty { background: rgb(15 23 42 / 0.45); border-color: #334155; }

    /* Compact .btn-navy for table rows */
    .btn-navy.btn-sm { min-height: 2rem; padding: 0.375rem 0.875rem; border-radius: 0.625rem; font-size: 0.75rem; }

    /* Secondary button (Cancel / Back / Preview) matching the admin pages */
    .st-btn-outline {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        min-height: 2.625rem; padding: 0.625rem 1.25rem; border: 1px solid #d5dcec; border-radius: 0.75rem;
        background: #fff; color: #455072; font-size: 0.875rem; font-weight: 600; white-space: nowrap;
        transition: background-color .15s, color .15s, border-color .15s;
    }
    .st-btn-outline:hover { background: #f0f4fd; border-color: #c9d6f5; color: #1e46a8; }
    .st-btn-outline.btn-sm { min-height: 2rem; padding: 0.375rem 0.875rem; border-radius: 0.625rem; font-size: 0.75rem; }
    html.dark .st-btn-outline { background: #1e293b; border-color: #475569; color: #e2e8f0; }
    html.dark .st-btn-outline:hover { background: rgb(59 130 246 / 0.12); border-color: rgb(96 165 250 / 0.4); color: #fff; }

    /* File input matching the admin support form */
    .st-file {
        display: block; max-width: 100%; font-size: 0.75rem; color: #737c95;
    }
    .st-file::file-selector-button {
        margin-right: 0.625rem; padding: 0.4375rem 0.75rem; border: 0; border-radius: 0.625rem; cursor: pointer;
        background: #eef3fd; color: #1e46a8; font-weight: 600; transition: background-color .15s;
    }
    .st-file:hover::file-selector-button { background: #dbe5fb; }
    html.dark .st-file { color: #94a3b8; }
    html.dark .st-file::file-selector-button { background: rgb(59 130 246 / 0.15); color: #93c5fd; }
</style>
