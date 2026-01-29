<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Menampilkan semua feedback dengan filter sederhana
     */
    public function index(Request $request)
    {
        // Query dasar dengan eager loading
        $query = Feedback::with(['user'])->latest();

        // Filter berdasarkan jenis feedback
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Pagination
        $perPage = $request->per_page ?? 20;
        $feedbacks = $query->paginate($perPage)->withQueryString();

        // Statistik sederhana
        $stats = [
            'total' => Feedback::count(),
            'pending' => Feedback::where('status', 'pending')->count(),
            'dibaca' => Feedback::where('status', 'dibaca')->count(),
            'ditindaklanjuti' => Feedback::where('status', 'ditindaklanjuti')->count(),
        ];

        // Statistik per jenis
        $typeStats = Feedback::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type')
            ->toArray();

        // Rata-rata rating
        $avgRating = Feedback::whereNotNull('rating')->avg('rating');

        return view('admins.feedback.index', compact(
            'feedbacks',
            'stats',
            'typeStats',
            'avgRating'
        ));
    }

    /**
     * Menampilkan detail feedback
     */
    public function show(Feedback $feedback)
    {
        $feedback->load(['user' => function($q) {
            $q->with('roles');
        }]);

        return view('admins.feedback.show', compact('feedback'));
    }

    /**
     * Update status feedback
     */
    public function updateStatus(Request $request, Feedback $feedback)
    {
        $request->validate([
            'status' => 'required|in:pending,dibaca,ditindaklanjuti'
        ]);

        $oldStatus = $feedback->status;
        $feedback->update(['status' => $request->status]);

        return back()->with('success', 'Status feedback berhasil diperbarui.');
    }

    /**
     * Hapus feedback
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()
            ->route('admin.feedback.index')
            ->with('success', 'Feedback berhasil dihapus.');
    }

    /**
     * Tandai semua pending sebagai dibaca
     */
    public function markAllAsRead()
    {
        $count = Feedback::where('status', 'pending')->count();

        Feedback::where('status', 'pending')->update(['status' => 'dibaca']);

        return back()->with('success', "{$count} feedback pending telah ditandai sebagai dibaca.");
    }
}
