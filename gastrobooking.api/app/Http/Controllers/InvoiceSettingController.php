<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 4/12/17
 * Time: 2:04 PM
 */

namespace App\Http\Controllers;

use App\Entities\Invoice;
use App\Repositories\InvoiceSettingRepository;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Webpatser\Uuid\Uuid;
use Dingo\Api\Routing\Helpers;

class InvoiceSettingController extends Controller
{
    use Helpers;
    protected $invoiceSettingRepository;

    public function __construct(InvoiceSettingRepository $invoiceSettingRepository)
    {
        $this->invoiceSettingRepository = $invoiceSettingRepository;
    }

    public function getInvoiceSetting(Request $request){
        $restaurant = $this->invoiceSettingRepository->getInvoiceSetting($request);
//        $response = $this->response->paginator($restaurant);
        return $restaurant;
    }
}