<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart-Lab</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Added centered card design animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes float-delayed {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 0.8;
            }
        }

        @keyframes slide-in-up {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-delayed 8s ease-in-out infinite;
        }

        .animate-pulse-slow {
            animation: pulse-slow 4s ease-in-out infinite;
        }

        .animate-slide-in-up {
            animation: slide-in-up 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fade-in 0.8s ease-out;
        }

        .animation-delay-200 {
            animation-delay: 0.2s;
        }

        .animation-delay-300 {
            animation-delay: 0.3s;
        }

        .input-focus-effect:focus {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }

        .button-hover-effect:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.25);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4"
    style="
          background-image: url('{{ asset('image/background-guest2.png') }}');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
          background-attachment: fixed;
      ">
    <!-- Changed to centered layout with floating background shapes -->
    <!-- Floating Background Shapes -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-blue-300/15 rounded-full blur-3xl animate-float-delayed">
        </div>
        <div class="absolute top-3/4 left-3/4 w-48 h-48 bg-white/10 rounded-full blur-2xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/2 left-1/2 w-32 h-32 bg-blue-200/20 rounded-full blur-xl animate-float"></div>
    </div>

    <!-- Main Card Container -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Created centered white card for login form -->
        <!-- Login Card -->
        <div class="bg-white bg-opacity-75 backdrop-blur-sm rounded-3xl shadow-2xl p-6 animate-slide-in-up">
            <div class="text-center mb-5">
                <h2 class="text-xl font-bold text-gray-900 mb-">Selamat Datang!</h2>
                <p class="text-gray-800 lg:text-sm text-xs">Masukkan akun Email beserta Sandi nya yang sesuai</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Field -->
                <div class="animate-slide-in-up animation-delay-200">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="block w-full pl-12 pr-4 py-3 h-12 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 input-focus-effect @error('email') border-red-300 @enderror"
                            placeholder="admin@smartlab.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="animate-slide-in-up animation-delay-300">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" required
                            class="block w-full pl-12 pr-12 py-3 h-12 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 input-focus-effect @error('password') border-red-300 @enderror"
                            placeholder="••••••••">
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center"
                            onclick="togglePassword()">
                            <svg id="eye-open" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="h-5 w-5 text-gray-400 hover:text-gray-600 hidden" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Login Button -->
                <button type="submit" id="login-btn"
                    class="w-full flex justify-center items-center py-3 px-4 h-12 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-[#477FF0] to-[#2B5DDE] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 button-hover-effect disabled:opacity-70">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span id="login-text">Masuk ke Dashboard</span>
                    <div id="login-spinner" class="hidden flex items-center">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2">
                        </div>
                        <span>Memproses...</span>
                    </div>
                </button>
            </form>

            <!-- Footer Text -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}"
                        class="font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-300 hover:underline">
                        Daftar Sekarang!
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('login-btn');
            const text = document.getElementById('login-text');
            const spinner = document.getElementById('login-spinner');

            btn.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
        });
    </script>
</body>

</html>
