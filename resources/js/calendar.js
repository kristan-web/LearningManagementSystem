// Student calendar page: mounts FullCalendar against the calendar.events
// JSON feed and wires the Alpine-driven create/edit/delete modals in
// shared/calendar/index.blade.php. Loaded only on that page (see @section('scripts')).
import axios from 'axios';

let calendar = null;

// Formats a JS Date as the local "YYYY-MM-DDTHH:mm" string <input type="datetime-local"> expects.
function toLocalDatetimeInput(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

window.initLmsCalendar = function initLmsCalendar(el, { eventsUrl, onDateSelect, onEventClick }) {
    calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        headerToolbar: { start: 'title', center: '', end: 'today prev,next' },
        height: 'auto',
        selectable: true,
        eventSources: [{ url: eventsUrl, method: 'GET' }],
        select(info) {
            const start = info.start;
            let end = info.end;

            if (info.allDay) {
                // FullCalendar's allDay end is exclusive (the day after the last
                // selected day). A single date/tile click selects exactly one day,
                // so collapse end back to start — only clicking-and-dragging across
                // multiple days should produce a different end date.
                const spanDays = Math.round((info.end - info.start) / 86400000);
                end = spanDays <= 1 ? start : new Date(info.end.getTime() - 86400000);
            }

            onDateSelect(toLocalDatetimeInput(start), toLocalDatetimeInput(end));
            calendar.unselect();
        },
        eventClick(info) {
            const link = info.event.extendedProps.meeting_link;
            if (link) {
                window.open(link, '_blank', 'noopener');
                return;
            }
            onEventClick(info);
        },
    });

    calendar.render();
};

window.submitLmsCalendarForm = async function submitLmsCalendarForm(data) {
    const payload = {
        title: data.form.title,
        description: data.form.description,
        start_datetime: data.form.start_datetime,
        end_datetime: data.form.end_datetime,
    };

    data.formError = null;

    try {
        if (data.mode === 'edit') {
            await axios.put(data.updateUrl.replace('__ID__', data.form.event_id), payload);
        } else {
            await axios.post(data.storeUrl, payload);
        }
    } catch (error) {
        const errors = error.response?.data?.errors;
        data.formError = errors ? Object.values(errors).flat().join(' ') : 'Something went wrong. Please try again.';
        return;
    }

    data.formOpen = false;
    calendar.refetchEvents();
};

window.deleteLmsCalendarEvent = async function deleteLmsCalendarEvent(data) {
    await axios.delete(data.destroyUrl.replace('__ID__', data.form.event_id));
    data.deleteOpen = false;
    calendar.refetchEvents();
};
