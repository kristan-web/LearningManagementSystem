{{-- Dashboard: Admin --}}
@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.css" />
    <style>
       /* Theme tokens: light mode (default) uses the landing/login navy palette (ink #16244f, brand #2f5fd0), html.dark switches to navy dark. */
       .admin-dashboard-content {
          --card-bg: linear-gradient(160deg, #ffffff 0%, #f7f9fe 100%);
          --card-border: #e3e8f4;
          --card-shadow: 0 1px 2px rgb(22 36 79 / 0.04), 0 8px 24px -12px rgb(22 36 79 / 0.16);
          --card-text: #16244f;
          --card-label: #1e46a8;
          --card-muted: #737c95;
          --card-grid: #e8ecf5;
          --fallback-bg: #f7f9fe;
          --fallback-border: #d5dcec;
       }
       html.dark .admin-dashboard-content {
          --card-bg: #0f1a38;
          --card-border: #24386f;
          --card-shadow: none;
          --card-text: #ffffff;
          --card-label: #a9bff0;
          --card-muted: #aab4cf;
          --card-grid: #24386f;
          --fallback-bg: rgba(15, 26, 56, 0.8);
          --fallback-border: #24386f;
       }

      .total_nam { color: #ffffff !important; font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; text-shadow: 2px 2px 4px #000; }
       .admin-bento-grid {
          display: grid;
          grid-template-columns: 1fr;
          gap: 0.75rem;
          width: 100%;
          max-width: 72rem;
          margin: 0 auto;
          padding: 0.75rem;
       }
       .admin-bento-card {
          display: flex;
          flex-direction: column;
          min-height: 20rem;
          padding: 1.25rem;
          overflow: visible;
          color: var(--card-text);
          background: var(--card-bg);
          border: 1px solid var(--card-border);
          border-radius: 1.25rem;
          box-shadow: var(--card-shadow);
          position: relative;
          transition: background .3s ease, border-color .3s ease, box-shadow .3s ease, transform .3s ease;
       }
       .admin-bento-card:hover { transform: translateY(-2px); }
       .admin-bento-card__label { color: var(--card-label); font-size: 0.875rem; font-weight: 500; }
       .admin-bento-card__title { margin: 0 0 0.25rem; font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; font-size: 1rem; font-weight: 700; }
       .admin-bento-card__description { margin: 0; color: var(--card-muted); font-size: 0.75rem; line-height: 1.2; }
       .admin-bento-card__total { margin-top: 0.5rem; font-size: 0.75rem; color: var(--card-muted); }

       /* Chart text/grid colors follow the theme (overrides the colors set in the chart options) */
       .admin-bento-card .apexcharts-text,
       .admin-bento-card .apexcharts-title-text { fill: var(--card-muted) !important; }
       .admin-bento-card .apexcharts-legend-text { color: var(--card-muted) !important; }
       .admin-bento-card .apexcharts-gridline,
       .admin-bento-card .apexcharts-xaxis-tick { stroke: var(--card-grid) !important; }
       .admin-bento-card .apexcharts-pie-area { stroke: var(--card-border) !important; }

       /* Image cards keep white text in both modes */
       .admin-subject-card .apexcharts-text,
       .admin-subject-card .apexcharts-title-text { fill: #fff !important; }
       .admin-subject-card .apexcharts-legend-text { color: #fff !important; }
    .admin-subject-card { background-image: url('/images/anya.jpeg'); background-position: center; background-size: cover; }
    .admin-teachers-card { background-image: url('/images/rara.jpeg'); background-position: center; background-size: cover; }
    .admin-subject-card .admin-bento-card__label,
    .admin-subject-card .admin-bento-card__title,
    .admin-subject-card .admin-bento-card__description,
    .admin-teachers-card .admin-bento-card__label,
    .admin-teachers-card .admin-bento-card__title,
    .admin-teachers-card .admin-bento-card__description { color: #fff; text-shadow: 0 2px 8px rgb(0 0 0 / 0.75); }
    #chart-students .apexcharts-tooltip,
    #chart-students .apexcharts-tooltip-title,
    #chart-enrollments .apexcharts-tooltip,
    #chart-enrollments .apexcharts-tooltip-title { color: #111827 !important; background: #fff !important; }
       .admin-bento-card__chart-wrap { min-height: 220px; width: 100%; position: relative; }
       .admin-bento-card__chart-wrap .apexcharts-canvas { width: 100% !important; }
    .admin-subject-count { position: absolute; top: 50%; left: 50%; z-index: 2; text-align: center; transform: translate(-50%, -50%); pointer-events: none; }
    .admin-subject-count__value { color: #fff; font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 700; line-height: 1; letter-spacing: 0; text-shadow: 0 0 24px rgb(47 95 208 / 0.55); }
    .admin-subject-count__label { margin-top: 0.5rem; color: #c4b5fd; font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; }
       .admin-bento-card__fallback { display: none; align-items: center; justify-content: center; flex-direction: column; gap: 0.5rem; color: var(--card-label); font-size: 0.85rem; text-align: center; min-height: 220px; background: var(--fallback-bg); border: 1px dashed var(--fallback-border); border-radius: 0.75rem; }
       .admin-bento-card__fallback svg { width: 40px; height: 40px; opacity: 0.4; }
       @media (min-width: 600px) { .admin-bento-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
       @media (min-width: 1024px) {
          .admin-bento-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
          .admin-bento-card:nth-child(3) { grid-column: span 2; grid-row: span 2; }
          .admin-bento-card:nth-child(4) { grid-column: 1 / span 2; grid-row: 2 / span 2; }
          .admin-bento-card:nth-child(6) { grid-column: 4; grid-row: 3; }
       }
               .chart-center {
            padding-top: 2rem;
            padding-bottom: 1rem;
            text-align: start;
            max-width: 72rem;
            margin: 0 auto 1rem auto;
            width: 100%;
        }
    </style>
@endsection

@section('content')
<div class="admin-dashboard-content">
        <div class="chart-center">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400"> Overview of the learning management system.</p>
        </div>
<div class="admin-bento-grid">

    <article class="admin-bento-card">
       <div class="admin-bento-card__label">Insights</div>
       <div><h2 class="admin-bento-card__title">Users by Role</h2><p class="admin-bento-card__description">Total users grouped by role</p></div>
       <div class="admin-bento-card__chart-wrap" id="chart-users-role">
          <div class="admin-bento-card__fallback" id="fallback-users-role"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>No user data available</span></div>
       </div>
       <div class="admin-bento-card__total">Total: {{ $usersByRole->sum('total') }} accounts</div>
    </article>

    <article class="admin-bento-card">
         <div class="admin-bento-card__label">Connectivity</div>
         <div><h2 class="admin-bento-card__title">Teachers by Specialization</h2><p class="admin-bento-card__description">Teacher distribution by specialization</p></div>
       <div class="admin-bento-card__chart-wrap" id="chart-attendance">
             <div class="admin-bento-card__fallback" id="fallback-attendance"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>No teacher data available</span></div>
       </div>
    <div class="admin-bento-card__total">Total teachers: {{ $teachersBySpecialization->sum('total') }}</div>
    </article>

    <article class="admin-bento-card">
       <div class="admin-bento-card__label">Teamwork</div>
       <div><h2 class="admin-bento-card__title">Student Demographics</h2><p class="admin-bento-card__description">Students by enrollment date and grade</p></div>
       <div class="admin-bento-card__chart-wrap" id="chart-students">
          <div class="admin-bento-card__fallback" id="fallback-students"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>No student data available</span></div>
       </div>
       <div class="admin-bento-card__total">Total students: {{ $studentsByGrade->sum('total') }}</div>
    </article>

    <article class="admin-bento-card">
       <div class="admin-bento-card__label">Efficiency</div>
       <div><h2 class="admin-bento-card__title">Enrollment Trends</h2><p class="admin-bento-card__description">By school year</p></div>
       <div class="admin-bento-card__chart-wrap" id="chart-enrollments">
          <div class="admin-bento-card__fallback" id="fallback-enrollments"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>No enrollment data available</span></div>
       </div>
       <div class="admin-bento-card__total">Total enrollments: {{ $enrollmentTrends->sum('total') }}</div>
    </article>

    <article class="admin-bento-card admin-teachers-card">
         <div class="admin-bento-card__label">LMS Content</div>
         <div><h2 class="admin-bento-card__title">Learning Materials</h2><p class="admin-bento-card__description">Manage modules, lessons, and digital resources</p></div>
    </article>

    <article class="admin-bento-card admin-subject-card">
       <div class="admin-bento-card__label">Protection</div>
       <div><h2 class="admin-bento-card__title">Subject Inventory</h2><p class="admin-bento-card__description">Total subjects by type</p></div>
         <div class="admin-subject-count" data-count-up="{{ $subjectCount }}" aria-label="{{ $subjectCount }} total subjects">
             <div>
                 <div class="admin-subject-count__value" data-count-up-value>0</div>
                 <div class="admin-subject-count__label total_nam">Total Subjects</div>
             </div>
         </div>
       <div class="admin-bento-card__chart-wrap" id="chart-subjects">
          <div class="admin-bento-card__fallback" id="fallback-subjects"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>No subject data available</span></div>
       </div>
    </article>

</div>
<x-admin.latest-users-table :users="$latestUsers" :showSearchFilter="false" :showActionsModal="false" :showActions="false" />
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        const usersByRoleData = {!! json_encode($usersByRole) !!};
        const teachersBySpecializationData = {!! json_encode($teachersBySpecialization) !!};
        const studentsByGradeData = {!! json_encode($studentsByGrade) !!};
        const studentsByStatusData = {!! json_encode($studentsByStatus) !!};
        const studentsByGenderData = {!! json_encode($studentsByGender) !!};
        const enrollmentBySemesterData = {!! json_encode($enrollmentBySemester) !!};
        const enrollmentByStatusData = {!! json_encode($enrollmentByStatus) !!};
        const enrollmentTrendsData = {!! json_encode($enrollmentTrends) !!};
        const studentsByCreatedAt = {!! json_encode($studentsByCreatedAt) !!};
        const subjectsByTypeData = {!! json_encode($subjectsByType) !!};
        // Landing/login palette: brand blue, navy, gold, deep orange, then lighter tints
        const chartColors = ['#2f5fd0','#16244f','#f4b301','#c85a08','#5b84e6','#16a34a','#8fb0f5','#ef4444'];
        const darkText = '#c4b5fd';

        document.addEventListener('DOMContentLoaded', function() {

            // CountUp-style reveal for the subject total when its card enters the viewport.
            document.querySelectorAll('[data-count-up]').forEach(function(counter) {
                const valueElement = counter.querySelector('[data-count-up-value]');
                const target = Number(counter.dataset.countUp);
                const duration = 1200;
                let hasStarted = false;

                const formatValue = function(value) {
                    return Math.round(value).toLocaleString('en-US');
                };

                const animate = function(timestamp) {
                    if (!counter.dataset.startTime) counter.dataset.startTime = timestamp;
                    const progress = Math.min((timestamp - Number(counter.dataset.startTime)) / duration, 1);
                    const easedProgress = 1 - Math.pow(1 - progress, 3);
                    valueElement.textContent = formatValue(target * easedProgress);

                    if (progress < 1) {
                        window.requestAnimationFrame(animate);
                    } else {
                        valueElement.textContent = formatValue(target);
                    }
                };

                const observer = new IntersectionObserver(function(entries) {
                    if (entries[0].isIntersecting && !hasStarted) {
                        hasStarted = true;
                        window.requestAnimationFrame(animate);
                        observer.disconnect();
                    }
                }, { threshold: 0.35 });

                observer.observe(counter);
            });

            // ===== CHART 1: Users by Role (Donut) =====
            (function() {
                const d = usersByRoleData;
                new ApexCharts(document.getElementById('chart-users-role'), {
                    chart: { type: 'donut', height: 220, width: '100%', fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                    labels: d.map(x => x.role),
                    series: d.map(x => x.total),
                    colors: chartColors,
                    legend: { position: 'bottom', labels: { colors: darkText, fontSize: '11px' } },
                    noData: { text: 'No user data', align: 'center', verticalAlign: 'middle', style: { color: '#c4b5fd', fontSize: '14px' } },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 220 } } }]
                }).render();
            })();

            // ===== CHART 2: Teachers by Specialization (Pie) =====
            (function() {
                const d = teachersBySpecializationData;
                new ApexCharts(document.getElementById('chart-attendance'), {
                    chart: { type: 'pie', height: 220, width: '100%', fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                    labels: d.map(x => x.specialization),
                    series: d.map(x => x.total),
                    colors: ['#2f5fd0', '#16244f', '#f4b301', '#c85a08'],
                    legend: { position: 'bottom', labels: { colors: darkText, fontSize: '11px' } },
                    noData: { text: 'No teacher data', align: 'center', verticalAlign: 'middle', style: { color: '#c4b5fd', fontSize: '14px' } },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 220 } } }]
                }).render();
            })();

            // ===== CHART 3: Students Bar (X: created_at, Y: grade_level) =====
            (function() {
                const d = studentsByCreatedAt;
                const periods = [...new Set(d.map(x => x.period))].sort();
                const gradeLevels = [...new Set(d.map(x => x.grade_level))].sort();
                const gradeColors = ['#2f5fd0', '#f4b301'];
                const series = gradeLevels.map(function(grade, i) {
                    return {
                        name: 'Grade ' + grade,
                        data: periods.map(function(p) {
                            const match = d.find(function(x) { return x.period === p && x.grade_level === grade; });
                            return match ? match.total : 0;
                        }),
                        color: gradeColors[i % gradeColors.length]
                    };
                });
                new ApexCharts(document.getElementById('chart-students'), {
                    chart: { type: 'bar', height: 200, fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                    tooltip: { theme: 'light' },
                    plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '40%' } },
                    xaxis: { categories: periods, labels: { style: { colors: darkText } }, axisBorder: { show: false }, axisTicks: { color: '#2f293a' } },
                    yaxis: { labels: { style: { colors: darkText } } },
                    colors: gradeColors,
                    series: series,
                    legend: { position: 'bottom', labels: { colors: darkText, fontSize: '11px' } },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 200 } } }]
                }).render();
            })();

            // ===== CHART 4: Enrollments Area Line (X: school_year) =====
            (function() {
                const d = enrollmentTrendsData;
                const labels = d.map(x => x.school_year);
                const data = d.map(x => x.total);
                new ApexCharts(document.getElementById('chart-enrollments'), {
                    chart: { type: 'area', height: 200, fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                    tooltip: { theme: 'light' },
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: { categories: labels, labels: { style: { colors: darkText } }, axisBorder: { show: false }, axisTicks: { color: '#2f293a' } },
                    yaxis: { labels: { style: { colors: darkText } } },
                    colors: ['#2f5fd0'],
                    series: [{ name: 'Enrollments', data: data }],
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                    legend: { position: 'bottom', labels: { colors: darkText, fontSize: '11px' } },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 200 } } }]
                }).render();
            })();

            // ===== CHART 5: Subjects Horizontal Bar =====
            (function() {
                const d = subjectsByTypeData;
                const total = d.reduce(function(a, b) { return a + b.total; }, 0);
                new ApexCharts(document.getElementById('chart-subjects'), {
                    chart: { type: 'bar', height: 200, fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 6, horizontal: true, columnWidth: '50%' } },
                    xaxis: { categories: d.map(x => x.subject_type), labels: { style: { colors: darkText } }, axisBorder: { show: false }, axisTicks: { color: '#2f293a' } },
                    yaxis: { labels: { style: { colors: darkText } } },
                    colors: ['#2f5fd0', '#f4b301', '#16244f'],
                    legend: { position: 'bottom', labels: { colors: darkText, fontSize: '11px' } },
                    title: { text: 'Total: ' + total + ' subjects', align: 'center', style: { color: darkText, fontSize: '12px' } },
                    noData: { text: 'No subject data', align: 'center', verticalAlign: 'middle', style: { color: '#c4b5fd', fontSize: '14px' } },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 200 } } }]
                }).render();
            })();

        });
    </script>
@endsection
