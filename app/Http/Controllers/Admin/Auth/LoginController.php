<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
        $this->middleware('auth:admin')->only('logout');
    }
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    protected function redirectTo()
    {
        return route('admin.dashboard');
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
     
        $request->session()->forget('guard_admin'); 
       
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->route('admin.dashboard');
    }

    // protected function authenticated(Request $request, $user)
    // {
    //     $users = User::findOrFail($user['id']);
    //     if ($user->status == 0 || $users->status == 0) {
    //         $this->guard()->logout();
    //         $contact_admin = optional(Setting::where('code', 'contact_admin')->first())->value;
    //         session()->put('failure_Message', 'Your account is currently disabled. Please contact '. $contact_admin);
    //         return view('admin.auth.login');
    //     }
    
    //     return redirect()->intended($this->redirectTo());
    // }
}
