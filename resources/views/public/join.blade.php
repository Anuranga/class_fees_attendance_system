@extends('layouts.public')

@section('title', 'Join ' . $classRoom->name)

@section('content')
<div class="max-w-xl mx-auto py-8">

    <!-- Class Header Banner -->
    <div class="rounded-2xl bg-gradient-to-br from-indigo-900/90 via-slate-900 to-slate-900 border border-indigo-500/30 p-8 shadow-2xl mb-8 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                {{ $classRoom->grade_level }}
            </span>

            @if($classRoom->is_registration_open)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Registration Open
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    Registration Closed
                </span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $classRoom->name }}</h1>
        
        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-300">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-indigo-400"></i>
                <span>Instructor: <strong class="text-white">{{ $classRoom->teacher->name }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-tag text-emerald-400"></i>
                <span>Monthly Fee: <strong class="text-emerald-400">{{ number_format($classRoom->monthly_fee, 2) }} LKR/Month</strong></span>
            </div>
        </div>
    </div>

    <!-- Registration Card -->
    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/80 p-8 shadow-2xl">

        @if(!$classRoom->is_registration_open)
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center text-3xl mx-auto mb-4 border border-rose-500/20">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Registration is Currently Closed</h2>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    The instructor has temporarily paused enrollment for this class. Please contact the teacher directly for assistance.
                </p>
                <div class="mt-6">
                    <a href="{{ route('public.lookup') }}" class="inline-flex items-center gap-2 text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Looking for an existing pass? Search here</span>
                    </a>
                </div>
            </div>
        @else
            <div class="mb-6">
                <h2 class="text-lg font-bold text-white">Student Enrollment Form</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Fill out your student details below. Once registered, your official Student ID Pass with attendance QR code will be generated immediately.
                </p>
            </div>

            <form action="{{ route('public.join.process', $classRoom->registration_token) }}" method="POST" enctype="multipart/form-data" 
                  x-data="{ gender: '{{ old('gender', 'male') }}', photoPreview: null }" class="space-y-4">
                @csrf

                <!-- Student Full Name -->
                <div>
                    <label for="full_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Student Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. Johnathan Doe">
                    @error('full_name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender & Default Avatar Choice -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Student Gender & Default Avatar *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Boy Option -->
                        <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-3 transition-all"
                               :class="gender === 'male' ? 'border-indigo-500 bg-indigo-950/60 shadow-lg shadow-indigo-500/20' : 'border-slate-700 bg-slate-900/60 hover:bg-slate-900'">
                            <input type="radio" name="gender" value="male" x-model="gender" class="sr-only">
                            <img src="{{ asset('images/avatars/male.svg') }}" alt="Boy" class="w-10 h-10 rounded-full border border-indigo-400/40 bg-slate-800 p-0.5 shrink-0 shadow">
                            <div>
                                <p class="text-xs font-bold text-white">Boy / Male</p>
                                <p class="text-[10px] text-slate-400">Default Avatar</p>
                            </div>
                            <i class="fa-solid fa-circle-check ml-auto text-indigo-400 text-sm" x-show="gender === 'male'"></i>
                        </label>

                        <!-- Girl Option -->
                        <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-3 transition-all"
                               :class="gender === 'female' ? 'border-pink-500 bg-pink-950/60 shadow-lg shadow-pink-500/20' : 'border-slate-700 bg-slate-900/60 hover:bg-slate-900'">
                            <input type="radio" name="gender" value="female" x-model="gender" class="sr-only">
                            <img src="{{ asset('images/avatars/female.svg') }}" alt="Girl" class="w-10 h-10 rounded-full border border-pink-400/40 bg-slate-800 p-0.5 shrink-0 shadow">
                            <div>
                                <p class="text-xs font-bold text-white">Girl / Female</p>
                                <p class="text-[10px] text-slate-400">Default Avatar</p>
                            </div>
                            <i class="fa-solid fa-circle-check ml-auto text-pink-400 text-sm" x-show="gender === 'female'"></i>
                        </label>
                    </div>
                    @error('gender')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Optional Student Photo / Selfie Upload with Live Preview -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Student Photo / Selfie (Optional)</label>
                    <div class="flex items-center gap-4 p-3.5 rounded-xl border border-dashed border-slate-600 bg-slate-900/80">
                        <!-- Live Avatar / Photo Preview -->
                        <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-indigo-500/50 bg-slate-800 shadow-md shrink-0 flex items-center justify-center">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <img :src="gender === 'female' ? '{{ asset('images/avatars/female.svg') }}' : '{{ asset('images/avatars/male.svg') }}'" class="w-full h-full object-cover">
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <input type="file" name="photo" id="photo_public" accept="image/*" class="sr-only"
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                            <div class="flex items-center gap-2">
                                <label for="photo_public" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow cursor-pointer transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-camera"></i>
                                    <span>Take Selfie / Upload Photo</span>
                                </label>
                                <button type="button" x-show="photoPreview" @click="photoPreview = null; document.getElementById('photo_public').value = ''" 
                                        class="text-xs text-rose-400 hover:text-rose-300 font-semibold p-1">
                                    <i class="fa-solid fa-trash-can"></i> Remove
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">If you don't upload a picture, your selected Boy or Girl avatar will appear on your Student ID Pass.</p>
                        </div>
                    </div>
                    @error('photo')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Phone -->
                <div>
                    <label for="contact_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Student Contact Number / WhatsApp *</label>
                    <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. +1 555-0199 or 0771234567">
                    <p class="mt-1 text-[11px] text-slate-400">Used to match existing student profiles across courses.</p>
                    @error('contact_number')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Guardian Name -->
                    <div>
                        <label for="guardian_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Guardian / Parent Name *</label>
                        <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                               placeholder="e.g. Sarah Doe">
                        @error('guardian_name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guardian Phone -->
                    <div>
                        <label for="guardian_phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Emergency Contact Phone *</label>
                        <input type="tel" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                               placeholder="e.g. +1 555-0299">
                        @error('guardian_phone')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Existing Admission Number (Optional) -->
                <div>
                    <label for="admission_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Existing Admission No. (Optional)</label>
                    <input type="text" id="admission_number" name="admission_number" value="{{ old('admission_number') }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="Leave blank if registering for the first time">
                    @error('admission_number')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Register & Generate Student ID Pass</span>
                    </button>
                </div>
            </form>
        @endif

    </div>

</div>
@endsection
