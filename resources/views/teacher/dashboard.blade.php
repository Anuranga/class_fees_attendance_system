@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher Analytics & Dashboard')
@section('page-subtitle', 'Welcome back, ' . $teacher->name . ' - Academic and Financial Overview')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Active Classes -->
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Classes</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalClasses }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                <a href="{{ route('teacher.classes.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                    <span>Manage all classes</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Total Enrolled Students -->
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Students</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalEnrollments }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 text-cyan-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-shield-check"></i>
                    <span>Enrolled across {{ $totalClasses }} classes</span>
                </span>
            </div>
        </div>

        <!-- Monthly Cash Revenue -->
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $currentMonthName }} Revenue</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">LKR {{ number_format($currentMonthRevenue, 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <span class="text-slate-600">{{ $paidCount }} Students Paid</span>
                <a href="{{ route('teacher.fees.index') }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">View Ledger &rarr;</a>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Today's Attendance</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $todayAttendanceCount }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1 text-purple-600 font-semibold">
                    <i class="fa-solid fa-camera"></i>
                    <span>Scanned via QR Code</span>
                </span>
            </div>
        </div>

    </div>

    <!-- Quick Action Launchpad -->
    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-indigo-500/30 p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-lg font-bold">Fast Class Scanner & Fee Collection</h2>
            <p class="text-xs text-slate-300 mt-1">Select an active class below to launch the camera scanner or log immediate cash receipts.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($classes->isNotEmpty())
                <a href="{{ route('teacher.attendance.show', $classes->first()) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-camera"></i>
                    <span>Launch Scanner ({{ $classes->first()->name }})</span>
                </a>
            @endif
            <a href="{{ route('teacher.fees.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-receipt text-emerald-400"></i>
                <span>Log Cash Payment</span>
            </a>
            <a href="{{ route('teacher.classes.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-plus text-indigo-400"></i>
                <span>Add New Class</span>
            </a>
        </div>
    </div>

    <!-- CHARTS SECTION (Chart.js Integration) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart 1: Monthly Cash Collection Trends (Past 6 Months) -->
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Monthly Cash Collection Trends</h2>
                        <p class="text-xs text-slate-500">Gross revenue collected over the past 6 billing cycles</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <i class="fa-solid fa-chart-simple mr-1"></i> Cash Flow
                    </span>
                </div>
                <div class="h-72 w-full">
                    <canvas id="monthlyCollectionChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Active Teacher Multi-Tenant Revenue Isolation</span>
                <span class="font-semibold text-slate-700">Currency: LKR</span>
            </div>
        </div>

        <!-- Chart 2: Paid vs Unpaid Student Ratio for Active Month -->
        <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $currentMonthName }} Payment Ratio</h2>
                        <p class="text-xs text-slate-500">Paid vs Pending student fees</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Active Cycle
                    </span>
                </div>
                <div class="h-60 w-full relative flex items-center justify-center">
                    <canvas id="paidUnpaidChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-center">
                <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase">Paid Students</span>
                    <p class="text-xl font-black text-emerald-900 mt-0.5">{{ $paidCount }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-100">
                    <span class="text-[11px] font-bold text-amber-700 uppercase">Pending Fees</span>
                    <p class="text-xl font-black text-amber-900 mt-0.5">{{ $unpaidCount }}</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Chart 3: Weekly Attendance Rate Percentage Per Class -->
    <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">Weekly Attendance Rate (%) Per Class</h2>
                <p class="text-xs text-slate-500">Daily present/late student turnout percentage over the last 7 calendar days</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fa-solid fa-qrcode text-xs"></i> Camera Scans Active
                </span>
            </div>
        </div>

        <div class="h-72 w-full">
            <canvas id="weeklyAttendanceChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Payments -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Recent Cash Collections</h2>
                    <p class="text-xs text-slate-500">Latest fee receipts issued</p>
                </div>
                <a href="{{ route('teacher.fees.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View All &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100 overflow-x-auto">
                @forelse($recentPayments as $payment)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $payment->student->full_name }}</h3>
                                <p class="text-xs text-slate-500">{{ $payment->classRoom->name }} &bull; <span class="font-mono text-indigo-600">{{ $payment->receipt_no }}</span></p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-extrabold text-emerald-600">+LKR {{ number_format($payment->amount, 2) }}</p>
                            <p class="text-[11px] text-slate-400">{{ $payment->paid_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">No cash collections recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Attendance Scans Today -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Today's Check-In Activity</h2>
                    <p class="text-xs text-slate-500">Students scanned at session entry</p>
                </div>
                <span class="text-xs font-semibold text-purple-600">{{ $todayAttendanceCount }} checked-in today</span>
            </div>

            <div class="divide-y divide-slate-100 overflow-x-auto">
                @forelse($recentAttendances as $att)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $att->student->full_name }}</h3>
                                <p class="text-xs text-slate-500">{{ $att->classRoom->name }} &bull; <span class="font-mono text-slate-400">{{ $att->student->admission_number }}</span></p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $att->status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ ucfirst($att->status) }}
                            </span>
                            <p class="text-[11px] text-slate-400 mt-1 font-mono">{{ Carbon\Carbon::parse($att->check_in_time)->format('h:i:s A') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">No students scanned yet today. Launch the QR Scanner to mark attendance.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Monthly Collections Chart (Line + Bar Hybrid)
    const ctxMonthly = document.getElementById('monthlyCollectionChart');
    if (ctxMonthly && window.Chart) {
        new window.Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: @json($monthsLabels),
                datasets: [
                    {
                        type: 'line',
                        label: 'Revenue Trend (LKR)',
                        data: @json($monthlyCollections),
                        borderColor: '#4f46e5',
                        borderWidth: 3,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: false
                    },
                    {
                        type: 'bar',
                        label: 'Cash Collected (LKR)',
                        data: @json($monthlyCollections),
                        backgroundColor: 'rgba(99, 102, 241, 0.25)',
                        borderColor: 'rgba(99, 102, 241, 0.8)',
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

    // 2. Paid vs Unpaid Ratio (Doughnut Chart)
    const ctxPaidUnpaid = document.getElementById('paidUnpaidChart');
    if (ctxPaidUnpaid && window.Chart) {
        new window.Chart(ctxPaidUnpaid, {
            type: 'doughnut',
            data: {
                labels: ['Paid Fees', 'Pending / Unpaid'],
                datasets: [{
                    data: [{{ $paidCount }}, {{ $unpaidCount }}],
                    backgroundColor: ['#10b981', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } } }
                }
            }
        });
    }

    // 3. Weekly Attendance Rate Per Class (Line Chart)
    const ctxWeekly = document.getElementById('weeklyAttendanceChart');
    if (ctxWeekly && window.Chart) {
        new window.Chart(ctxWeekly, {
            type: 'line',
            data: {
                labels: @json($weekLabels),
                datasets: @json($attendanceDatasets)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '% Turnout';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            callback: function(value) { return value + '%'; },
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        }
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
