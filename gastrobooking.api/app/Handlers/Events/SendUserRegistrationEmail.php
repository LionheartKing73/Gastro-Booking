<?php

namespace App\Handlers\Events;

use App\Events\UserWasRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserRegistrationEmail implements ShouldQueue{

    use InteractsWithQueue;

    public function __construct()
    {

    }
    public function handle(UserWasRegistered $event){
        $user = $event->user;

        Mail::send('emails.client', ['user' => $user], function ($m) use($user){
            $m->from('cesko@gastro-booking.com', "Gastro Booking");
            $m->to($user->email, $user->name)->subject('Gastro Booking registration successful');
        });
    }

}