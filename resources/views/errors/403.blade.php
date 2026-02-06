<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak - Smart-Lab</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

        :root {
            --sl-blue: #00A3FF; /* Biru dari logo Smart-Lab */
            --sl-dark: #1A1A1A;
            --sl-red: #FF4757; /* Warna aksen untuk pesan error 403 */
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FFFFFF;
        }

        .text-sl-blue { color: var(--sl-blue); }
        .text-sl-red { color: var(--sl-red); }
        .bg-sl-blue { background-color: var(--sl-blue); }
        .bg-sl-red { background-color: var(--sl-red); }
        .border-sl-blue { border-color: var(--sl-blue); }

        /* Animasi Mengapung */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        /* Animasi Pulse untuk ikon kunci */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .animate-pulse-slow {
            animation: pulse 2s ease-in-out infinite;
        }

        /* Dekorasi Laboratorium / Sains */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 163, 255, 0.1);
        }

        /* Efek akses ditolak */
        .access-denied-effect {
            position: relative;
            overflow: hidden;
        }

        .access-denied-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(0, 163, 255, 0.05) 50%, transparent 70%);
            z-index: -1;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 overflow-hidden relative">

    <!-- Latar Belakang Dekoratif (Molekul/Grid) -->
    <div class="absolute inset-0 z-0 opacity-[0.03] pointer-events-none">
        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Konten Utama -->
    <main class="z-10 text-center max-w-2xl mx-auto flex flex-col items-center access-denied-effect">
        <!-- Logo -->
        <div class="mb-12">
            <!-- Ganti dengan path logo yang sesuai -->
            <div class="h-20 w-auto flex items-center justify-center">
                <span class="text-3xl font-extrabold text-sl-blue">SMART</span>
                <span class="text-3xl font-extrabold text-gray-800">-LAB</span>
            </div>
        </div>

        <!-- Ilustrasi 403 Estetik -->
        <div class="relative mb-8 animate-float">
            <h1 class="text-[12rem] md:text-[16rem] font-extrabold leading-none tracking-tighter text-gray-100 select-none">
                403
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="glass-card p-8 rounded-[2.5rem] shadow-2xl shadow-blue-500/10 border-t border-l border-white/50">
                    <div class="flex items-center gap-4 text-sl-red">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 animate-pulse-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="text-left">
                            <span class="block text-sm font-semibold uppercase tracking-widest text-gray-400">Akses Ditolak</span>
                            <span class="block text-2xl font-bold text-gray-800">Izin Tidak Cukup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesan Error -->
        <div class="space-y-4 px-4">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800 tracking-tight">
                Akses Laboratorium Dibatasi
            </h2>
            <p class="text-gray-500 text-lg max-w-md mx-auto leading-relaxed">
                Maaf, Anda tidak memiliki izin yang diperlukan untuk mengakses halaman ini.
                Peran pengguna Anda tidak memiliki hak akses ke sumber daya ini.
            </p>

            <!-- Pesan spesifik dari sistem -->
            <div class="mt-6 p-4 bg-red-50 border-l-4 border-sl-red rounded-r-lg text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-sl-red" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <span class="font-semibold">Pesan Sistem:</span> User does not have the right roles.
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-gray-500 text-sm mt-4">
                Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator sistem atau pemilik laboratorium.
            </p>
        </div>

        <!-- Tombol Navigasi -->
        <div class="mt-10 flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="/Beranda"
               class="bg-sl-blue text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/40 hover:-translate-y-1 focus:ring-4 focus:ring-blue-100 text-center">
                Kembali ke Beranda
            </a>
            <button onclick="window.history.back()"
                    class="bg-white border-2 border-gray-100 text-gray-600 px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:border-sl-blue hover:text-sl-blue hover:bg-blue-50 text-center">
                Kembali Sebelumnya
            </button>
            <a href="#"
               class="bg-white border-2 border-gray-100 text-gray-600 px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:border-sl-red hover:text-sl-red hover:bg-red-50 text-center">
                Hubungi Admin
            </a>
        </div>

        <!-- Panduan Peran Akses -->
        <div class="mt-12 w-full max-w-lg">
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-left">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-sl-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Informasi Peran Akses
                </h3>
                <p class="text-gray-600 text-sm">
                    Akses ke sistem Smart-Lab dibatasi berdasarkan peran pengguna. Pastikan Anda telah login dengan akun yang memiliki hak akses yang sesuai untuk mengakses halaman ini. Beberapa halaman hanya dapat diakses oleh:
                </p>
                <ul class="mt-3 text-sm text-gray-600 space-y-1">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-sl-blue mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Administrator Laboratorium</span>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-sl-blue mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Peneliti dengan izin khusus</span>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-sl-blue mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Staf dengan hak akses tertentu</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <!-- Footer / Link Bantuan -->
    <footer class="mt-12 text-gray-400 text-sm font-medium z-10 flex flex-col items-center">
        <div class="mb-2">
            © <span id="year"></span> Smart-Lab • Solusi Laboratorium Terpadu
        </div>
        <div class="text-xs">
            Kode Error: 403 - Forbidden Access
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        // Sedikit interaksi mouse untuk efek paralaks halus pada elemen dekoratif
        document.addEventListener('mousemove', (e) => {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
            document.querySelector('.animate-float').style.transform = `translate(${moveX}px, ${moveY}px)`;
        });

        // Animasi ikon kunci saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const lockIcon = document.querySelector('.animate-pulse-slow');
            setTimeout(() => {
                lockIcon.classList.remove('animate-pulse-slow');
                setTimeout(() => {
                    lockIcon.classList.add('animate-pulse-slow');
                }, 100);
            }, 500);
        });
    </script>
</body>
</html>
