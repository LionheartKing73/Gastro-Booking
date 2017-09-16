<?php
namespace App\Http\Controllers\WebService\ServiceHelpers;

use App;

class EncryptionHelper
{
    //Insert the secret key here:
    const KEY = "RjgMGt76OH7nFchlNTu08MPa2w2i/CoB2SXzM8CTTQ0=";
    function decryptRJ256($iv,$encrypted)
    {
        //PHP strips "+" and replaces with " ", but we need "+" so add it back in...
        $encrypted = str_replace(' ', '+', $encrypted);

        //get all the bits
        $key = base64_decode(self::KEY);
        $iv = base64_decode($iv);
        $encrypted = base64_decode($encrypted);

        $rtn = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
//        $rtn = $this->unpad($rtn);
        return ($rtn);
    }


    function encryptRJ256($iv,$data)
    {
        $key = base64_decode(self::KEY);
        $iv = base64_decode($iv);
        $rtn = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);
        return base64_encode($rtn);
    }
}