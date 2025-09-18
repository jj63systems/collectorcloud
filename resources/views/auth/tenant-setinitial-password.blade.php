<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CollectorCloud – Set Initial Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 antialiased">
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-xl p-8">
        <h1 class="text-center text-2xl font-extrabold tracking-tight text-gray-900 mb-2">CollectorCloud</h1>
        <p class="text-center text-sm text-gray-600 mb-6">Set your initial password</p>

        @if ($errors->any())
            <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $email) }}"
                    readonly
                    class="block w-full h-10 rounded-lg bg-gray-100 text-gray-900 ring-1 ring-gray-300 placeholder:text-gray-400 px-3 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                />
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="block w-full h-10 rounded-lg bg-white text-gray-900 ring-1 ring-gray-300 placeholder:text-gray-400 pr-12 px-3 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    />
                    <button type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-gray-100 border border-gray-200
                                   flex items-center justify-center text-gray-500 hover:text-gray-700"
                            onclick="togglePw('password')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password
                    <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="block w-full h-10 rounded-lg bg-white text-gray-900 ring-1 ring-gray-300 placeholder:text-gray-400 pr-12 px-3 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    />
                    <button type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-gray-100 border border-gray-200
                                   flex items-center justify-center text-gray-500 hover:text-gray-700"
                            onclick="togglePw('password_confirmation')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full h-11 rounded-lg text-white font-semibold bg-black hover:bg-gray-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                Set Password
            </button>
        </form>
    </div>
</div>

<script>
    function togglePw(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>
