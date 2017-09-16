<?php
/**
 * Created by PhpStorm.
 * User: yido
 * Date: 10/6/16
 * Time: 9:38 AM
 */

namespace App\Entities;
use App\Cart;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Sofa\Eloquence\Eloquence;

class Client extends Model
{
    use Eloquence;
    public $table = "client";

    public $timestamps = false;

    public $primaryKey = "ID";

    public $fillable = [
      "ID_diet", "email_new","email_update","email_restaurant_update"
    ];

    public function user(){
        return $this->belongsTo(User::class, 'ID_user');
    }

    public function cart(){
        return $this->hasOne(Cart::class, 'ID_cart');
    }

    public function scopeSearchClient($query, Request $request){
        if ($request->has('search')){
            $search_key = $request->get('search');
            return $query->search($search_key, ["user.name", "user.email" ]);
        }
    }

    public function scopeFilterByFriends($query, Request $request){
//        $friends = ClientGroup::where("ID_client", )
//        return $query->where("approved", '<>', 'Y');
//
    }

}