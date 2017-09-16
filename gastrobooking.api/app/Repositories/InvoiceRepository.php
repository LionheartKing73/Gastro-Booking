<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 4/12/17
 * Time: 12:33 PM
 */

namespace App\Repositories;

use App\Entities\Invoice;
use App\Entities\InvoicePayment;
use Illuminate\Http\Request;

class InvoiceRepository
{
    public function getInvoiceNumber($restaurant_id){
        $invoice = Invoice::where('ID_restaurant',$restaurant_id)->max('invoice_number');
        return $invoice;
    }

//    public function getRestaurantAllInvoices($restaurant_id){
//        $invoice = Invoice::where('ID_restaurant',$restaurant_id)->get();
//        return $invoice;
//    }

    public function setInvoice($invoice){
        $invoice = Invoice::insert($invoice);
        return $invoice;
    }

    public function setInvoicePayment($request){
        //TODO if invoice ID is AUTO_INCREMENT, start
//        $invoice_id = Invoice::select('ID')->where('invoice_number', '=', $request->invoice_number)->get();
//        $invoice_payment = array(
//            "ID_invoice" => $invoice_id[0]->ID ? $invoice_id[0]->ID : null,
//            "pay_date" => $request->issue_date ? $request->issue_date : null,
//            "amount" => $request->invoice_payment ? $request->invoice_payment : null,
//            "currency" => $request->currency ? $request->currency : "CZK",
//            "payer_account" => $request->payer_account ? $request->payer_account : null
//        );
        //if invoice ID is AUTO_INCREMENT, end

        $invoice_payment = array(
            "ID_invoice" => $request->invoice_number ? $request->invoice_number : null,
            "pay_date" => $request->issue_date ? $request->issue_date : null,
            "amount" => $request->invoice_payment ? $request->invoice_payment : 0.00,
            "currency" => $request->currency ? $request->currency : "CZK",
            "payer_account" => $request->payer_account ? $request->payer_account : null
        );

        return InvoicePayment::insert($invoice_payment);

    }
}