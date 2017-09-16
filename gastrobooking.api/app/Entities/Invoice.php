<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 4/12/17
 * Time: 1:32 PM
 */

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

class Invoice extends Model
{
    use Eloquence;

    public $table = "invoice";

    public function restaurant(){
        return $this->belongsTo(Restaurant::class, 'ID_restaurant', 'id');
    }

    public function payments(){
        return $this->hasMany(InvoicePayment::class, 'ID_invoice', 'ID');
    }
    
    public function paymentSum(){
        return $this->hasMany(InvoicePayment::class, 'ID_invoice', 'ID')
            ->selectRaw('ID_invoice, sum(amount) as paid')
            ->groupBy('ID_invoice');
    }
}