<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\Configuration;

class ChargeController extends Controller
{
    public  function index(){
        return view('square');
    }

    public  function create(Request  $request){

        $access_token = "EAAAEKExY0xxSwrxf83OcqLlAdobX-UrEf5LOVUOzz8XhlNwVH1Z7ukUdMbflt-m";
        Configuration::getDefaultConfiguration()->setAccessToken($access_token);

        $makeTransaction = new TransactionsApi();
        $location_id = 'LE6GF19FYQ1KX';
        $nonce = $request->input('nonce');

        $body = array (
            "card_nonce" => $nonce,
            "amount_money" => array (
                "amount" => (int) $_POST['amount'],
                "currency" => "USD"
            ),
            "idempotency_key" => uniqid()
        );

        try {
            $result = $makeTransaction->charge($location_id,  $body);
            dd($result);

            // echo '';
            if($result['transaction']['id']){
                echo 'Payment success!';
                echo "Transation ID: ".$result['transaction']['id']."";
            }
        } catch (\SquareConnect\ApiException $e) {
            echo "Exception when calling TransactionApi->charge:";
            var_dump($e->getResponseBody());
        }
    }
}
