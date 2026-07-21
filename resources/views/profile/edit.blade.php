@extends('layouts.admin')

@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Manage your account settings')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        @include('profile.partials.update-profile-information-form')
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        @include('profile.partials.update-password-form')
    </div>
</div>
@endsection
