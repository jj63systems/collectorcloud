{{-- resources/views/auth/tenant-reset-password.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password</title>
    @vite('resources/css/app.css') {{-- ensure Tailwind is loaded --}}
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="w-full max-w-md px-6 py-8 bg-white shadow rounded-xl">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
        Set Your Password
    </h1>

    <form method="POST" action="{{ route('tenant.password.update') }}" class="space-y-6">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input id="password" type="password" name="password" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                              focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                              focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
        </div>

        <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 px-4 rounded-md
                           hover:bg-primary-700 focus:outline-none focus:ring-2
                           focus:ring-offset-2 focus:ring-primary-500">
            Set Password
        </button>
    </form>
</div>
</body>
</html>
