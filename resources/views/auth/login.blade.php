@extends('layouts.public')

@section('title', 'Sign In')

@section('content')
<div class="max-w-md mx-auto py-10">

    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl gradient-brand flex items-center justify-center text-white text-2xl mx-auto shadow-lg shadow-indigo-500/30 mb-4">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Staff Portal Login</h1>
        <p class="text-sm text-slate-400 mt-1">Sign in to manage your classes, mark attendance, and track fees</p>
    </div>

    <!-- Main Login Card -->
    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/80 p-8 shadow-2xl backdrop-blur-sm">
        
        @if(session('info'))
            <div class="mb-5 p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="you@edutrack.test">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 text-white placeholder-slate-500 text-sm transition-all"
                           placeholder="••••••••">
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500/30">
                    <span>Remember this device</span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" 
                    class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                <span>Sign In to Account</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>
    </div>

</div>
@endsection
