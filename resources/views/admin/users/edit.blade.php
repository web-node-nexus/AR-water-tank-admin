@extends('layouts.admin')

@section('title', 'Edit ' . $user->name)
@section('page-title', 'Edit Admin User')
@section('page-subtitle', $user->name)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Role *</label>
            <select name="role" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @foreach(['super_admin', 'manager'] as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role->value) === $role)>{{ $role === 'super_admin' ? 'Super Admin' : 'Manager' }}</option>
                @endforeach
            </select>
            @error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                <x-password-input name="password" autocomplete="new-password" />
                <p class="text-xs text-slate-500 mt-1">Leave blank to keep current</p>
                @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" />
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded text-cyan-600 focus:ring-cyan-500">
            Active user
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
