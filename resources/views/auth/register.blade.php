@extends('layouts.public')

@section('title', 'Pre-School Principal Registration')

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12">

    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl gradient-brand flex items-center justify-center text-white text-2xl mx-auto shadow-lg shadow-indigo-500/30 mb-4">
            <i class="fa-solid fa-school-flag"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Register Your Pre-School</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Principal account registration & campus onboarding</p>
    </div>

    <!-- Registration Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 p-8 shadow-xl shadow-slate-200/50 dark:shadow-2xl backdrop-blur-sm transition-colors">
        
        <!-- Super Admin Approval Process Notice -->
        <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-900 dark:text-amber-300 text-xs flex items-start gap-3">
            <i class="fa-solid fa-shield-halved text-base shrink-0 mt-0.5 text-amber-600 dark:text-amber-400"></i>
            <div class="leading-relaxed">
                <strong class="block text-amber-950 dark:text-amber-200 font-bold mb-0.5">Pre-School Principal Account Process:</strong>
                Each new Pre-School and Principal account is reviewed and activated by the Super Administrator. Once verified, the Principal has full authority to onboard their teachers, assign permissions, create classes (LKG, UKG), and issue admission links.
            </div>
        </div>

        @if(session('error'))
            <div class="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Pre-School / Nursery Campus Name -->
            <div>
                <label for="preschool_name" class="block text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-school text-amber-600 dark:text-amber-400 mr-1"></i> Pre-School / Nursery Name *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-shapes text-sm"></i>
                    </div>
                    <input type="text" id="preschool_name" name="preschool_name" value="{{ old('preschool_name') }}" required
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-amber-300 dark:border-amber-500/50 focus:border-amber-500 focus:ring-2 focus:ring-amber-400/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. Global International Pre-School, Seven Bees">
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">This will register your school in the SaaS network and isolate all student and staff data.</p>
                @error('preschool_name')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Principal Full Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-user-tie text-indigo-600 dark:text-indigo-400 mr-1"></i> Principal Full Name *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. Mrs. Sunethra Perera">
                </div>
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Official WhatsApp / Phone -->
            <div>
                <label for="contact_phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    <i class="fa-brands fa-whatsapp text-emerald-600 dark:text-emerald-400 mr-1"></i> Official WhatsApp / Contact Phone
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-phone text-sm"></i>
                    </div>
                    <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. 077 123 4567">
                </div>
                @error('contact_phone')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address (Login) -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-envelope text-indigo-600 dark:text-indigo-400 mr-1"></i> Principal Email Address (Login) *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="principal@school.lk">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-lock text-indigo-600 dark:text-indigo-400 mr-1"></i> Password *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="Minimum 6 characters">
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-lock-check text-indigo-600 dark:text-indigo-400 mr-1"></i> Confirm Password *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock-check text-sm"></i>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-300 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all"
                           placeholder="Repeat your password">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 active:scale-[0.99] text-slate-950 font-black text-sm shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-school-flag text-base"></i>
                    <span>Register Pre-School & Principal Account</span>
                </button>
            </div>
        </form>

        <!-- Teacher Information Callout -->
        <div class="mt-6 p-4 rounded-xl bg-slate-100 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-400 space-y-1">
            <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-300">
                <i class="fa-solid fa-chalkboard-user text-indigo-600 dark:text-indigo-400"></i>
                <span>Are you a Teacher or Staff Member?</span>
            </div>
            <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                Teachers do not register publicly. Teacher accounts and individual permissions are created directly by your Pre-School's Principal. Please contact your Principal to receive your login credentials.
            </p>
        </div>

        <!-- Back to Login -->
        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-700/60 text-center text-xs text-slate-500 dark:text-slate-400">
            Already have an approved account? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 hover:underline transition-colors ml-1">
                Sign In here &rarr;
            </a>
        </div>
    </div>

</div>
@endsection
