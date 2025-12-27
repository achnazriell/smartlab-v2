<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        // Hitung jumlah total Murid
        $totalMurid = User::whereHas('roles', function ($query) {
            $query->where('name', 'Murid');
        })->count();

        // Hitung jumlah total Guru
        $totalguru = User::whereHas('roles', function ($query) {
            $query->where('name', 'Guru');
        })->count();

        // Karena subject_id dan relasi class sudah tidak ada,
        // maka assignCount dan notAssignCount dibuat default 0
        $assignCount = 0;
        $notAssignCount = $totalguru;

        // Jumlah murid per tahun
        $muridPerTahun = User::selectRaw('YEAR(created_at) as year, COUNT(*) as total')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Murid');
            })
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $totals = $muridPerTahun->pluck('total')->toArray();

        return view('Admins.dashboardAdmin', compact(
            'totalMurid',
            'totalguru',
            'assignCount',
            'notAssignCount',
            'totals'
        ));
    }

}
