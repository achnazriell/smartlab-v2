@extends(Auth::user()->hasRole('Guru') ? 'layouts.appTeacher' : 'layouts.appSiswa')

@section('title', 'Feedback & Laporan')

@section('content')
<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">Feedback & Laporan</h1>
            <p class="text-blue-700">Sampaikan saran, kritik, pertanyaan, atau rating untuk pengembangan sistem</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Feedback Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-bold text-blue-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        Kirim Feedback Baru
                    </h2>

                    <form action="{{ route('feedbacks.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Feedback</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                    <input type="radio" name="type" value="saran" class="mr-3" required>
                                    <div>
                                        <div class="font-medium text-gray-800">Saran</div>
                                        <div class="text-xs text-gray-500">Usulan perbaikan</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-red-500 transition-colors">
                                    <input type="radio" name="type" value="kritik" class="mr-3" required>
                                    <div>
                                        <div class="font-medium text-gray-800">Kritik</div>
                                        <div class="text-xs text-gray-500">Masukan konstruktif</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-green-500 transition-colors">
                                    <input type="radio" name="type" value="pertanyaan" class="mr-3" required>
                                    <div>
                                        <div class="font-medium text-gray-800">Pertanyaan</div>
                                        <div class="text-xs text-gray-500">Butuh penjelasan</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-yellow-500 transition-colors">
                                    <input type="radio" name="type" value="rating" class="mr-3" required>
                                    <div>
                                        <div class="font-medium text-gray-800">Rating</div>
                                        <div class="text-xs text-gray-500">Penilaian sistem</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <select name="category" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Kategori</option>
                                <option value="akademik">Akademik</option>
                                <option value="fasilitas">Fasilitas</option>
                                <option value="sistem">Sistem Aplikasi</option>
                                <option value="guru">Tenaga Pengajar</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <!-- Rating (only shown when rating type selected) -->
                        <div id="ratingSection" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating Sistem (1-5)</label>
                            <div class="flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden">
                                        <svg class="w-10 h-10 text-gray-300 hover:text-yellow-400 star" data-value="{{ $i }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesan</label>
                            <textarea name="message" rows="6"
                                class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tuliskan feedback Anda secara detail..."
                                required></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: History & Tips -->
            <div class="lg:col-span-1">
                <!-- Tips Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6">
                    <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tips Feedback yang Baik
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Jelas dan spesifik</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Gunakan bahasa yang sopan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Sertakan contoh jika ada</span>
                        </li>
                    </ul>
                </div>

                <!-- Recent Feedback -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="font-bold text-blue-900 mb-4">Feedback Terakhir</h3>
                    <div class="space-y-4">
                        @forelse($feedbacks->take(3) as $feedback)
                        <div class="border-l-4 pl-4
                            @if($feedback->type === 'saran') border-green-500
                            @elseif($feedback->type === 'kritik') border-red-500
                            @elseif($feedback->type === 'pertanyaan') border-blue-500
                            @else border-yellow-500 @endif">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-sm font-medium capitalize">{{ $feedback->type }}</span>
                                <span class="text-xs text-gray-500">{{ $feedback->created_at->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 truncate">{{ Str::limit($feedback->message, 50) }}</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($feedback->status === 'pending') bg-gray-100 text-gray-800
                                    @elseif($feedback->status === 'dibaca') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ $feedback->status }}
                                </span>
                                @if($feedback->rating)
                                <div class="flex">
                                    @for($i = 0; $i < $feedback->rating; $i++)
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm">Belum ada feedback</p>
                        @endforelse

                        @if($feedbacks->count() > 0)
                        <a href="{{ route('feedbacks.index') }}" class="block text-center text-blue-600 hover:text-blue-800 text-sm font-medium mt-4">
                            Lihat Semua Feedback →
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback History Table -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-blue-900">Riwayat Feedback</h2>
                <div class="flex gap-2">
                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option>Semua Jenis</option>
                        <option>Saran</option>
                        <option>Kritik</option>
                        <option>Pertanyaan</option>
                        <option>Rating</option>
                    </select>
                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option>Semua Status</option>
                        <option>Pending</option>
                        <option>Dibaca</option>
                        <option>Ditindaklanjuti</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tanggal</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Jenis</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Kategori</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Pesan</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Rating</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $feedback)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm text-gray-700">
                                {{ $feedback->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($feedback->type === 'saran') bg-green-100 text-green-800
                                    @elseif($feedback->type === 'kritik') bg-red-100 text-red-800
                                    @elseif($feedback->type === 'pertanyaan') bg-blue-100 text-blue-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($feedback->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                {{ $feedback->category ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700 max-w-xs">
                                <div class="truncate" title="{{ $feedback->message }}">
                                    {{ Str::limit($feedback->message, 60) }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($feedback->rating)
                                <div class="flex">
                                    @for($i = 0; $i < $feedback->rating; $i++)
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                @else
                                <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($feedback->status === 'pending') bg-gray-100 text-gray-800
                                    @elseif($feedback->status === 'dibaca') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($feedback->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <button type="button" onclick="confirmDeleteFeedback('{{ route('feedbacks.destroy', $feedback) }}')" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                                </svg>
                                Belum ada feedback yang dikirim
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($feedbacks->hasPages())
                <div class="mt-4">
                    {{ $feedbacks->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide rating section
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const ratingSection = document.getElementById('ratingSection');

    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'rating') {
                ratingSection.classList.remove('hidden');
                // Require rating for rating type
                document.querySelector('input[name="rating"]').required = true;
            } else {
                ratingSection.classList.add('hidden');
                document.querySelector('input[name="rating"]').required = false;
            }
        });
    });

    // Star rating interaction
    const stars = document.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            document.querySelector(`input[name="rating"][value="${value}"]`).checked = true;

            // Update star colors
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });

        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('text-yellow-300');
                }
            });
        });

        star.addEventListener('mouseout', function() {
            const selectedValue = document.querySelector('input[name="rating"]:checked')?.value;
            stars.forEach(s => {
                s.classList.remove('text-yellow-300');
                if (selectedValue && s.getAttribute('data-value') <= selectedValue) {
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.add('text-gray-300');
                }
            });
        });
    });

    // Confirmation Delete Function
    function confirmDeleteFeedback(url) {
        Swal.fire({
            title: 'Hapus Feedback?',
            text: 'Feedback yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit hidden form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>
@endsection
