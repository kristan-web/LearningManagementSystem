{{-- Admin: Manage Users --}}
@extends('layouts.admin')
@section('title', 'User Management')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.css" />
    <style>
        /* Theme tokens: light mode (default) uses the navy palette (ink #16244f) for text; html.dark switches to dark. */
        .charts-container {
            --card-bg: linear-gradient(160deg, #ffffff 0%, #f0f7ff 100%);
            --card-border: #dbeafe;
            --card-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 8px 24px -12px rgb(37 99 235 / 0.18);
            --card-text: #16244f;
            --card-muted: #737c95;
            --card-grid: #e2e8f0;
        }
        html.dark .charts-container {
            --card-bg: #1f2937;
            --card-border: #374151;
            --card-shadow: none;
            --card-text: #f9fafb;
            --card-muted: #9ca3af;
            --card-grid: #374151;
        }
        .charts-container {
            max-width: 72rem;
            margin: 0 auto 1rem auto;
            display: flex;
            gap: 1rem;
            width: 100%;
        }
        .chart-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            padding: 1rem;
            color: var(--card-text);
            transition: background .3s ease, border-color .3s ease, box-shadow .3s ease;
        }
        .chart-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--card-text);
        }
        .chart-card .chart-subtitle {
            margin: 0 0 0.75rem 0;
            font-size: 0.75rem;
            color: var(--card-muted);
        }
        /* Chart text/grid colors follow the theme (overrides the colors set in the chart options) */
        .chart-card .apexcharts-text { fill: var(--card-muted) !important; }
        .chart-card .apexcharts-legend-text { color: var(--card-muted) !important; }
        .chart-card .apexcharts-gridline,
        .chart-card .apexcharts-xaxis-tick { stroke: var(--card-grid) !important; }
        .chart-card .apexcharts-pie-area { stroke: var(--card-border) !important; }
        .chart-bar { flex: 2; }
        .chart-pie { flex: 1; }
        .chart-bar .apexcharts-canvas,
        .chart-pie .apexcharts-canvas {
            width: 100% !important;
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
    <div class="space-y-6">
        <div class="chart-center">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-bold text-ink dark:text-white">User Management</h1>
                    <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">All registered users in the learning management system.</p>
                </div>
                <a href="{{ route('admin.users.create') }}" class="btn-navy">
                    <svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20"><path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" /></svg>
                     Add Account
                </a>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-card chart-bar">
                <h3>Users by Role</h3>
                <p class="chart-subtitle">Total users per role</p>
                <div id="chart-users-role"></div>
            </div>
            <div class="chart-card chart-pie">
                <h3>Users by Status</h3>
                <p class="chart-subtitle">Distribution by status</p>
                <div id="chart-users-status"></div>
            </div>
        </div>

        <x-admin.latest-users-table :users="$users" />
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        const usersByRoleData = {!! json_encode($usersByRole) !!};
        const usersByStatusData = {!! json_encode($usersByStatus) !!};

        document.addEventListener('DOMContentLoaded', function() {
            // Chart 1: Users by Role (Bar chart)
            const roleLabels = usersByRoleData.map(x => x.role);
            const roleCounts = usersByRoleData.map(x => x.total);

            new ApexCharts(document.getElementById('chart-users-role'), {
                chart: { type: 'bar', height: 250, fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                xaxis: { categories: roleLabels, labels: { style: { colors: '#c4b5fd', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { color: '#374151' } },
                yaxis: { labels: { style: { colors: '#c4b5fd', fontSize: '11px' } } },
                colors: ['#2f5fd0'],
                series: [{ name: 'Users', data: roleCounts }],
                legend: { show: false },
                tooltip: { theme: 'dark' },
                responsive: [{ breakpoint: 640, options: { chart: { height: 200 } } }]
            }).render();

            // Chart 2: Users by Status (Pie chart)
            const statusLabels = usersByStatusData.map(x => x.status);
            const statusCounts = usersByStatusData.map(x => x.total);

            new ApexCharts(document.getElementById('chart-users-status'), {
                chart: { type: 'pie', height: 250, fontFamily: 'Plus Jakarta Sans', background: 'transparent', toolbar: { show: false } },
                labels: statusLabels,
                series: statusCounts,
                colors: ['#10b981', '#f59e0b', '#ef4444', '#2f5fd0', '#16244f'],
                legend: { position: 'bottom', labels: { colors: '#c4b5fd', fontSize: '11px' } },
                noData: { text: 'No status data', align: 'center', style: { color: '#c4b5fd', fontSize: '14px' } },
                responsive: [{ breakpoint: 640, options: { chart: { height: 200 } } }]
            }).render();
        });
    </script>
@endsection
