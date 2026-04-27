<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $feedbacks = Feedback::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('Users.feedback', compact('feedbacks'));
    }

    public function create()
    {
        return view('feedback.create-siswa');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'     => 'required|in:saran,kritik,pertanyaan,rating',
            'rating'   => 'nullable|integer|min:1|max:5',
            'message'  => 'required|string|min:20|max:2000',
            'category' => 'nullable|string',
        ], [
            'type.required'    => 'Jenis feedback wajib dipilih.',
            'type.in'          => 'Jenis feedback tidak valid.',
            'rating.integer'   => 'Rating harus berupa angka.',
            'rating.min'       => 'Rating minimal 1.',
            'rating.max'       => 'Rating maksimal 5.',
            'message.required' => 'Pesan feedback wajib diisi.',
            'message.min'      => 'Pesan feedback terlalu pendek. Minimal 20 karakter agar admin dapat memahami masukan Anda.',
            'message.max'      => 'Pesan feedback terlalu panjang. Maksimal 2000 karakter.',
        ]);

        Feedback::create([
            'user_id'  => Auth::id(),
            'type'     => $request->type,
            'rating'   => $request->rating,
            'message'  => $request->message,
            'category' => $request->category,
            'status'   => 'pending',
        ]);

        return redirect()->route('feedbacks.index')
            ->with('success', 'Feedback berhasil dikirim! Terima kasih atas masukan Anda.');
    }

    public function destroy(Feedback $feedback)
    {
        // Hanya pemilik feedback yang bisa menghapus
        if ($feedback->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus feedback ini.');
        }

        $feedback->delete();

        return redirect()->route('feedbacks.index')
            ->with('success', 'Feedback berhasil dihapus.');
    }
}
