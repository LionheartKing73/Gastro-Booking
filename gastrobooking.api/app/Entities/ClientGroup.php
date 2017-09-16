<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

class ClientGroup extends Model
{
    use Eloquence;
    public $table = "client_group";

    public $timestamps = false;

    public $primaryKey = "ID";

    public function client(){
        return ClientGroup::hasOne(Client::class, 'ID_client');
    }
    public function friend(){
        return ClientGroup::hasOne(Client::class, 'ID_grouped_client');
    }



}
