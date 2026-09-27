@extends('layouts.admin')

@section('title', 'User Details')
@section('page_title', 'User Account')
@section('page_subtitle', 'Read-only account summary')

@section('topbar_actions')
<a href="/admin/users" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[var(--deep-teal)] bg-[var(--card)] border border-[var(--line)] rounded-lg hover:border-[var(--deep-teal)] transition">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Users</span>
</a>
@endsection

@section('content')
<div class="max-w-3xl bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-sm overflow-hidden">
    <!-- Identity header -->
    <div class="p-5 lg:p-6 border-b border-[var(--line)] flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="font-display text-xl lg:text-2xl font-bold text-[var(--deep-teal)] leading-tight truncate">{{ $user->name }}</h2>
            <p class="text-sm font-mono text-[var(--ink-soft)] mt-1 truncate">{{ $user->email }}</p>
            <span class="badge mt-3 {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">{{ ucfirst($user->role) }}</span>
        </div>
        <a href="/admin/users" class="btn-secondary px-3 py-1.5 text-xs font-semibold flex-shrink-0">Back</a>
    </div>

    <!-- Account details -->
    <div class="divide-y divide-[var(--line)]">
        <div class="flex items-center justify-between gap-4 px-5 lg:px-6 py-3.5">
            <span class="font-mono text-[0.68rem] uppercase tracking-wider text-[var(--sage)] font-bold">Account ID</span>
            <span class="text-sm font-mono text-[var(--ink)]">#{{ $user->id }}</span>
        </div>
        <div class="flex items-center justify-between gap-4 px-5 lg:px-6 py-3.5">
            <span class="font-mono text-[0.68rem] uppercase tracking-wider text-[var(--sage)] font-bold">Verification</span>
            <span class="text-sm text-[var(--ink)]">{{ $user->email_verified_at ? 'Verified on ' . $user->email_verified_at->format('M d, Y') : 'Not verified' }}</span>
        </div>
        <div class="flex items-center justify-between gap-4 px-5 lg:px-6 py-3.5">
            <span class="font-mono text-[0.68rem] uppercase tracking-wider text-[var(--sage)] font-bold">Member Since</span>
            <span class="text-sm text-[var(--ink)]">{{ $user->created_at->format('M d, Y h:i A') }}</span>
        </div>
        <div class="flex items-center justify-between gap-4 px-5 lg:px-6 py-3.5">
            <span class="font-mono text-[0.68rem] uppercase tracking-wider text-[var(--sage)] font-bold">Last Updated</span>
            <span class="text-sm text-[var(--ink)]">{{ $user->updated_at->format('M d, Y h:i A') }}</span>
        </div>
        <div class="flex items-center justify-between gap-4 px-5 lg:px-6 py-3.5">
            <span class="font-mono text-[0.68rem] uppercase tracking-wider text-[var(--sage)] font-bold">Saved Plans</span>
            <span class="text-sm text-[var(--ink)]">{{ $user->plans_count }}{{ $user->plans_count === 1 ? '' : ' itineraries' }}</span>
        </div>
    </div>

    @if(auth()->id() !== $user->id)
    <!-- Danger zone -->
    <div class="p-5 lg:p-6 border-t border-[var(--line)] bg-[var(--sand)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-[var(--ink-soft)]">Deleting this account is permanent and removes its saved itineraries.</p>
        <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger px-4 py-2 text-xs font-semibold">Delete Account</button>
        </form>
    </div>
    @endif
</div>
@endsection
