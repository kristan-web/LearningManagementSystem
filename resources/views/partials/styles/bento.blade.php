{{--
    Partial: Bento Card Styles
    Shared "bento grid" card look (gradient background, dark-mode aware) used on
    dashboard-style pages. Extracted from the admin dashboard so student pages can
    reuse the same visual language without duplicating the CSS.

    Usage:
        @section('styles')
            @include('partials.styles.bento')
        @endsection

        <div class="bento-content">
            <div class="bento-header">
                <h1>...</h1>
            </div>
            <div class="bento-grid">
                <article class="bento-card">
                    <div class="bento-card__label">Label</div>
                    <h2 class="bento-card__title">Title</h2>
                    <p class="bento-card__description">Description</p>
                    <div class="bento-card__value">{{ $value }}</div>
                </article>
            </div>
        </div>
--}}
<style>
    /* Theme tokens: light mode (default), html.dark switches to dark. */
    .bento-content {
        --card-bg: linear-gradient(160deg, #ffffff 0%, #f0f7ff 100%);
        --card-border: #dbeafe;
        --card-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 8px 24px -12px rgb(37 99 235 / 0.18);
        --card-text: #0f172a;
        --card-label: #2563eb;
        --card-muted: #64748b;
    }
    html.dark .bento-content {
        --card-bg: #120f17;
        --card-border: #2f293a;
        --card-shadow: none;
        --card-text: #ffffff;
        --card-label: #c4b5fd;
        --card-muted: #c4bdca;
    }

    .bento-header {
        padding-top: 1rem;
        padding-bottom: 0.5rem;
        max-width: 72rem;
        margin: 0 auto;
        width: 100%;
    }

    .bento-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        width: 100%;
        max-width: 72rem;
        margin: 0 auto;
        padding: 0.75rem;
    }
    .bento-card {
        display: flex;
        flex-direction: column;
        min-height: 10rem;
        padding: 1.25rem;
        color: var(--card-text);
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1.25rem;
        box-shadow: var(--card-shadow);
        text-decoration: none;
        transition: background .3s ease, border-color .3s ease, box-shadow .3s ease, transform .3s ease;
    }
    .bento-card:hover { transform: translateY(-2px); }
    .bento-card__thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        margin-bottom: 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: #fff;
        background: hsl(var(--thumb-hue, 210) 65% 45%);
        flex-shrink: 0;
    }
    .bento-card__label { color: var(--card-label); font-size: 0.875rem; font-weight: 500; }
    .bento-card__title { margin: 0 0 0.25rem; font-size: 1rem; font-weight: 600; }
    .bento-card__description { margin: 0; color: var(--card-muted); font-size: 0.75rem; line-height: 1.2; }
    .bento-card__value { margin-top: auto; padding-top: 0.75rem; font-size: 2.25rem; font-weight: 700; color: var(--card-text); }
    .bento-card__total { margin-top: 0.5rem; font-size: 0.75rem; color: var(--card-muted); }
    .bento-card--span-2 { grid-column: span 1; }

    @media (min-width: 600px) { .bento-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) {
        .bento-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bento-card--span-2 { grid-column: span 2; }
    }
</style>
