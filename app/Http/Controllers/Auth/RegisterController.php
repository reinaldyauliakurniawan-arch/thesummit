<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\Hash;use App\Models\User;
class RegisterController extends Controller{
public function showRegistrationForm(){return view('auth.register');}
public function register(Request $r){$d=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|unique:users,email','password'=>'required|string|min:8|confirmed']);
User::create(['name'=>$d['name'],'email'=>$d['email'],'password'=>Hash::make($d['password'])]);Auth::login(Auth::user());return redirect()->route('dashboard');}
}
