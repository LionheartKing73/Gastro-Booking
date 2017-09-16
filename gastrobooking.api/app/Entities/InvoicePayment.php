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

class InvoicePayment extends Model
{
    use Eloquence;

    public $table = "invoice_payment";

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'ID_invoice', 'ID');
    }
}