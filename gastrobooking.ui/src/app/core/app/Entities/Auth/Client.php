<?php

namespace App\Entities\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 *
 * @package App\Entities\Auth
 */
class Client extends Model
{
    protected $fillable = ["id","secret", "name"];
    /**
     * @var string
     */
    public $table = "oauth_clients";

}
