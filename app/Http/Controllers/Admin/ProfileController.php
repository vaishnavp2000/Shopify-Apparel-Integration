<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.account.profile');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $user_id)
    {
         $this->validate($request,[
            'name'=>'required',
            'email'=>'required|email']);
            $user = User::find($user_id);
            $user->update($request->all());
            return redirect()->route('admin.profile.index')->with('success_message','Profile Updated Successfully');
    }
        public function update_password(Request $request){
            $this->validate($request,[
                'current_password'=>'required',
                'password'=>'required|min:6',
                'password_confirmation'=>'required|same:password']);

            $user = User::find(Auth::user()->id);
            if(!Hash::check($request->current_password,$user->password)){
                return redirect()->route('admin.profile.index')->with('error_message','Password does not match');
            }

            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('admin.profile.index')->with('success_message','Password Updated Successfully');
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
