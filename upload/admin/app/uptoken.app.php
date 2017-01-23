<?php

use Qiniu\Auth;
class UptokenApp extends BackendApp
{   
     function __construct()
    {    parent::__construct();
    }

    function uptoken()
    {
        $accessKey = 'hFaKoTZ9jOLKolYMJbZotnWVGH6_qnUSLGF8y4GY';
        $secretKey = 'P2WJXWY6ROsaINwM89Imqe-TCAM0yWvj_QUciTDC';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'fulucangjjc';
        $upToken = $auth->uploadToken($bucket);
        $arr=array("uptoken"=>$upToken);
        $arr=json_encode($arr);
        echo $arr;

    }
    
}