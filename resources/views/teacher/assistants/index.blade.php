@extends('layouts.app')

@section('title', 'Teaching Assistants')
@section('page-title', 'Teaching Assistants')
@section('page-subtitle', 'Manage your team of assistants who can mark attendance, distribute tutes, and collect class fees')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Your Assistants ({{ $assistants->count() }})</h2>
            <p class="text-xs text-slate-500">Assistants can scan attendance, verify tute distribution, and log fee payments for your classes.</p>
        </div>

        <button @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add New Assistant</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-600"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Assistants Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($assistants as $assistant)
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <!-- Avatar & Status -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                                {{ strtoupper(substr($assistant->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $assistant->name }}</h3>
                                <p class="text-xs text-slate-400 font-mono">{{ $assistant->email }}</p>
                            </div>
                        </div>

                        @if($assistant->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @elseif($assistant->status === 'blocked')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inactive
                            </span>
                        @endif
                    </div>

                    <!-- Permissions Summary -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-2 text-slate-700 font-semibold">
                            <i class="fa-solid fa-shield-check text-indigo-500 text-xs"></i>
                            <span>Assigned Permissions:</span>
                        </div>
                        <ul class="list-disc list-inside text-[11px] text-slate-500 space-y-0.5 pl-1">
                            <li>Mark class attendance & QR scanning</li>
                            <li>Distribute lesson sheets & tutes</li>
                            <li>Collect cash fees & view receipts</li>
                        </ul>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span>Attendance Scanned: <strong class="text-slate-800 font-mono">{{ $assistant->attendances_marked_count }}</strong></span>
                        <span class="text-[10px] text-slate-400 font-mono">ID: #{{ $assistant->id }}</span>
                    </div>
                </div>

                <!-- Action Buttons: Toggle Active/Inactive & Delete -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <form action="{{ route('teacher.assistants.toggle-status', $assistant) }}" method="POST">
                        @csrf
                        @if($assistant->status === 'active')
                            <button type="submit" 
                                    class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-pause"></i>
                                <span>Deactivate Login</span>
                            </button>
                        @else
                            <button type="submit" 
                                    class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-play"></i>
                                <span>Activate Login</span>
                            </button>
                        @endif
                    </form>

                    <form action="{{ route('teacher.assistants.destroy', $assistant) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this assistant account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 text-xs font-semibold transition-colors" title="Delete assistant">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-300 p-8">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No Teaching Assistants Added</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                    Create assistant accounts for your staff or lab helpers so they can help you scan attendance, check off tute distribution, and collect fees during class.
                </p>
                <button @click="createModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow transition-all">
                    <i class="fa-solid fa-user-plus mr-1"></i> Add Your First Assistant
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add Assistant Modal -->
    <div x-show="createModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="createModal = false" class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Add Teaching Assistant</h3>
                        <p class="text-xs text-slate-500">Provide login credentials for your assistant</p>
                    </div>
                </div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.assistants.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Assistant Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Kasun Perera"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address (Login)</label>
                    <input type="email" name="email" required placeholder="e.g. kasun@edutrack.test"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Account Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 text-[11px] text-indigo-900 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-indigo-500 mr-1"></i>
                    This assistant will be able to access your classes to mark attendance, record tutes, and collect fee payments. You can deactivate their login at any time.
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Create Assistant Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
