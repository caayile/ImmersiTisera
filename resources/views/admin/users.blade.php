@extends('layouts.app')
@section('title', 'Users')
@section('content')
<h1 class="text-2xl font-semibold">User Management</h1>
<form method="POST" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-5">
    @csrf
    <input name="name" placeholder="Nama" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="email" type="email" placeholder="Email" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="password" placeholder="Password" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <select name="role" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="participant">Participant</option>
        <option value="mentor">Mentor</option>
        <option value="admin">Admin</option>
    </select>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Buat user</button>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $user->name }}</td>
                <td class="px-4 py-3">{{ $user->email }}</td>
                <td class="px-4 py-3">{{ $user->role }}</td>
                <td class="px-4 py-3"><x-badge :status="$user->status ?? 'active'" /></td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex gap-2">
                        @csrf @method('PUT')
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <input type="hidden" name="status" value="{{ ($user->status ?? 'active') === 'active' ? 'disabled' : 'active' }}">
                        <button onclick="return confirm('Ubah status user ini?')" class="text-sm text-primary-dark">{{ ($user->status ?? 'active') === 'active' ? 'Disable' : 'Enable' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
