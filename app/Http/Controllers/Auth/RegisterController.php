<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function register(Request $request)
    {
        if ($request->user_type === 'guru') {
            return $this->registerGuru($request);
        }
        return $this->registerMurid($request);
    }

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected $redirectTo = '/';

    /**
     * Validasi dan Registrasi Murid.
     */
    public function registerMurid(Request $request)
    {
        try {
            $this->validateMurid($request);

            // Buat user baru
            $murid = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => 'siswa',
                'graduation_date' => Carbon::now()->addYears(3),
                'password' => Hash::make($request->password),
            ])->assignRole('Murid');

            Auth::login($murid);
            return redirect('/PilihKelas');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('activeTab', 'murid'); // Menyimpan informasi tab aktif
        }
    }

    public function registerGuru(Request $request)
    {
        try {
            $this->validateGuru($request);

            // Buat user baru
            $guru = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'NIP' => $request->NIP,
                'password' => Hash::make($request->password),
            ])->assignRole('Guru');

            Auth::login($guru);
            return redirect('/teacher/dashboard/')->with('success', 'Registrasi Guru berhasil!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('activeTab', 'guru'); // Menyimpan informasi tab aktif
        }
    }

    protected function validateMurid(Request $request)
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama belum diisi.',
            'name.string' => 'Nama harus berupa huruf.',
            'name.max' => 'Nama terlalu panjang.',
            'email.required' => 'Email belum diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password belum diisi.',
            'password.string' => 'Password harus berupa huruf.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Password tidak cocok dengan konfirmasi.',
        ]);
    }
    /**
     * Validasi data Guru.
     */
    protected function validateGuru(Request $request)
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'NIP' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama belum diisi.',
            'name.string' => 'Nama harus berupa huruf.',
            'name.max' => 'Nama terlalu panjang.',
            'email.required' => 'Email belum diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang.',
            'email.unique' => 'Email sudah digunakan.',
            'NIP.required' => 'NIP belum diisi.',
            'NIP.string' => 'NIP harus berupa string.',
            'NIP.max' => 'NIP terlalu panjang.',
            'NIP.unique' => 'NIP sudah digunakan.',
            'password.required' => 'Password belum diisi.',
            'password.string' => 'Password harus berupa huruf.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Password tidak cocok dengan konfirmasi.',
        ]);
    }
    /**
     * Validasi data Guru.
     */

    /**
     * Handle the user after login based on role.
     */
    // protected function authenticated(Request $request, $user)
    // {
    //     if ($user->hasRole('Admin')) {
    //         return redirect('/admin');
    //     } elseif ($user->hasRole('Guru')) {
    //         return redirect('/guru');
    //     } elseif ($user->hasRole('Murid')) {
    //         return redirect('/murid');
    //     }
    //     return redirect('/');
    // }

}
