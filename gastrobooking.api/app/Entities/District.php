<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 5/25/17
 * Time: 11:54 AM
 */

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

class District extends Model
{
    use Eloquence;

    public $table = "district";

}