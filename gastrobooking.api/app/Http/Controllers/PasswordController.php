<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{

    public function sendResetLink(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return ["error" => "User doesn't Exist!"];
        }

        $token = $this->generateToken(50);

        $user->token = $token;

        $user->save();

        $resetLink = $request->header('referer').'#/app/password/reset/'.$token;

        Mail::send('emails.reset-password', compact('resetLink'), function ($m) use($user) {
            $m->from('cesko@gastro-booking.com', "Gastro Booking");
            $m->to($user->email, $user->name)->subject('Reset Password');
        });

        return ['success' => "Email sent!"];
    }


    public function resetPassword(Request $request)
    {
        $user = User::where('token', $request->token)->first();

        if(!$user){
            return ["error" => "Invalid token!"];
        }

        $user->token = '';
        $user->password = bcrypt($request->password);
        $user->save();

        return ['success' => "Passwordn changed!"];
    }

    function generateToken($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        return $token;
    }

    function getTokenEmail($token){
        $user = User::where('token', $token)->first();

        if(!$user){
            return ["error" => "User doesn't Exist!"];
        }

        return ['email' => $user->email];
    }
}
