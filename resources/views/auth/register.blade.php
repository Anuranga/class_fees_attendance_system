@extends('layouts.public')

@section('title', 'Teacher Registration')

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12">

    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl gradient-brand flex items-center justify-center text-white text-2xl mx-auto shadow-lg shadow-indigo-500/30 mb-4">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Create Teacher Account</h1>
        <p class="text-sm text-slate-400 mt-1">Join the faculty to manage your classes, student attendance, and fee collections</p>
    </div>

    <!-- Registration Card -->
    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/80 p-8 shadow-2xl backdrop-blur-sm">
        
        <div class="mb-6 p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs flex items-start gap-2.5">
            <i class="fa-solid fa-shield-halved text-sm shrink-0 mt-0.5"></i>
            <span class="leading-relaxed">New teacher registrations undergo administrator review. Your account will be activated once approved by the system admin.</span>
        </div>

        @if(session('error'))
            <div class="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4" x-data="{ selectedRole: 'teacher' }">
            @csrf

            <!-- Role Selector: Principal vs Teacher -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Registering As *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-2.5 transition-all"
                           :class="selectedRole === 'principal' ? 'border-amber-400 bg-amber-950/40 text-amber-200' : 'border-slate-700 bg-slate-900 text-slate-400'">
                        <input type="radio" name="role" value="principal" x-model="selectedRole" class="sr-only">
                        <i class="fa-solid fa-crown text-amber-400"></i>
                        <span class="text-xs font-bold">Principal / Admin</span>
                    </label>

                    <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-2.5 transition-all"
                           :class="selectedRole === 'teacher' ? 'border-indigo-400 bg-indigo-950/40 text-indigo-200' : 'border-slate-700 bg-slate-900 text-slate-400'">
                        <input type="radio" name="role" value="teacher" x-model="selectedRole" class="sr-only">
                        <i class="fa-solid fa-chalkboard-user text-indigo-400"></i>
                        <span class="text-xs font-bold">Teacher</span>
                    </label>
                </div>
            </div>

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Full Name *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="e.g. Prof. Alan Turing">
                </div>
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="you@edutrack.test">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="Minimum 6 characters">
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Confirm Password *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock-check text-sm"></i>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="Repeat your password">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Register Teacher Account</span>
                </button>
            </div>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 pt-5 border-t border-slate-700/60 text-center text-xs text-slate-400">
            Already have an approved account? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-400 hover:text-indigo-300 hover:underline transition-colors ml-1">
                Sign In here &rarr;
            </a>
        </div>
    </div>

</div>
@endsection
