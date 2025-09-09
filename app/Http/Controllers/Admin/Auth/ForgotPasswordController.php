<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
class ForgotPasswordController extends Controller
{
    
    use SendsPasswordResetEmails;
    
     public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

     protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function broker(){
        return Password::broker('admins');
    }

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */


    public function showLinkRequestForm()
    {
        return view('admin.auth.passwords.email');
    }
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = User::where('email', $request->email)->first();

        // if (!$user || $user->status == 0) {
        //     return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);

        // }

        if (!$user || $user->status == 0) {
            return back()->withErrors([
                'email' => 'We could not find an active user with that email address.',
            ]);
        }


        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );
        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }
}
