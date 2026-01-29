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


        return view('users.feedback', compact('feedbacks'));
    }

    public function create()
    {
        $user = Auth::user();


        return view('feedback.create-siswa');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:saran,kritik,pertanyaan,rating',
            'rating' => 'nullable|integer|min:1|max:5',
            'message' => 'required|string|min:10|max:2000',
            'category' => 'nullable|string'
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'rating' => $request->rating,
            'message' => $request->message,
            'category' => $request->category,
            'status' => 'pending'
        ]);

        return redirect()->route('feedbacks.index')
            ->with('success', 'Feedback berhasil dikirim! Terima kasih atas masukan Anda.');
    }

    public function destroy(Feedback $feedback)
    {
        // Hanya pemilik feedback yang bisa menghapus
        if ($feedback->user_id !== Auth::id()) {
            abort(403);
        }

        $feedback->delete();

        return redirect()->route('feedbacks.index')
            ->with('success', 'Feedback berhasil dihapus.');
    }
}
