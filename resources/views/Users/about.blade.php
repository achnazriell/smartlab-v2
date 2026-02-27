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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite([ 'resources/js/app.js'])

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .team-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .team-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
        }

        .gradient-hero {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%);
            position: relative;
        }

        .gradient-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .timeline-item {
            position: relative;
            padding-left: 50px;
            margin-bottom: 40px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            z-index: 2;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 9px;
            top: 25px;
            bottom: -40px;
            width: 2px;
            background: linear-gradient(180deg, #3b82f6 0%, #0ea5e9 100%);
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .stat-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px);
        }

        .value-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(14, 165, 233, 0.1) 100%);
            transition: all 0.3s ease;
        }

        .team-card:hover .value-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(14, 165, 233, 0.2) 100%);
            transform: rotate(5deg) scale(1.1);
        }

        .skill-badge {
            transition: all 0.3s ease;
        }

        .skill-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .section-badge {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
            color: white;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-slate-50">
    <!-- Navbar -->
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- Hero Section -->
    <section class="gradient-hero text-white relative overflow-hidden pt-24 pb-24 lg:pt-36 lg:pb-36">
        <div class="container mx-auto px-4 lg:px-6 relative z-10">
            <div class="max-w-5xl mx-auto text-center">
                <span class="section-badge float-animation">Tentang Kami</span>
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold mb-8 leading-tight tracking-tight">
                    Revolusi Pembelajaran
                    <span class="block bg-gradient-to-r from-yellow-200 to-orange-300 bg-clip-text text-transparent">Digital Indonesia</span>
                </h1>
                <p class="text-lg md:text-xl lg:text-2xl opacity-95 font-light leading-relaxed max-w-3xl mx-auto mb-12">
                    Platform pembelajaran digital yang mengintegrasikan teknologi modern dengan metode pengajaran inovatif untuk menciptakan pengalaman belajar yang transformatif
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <div class="stat-card rounded-2xl p-8">
                        <div class="text-4xl lg:text-5xl font-bold mb-3">1000+</div>
                        <div class="text-sm lg:text-base opacity-90 font-medium">Siswa Aktif</div>
                    </div>
                    <div class="stat-card rounded-2xl p-8">
                        <div class="text-4xl lg:text-5xl font-bold mb-3">50+</div>
                        <div class="text-sm lg:text-base opacity-90 font-medium">Guru Profesional</div>
                    </div>
                    <div class="stat-card rounded-2xl p-8">
                        <div class="text-4xl lg:text-5xl font-bold mb-3">200+</div>
                        <div class="text-sm lg:text-base opacity-90 font-medium">Materi Pembelajaran</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section class="py-20 lg:py-32 bg-white">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="order-2 lg:order-1">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-3xl opacity-10 blur-2xl"></div>
                        <img src="{{ asset('image/about-vision.jpg') }}" alt="Vision"
                            class="relative rounded-3xl shadow-2xl w-full h-auto"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http:www.w3.org/2000/svg\' width=\'600\' height=\'600\'%3E%3Crect fill=\'%233b82f6\' width=\'600\' height=\'600\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-family=\'Inter\' font-size=\'24\' fill=\'white\'%3EVisi & Misi%3C/text%3E%3C/svg%3E'">
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <span class="section-badge">Visi & Misi</span>
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-10 leading-tight">
                        Membangun Masa Depan Pendidikan
                    </h2>

                    <div class="space-y-10">
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-8 border border-blue-100">
                            <div class="flex items-center mb-5">
                                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold text-blue-900">Visi Kami</h3>
                            </div>
                            <p class="text-gray-700 text-base lg:text-lg leading-relaxed">
                                Menjadi platform pembelajaran digital terdepan di Indonesia yang memberdayakan setiap individu untuk mencapai potensi maksimal mereka melalui teknologi pendidikan yang inovatif dan inklusif.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-2xl p-8 border border-cyan-100">
                            <div class="flex items-center mb-5">
                                <div class="w-12 h-12 bg-cyan-600 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold text-cyan-900">Misi Kami</h3>
                            </div>
                            <ul class="space-y-4">
                                <li class="flex items-start text-gray-700 text-base lg:text-lg">
                                    <svg class="w-6 h-6 text-cyan-600 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Menyediakan akses pembelajaran berkualitas tinggi untuk semua kalangan</span>
                                </li>
                                <li class="flex items-start text-gray-700 text-base lg:text-lg">
                                    <svg class="w-6 h-6 text-cyan-600 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Mengintegrasikan teknologi terkini dalam proses pembelajaran</span>
                                </li>
                                <li class="flex items-start text-gray-700 text-base lg:text-lg">
                                    <svg class="w-6 h-6 text-cyan-600 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Membangun komunitas pembelajar yang kolaboratif dan suportif</span>
                                </li>
                                <li class="flex items-start text-gray-700 text-base lg:text-lg">
                                    <svg class="w-6 h-6 text-cyan-600 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Mengembangkan sistem evaluasi yang komprehensif dan adaptif</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Developer Section -->
    <section class="py-20 lg:py-32 bg-gradient-to-br from-slate-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="text-center mb-16">
                <span class="section-badge">Tim Pengembang</span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Dibangun dengan Keahlian & Dedikasi
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Platform Smart-LAB dikembangkan oleh profesional berpengalaman dengan fokus pada inovasi dan kualitas
                </p>
            </div>

            <div class="max-w-5xl mx-auto">
                <div class="team-card p-10 lg:p-14 shadow-xl">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                        <!-- Profile Photo -->


                        <!-- Profile Info -->
                        <div class="lg:col-span-2">
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">Achmad Nazriell Zulfi</h3>
                            <p class="text-xl text-blue-600 font-semibold mb-2">Full-Stack Developer</p>
                            <div class="flex flex-wrap gap-2 mb-6">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">Laravel Expert</span>
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">React Specialist</span>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">UI/UX Designer</span>
                            </div>

                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-8">
                                Pengembang berpengalaman dengan keahlian mendalam dalam pengembangan aplikasi web full-stack.
                                Spesialis dalam Laravel, React, dan teknologi modern untuk menciptakan solusi pembelajaran digital yang inovatif dan user-friendly.
                            </p>

                            <div class="mb-8">
                                <h4 class="font-bold text-gray-900 mb-5 text-xl">Teknologi & Keahlian:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="skill-badge bg-gradient-to-br from-red-50 to-red-100 border border-red-200 px-4 py-3 rounded-xl text-center">
                                        <div class="text-2xl mb-1"></div>
                                        <span class="text-red-700 font-semibold text-sm">Laravel</span>
                                    </div>
                                    <div class="skill-badge bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 px-4 py-3 rounded-xl text-center">
                                        <div class="text-2xl mb-1"></div>
                                        <span class="text-blue-700 font-semibold text-sm">React</span>
                                    </div>
                                    <div class="skill-badge bg-gradient-to-br from-cyan-50 to-cyan-100 border border-cyan-200 px-4 py-3 rounded-xl text-center">
                                        <div class="text-2xl mb-1"></div>
                                        <span class="text-cyan-700 font-semibold text-sm">Tailwind</span>
                                    </div>
                                    <div class="skill-badge bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 px-4 py-3 rounded-xl text-center">
                                        <div class="text-2xl mb-1"></div>
                                        <span class="text-orange-700 font-semibold text-sm">MySQL</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="https://achnazriell.vercel.app" target="_blank"
                                    class="inline-flex items-center space-x-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-8 py-4 rounded-xl font-bold hover:from-blue-700 hover:to-cyan-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl text-base lg:text-lg group">
                                    <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                        </path>
                                    </svg>
                                    <span>Lihat Portofolio Lengkap</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Development -->
                <div class="mt-20">
                    <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-12 text-center">Milestone Pengembangan</h3>
                    <div class="bg-white rounded-3xl p-10 lg:p-14 shadow-xl border border-gray-100">
                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Konseptualisasi & Riset</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Juni 2025</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Analisis kebutuhan pembelajaran modern dan perancangan solusi e-learning yang komprehensif dengan fokus pada user experience dan teknologi STEM.
                            </p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Design System & Prototype</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Juli 2025</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Pembuatan design system, wireframes, dan high-fidelity prototype dengan mempertimbangkan accessibility dan responsive design untuk semua perangkat.
                            </p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Pengembangan Core Features</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Agustus - November 2025</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Implementasi fitur utama menggunakan Laravel 11, integrasi sistem quiz interaktif, manajemen materi, dan dashboard analitik dengan teknologi modern.
                            </p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Quality Assurance & Testing</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Desember 2025</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Testing menyeluruh dengan automated testing, user acceptance testing, dan optimization untuk performa maksimal di berbagai device dan network condition.
                            </p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Beta Launch</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Januari 2026</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Soft launch dengan 500 early adopters untuk gathering feedback, iterasi cepat, dan fine-tuning berdasarkan data penggunaan nyata.
                            </p>
                        </div>

                        <div class="timeline-item">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Official Release 🚀</h4>
                            <p class="text-blue-600 font-bold text-base mb-3">Februari 2026</p>
                            <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                                Peluncuran resmi Smart-LAB dengan full features, support untuk 5000+ concurrent users, dan komitmen continuous improvement berdasarkan user feedback.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values Section -->
    <section class="py-20 lg:py-32 bg-white">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="text-center mb-16">
                <span class="section-badge">Nilai-Nilai Kami</span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Prinsip yang Memandu Kami
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Setiap keputusan yang kami buat didasarkan pada nilai-nilai fundamental ini
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Inovasi Berkelanjutan</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Terus mengembangkan dan mengadopsi teknologi terbaru untuk memberikan pengalaman pembelajaran yang selalu relevan dan engaging.
                    </p>
                </div>

                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Keamanan Data</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Menjamin privasi dan keamanan data pengguna dengan enkripsi tingkat enterprise dan compliance terhadap standar keamanan internasional.
                    </p>
                </div>

                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Kolaborasi</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Memfasilitasi interaksi antara siswa, guru, dan orang tua untuk menciptakan ekosistem pembelajaran yang kolaboratif dan supportif.
                    </p>
                </div>

                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Data-Driven</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Menggunakan analytics dan insights untuk continuous improvement dan personalisasi pengalaman belajar setiap individu.
                    </p>
                </div>

                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Aksesibilitas</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Memberikan akses pendidikan berkualitas untuk semua kalangan dengan harga yang affordable dan platform yang mudah digunakan.
                    </p>
                </div>

                <div class="team-card p-8 lg:p-10 text-center border border-gray-100 shadow-lg">
                    <div class="value-icon w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">User-Centric</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed">
                        Menempatkan kebutuhan pengguna sebagai prioritas utama dalam setiap fitur dan perbaikan yang kami kembangkan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16 lg:py-20">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 pb-12 border-b border-gray-700">
                <div class="lg:col-span-2">
                    <img src="{{ asset('image/Smart-LAB white logo.webp') }}" alt="Smart-LAB" class="h-14 mb-6">
                    <p class="text-gray-300 text-base lg:text-lg leading-relaxed mb-6">
                        Platform pembelajaran digital terdepan yang mengintegrasikan teknologi modern untuk menciptakan pengalaman belajar yang transformatif.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-blue-400 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-pink-600 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-xl font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors">Masuk</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xl font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:info@smartlab.id" class="hover:text-white transition-colors">info@smartlab.id</a>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Surabaya, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 text-center">
                <p class="text-gray-400 text-sm lg:text-base">&copy; {{ date('Y') }} Smart-LAB. All Rights Reserved.</p>
                <p class="text-gray-500 text-xs lg:text-sm mt-2">Dikembangkan dengan ❤️ untuk masa depan pendidikan Indonesia</p>
            </div>
        </div>
    </footer>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
</body>

</html>
