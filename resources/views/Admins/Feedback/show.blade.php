@extends('layouts.app')

@section('title', 'Detail Feedback - Admin')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="p-6">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('feedback.index') }}"
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Feedback
            </a>
        </div>

        <!-- Feedback Detail Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Detail Feedback</h2>
                        <p class="text-sm text-blue-100">ID: #{{ str_pad($feedback->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-blue-100">
                            {{ $feedback->created_at->format('d F Y, H:i') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($feedback->type == 'saran') bg-green-100 text-green-800
                            @elseif($feedback->type == 'kritik') bg-red-100 text-red-800
                            @elseif($feedback->type == 'pertanyaan') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($feedback->type) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- User Info -->
                <div class="flex items-start space-x-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                            {{ strtoupper(substr($feedback->user->name, 0, 2)) }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ $feedback->user->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $feedback->user->email }}</p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $feedback->user->getRoleNames()->first() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Message -->
                    <div class="md:col-span-2">
                        <h4 class="font-medium text-slate-700 mb-2">Pesan Feedback</h4>
                        <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-slate-800 whitespace-pre-line">{{ $feedback->message }}</p>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div>
                        <h4 class="font-medium text-slate-700 mb-2">Informasi</h4>
                        <div class="space-y-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Kategori:</span>
                                <span class="font-medium">{{ $feedback->category ?? 'Tidak ada kategori' }}</span>
                            </div>

                            @if($feedback->rating)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Rating:</span>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $feedback->rating)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                    <span class="ml-2 font-medium">{{ $feedback->rating }}/5</span>
                                </div>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-slate-600">Status:</span>
                                <span class="font-medium">
                                    <form action="{{ route('feedback.update-status', $feedback) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="border-none bg-transparent font-medium focus:ring-0 focus:border-none
                                                    @if($feedback->status == 'pending') text-yellow-600
                                                    @elseif($feedback->status == 'dibaca') text-blue-600
                                                    @else text-green-600 @endif">
                                            <option value="pending" {{ $feedback->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="dibaca" {{ $feedback->status == 'dibaca' ? 'selected' : '' }}>Dibaca</option>
                                            <option value="ditindaklanjuti" {{ $feedback->status == 'ditindaklanjuti' ? 'selected' : '' }}>Ditindaklanjuti</option>
                                        </select>
                                    </form>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
                <form action="{{ route('feedback.destroy', $feedback) }}"
                      method="POST"
                      id="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Feedback
                    </button>
                </form>
                <a href="{{ route('feedback.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete() {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Hapus feedback ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    }
    </script>
@endsection