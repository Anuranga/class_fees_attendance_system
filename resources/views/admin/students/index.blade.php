@extends('layouts.app')

@section('title', 'Global Students Directory')
@section('page-title', 'Global Students Directory')
@section('page-subtitle', 'System-wide list of registered students and their enrolled classes')

@section('content')
<div class="space-y-6">

    <!-- Search Bar -->
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs">
        <form action="{{ route('admin.students.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by Admission No, Student Name, Phone, or Guardian Phone..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shadow-xs transition-all">
                Search Directory
            </button>

            @if(request('search'))
                <a href="{{ route('admin.students.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Students Directory Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">All Registered Students ({{ $students->total() }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Admission #</th>
                        <th class="py-3 px-4">Student Details</th>
                        <th class="py-3 px-4">Guardian Contact</th>
                        <th class="py-3 px-4">Enrolled Classes</th>
                        <th class="py-3 px-4 text-right">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">
                                {{ $s->admission_number }}
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $s->full_name }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $s->contact_number }}</span>
                            </td>

                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="font-semibold text-slate-800">{{ $s->guardian_name }}</span>
                                <span class="text-[11px] text-slate-400 block font-mono">{{ $s->guardian_phone }}</span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($s->classes as $c)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $c->name }} ({{ $c->teacher->name }})
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic text-[11px]">Not enrolled</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-[11px]">
                                {{ $s->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500">No students found matching query.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
