<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 4/12/17
 * Time: 2:08 PM
 */

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

class InvoiceSetting extends Model
{
    use Eloquence;

    public $table = "invoice_setting";
}