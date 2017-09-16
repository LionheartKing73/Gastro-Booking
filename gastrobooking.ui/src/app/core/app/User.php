<?php

namespace App;

use App\Entities\Client;
use App\Entities\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes;

    public $table = "user";

    protected $fillable = ["id","name", "email", "password", "user_type", "status"];

    protected $guerd = [];

    protected $hidden = ['password'];

    public function restaurants(){
        return $this->hasMany(Restaurant::class, 'ID_user');
    }

    public function client(){
        return $this->hasOne(Client::class, 'ID_user');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class,'client_user', 'follower_id', 'client_id')->withPivot('client_id', 'follower_id')->withTimestamps();
    }


}
