<?php
namespace App\Http\Controllers\WebService;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebService\ServiceHelpers;
use App\Repositories\RestaurantRepository;
use App\Repositories\WebServiceRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class WebServiceController
 *
 * @package App\Http\Controllers\WebService
 */
class WebServiceController extends Controller
{
    use Helpers;
    protected $webServiceRepository;

    public function __construct(WebServiceRepository $webServiceRepository)
    {
        $this->webServiceRepository = $webServiceRepository;
    }

    public function run(Request $request) {

        $securityHelper = new ServiceHelpers\EncryptionHelper();
        $input = $securityHelper->decryptRJ256($request->IV, $request->Data);
        $input = trim($input);
        $requestJson = json_decode($input);

        if(!isset($requestJson->Email) || !$requestJson->Email || !isset($requestJson->Password) || !$requestJson->Password ||
            !isset($requestJson->QueryStr) || !$requestJson->QueryStr) {
            return $this->response->errorBadRequest();
        }

        if(!isset($requestJson->ClientAddress) || $requestJson->ClientAddress != $request->ip()) {
            return $this->response->errorForbidden();
        }

        if (RestaurantRepository::authRestaurant($requestJson->Email, $requestJson->Password)) {

            $result = $this->webServiceRepository->runSQLQuery($requestJson->QueryStr);
            $encrypted = $securityHelper->encryptRJ256($request->IV, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));

            return response()->json(['data' => $encrypted, 'message' => 'Success', 'status_code' => 200]);

        }

        return $this->response->errorUnauthorized();
    }
}
