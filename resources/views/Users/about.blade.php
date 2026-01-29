<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Smart-LAB</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">

    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .team-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        .gradient-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
        }

        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 30px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 16px;
            height: 16px;
            background: #0ea5e9;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #0ea5e9;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 16px;
            bottom: -30px;
            width: 2px;
            background: #0ea5e9;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>

<body class="bg-white">
    <!-- Navbar -->
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- Hero Section -->
    <section class="gradient-hero text-white relative overflow-hidden pt-20 pb-20 lg:pt-32 lg:pb-32">
        <div class="absolute inset-0 opacity-10">
            <div
                class="absolute top-10 left-10 lg:top-20 lg:left-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-10 right-10 lg:bottom-20 lg:right-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl">
            </div>
        </div>

        <div class="container mx-auto px-4 lg:px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 lg:mb-8 leading-tight">
                    Tentang Smart-LAB
                </h1>
                <p
                    class="text-lg md:text-xl lg:text-2xl opacity-95 font-light leading-relaxed max-w-3xl mx-auto mb-10 lg:mb-14">
                    Mengubah cara belajar tradisional menjadi pengalaman digital yang interaktif dan menyenangkan
                </p>

                <div class="grid grid-cols-3 gap-4 lg:gap-6 max-w-2xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 lg:p-6">
                        <div class="text-2xl lg:text-3xl font-bold mb-2">5JT+</div>
                        <div class="text-xs lg:text-sm opacity-80">Siswa Aktif</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 lg:p-6">
                        <div class="text-2xl lg:text-3xl font-bold mb-2">40+</div>
                        <div class="text-xs lg:text-sm opacity-80">Guru Ahli</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 lg:p-6">
                        <div class="text-2xl lg:text-3xl font-bold mb-2">100+</div>
                        <div class="text-xs lg:text-sm opacity-80">Materi Kursus</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-8 leading-tight">
                        Visi & Misi Kami
                    </h2>
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-xl lg:text-2xl font-bold text-blue-600 mb-4">Visi</h3>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                                Menjadi platform e-learning terdepan di Indonesia yang menghubungkan pelajar dengan
                                pendidikan berkualitas melalui teknologi inovatif.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-xl lg:text-2xl font-bold text-blue-600 mb-4">Misi</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start text-gray-600 text-sm lg:text-base">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Menyediakan akses pendidikan berkualitas untuk semua kalangan</span>
                                </li>
                                <li class="flex items-start text-gray-600 text-sm lg:text-base">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Mengembangkan metode pembelajaran yang interaktif dan menarik</span>
                                </li>
                                <li class="flex items-start text-gray-600 text-sm lg:text-base">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Membangun komunitas belajar yang kolaboratif dan suportif</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 lg:py-24">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-gray-900 mb-12 lg:mb-20">
                    Tim Pengembang
                </h2>

                <!-- Developer Portfolio Card -->
                <div class="team-card bg-white p-6 lg:p-10 shadow-sm border-t-4 border-sky-400 mb-12 lg:mb-20">
                    <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">
                        <div class="flex-shrink-0">
                            <div
                                class="w-40 h-40 lg:w-48 lg:h-48 bg-gradient-to-br from-blue-500 to-sky-500 rounded-full flex items-center justify-center text-white text-4xl lg:text-5xl font-bold shadow-lg">
                                AN
                            </div>
                        </div>
                        <div class="flex-grow w-full">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Achmad Nazriel Pradita</h3>
                            <p class="text-blue-600 font-semibold mb-6 text-base lg:text-lg">Full Stack Developer</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-8">
                                Pengembang utama platform Smart-LAB dengan pengalaman lebih dari 2 tahun dalam
                                pengembangan aplikasi web. Spesialis dalam Laravel, dan React Native.
                            </p>

                            <div class="mb-8">
                                <h4 class="font-bold text-gray-900 mb-4 text-base lg:text-lg">Keahlian:</h4>
                                <div class="flex flex-wrap gap-2 lg:gap-3">
                                    <span
                                        class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-xs lg:text-sm font-medium">Laravel</span>
                                    <span
                                        class="px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-xs lg:text-sm font-medium">React
                                    </span>
                                    <span
                                        class="px-4 py-2 bg-red-100 text-red-700 rounded-full text-xs lg:text-sm font-medium">MySQL</span>
                                    <span
                                        class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-xs lg:text-sm font-medium">Tailwind
                                        CSS</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900 mb-4 text-base lg:text-lg">Portofolio:</h4>
                                <a href="https://achnazriell.vercel.app" target="_blank"
                                    class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-sky-600 text-white px-6 lg:px-8 py-3 lg:py-4 rounded-lg font-semibold hover:from-blue-700 hover:to-sky-700 transition-all duration-300 transform hover:scale-105 text-sm lg:text-base">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                        </path>
                                    </svg>
                                    <span>Kunjungi Portofolio Saya</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Development -->
                <div class="mt-16 lg:mt-24">
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-12 text-center">Perjalanan Pengembangan
                    </h3>
                    <div class="bg-white rounded-2xl p-6 lg:p-10 shadow-sm border-t-4 border-sky-400">
                        <div class="timeline-item">
                            <h4 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">Ide & Konsep</h4>
                            <p class="text-blue-600 font-semibold text-sm mb-2">Juni 2025</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">Pengembangan konsep awal
                                platform e-learning interaktif dengan fokus pada pembelajaran STEM.</p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">Prototype & Desain</h4>
                            <p class="text-blue-600 font-semibold text-sm mb-2">Juli 2025</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">Pembuatan prototype UI/UX dan
                                pengembangan arsitektur sistem menggunakan teknologi modern.</p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">Pengembangan Aplikasi</h4>
                            <p class="text-blue-600 font-semibold text-sm mb-2">Agustus 2025</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">Implementasi fitur utama
                                menggunakan Laravel dan integrasi berbagai API pendidikan.</p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">Peluncuran Beta</h4>
                            <p class="text-blue-600 font-semibold text-sm mb-2">Januari 2026</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">Peluncuran versi beta dengan
                                1000 pengguna awal untuk pengujian dan pengumpulan feedback.</p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">Peluncuran Resmi</h4>
                            <p class="text-blue-600 font-semibold text-sm mb-2">Februari 2026</p>
                            <p class="text-gray-600 text-sm lg:text-base leading-relaxed">Peluncuran resmi Smart-LAB
                                dengan berbagai fitur lengkap dan dukungan untuk 10.000+ pengguna.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="container mx-auto px-4 lg:px-6">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-gray-900 mb-12 lg:mb-20">
                Nilai-Nilai Kami
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <div class="team-card bg-white p-6 lg:p-8 rounded-2xl text-center border-t-4 border-sky-400 shadow-sm">
                    <div
                        class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Inovasi</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                        Terus berinovasi dalam metode pembelajaran untuk memberikan pengalaman belajar terbaik.
                    </p>
                </div>

                <div class="team-card bg-white p-6 lg:p-8 rounded-2xl text-center border-t-4 border-sky-400 shadow-sm">
                    <div
                        class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Keamanan</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                        Menjamin keamanan data dan privasi pengguna dengan sistem enkripsi terbaru.
                    </p>
                </div>

                <div class="team-card bg-white p-6 lg:p-8 rounded-2xl text-center border-t-4 border-sky-400 shadow-sm">
                    <div
                        class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Profesionalisme</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                        Menyediakan konten pembelajaran yang akurat dan dikembangkan oleh profesional.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 lg:py-16">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 pb-8 border-b border-gray-700">
                <div>
                    <img src="{{ asset('image/Smart-LAB white logo.webp') }}" alt="Smart-LAB" class="h-12 mb-6">
                    <p class="text-gray-400 text-sm lg:text-base">Platform belajar online terdepan di Indonesia</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-sm lg:text-base">&copy; {{ date('Y') }} Smart-LAB. Hak Cipta
                        Dilindungi.</p>
                    <p class="text-gray-400 text-xs lg:text-sm mt-2">Hubungi kami: support@smartlab.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>
