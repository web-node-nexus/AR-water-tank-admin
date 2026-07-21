<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - AR Water Tank Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-cyan-950 to-blue-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-20 left-20 w-72 h-72 bg-cyan-400 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10 flex flex-col justify-center px-16 text-white">
                <x-brand-logo class="h-24 w-auto max-w-sm mb-8" />
                <p class="text-lg text-cyan-100/80 mb-8 max-w-md">Experience Cleanliness Like Never Before. Manage bookings, providers, and operations from one powerful admin panel.</p>
                <div class="space-y-3 text-sm text-cyan-200/70">
                    <p>✓ Booking & Job Management</p>
                    <p>✓ Service Provider Tracking</p>
                    <p>✓ Revenue & Analytics</p>
                    <p>✓ Customer Management</p>
                </div>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center p-6 bg-slate-50">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex justify-center mb-8">
                    <x-brand-logo class="h-16 w-auto max-w-xs" />
                </div>

                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Welcome back</h2>
                    <p class="text-slate-500 text-sm mb-6">Sign in to your admin account</p>

                    @if(session('status'))
                        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-800 text-sm">{{ session('status') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                            <x-password-input id="password" name="password" required />
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                Remember me
                            </label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-cyan-600 hover:text-cyan-700">Forgot password?</a>
                            @endif
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold py-2.5 px-4 rounded-xl hover:from-cyan-700 hover:to-blue-700 transition shadow-lg shadow-cyan-500/25">
                            Sign In
                        </button>
                    </form>
                </div>
                <p class="text-center text-xs text-slate-400 mt-6">© {{ date('Y') }} AR Water Tank Cleaners. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
