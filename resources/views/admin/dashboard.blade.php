@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Administration & Global Analytics')
@section('page-subtitle', 'Global oversight across all teachers, classes, enrollments, and cash collections')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Teachers</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalTeachers }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <a href="{{ route('admin.teachers.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Manage Teachers &rarr;</a>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Classes</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalClasses }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 text-cyan-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $totalEnrollments }} Total Enrollments</span>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Registered Students</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalStudents }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <a href="{{ route('admin.students.index') }}" class="text-purple-600 hover:text-purple-800 font-semibold">View Directory &rarr;</a>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">All-Time Cash Revenue</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-2">LKR {{ number_format($allTimeRevenue, 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <span class="text-emerald-700 font-semibold">{{ $currentMonthName }}: LKR {{ number_format($currentMonthRevenue, 2) }}</span>
            </div>
        </div>

    </div>

    <!-- System Revenue Trend Chart -->
    <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Institute Gross Cash Flow (Past 6 Months)</h2>
                <p class="text-xs text-slate-500">Aggregated monthly fees across all academic departments</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i> System-Wide
            </span>
        </div>

        <div class="h-72 w-full">
            <canvas id="adminRevenueChart"></canvas>
        </div>
    </div>

    <!-- Teacher Oversight Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Instructor Performance & Multi-Tenant Directory</h2>
                <p class="text-xs text-slate-500">Overview of teachers, class rosters, and monthly collections</p>
            </div>
            <a href="{{ route('admin.teachers.index') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all shadow-xs">
                Manage Instructors
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Instructor</th>
                        <th class="py-3 px-4">Active Classes</th>
                        <th class="py-3 px-4">Total Students</th>
                        <th class="py-3 px-4">{{ $currentMonthName }} Cash</th>
                        <th class="py-3 px-4">All-Time Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $t)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        {{ strtoupper(substr($t->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $t->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $t->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $t->classes_count }} {{ Str::plural('Class', $t->classes_count) }}
                            </td>

                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $t->total_students }} {{ Str::plural('Student', $t->total_students) }}
                            </td>

                            <td class="py-3.5 px-4 font-bold text-emerald-700 font-mono">
                                LKR {{ number_format($t->current_month_revenue, 2) }}
                            </td>

                            <td class="py-3.5 px-4 font-black text-slate-900 font-mono">
                                LKR {{ number_format($t->total_revenue, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No teachers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('adminRevenueChart');
    if (ctx && window.Chart) {
        new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($monthsLabels),
                datasets: [
                    {
                        type: 'line',
                        label: 'Total Revenue (LKR)',
                        data: @json($monthlyRevenues),
                        borderColor: '#059669',
                        borderWidth: 3,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: false
                    },
                    {
                        type: 'bar',
                        label: 'Cash Inflow (LKR)',
                        data: @json($monthlyRevenues),
                        backgroundColor: 'rgba(16, 185, 129, 0.25)',
                        borderColor: 'rgba(16, 185, 129, 0.8)',
                        borderWidth: 1,
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': LKR ' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
