<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 4/12/17
 * Time: 2:06 PM
 */

namespace App\Repositories;

use App\Entities\InvoiceSetting;
use Illuminate\Http\Request;

class InvoiceSettingRepository
{
    public function getInvoiceSetting(Request $request){
        $invoice = InvoiceSetting::all();
        return $invoice;
    }
}