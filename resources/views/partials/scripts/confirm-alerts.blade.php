{{--
    Partial: SweetAlert2 confirmations on the navy theme (loaded by layouts.student and layouts.admin)

    Usage: add data-confirm to any <form> or <a>; the action runs only after the user confirms.
        <form data-confirm="Submit this assignment?"            title
              data-confirm-text="You can only submit once."     optional body text
              data-confirm-button="Yes, submit"                 optional confirm label
              data-confirm-icon="question|warning"              optional, default question
              data-confirm-danger>                              optional, red confirm button
    window.LmsSwal is the themed instance for Alpine code (see student/calendar).
--}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
    /* Popup on the admin navy theme: card gradient, Space Grotesk title, .btn-navy confirm button */
    .swal2-container.swal2-backdrop-show { background: rgb(22 36 79 / 0.45); backdrop-filter: blur(4px); }
    .swal2-popup.lms-swal {
        width: min(26rem, calc(100% - 2rem)); padding: 1.75rem 1.5rem 1.5rem; border: 1px solid #e3e8f4; border-radius: 1.25rem;
        background: linear-gradient(160deg, #ffffff 0%, #f7f9fe 100%); color: #16244f;
        box-shadow: 0 24px 60px -12px rgb(22 36 79 / 0.3); font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    }
    .swal2-popup.lms-swal::after { /* gold hairline, same accent as .btn-navy */
        content: ""; position: absolute; inset: auto 35% 0; height: 3px; border-radius: 3px 3px 0 0;
        background: linear-gradient(90deg, transparent, #f4b301, transparent);
    }
    .lms-swal .swal2-title { padding: 0.75rem 0 0; color: #16244f; font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; font-size: 1.375rem; font-weight: 700; letter-spacing: -0.02em; }
    .lms-swal .swal2-html-container { margin: 0.5rem 0 0; padding: 0; color: #737c95; font-size: 0.875rem; line-height: 1.5; }
    .lms-swal .swal2-actions { gap: 0.75rem; margin: 1.5rem 0 0; }
    .lms-swal .swal2-actions button { margin: 0; }
    .lms-swal .swal2-actions button:focus-visible { outline: none; box-shadow: 0 0 0 3px #fff, 0 0 0 5px rgb(47 95 208 / 0.55); }
    html.dark .lms-swal .swal2-actions button:focus-visible { box-shadow: 0 0 0 3px #0f1a38, 0 0 0 5px rgb(143 176 245 / 0.7); }
    .lms-swal .swal2-icon { margin: 0.25rem auto 0; transform: scale(0.85); }
    .lms-swal .swal2-icon.swal2-question { border-color: #c9d6f5; color: #2f5fd0; }
    .lms-swal .swal2-icon.swal2-warning { border-color: #f4b301; color: #c85a08; }

    /* Cancel: outlined, same as the admin/student secondary buttons */
    .lms-swal-cancel {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        min-height: 2.625rem; padding: 0.625rem 1.25rem; border: 1px solid #d5dcec; border-radius: 0.75rem;
        background: #fff; color: #455072; font-size: 0.875rem; font-weight: 600; cursor: pointer;
        transition: background-color .15s, color .15s, border-color .15s;
    }
    .lms-swal-cancel:hover { background: #f0f4fd; border-color: #c9d6f5; color: #1e46a8; }
    html.dark .lms-swal-cancel { background: #1e293b; border-color: #475569; color: #e2e8f0; }
    html.dark .lms-swal-cancel:hover { background: rgb(59 130 246 / 0.12); border-color: rgb(96 165 250 / 0.4); color: #fff; }

    /* Danger confirm (delete): same shape as .btn-navy, red gradient */
    .lms-swal-danger {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        min-height: 2.625rem; padding: 0.625rem 1.25rem; border: 1px solid #991b1b; border-radius: 0.75rem;
        background: linear-gradient(180deg, rgb(255 255 255 / 0.14), rgb(255 255 255 / 0) 55%), linear-gradient(150deg, #ef4444, #b91c1c 78%);
        color: #fff; font-size: 0.875rem; font-weight: 700; cursor: pointer; text-shadow: 0 1px 0 rgb(0 0 0 / 0.2);
        box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.22), 0 8px 20px -8px rgb(185 28 28 / 0.55);
        transition: transform .2s ease, filter .2s ease;
    }
    .lms-swal-danger:hover { transform: translateY(-1px); filter: brightness(1.08); }

    html.dark .swal2-popup.lms-swal { background: #0f1a38; border-color: #24386f; color: #fff; box-shadow: 0 24px 60px -12px rgb(0 0 0 / 0.7); }
    html.dark .lms-swal .swal2-title { color: #fff; }
    html.dark .lms-swal .swal2-html-container { color: #aab4cf; }
    html.dark .lms-swal .swal2-icon.swal2-question { border-color: rgb(143 176 245 / 0.45); color: #8fb0f5; }
    html.dark .lms-swal .swal2-icon.swal2-warning { color: #f4b301; }
    html.dark .swal2-container.swal2-backdrop-show { background: rgb(2 6 23 / 0.7); }
</style>

<script>
    (() => {
        const classes = (danger) => ({ popup: 'lms-swal', confirmButton: danger ? 'lms-swal-danger' : 'btn-navy', cancelButton: 'lms-swal-cancel' });

        const LmsSwal = Swal.mixin({
            buttonsStyling: false,
            reverseButtons: true,
            focusCancel: true,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            customClass: classes(false),
        });
        LmsSwal.classes = classes;
        window.LmsSwal = LmsSwal;

        const ask = (el) => LmsSwal.fire({
            title: el.dataset.confirm,
            text: el.dataset.confirmText || '',
            icon: el.dataset.confirmIcon || 'question',
            confirmButtonText: el.dataset.confirmButton || 'Yes, continue',
            customClass: classes(el.hasAttribute('data-confirm-danger')),
        }).then((result) => result.isConfirmed);

        // Forms: browser validation has already passed when "submit" fires; form.submit() skips this handler.
        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('form[data-confirm]');
            if (!form) return;
            event.preventDefault();
            if (await ask(form)) form.submit();
        });

        // Links (e.g. starting a quiz)
        document.addEventListener('click', async (event) => {
            const link = event.target.closest('a[data-confirm]');
            if (!link || event.ctrlKey || event.metaKey || event.shiftKey) return;
            event.preventDefault();
            if (await ask(link)) window.location.href = link.href;
        });

        // Shared comment partials use onsubmit="return confirm('...')"; swap that for the themed dialog.
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
                const message = (form.getAttribute('onsubmit').match(/confirm\((['"])(.*?)\1\)/) || [])[2] || 'Are you sure?';
                form.removeAttribute('onsubmit');
                form.dataset.confirm = message;
                form.dataset.confirmIcon = 'warning';
                form.dataset.confirmButton = 'Delete';
                form.setAttribute('data-confirm-danger', '');
            });
        });
    })();
</script>
