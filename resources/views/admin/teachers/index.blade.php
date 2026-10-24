@extends('layouts.app')

@section('title', 'Manage Teachers')
@section('page-title', 'Teacher Management')
@section('page-subtitle', 'Manage faculty accounts, multi-tenant instructor isolation, and view class portfolios')

@section('content')
<div class="space-y-6" x-data="{ createTeacherModal: false }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Registered Instructors ({{ $teachers->count() }})</h2>
            <p class="text-xs text-slate-500">Each teacher manages their own isolated classes, students, and cash logs</p>
        </div>

        <button @click="createTeacherModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add New Instructor</span>
        </button>
    </div>

    <!-- Teachers Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teachers as $t)
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                            {{ strtoupper(substr($t->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $t->name }}</h3>
                            <p class="text-xs text-slate-400 font-mono">{{ $t->email }}</p>
                        </div>
                    </div>

                    <!-- Classes List -->
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
                                <div class="p-3 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">No classes assigned yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-semibold text-emerald-600">Total Revenue: LKR {{ number_format($t->payments()->sum('amount'), 2) }}</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono text-[10px]">ID: #{{ $t->id }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-12 text-center text-slate-500 border border-slate-200">
                No teachers registered yet.
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
                    <p class="text-xs text-slate-500 mt-0.5">Create a teacher user for isolated class management</p>
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
                    <input type="password" name="password" required placeholder="At least 6 characters"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm">
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
