<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Update Password</h2>
        <p class="mt-1 text-sm text-slate-500">Use a long, random password to keep your account secure.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
            <x-password-input id="update_password_current_password" name="current_password" autocomplete="current-password" required />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
            <x-password-input id="update_password_password" name="password" autocomplete="new-password" required />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password" required />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700">Save</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600">Saved.</p>
            @endif
        </div>
    </form>
</section>
