@extends('layouts.app')

@section('title', 'Teaching Assistants')
@section('page-title', 'Teaching Assistants')
@section('page-subtitle', 'Manage your team of assistants and assign specific classes they are allowed to access')

@section('content')
<div class="space-y-6" x-data="{ 
    createModal: false,
    editClassesModal: false,
    editingAssistantId: null,
    editingAssistantName: '',
    selectedClassIds: [],
    openEditClasses(id, name, assignedIds) {
        this.editingAssistantId = id;
        this.editingAssistantName = name;
        this.selectedClassIds = assignedIds.map(String);
        this.editClassesModal = true;
    }
}">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Your Assistants ({{ $assistants->count() }})</h2>
            <p class="text-xs text-slate-500">Assign specific classes to each assistant. They can only view and manage the classes you assign to them.</p>
        </div>

        <button @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add New Assistant</span>
        </button>
    </div>

    <!-- Assistants Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($assistants as $assistant)
            @php
                $assignedClassIds = $assistant->assignedClasses->pluck('id')->toArray();
            @endphp
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

                    <!-- Assigned Classes Section -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                                <i class="fa-solid fa-chalkboard-user text-indigo-500 text-xs"></i>
                                <span>Assigned Classes ({{ $assistant->assignedClasses->count() }})</span>
                            </div>
                            <button type="button"
                                    @click="openEditClasses({{ $assistant->id }}, '{{ addslashes($assistant->name) }}', {{ json_encode($assignedClassIds) }})"
                                    class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                <span>Assign / Edit</span>
                            </button>
                        </div>

                        @if($assistant->assignedClasses->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($assistant->assignedClasses as $assignedClass)
                                    <a href="{{ route('teacher.classes.show', $assignedClass) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 text-[11px] font-bold transition-colors">
                                        <i class="fa-solid fa-book-open text-[9px] text-indigo-500"></i>
                                        <span>{{ $assignedClass->name }}</span>
                                        <span class="text-[9px] text-indigo-400 font-normal">({{ $assignedClass->grade_level }})</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-lg bg-amber-50/80 border border-amber-200/80 text-amber-800 text-[11px] flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                    <span>No classes assigned yet.</span>
                                </span>
                                <button type="button"
                                        @click="openEditClasses({{ $assistant->id }}, '{{ addslashes($assistant->name) }}', [])"
                                        class="font-extrabold underline hover:text-amber-950 cursor-pointer shrink-0">
                                    Assign Now
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span>Attendance Scanned: <strong class="text-slate-800 font-mono">{{ $assistant->attendances_marked_count }}</strong></span>
                        <span class="text-[10px] text-slate-400 font-mono">ID: #{{ $assistant->id }}</span>
                    </div>
                </div>

                <!-- Action Buttons: Assign Classes, Toggle Active/Inactive & Delete -->
                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="openEditClasses({{ $assistant->id }}, '{{ addslashes($assistant->name) }}', {{ json_encode($assignedClassIds) }})"
                                class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Classes & Permissions</span>
                        </button>

                        <form action="{{ route('teacher.assistants.toggle-status', $assistant) }}" method="POST">
                            @csrf
                            @if($assistant->status === 'active')
                                <button type="submit" 
                                        class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <i class="fa-solid fa-pause"></i>
                                    <span>Deactivate</span>
                                </button>
                            @else
                                <button type="submit" 
                                        class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <i class="fa-solid fa-play"></i>
                                    <span>Activate</span>
                                </button>
                            @endif
                        </form>
                    </div>

                    <form action="{{ route('teacher.assistants.destroy', $assistant) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this assistant account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 text-xs font-semibold transition-colors cursor-pointer" title="Delete assistant">
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
                    Create assistant accounts and assign specific classes to them so they can scan attendance, check off tutes, and collect fees only for their assigned classes.
                </p>
                <button @click="createModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow transition-all cursor-pointer">
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

        <div @click.away="createModal = false" class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Add Teaching Assistant</h3>
                        <p class="text-xs text-slate-500">Provide login credentials and assign allowed classes</p>
                    </div>
                </div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('teacher.assistants.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Assistant Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Kasun Perera"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address (Login) *</label>
                        <input type="email" name="email" required placeholder="e.g. kasun@edutrack.test"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Account Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Min 6 characters"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <!-- Assign Classes Checkboxes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Special Pre-School Collection Permissions
                    </label>
                    <div class="space-y-2 p-3 rounded-xl bg-slate-50 border border-slate-200 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="can_collect_fees" value="1" class="w-4 h-4 rounded text-indigo-600 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Authorized to collect student tuition and activity cash fees</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="can_manage_uniforms" value="1" class="w-4 h-4 rounded text-pink-600 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Authorized to manage uniforms, take money & mark delivery</span>
                        </label>
                    </div>

                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Select & Assign Classes
                    </label>
                    <p class="text-[11px] text-slate-500 mb-2">This assistant will ONLY be able to view and manage the classes selected below:</p>

                    @if($classes->isNotEmpty())
                        <div class="max-h-48 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100 bg-slate-50/50">
                            @foreach($classes as $classItem)
                                <label class="flex items-center justify-between p-3 hover:bg-indigo-50/40 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="checkbox" name="class_ids[]" value="{{ $classItem->id }}"
                                               class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/20">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $classItem->name }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $classItem->grade_level }} &bull; LKR {{ number_format($classItem->monthly_fee, 2) }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-600 shrink-0">
                                        {{ $classItem->grade_level }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            You haven't created any classes yet. You can assign classes after creating them.
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 cursor-pointer">
                        Create & Assign Assistant
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Assigned Classes Modal -->
    <div x-show="editClassesModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="editClassesModal = false" class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Assign Classes to <span class="text-indigo-600" x-text="editingAssistantName"></span></h3>
                        <p class="text-xs text-slate-500">Select which classes this assistant is allowed to view and manage</p>
                    </div>
                </div>
                <button @click="editClassesModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/teacher/assistants') }}/' + editingAssistantId + '/classes'" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Assigned Role Permissions
                    </label>
                    <div class="space-y-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="can_collect_fees" value="1" class="w-4 h-4 rounded text-indigo-600 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Assign to collect money & record cash fees</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="can_manage_uniforms" value="1" class="w-4 h-4 rounded text-pink-600 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Assign to collect uniform money & deliver uniforms</span>
                        </label>
                    </div>
                </div>

                @if($classes->isNotEmpty())
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100 bg-slate-50/50">
                        @foreach($classes as $classItem)
                            <label class="flex items-center justify-between p-3.5 hover:bg-indigo-50/40 cursor-pointer transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" name="class_ids[]" value="{{ $classItem->id }}"
                                           x-model="selectedClassIds"
                                           class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500/20">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $classItem->name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $classItem->grade_level }} &bull; LKR {{ number_format($classItem->monthly_fee, 2) }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-600 shrink-0">
                                    {{ $classItem->grade_level }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                        You do not have any classes yet. Create a class first to assign it to an assistant.
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editClassesModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 cursor-pointer">
                        Save Assigned Classes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
