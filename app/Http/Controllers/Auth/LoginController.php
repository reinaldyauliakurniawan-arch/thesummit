<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;
class LoginController extends Controller{
public function showLoginForm(){return view('auth.login');}
public function login(Request $r){$c=$r->validate(['email'=>'required|email','password'=>'required|string']);
if(Auth::attempt($c,$r->boolean('remember'))){$r->session()->regenerate();return redirect()->intended(route('dashboard'));}
return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');}
public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/');}
}
