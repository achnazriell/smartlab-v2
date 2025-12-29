<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Smart-Lab</title>
    <!-- Tailwind CSS via CDN untuk kemudahan penggunaan (disarankan via Vite/Mix di Laravel asli) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        :root {
            --sl-blue: #00A3FF; /* Biru dari logo Smart-Lab */
            --sl-dark: #1A1A1A;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FFFFFF;
        }

        .text-sl-blue { color: var(--sl-blue); }
        .bg-sl-blue { background-color: var(--sl-blue); }
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

        /* Dekorasi Laboratorium / Sains */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 163, 255, 0.1);
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
    <main class="z-10 text-center max-w-2xl mx-auto flex flex-col items-center">
        <!-- Logo -->
        <div class="mb-12">
            <img src="{{ asset('image/logo.png') }}" 
                 alt="Smart-Lab Logo" 
                 class="h-20 w-auto">
        </div>

        <!-- Ilustrasi 404 Estetik -->
        <div class="relative mb-8 animate-float">
            <h1 class="text-[12rem] md:text-[16rem] font-extrabold leading-none tracking-tighter text-gray-100 select-none">
                404
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="glass-card p-8 rounded-[2.5rem] shadow-2xl shadow-blue-500/10 border-t border-l border-white/50">
                    <div class="flex items-center gap-4 text-sl-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <div class="text-left">
                            <span class="block text-sm font-semibold uppercase tracking-widest text-gray-400">Analisis Gagal</span>
                            <span class="block text-2xl font-bold text-gray-800">Unfound Element</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesan Error -->
        <div class="space-y-4 px-4">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800 tracking-tight">
                Eksperimen Tidak Ditemukan
            </h2>
            <p class="text-gray-500 text-lg max-w-md mx-auto leading-relaxed">
                Maaf, halaman yang Anda cari telah dipindahkan ke laboratorium lain atau mungkin tidak pernah ada dalam basis data kami.
            </p>
        </div>

        <!-- Tombol Navigasi -->
        <div class="mt-10 flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="/" 
               class="bg-sl-blue text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/40 hover:-translate-y-1 focus:ring-4 focus:ring-blue-100 text-center">
                Kembali ke Beranda
            </a>
            <button onclick="window.history.back()" 
                    class="bg-white border-2 border-gray-100 text-gray-600 px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:border-sl-blue hover:text-sl-blue hover:bg-blue-50 text-center">
                Kembali Sebelumnya
            </button>
        </div>
    </main>

    <!-- Footer / Link Bantuan -->
    <footer class="mt-20 text-gray-400 text-sm font-medium z-10">
        © <span id="year"></span> Smart-Lab • Solusi Laboratorium Terpadu
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
        
        // Sedikit interaksi mouse untuk efek paralaks halus pada elemen dekoratif
        document.addEventListener('mousemove', (e) => {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
            document.querySelector('.animate-float').style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    </script>
</body>
</html>
