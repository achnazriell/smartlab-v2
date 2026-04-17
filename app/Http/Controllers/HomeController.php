<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalMurid = User::whereHas('roles', function ($query) {
            $query->where('name', 'Murid');
        })->count();

        $totalGuru = User::whereHas('roles', function ($query) {
            $query->where('name', 'Guru');
        })->count();

        $totalClasses = Classes::count();
        $totalSubjects = Subject::count();

        $muridPerTahun = User::whereHas('roles', function ($query) {
            $query->where('name', 'Murid');
        })
            ->select(DB::raw('EXTRACT(YEAR FROM created_at) as year'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM created_at)'))
            ->get();

        $totals = $muridPerTahun->pluck('total')->toArray();

        // Ambil tahun ajaran aktif (untuk chart bisa semua, atau filter)
        $activeAcademicYear = AcademicYear::active()->first();

        // Guru per mapel (menggunakan teacher_subject_assignments)
        $guruPerMapel = DB::table('teacher_subject_assignments')
            ->join('subjects', 'teacher_subject_assignments.subject_id', '=', 'subjects.id')
            ->select('subjects.name_subject', DB::raw('COUNT(DISTINCT teacher_subject_assignments.teacher_id) as total'))
            ->groupBy('subjects.name_subject')
            ->get();

        $mapelLabels = $guruPerMapel->pluck('name_subject');
        $mapelTotals = $guruPerMapel->pluck('total');

        return view('Admins.dashboardAdmin', compact(
            'totalMurid',
            'totalGuru',
            'totalClasses',
            'totalSubjects',
            'totals',
            'mapelLabels',
            'mapelTotals'
        ));
    }
}
