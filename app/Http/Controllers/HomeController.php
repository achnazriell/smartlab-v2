<?php

namespace App\Http\Controllers;

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

        $muridPerTahun = User::selectRaw('YEAR(created_at) as year, COUNT(*) as total')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Murid');
            })
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $totals = $muridPerTahun->pluck('total')->toArray();

        $guruPerMapel = DB::table('teacher_subjects')
            ->join('subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->select('subjects.name_subject', DB::raw('COUNT(DISTINCT teacher_subjects.teacher_id) as total'))
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
