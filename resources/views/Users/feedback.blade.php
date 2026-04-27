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

        {{-- ===================== ALERT SUCCESS ===================== --}}
        @if(session('success'))
        <div id="alert-success"
             class="flex items-center gap-3 mb-6 p-4 bg-green-50 border border-green-300 text-green-800 rounded-xl shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="font-medium">{{ session('success') }}</p>
            <button onclick="document.getElementById('alert-success').remove()" class="ml-auto text-green-500 hover:text-green-700">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- ===================== ALERT ERROR UMUM ===================== --}}
        @if($errors->any())
        <div id="alert-error"
             class="flex items-start gap-3 mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold mb-1">Feedback gagal dikirim. Perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="document.getElementById('alert-error').remove()" class="text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        @endif

        <div class="grid grid-cols-1 gap-8">
            <!-- Form Feedback -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-bold text-blue-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        Kirim Feedback Baru
                    </h2>

                    <form action="{{ route('feedbacks.store') }}" method="POST" class="space-y-6" id="feedbackForm">
                        @csrf

                        {{-- Jenis Feedback --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Feedback <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach(['saran' => ['label'=>'Saran','desc'=>'Usulan perbaikan','border'=>'hover:border-blue-500'],
                                          'kritik' => ['label'=>'Kritik','desc'=>'Masukan konstruktif','border'=>'hover:border-red-500'],
                                          'pertanyaan' => ['label'=>'Pertanyaan','desc'=>'Butuh penjelasan','border'=>'hover:border-green-500'],
                                          'rating' => ['label'=>'Rating','desc'=>'Penilaian sistem','border'=>'hover:border-yellow-500']] as $val => $opt)
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer {{ $opt['border'] }} transition-colors
                                    {{ old('type') == $val ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                    <input type="radio" name="type" value="{{ $val }}" class="mr-3"
                                        {{ old('type') == $val ? 'checked' : '' }} required>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $opt['label'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $opt['desc'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('type')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <select name="category"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    {{ $errors->has('category') ? 'border-red-400' : '' }}">
                                <option value="">Pilih Kategori (Opsional)</option>
                                <option value="akademik"  {{ old('category') == 'akademik'  ? 'selected' : '' }}>Akademik</option>
                                <option value="fasilitas" {{ old('category') == 'fasilitas' ? 'selected' : '' }}>Fasilitas</option>
                                <option value="sistem"    {{ old('category') == 'sistem'    ? 'selected' : '' }}>Sistem Aplikasi</option>
                                <option value="guru"      {{ old('category') == 'guru'      ? 'selected' : '' }}>Tenaga Pengajar</option>
                                <option value="lainnya"   {{ old('category') == 'lainnya'   ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        {{-- Rating (hanya muncul jika type = rating) --}}
                        <div id="ratingSection" class="{{ old('type') == 'rating' ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rating Sistem (1-5) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" class="hidden"
                                        {{ old('rating') == $i ? 'checked' : '' }}>
                                    <svg class="w-10 h-10 star {{ old('rating') && old('rating') >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                        data-value="{{ $i }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </label>
                                @endfor
                            </div>
                            @error('rating')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Pesan --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pesan <span class="text-red-500">*</span>
                            </label>

                            {{-- Info minimum karakter --}}
                            <div class="flex items-center gap-2 mb-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Tulis pesan minimal <strong>20 karakter</strong> agar admin dapat memahami masukan Anda dengan baik.
                            </div>

                            <textarea name="message" id="messageInput" rows="6"
                                class="w-full p-4 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    {{ $errors->has('message') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}"
                                placeholder="Tuliskan feedback Anda secara detail (minimal 20 karakter)..."
                                maxlength="2000"
                                required>{{ old('message') }}</textarea>

                            {{-- Counter karakter --}}
                            <div class="flex justify-between items-center mt-1">
                                <div id="charWarning" class="text-sm hidden">
                                    <span class="text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span id="charWarningText"></span>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 ml-auto">
                                    <span id="charCount">0</span>/2000 karakter
                                    <span id="minIndicator" class="ml-2 font-medium"></span>
                                </div>
                            </div>

                            @error('message')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="flex justify-end">
                            <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Riwayat Feedback -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-blue-900">Riwayat Feedback</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Jenis</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Kategori</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Pesan</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Rating</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($feedbacks as $feedback)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 text-sm text-gray-600 whitespace-nowrap">
                                {{ $feedback->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($feedback->type == 'saran') bg-blue-100 text-blue-800
                                    @elseif($feedback->type == 'kritik') bg-red-100 text-red-800
                                    @elseif($feedback->type == 'pertanyaan') bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($feedback->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
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
                                <button type="button"
                                    onclick="confirmDeleteFeedback('{{ route('feedbacks.destroy', $feedback) }}')"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                </svg>
                                <p class="font-medium">Belum ada feedback yang dikirim</p>
                                <p class="text-sm mt-1">Sampaikan saran atau masukan Anda di form di atas</p>
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
// ============================================================
// PERBAIKAN: fungsi ini di luar DOMContentLoaded agar bisa
// dipanggil dari atribut onclick="..." di HTML
// ============================================================
function confirmDeleteFeedback(url) {
    Swal.fire({
        title: 'Hapus Feedback?',
        text: 'Feedback yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const MIN_CHARS = 20;
    const MAX_CHARS = 2000;

    // ============================================================
    // Karakter counter & validasi panjang pesan
    // ============================================================
    const messageInput  = document.getElementById('messageInput');
    const charCount     = document.getElementById('charCount');
    const charWarning   = document.getElementById('charWarning');
    const charWarningText = document.getElementById('charWarningText');
    const minIndicator  = document.getElementById('minIndicator');
    const submitBtn     = document.getElementById('submitBtn');

    function updateCharCount() {
        const len = messageInput.value.length;
        charCount.textContent = len;

        if (len === 0) {
            // Reset
            minIndicator.textContent = '';
            charWarning.classList.add('hidden');
            messageInput.classList.remove('border-red-400', 'bg-red-50', 'border-green-400');
            messageInput.classList.add('border-gray-300');
        } else if (len < MIN_CHARS) {
            const remaining = MIN_CHARS - len;
            charWarningText.textContent = `Tambahkan ${remaining} karakter lagi (minimal ${MIN_CHARS} karakter).`;
            charWarning.classList.remove('hidden');
            minIndicator.textContent = `(kurang ${remaining} karakter)`;
            minIndicator.className = 'ml-2 font-medium text-red-500';
            messageInput.classList.add('border-red-400', 'bg-red-50');
            messageInput.classList.remove('border-gray-300', 'border-green-400');
        } else {
            charWarning.classList.add('hidden');
            minIndicator.textContent = '✓ Cukup';
            minIndicator.className = 'ml-2 font-medium text-green-600';
            messageInput.classList.remove('border-red-400', 'bg-red-50');
            messageInput.classList.add('border-green-400');
        }
    }

    // Init counter jika ada nilai lama (setelah validasi gagal)
    if (messageInput.value.length > 0) {
        updateCharCount();
    }

    messageInput.addEventListener('input', updateCharCount);

    // ============================================================
    // Cegah submit jika pesan kurang dari MIN_CHARS
    // ============================================================
    document.getElementById('feedbackForm').addEventListener('submit', function (e) {
        const len = messageInput.value.trim().length;
        if (len < MIN_CHARS) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Pesan Terlalu Pendek',
                text: `Pesan minimal ${MIN_CHARS} karakter. Saat ini pesan Anda baru ${len} karakter. Tambahkan ${MIN_CHARS - len} karakter lagi.`,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK, Saya Perbaiki'
            }).then(() => {
                messageInput.focus();
            });
            return;
        }

        // Validasi rating wajib jika type = rating
        const typeSelected = document.querySelector('input[name="type"]:checked');
        if (typeSelected && typeSelected.value === 'rating') {
            const ratingSelected = document.querySelector('input[name="rating"]:checked');
            if (!ratingSelected) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Rating Belum Dipilih',
                    text: 'Harap pilih rating bintang (1-5) terlebih dahulu.',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }
    });

    // ============================================================
    // Show/hide rating section
    // ============================================================
    const typeRadios   = document.querySelectorAll('input[name="type"]');
    const ratingSection = document.getElementById('ratingSection');

    typeRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'rating') {
                ratingSection.classList.remove('hidden');
            } else {
                ratingSection.classList.add('hidden');
                // Reset rating saat type bukan rating
                document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = false);
                document.querySelectorAll('.star').forEach(s => {
                    s.classList.remove('text-yellow-400', 'text-yellow-300');
                    s.classList.add('text-gray-300');
                });
            }
        });
    });

    // ============================================================
    // Star rating interaction
    // ============================================================
    const stars = document.querySelectorAll('.star');

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            document.querySelector(`input[name="rating"][value="${value}"]`).checked = true;
            stars.forEach(s => {
                if (parseInt(s.getAttribute('data-value')) <= parseInt(value)) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });

        star.addEventListener('mouseover', function () {
            const value = this.getAttribute('data-value');
            stars.forEach(s => {
                if (parseInt(s.getAttribute('data-value')) <= parseInt(value)) {
                    s.classList.add('text-yellow-300');
                }
            });
        });

        star.addEventListener('mouseout', function () {
            const selectedValue = document.querySelector('input[name="rating"]:checked')?.value;
            stars.forEach(s => {
                s.classList.remove('text-yellow-300');
                if (selectedValue && parseInt(s.getAttribute('data-value')) <= parseInt(selectedValue)) {
                    s.classList.add('text-yellow-400');
                } else if (!selectedValue) {
                    s.classList.add('text-gray-300');
                }
            });
        });
    });

    // ============================================================
    // Auto-scroll ke alert error jika ada
    // ============================================================
    const alertError = document.getElementById('alert-error');
    if (alertError) {
        alertError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endsection
