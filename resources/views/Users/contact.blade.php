<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Smart-LAB</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">

    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .contact-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            width: 100%;
        }

        .form-input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .gradient-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
    </style>
</head>

<body class="bg-white" x-data="{ submitted: false }">
    <!-- Navbar -->
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- Hero Section -->
    <section class="gradient-hero text-white relative overflow-hidden pt-20 pb-20 lg:pt-32 lg:pb-32">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 lg:top-20 lg:left-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 lg:bottom-20 lg:right-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 lg:mb-8 leading-tight">
                    Hubungi Kami
                </h1>
                <p class="text-lg md:text-xl lg:text-2xl opacity-95 font-light leading-relaxed max-w-3xl mx-auto">
                    Kami siap membantu Anda dalam perjalanan belajar bersama Smart-LAB
                </p>
                <div class="inline-flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-full px-6 lg:px-8 py-3 lg:py-4 mt-8">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span class="text-base lg:text-lg font-semibold">+62 812-3456-7890</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-12 lg:mb-20">

                <!-- Contact Card 1 -->
                <div class="contact-card bg-white p-6 lg:p-8 text-center border-t-4 border-sky-400 shadow-sm">
                    <div class="mb-6">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Email</h3>
                    <p class="text-gray-600 text-sm lg:text-base mb-2">support@smartlab.com</p>
                    <p class="text-gray-600 text-sm lg:text-base mb-4">info@smartlab.com</p>
                    <p class="text-gray-500 text-xs lg:text-sm">Respon dalam 24 jam</p>
                </div>

                <!-- Contact Card 2 -->
                <div class="contact-card bg-white p-6 lg:p-8 text-center border-t-4 border-sky-400 shadow-sm">
                    <div class="mb-6">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Kantor</h3>
                    <p class="text-gray-600 text-sm lg:text-base mb-2">Jl. Pendidikan No. 123</p>
                    <p class="text-gray-600 text-sm lg:text-base mb-2">Jakarta Pusat, 10110</p>
                    <p class="text-gray-600 text-sm lg:text-base mb-4">Indonesia</p>
                    <p class="text-gray-500 text-xs lg:text-sm">Buka: Senin-Jumat, 08:00-17:00</p>
                </div>

                <!-- Contact Card 3 -->
                <div class="contact-card bg-white p-6 lg:p-8 text-center border-t-4 border-sky-400 shadow-sm">
                    <div class="mb-6">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Chat Langsung</h3>
                    <p class="text-gray-600 text-sm lg:text-base mb-2">WhatsApp: +62 812-3456-7890</p>
                    <p class="text-gray-600 text-sm lg:text-base mb-4">Telegram: @smartlab_support</p>
                    <p class="text-gray-500 text-xs lg:text-sm">Respon cepat via chat</p>
                </div>
            </div>

            <!-- Contact Form & Map Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                <!-- Contact Form -->
                <div>
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-8">Kirim Pesan</h2>
                    <form id="contactForm" class="space-y-6" @submit.prevent="submitted = true">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-gray-700 font-semibold mb-3 text-sm lg:text-base">Nama Lengkap</label>
                                <input type="text" id="name" name="name" required
                                       class="form-input text-sm lg:text-base" placeholder="Masukkan nama Anda">
                            </div>
                            <div>
                                <label for="email" class="block text-gray-700 font-semibold mb-3 text-sm lg:text-base">Email</label>
                                <input type="email" id="email" name="email" required
                                       class="form-input text-sm lg:text-base" placeholder="nama@email.com">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-gray-700 font-semibold mb-3 text-sm lg:text-base">Subjek</label>
                            <input type="text" id="subject" name="subject" required
                                   class="form-input text-sm lg:text-base" placeholder="Subjek pesan">
                        </div>

                        <div>
                            <label for="category" class="block text-gray-700 font-semibold mb-3 text-sm lg:text-base">Kategori</label>
                            <select id="category" name="category" required class="form-input text-sm lg:text-base">
                                <option value="" disabled selected>Pilih kategori</option>
                                <option value="technical">Bantuan Teknis</option>
                                <option value="content">Konten Pembelajaran</option>
                                <option value="billing">Pembayaran</option>
                                <option value="partnership">Kemitraan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-gray-700 font-semibold mb-3 text-sm lg:text-base">Pesan</label>
                            <textarea id="message" name="message" rows="5" required
                                      class="form-input text-sm lg:text-base" placeholder="Tulis pesan Anda di sini..."></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-600 to-sky-600 text-white font-semibold py-3 lg:py-4 px-6 rounded-lg hover:from-blue-700 hover:to-sky-700 transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl text-base lg:text-lg">
                            <div class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                <span>Kirim Pesan</span>
                            </div>
                        </button>
                    </form>
                </div>

                <!-- Map & Social Media -->
                <div>
                    <!-- Map -->
                    <div class="mb-12">
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-8">Lokasi Kami</h2>
                        <div class="w-full h-72 lg:h-80 bg-gradient-to-br from-sky-100 to-blue-100 flex items-center justify-center rounded-2xl shadow-sm overflow-hidden">
                            <div class="text-center p-8">
                                <svg class="w-16 h-16 lg:w-20 lg:h-20 text-blue-600 mx-auto mb-4 float-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">Smart-LAB Headquarters</h3>
                                <p class="text-gray-600 text-sm lg:text-base">Jl. Pendidikan No. 123, Jakarta Pusat</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-8">Ikuti Kami</h2>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="#" class="contact-card bg-white p-5 lg:p-6 text-center hover:bg-sky-50 transition-colors border-t-4 border-sky-400 shadow-sm">
                                <div class="w-12 h-12 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 text-sm">Facebook</span>
                            </a>

                            <a href="#" class="contact-card bg-white p-5 lg:p-6 text-center hover:bg-pink-50 transition-colors border-t-4 border-pink-400 shadow-sm">
                                <div class="w-12 h-12 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 text-sm">Instagram</span>
                            </a>

                            <a href="#" class="contact-card bg-white p-5 lg:p-6 text-center hover:bg-sky-50 transition-colors border-t-4 border-sky-400 shadow-sm">
                                <div class="w-12 h-12 bg-sky-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417a9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.213c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 text-sm">Twitter</span>
                            </a>

                            <a href="#" class="contact-card bg-white p-5 lg:p-6 text-center hover:bg-red-50 transition-colors border-t-4 border-red-400 shadow-sm">
                                <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 text-sm">YouTube</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 lg:py-24">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-gray-900 mb-12 lg:mb-20">
                    Pertanyaan Umum
                </h2>

                <div class="space-y-4 lg:space-y-6">
                    <div class="contact-card bg-white p-6 lg:p-8 border-l-4 border-sky-400 shadow-sm">
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-3">
                            Bagaimana cara mendaftar di Smart-LAB?
                        </h3>
                        <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                            Klik tombol "Masuk" di pojok kanan atas, lalu pilih "Daftar". Isi formulir pendaftaran dengan data yang valid, dan Anda akan mendapatkan akses langsung ke platform.
                        </p>
                    </div>

                    <div class="contact-card bg-white p-6 lg:p-8 border-l-4 border-sky-400 shadow-sm">
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-3">
                            Apakah Smart-LAB benar-benar gratis?
                        </h3>
                        <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                            Ya, Smart-LAB sepenuhnya gratis untuk semua pengguna. Kami percaya pendidikan berkualitas harus dapat diakses oleh semua orang tanpa hambatan biaya.
                        </p>
                    </div>

                    <div class="contact-card bg-white p-6 lg:p-8 border-l-4 border-sky-400 shadow-sm">
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-3">
                            Bagaimana jika saya mengalami kendala teknis?
                        </h3>
                        <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                            Hubungi tim support kami melalui email support@smartlab.com atau chat langsung di WhatsApp. Tim kami akan merespons dalam waktu 24 jam.
                        </p>
                    </div>

                    <div class="contact-card bg-white p-6 lg:p-8 border-l-4 border-sky-400 shadow-sm">
                        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-3">
                            Apakah tersedia aplikasi mobile?
                        </h3>
                        <p class="text-gray-600 text-sm lg:text-base leading-relaxed">
                            Smart-LAB sepenuhnya responsif dan dapat diakses melalui browser mobile. Aplikasi native sedang dalam pengembangan dan akan segera hadir di App Store dan Play Store.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 lg:py-16">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 pb-8 border-b border-gray-700">
                <div>
                    <img src="{{ asset('image/Smart-LAB white logo.webp') }}" alt="Smart-LAB" class="h-12 mb-6">
                    <p class="text-gray-400 text-sm lg:text-base">Platform belajar online terdepan di Indonesia</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-sm lg:text-base">&copy; {{ date('Y') }} Smart-LAB. Hak Cipta Dilindungi.</p>
                    <p class="text-gray-400 text-xs lg:text-sm mt-2">Hubungi kami: support@smartlab.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>
