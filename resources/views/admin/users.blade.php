@extends('layouts.admin')

@section('title', 'User Accounts')
@section('page_title', 'User Management')
@section('page_subtitle', 'List of registered users')

@section('content')
<!-- Table Card Container -->
<div class="bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="w-12 text-center">#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th class="w-32">Role</th>
                    <th class="w-32">Member Since</th>
                    <th class="w-40 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-center font-mono text-xs text-[var(--sage)]">{{ $loop->iteration }}</td>
                    <td>
                        <a href="/admin/users/{{ $user->id }}" class="font-semibold text-sm text-[var(--deep-teal)] hover:underline leading-snug block">
                            {{ $user->name }}
                        </a>
                    </td>
                    <td class="text-sm font-mono text-[var(--ink-soft)]">{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="text-xs text-[var(--ink-soft)] font-mono">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/admin/users/{{ $user->id }}" class="btn-secondary px-3 py-1.5 text-xs font-semibold">View</a>
                            @if(auth()->id() !== $user->id)
                            <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger px-3 py-1.5 text-xs font-semibold">Delete</button>
                            </form>
                            @else
                            <span class="text-xs italic text-[var(--sage)] px-2">You</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-16 text-[var(--ink-soft)]">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-[var(--sand-deep)] flex items-center justify-center">
                            <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="font-semibold text-[var(--ink)]">No registered users found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t border-[var(--line)] bg-[var(--sand)]">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
