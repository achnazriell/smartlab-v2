<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/Beranda'; // Default redirect

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override method authenticated untuk handle redirect berdasarkan role.
     * Method ini DIPASTIKAN dipanggil setelah login berhasil.
     *
     * @param Request $request
     * @param mixed $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        // Hapus intended URL untuk mencegah redirect ke halaman sebelumnya
        $request->session()->forget('url.intended');

        // Debug log (opsional, bisa dihapus di production)
        \Log::info('Login attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'timestamp' => now()
        ]);

        // Redirect berdasarkan role dengan prioritas
        if ($user->hasRole('Admin')) {
            return redirect()->route('home')->with('success', 'Selamat datang, Admin!');
        }

        if ($user->hasRole('Guru')) {
            return redirect()->route('homeguru')->with('success', 'Selamat datang, Guru!');
        }

        if ($user->hasRole('Murid')) {
            return redirect()->route('dashboard')->with('success', 'Selamat datang!');
        }

        // Fallback ke default
        return redirect($this->redirectTo)->with('success', 'Login berhasil!');
    }

    /**
     * Override method sendLoginResponse untuk memastikan authenticated() selalu dipanggil.
     * Ini adalah SOLUSI UTAMA untuk masalah redirect tidak konsisten.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        // Selalu panggil authenticated() dan gunakan return value-nya
        $authenticatedResponse = $this->authenticated($request, $this->guard()->user());

        if ($authenticatedResponse) {
            return $authenticatedResponse;
        }

        // Fallback jika authenticated() tidak mengembalikan response
        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Custom login method with validation in Bahasa Indonesia.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validasi input login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi harus terdiri dari minimal 6 karakter.',
        ]);

        // Coba login
        if (!Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return back()->withErrors([
                'email' => 'Alamat email atau kata sandi salah.',
            ])->withInput($request->except('password'));
        }

        // Jika berhasil, gunakan sendLoginResponse yang sudah di-override
        return $this->sendLoginResponse($request);
    }

    /**
     * Override redirectPath untuk consistency.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/Beranda';
    }
}
