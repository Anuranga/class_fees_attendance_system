@extends('layouts.app')

@section('title', 'Manage Teachers & Assistants')
@section('page-title', 'Faculty & Staff Oversight')
@section('page-subtitle', 'Review and approve teacher registrations, control assistant accounts, and toggle Active, Inactive, or Blocked status')

@section('content')
<div class="space-y-6" x-data="{ createTeacherModal: false }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Faculty Management ({{ $teachers->count() }})</h2>
            <p class="text-xs text-slate-500">Each instructor manages their own isolated classes, teaching assistants, and cash flows</p>
        </div>

        <button @click="createTeacherModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add Instructor Manually</span>
        </button>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ route('admin.teachers.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>All Instructors</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ empty($statusFilter) ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
        </a>

        <a href="{{ route('admin.teachers.index', ['status' => 'pending']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-sm shadow-amber-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-user-clock text-amber-500 {{ $statusFilter === 'pending' ? 'text-white' : '' }}"></i>
            <span>Pending Approval</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'pending' ? 'bg-amber-700 text-white' : ($counts['pending'] > 0 ? 'bg-amber-100 text-amber-800 font-extrabold' : 'bg-slate-100 text-slate-600') }}">{{ $counts['pending'] }}</span>
        </a>

        <a href="{{ route('admin.teachers.index', ['status' => 'active']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-check text-emerald-500 {{ $statusFilter === 'active' ? 'text-white' : '' }}"></i>
            <span>Active</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'active' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['active'] }}</span>
        </a>

        <a href="{{ route('admin.teachers.index', ['status' => 'inactive']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'inactive' ? 'bg-slate-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-pause text-slate-400 {{ $statusFilter === 'inactive' ? 'text-white' : '' }}"></i>
            <span>Inactive</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'inactive' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['inactive'] }}</span>
        </a>

        <a href="{{ route('admin.teachers.index', ['status' => 'blocked']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ $statusFilter === 'blocked' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <i class="fa-solid fa-ban text-rose-500 {{ $statusFilter === 'blocked' ? 'text-white' : '' }}"></i>
            <span>Blocked</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $statusFilter === 'blocked' ? 'bg-rose-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['blocked'] }}</span>
        </a>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Teachers Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teachers as $t)
            <div class="rounded-2xl bg-white border {{ $t->status === 'pending' ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200/80' }} p-6 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <!-- Header with Avatar, Name & Status Badge -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl {{ $t->status === 'pending' ? 'bg-gradient-to-tr from-amber-500 to-orange-500' : ($t->status === 'blocked' ? 'bg-gradient-to-tr from-rose-500 to-red-600' : 'bg-gradient-to-tr from-indigo-500 to-purple-600') }} text-white flex items-center justify-center font-bold text-base shadow-xs">
                                {{ strtoupper(substr($t->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $t->name }}</h3>
                                <p class="text-xs text-slate-400 font-mono">{{ $t->email }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($t->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @elseif($t->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending Approval
                            </span>
                        @elseif($t->status === 'blocked')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Blocked
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                            </span>
                        @endif
                    </div>

                    <!-- Pending Approval Special Banner & Quick Action -->
                    @if($t->status === 'pending')
                        <div class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs">
                            <div class="flex items-center gap-2 font-bold mb-2 text-amber-800">
                                <i class="fa-solid fa-clock text-amber-600"></i>
                                <span>Awaiting Administrator Review</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.users.approve', $t) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Approve Account</span>
                                    </button>
                                </form>

                                <form action="{{ route('admin.users.status', $t) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="blocked">
                                    <button type="submit" 
                                            class="py-1.5 px-3 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold text-xs border border-rose-200 flex items-center gap-1 transition-all"
                                            title="Block this request">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>Block</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Status Action Dropdown / Controls -->
                    <div class="my-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <form action="{{ route('admin.users.status', $t) }}" method="POST" class="flex items-center justify-between gap-2">
                            @csrf
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Account Status:</label>
                            <div class="flex items-center gap-1.5">
                                <select name="status" onchange="this.form.submit()" 
                                        class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="active" {{ $t->status === 'active' ? 'selected' : '' }}>Active (Approved)</option>
                                    <option value="pending" {{ $t->status === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="inactive" {{ $t->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="blocked" {{ $t->status === 'blocked' ? 'selected' : '' }}>Temporary Block</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Assigned Classes -->
                    <div class="mt-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Assigned Classes ({{ $t->classes->count() }})
                        </p>
                        <div class="space-y-1.5">
                            @forelse($t->classes as $c)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-800">{{ $c->name }}</span>
                                    <span class="text-[11px] font-mono text-indigo-600 font-bold">{{ $c->enrollments_count }} students</span>
                                </div>
                            @empty
                                <div class="p-2.5 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">No classes created yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Teaching Assistants Section -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>Assistants ({{ $t->assistants->count() }})</span>
                            <i class="fa-solid fa-users-gear text-slate-400"></i>
                        </p>

                        <div class="space-y-2">
                            @forelse($t->assistants as $assistant)
                                <div class="p-2.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $assistant->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $assistant->email }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <form action="{{ route('admin.users.status', $assistant) }}" method="POST">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-[11px] px-2 py-0.5 rounded-lg border border-slate-200 bg-white font-semibold {{ $assistant->status === 'active' ? 'text-emerald-700' : ($assistant->status === 'blocked' ? 'text-rose-700' : 'text-amber-700') }}">
                                                <option value="active" {{ $assistant->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $assistant->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="blocked" {{ $assistant->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                            </select>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $assistant) }}" method="POST"
                                              onsubmit="return confirm('Delete this assistant account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-600 p-1" title="Delete assistant">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-2 text-center text-xs text-slate-400 bg-slate-50/50 rounded-xl">No assistants added by instructor.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Footer with Quick Action Buttons and Total Revenue -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-emerald-600">Revenue: LKR {{ number_format($t->payments()->sum('amount'), 2) }}</span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('public.teacher.pass', $t->qr_token) }}" target="_blank"
                           class="px-2.5 py-1 rounded-lg text-indigo-600 hover:bg-indigo-50 font-bold transition-colors flex items-center gap-1" 
                           title="View & Print Teacher ID Badge">
                            <i class="fa-solid fa-id-badge text-xs"></i>
                            <span>ID Badge</span>
                        </a>

                        @if($t->status === 'pending')
                            <form action="{{ route('admin.users.approve', $t) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-emerald-600 hover:bg-emerald-50 font-bold transition-colors flex items-center gap-1" title="Approve and activate account">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <span>Approve</span>
                                </button>
                            </form>
                        @elseif($t->status !== 'active')
                            <form action="{{ route('admin.users.status', $t) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-emerald-600 hover:bg-emerald-50 font-bold transition-colors flex items-center gap-1" title="Activate and unblock account">
                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                    <span>Activate</span>
                                </button>
                            </form>
                        @endif

                        @if($t->status !== 'blocked')
                            <form action="{{ route('admin.users.status', $t) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="blocked">
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-rose-500 hover:bg-rose-50 font-semibold transition-colors flex items-center gap-1" title="Temporarily block account">
                                    <i class="fa-solid fa-ban text-xs"></i>
                                    <span>Block</span>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.users.destroy', $t) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this instructor account and all their assigned classes?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 font-semibold transition-colors flex items-center gap-1" title="Delete instructor">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-12 text-center text-slate-500 border border-slate-200">
                <i class="fa-solid fa-chalkboard-user text-3xl text-slate-300 mb-3"></i>
                <p class="font-semibold text-slate-700">No teachers found{{ !empty($statusFilter) ? " with status '{$statusFilter}'" : '' }}.</p>
                @if(!empty($statusFilter))
                    <a href="{{ route('admin.teachers.index') }}" class="text-xs text-indigo-600 font-bold mt-2 inline-block hover:underline">
                        View All Instructors &rarr;
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Create Teacher Modal -->
    <div x-show="createTeacherModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="createTeacherModal = false" 
             class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add Instructor Account</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Create an active teacher user for isolated class management</p>
                </div>
                <button @click="createTeacherModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.teachers.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Dr. Richard Feynman"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Address *</label>
                    <input type="email" name="email" required placeholder="e.g. feynman@edutrack.test"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password *</label>
                    <input type="password" name="password" required minlength="6" placeholder="At least 6 characters"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Operational Permissions</span>
                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="can_collect_fees" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Can Collect & Issue Monthly School Fees</span>
                    </label>
                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="can_manage_uniforms" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Can Manage Pre-School Uniform Orders & Deliveries</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createTeacherModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">Create Teacher</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
