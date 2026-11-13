@extends('layouts.app')

@section('title', 'Manage Classes & Groups')
@section('page-title', 'Classes & Grades')
@section('page-subtitle', 'Create and oversee classes (e.g., LKG, UKG, Nursery, Playgroup), set monthly fees, and assign lead teachers.')

@section('content')
<div class="space-y-6" x-data="{ 
    createModal: false, 
    editModal: false, 
    editClass: { id: null, name: '', grade_level: '', monthly_fee: 0, fee_type: 'monthly', teacher_id: '', is_registration_open: true },
    setPreset(name, grade) {
        $refs.newClassName.value = name;
        $refs.newGradeLevel.value = grade;
    },
    openEdit(c) {
        this.editClass = Object.assign({}, c);
        this.editModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">All Preschool Classes ({{ $classes->count() }})</h2>
            <p class="text-xs text-slate-500">Classes created here are used for student admissions, central attendance scanning, and tuition fee tracking.</p>
        </div>

        <button @click="createModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Class (නව පන්තියක් සාදන්න)</span>
        </button>
    </div>

    <!-- Quick Presets Helper Bar -->
    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-indigo-900 font-semibold">
            <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i>
            <span>Common Preschool Levels:</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="createModal = true; $nextTick(() => setPreset('Playgroup / Toddlers', 'Playgroup'))" 
                    class="px-3 py-1 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100/50 font-medium transition-colors">
                + Playgroup
            </button>
            <button type="button" @click="createModal = true; $nextTick(() => setPreset('Nursery (පෙර පාසල)', 'Nursery'))" 
                    class="px-3 py-1 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100/50 font-medium transition-colors">
                + Nursery
            </button>
            <button type="button" @click="createModal = true; $nextTick(() => setPreset('LKG - Lower Kindergarten', 'LKG'))" 
                    class="px-3 py-1 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100/50 font-medium transition-colors">
                + LKG (Lower KG)
            </button>
            <button type="button" @click="createModal = true; $nextTick(() => setPreset('UKG - Upper Kindergarten', 'UKG'))" 
                    class="px-3 py-1 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100/50 font-medium transition-colors">
                + UKG (Upper KG)
            </button>
            <button type="button" @click="createModal = true; $nextTick(() => setPreset('Daycare / Activity Class', 'Daycare'))" 
                    class="px-3 py-1 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100/50 font-medium transition-colors">
                + Daycare
            </button>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($classes as $c)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition-all p-5 flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-1">
                                {{ $c->grade_level ?: 'Preschool' }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                {{ $c->name }}
                            </h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase {{ $c->is_registration_open ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            {{ $c->is_registration_open ? 'Enrolling' : 'Closed' }}
                        </span>
                    </div>

                    <!-- Teacher in charge -->
                    <div class="mt-3 py-2 px-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2.5 text-xs text-slate-700">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase">Lead Teacher</p>
                            <p class="font-bold text-slate-800 truncate">{{ $c->teacher?->name ?: 'Not Assigned' }}</p>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-medium block">Active Students</span>
                            <span class="text-sm font-extrabold text-slate-900">{{ $c->active_students_count }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-medium block">Monthly Fee</span>
                            <span class="text-sm font-extrabold text-emerald-700">LKR {{ number_format($c->monthly_fee, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">
                        Fee: <strong class="text-slate-600 capitalize">{{ $c->fee_type }}</strong>
                    </span>

                    <div class="flex items-center gap-1.5">
                        <button type="button" 
                                @click="openEdit({{ json_encode([
                                    'id' => $c->id,
                                    'name' => $c->name,
                                    'grade_level' => $c->grade_level,
                                    'monthly_fee' => (float)$c->monthly_fee,
                                    'fee_type' => $c->fee_type,
                                    'teacher_id' => $c->teacher_id,
                                    'is_registration_open' => (bool)$c->is_registration_open
                                ]) }})"
                                class="px-2.5 py-1 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-slate-100 font-semibold transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Edit</span>
                        </button>

                        <form action="{{ route('admin.classes.destroy', $c) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete class {{ $c->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors" title="Delete class">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 px-6 rounded-2xl bg-white border border-dashed border-slate-300 text-center">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No Classes Created Yet</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                    Create preschool groups such as Playgroup, Nursery, LKG, and UKG to start enrolling students and taking attendance.
                </p>
                <button @click="createModal = true" 
                        class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-500 transition-all">
                    <i class="fa-solid fa-plus mr-1"></i> Create Your First Class
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create Class Modal -->
    <div x-show="createModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="createModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-folder-plus text-indigo-600"></i>
                    <span>Create New Preschool Class</span>
                </h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Class Name (පන්තියේ නම) *</label>
                    <input type="text" name="name" x-ref="newClassName" required placeholder="e.g. LKG - Rose Buds, UKG - Morning"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Grade / Level *</label>
                        <input type="text" name="grade_level" x-ref="newGradeLevel" placeholder="e.g. LKG, UKG, Nursery"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fee Type</label>
                        <select name="fee_type" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="monthly">Monthly Fee (මාසික)</option>
                            <option value="daily">Daily Fee (දෛනික)</option>
                            <option value="free">Free / Fully Funded</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Fee (LKR) *</label>
                        <input type="number" step="0.01" name="monthly_fee" value="2500" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lead Teacher</label>
                        <select name="teacher_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Assign Later / Principal --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Save Class
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div x-show="editModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="editModal = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
                    <span>Edit Class</span>
                </h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/classes') }}/' + editClass.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Class Name *</label>
                    <input type="text" name="name" x-model="editClass.name" required
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Grade / Level</label>
                        <input type="text" name="grade_level" x-model="editClass.grade_level"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fee Type</label>
                        <select name="fee_type" x-model="editClass.fee_type" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="monthly">Monthly Fee</option>
                            <option value="daily">Daily Fee</option>
                            <option value="free">Free / Fully Funded</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Fee (LKR) *</label>
                        <input type="number" step="0.01" name="monthly_fee" x-model="editClass.monthly_fee" required
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lead Teacher</label>
                        <select name="teacher_id" x-model="editClass.teacher_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Assign Later / Principal --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="edit_reg_open" name="is_registration_open" value="1" :checked="editClass.is_registration_open"
                           class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="edit_reg_open" class="text-xs font-semibold text-slate-700">Open for online student enrollment</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
