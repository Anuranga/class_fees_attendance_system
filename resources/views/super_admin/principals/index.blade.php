@extends('layouts.app')

@section('title', 'Super Admin - Pre-School Principals')
@section('page-title', 'Super Admin Oversight (මධ්‍යම පරිපාලන පද්ධතිය)')
@section('page-subtitle', 'Review, approve, and manage registered Pre-School Principal accounts, and inspect campus networks.')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Pre-School Principals & Campuses ({{ $counts['all'] }})</h2>
            <p class="text-xs text-slate-500">Each approved Principal manages their own isolated pre-school, classes, teachers, and admissions.</p>
        </div>

        <button @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-school-flag"></i>
            <span>Register Pre-School Principal Manually</span>
        </button>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-school"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Pre-Schools</p>
                <p class="text-xl font-extrabold text-slate-900">{{ $totalSchools }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Pending Approvals</p>
                <p class="text-xl font-extrabold text-amber-600">{{ $counts['pending'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Active Principals</p>
                <p class="text-xl font-extrabold text-emerald-600">{{ $counts['active'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Network Faculty</p>
                <p class="text-xl font-extrabold text-purple-600">{{ $totalTeachers }}</p>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ route('super_admin.principals.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>All Principals</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ empty($statusFilter) ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
        </a>

        <a href="{{ route('super_admin.principals.index', ['status' => 'pending']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-sm shadow-amber-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-clock text-amber-500 {{ $statusFilter === 'pending' ? 'text-white' : '' }}"></i>
            <span>Pending Review</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'pending' ? 'bg-amber-700 text-white' : ($counts['pending'] > 0 ? 'bg-amber-100 text-amber-800 font-extrabold' : 'bg-slate-100 text-slate-600') }}">{{ $counts['pending'] }}</span>
        </a>

        <a href="{{ route('super_admin.principals.index', ['status' => 'active']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-check text-emerald-500 {{ $statusFilter === 'active' ? 'text-white' : '' }}"></i>
            <span>Active</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'active' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['active'] }}</span>
        </a>

        <a href="{{ route('super_admin.principals.index', ['status' => 'blocked']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'blocked' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-ban text-rose-500 {{ $statusFilter === 'blocked' ? 'text-white' : '' }}"></i>
            <span>Blocked</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'blocked' ? 'bg-rose-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['blocked'] }}</span>
        </a>
    </div>

    <!-- Principals & Pre-Schools Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3">Pre-School & Identity</th>
                        <th class="px-4 py-3">Principal / Administrator</th>
                        <th class="px-4 py-3">Classes & Staff</th>
                        <th class="px-4 py-3">Registered Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($principals as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl gradient-brand text-white flex items-center justify-center text-sm font-bold shadow-xs shrink-0">
                                        <i class="fa-solid fa-school"></i>
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-slate-900 text-xs">{{ $p->school?->name ?: 'Unassigned Pre-School' }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $p->school?->slug ?: 'No slug' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-bold text-slate-800">{{ $p->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $p->email }}</p>
                                @if($p->school?->contact_phone)
                                    <p class="text-[10px] text-emerald-600"><i class="fa-brands fa-whatsapp"></i> {{ $p->school->contact_phone }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $p->school?->classes_count ?: 0 }} Classes
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 ml-1">
                                    {{ $p->school?->staff_count ?: 0 }} Teachers
                                </span>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                {{ $p->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($p->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check"></i> Active
                                    </span>
                                @elseif($p->status === 'blocked')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-ban"></i> Blocked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clock"></i> Pending Approval
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($p->status !== 'active')
                                        <form action="{{ route('super_admin.principals.approve', $p) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] shadow-xs flex items-center gap-1 transition-all"
                                                    title="Approve and activate preschool principal">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Approve</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($p->status === 'active')
                                        <form action="{{ route('super_admin.principals.block', $p) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] border border-rose-200 flex items-center gap-1 transition-all"
                                                    title="Temporarily block principal">
                                                <i class="fa-solid fa-ban"></i>
                                                <span>Block</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($p->status === 'blocked')
                                        <form action="{{ route('super_admin.principals.activate', $p) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold text-[11px] border border-indigo-200 flex items-center gap-1 transition-all"
                                                    title="Reactivate principal account">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                                <span>Activate</span>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('super_admin.principals.destroy', $p) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to completely delete this principal and preschool account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete principal">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-school text-3xl mb-2 text-slate-300 block"></i>
                                No preschool principals found for the selected status.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($principals->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $principals->links() }}
            </div>
        @endif
    </div>

    <!-- Create Principal & Pre-School Modal -->
    <div x-show="createModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="createModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-school-flag text-indigo-600"></i>
                    <span>Register New Pre-School Principal Directly</span>
                </h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('super_admin.principals.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pre-School Name (පෙර පාසලේ නම) *</label>
                    <input type="text" name="preschool_name" required placeholder="e.g. Global International Pre-School"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Principal Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Mrs. Sunethra Perera"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Official WhatsApp / Phone</label>
                        <input type="text" name="contact_phone" placeholder="0771234567"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address (Login) *</label>
                        <input type="email" name="email" required placeholder="principal@school.lk"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                        <input type="password" name="password" required placeholder="Min 6 characters"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>This account will be created as <strong>Active</strong> immediately and granted Principal privileges.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Create & Activate Pre-School
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
