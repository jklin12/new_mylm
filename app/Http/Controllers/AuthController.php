<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function index()
    {
        if ( Auth::user()) {
            return redirect()->intended('/');
        }
        return view('pages/login-v1');
    }

    public function do_login(Request $request)
    {
        //print_r($request->all());die;
        request()->validate(
            [
                'username' => 'required',
                'password' => 'required',
            ]
        );

        /*$user = Employee::where('emp_number', $request->post('username'))->where('emp_pwd2', sha1($request->post('password')))->first();

        if ($user) {
            if (!$user->emp_pwd_bcrypt) {
                //$user->emp_pwd_bcrypt = bcrypt($request->post('password'));
                $user->emp_pwd_bcrypt = bcrypt('1234');
                $user->update();
            }
        }*/

        //$kredensil = ['emp_number' => $request->post('username'), 'emp_pwd_bcrypt' => $request->post('password')];
        //echo (sha1($request->post('password')));die;
        $kredensil = $request->only('username','password');
        if (Auth::attempt($kredensil)) {
            $user = Auth::user();
            return redirect()->intended('/');
        }

        return redirect('login')
            ->withInput()
            ->withErrors(['login_gagal' => 'These credentials do not match our records.']);
    }
    public function username()
    {
        return 'username';
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        return Redirect('login');
    }
}
