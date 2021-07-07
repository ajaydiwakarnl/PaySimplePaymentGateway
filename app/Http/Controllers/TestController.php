<?php

namespace App\Http\Controllers;

use App\Http\Helper\GateWayMethods;
use App\Http\Helper\GatewayTest;
use Controller;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use Omnipay\Paysimple\Gateway;

class TestController extends Controller
{
    public function  doPayment(){
        /**
         * @var $gateway Gateway
         */
        $gateway = Omnipay::create('Paysimple');
        $gateway->setTestMode('false');
        $gateway->setUsername('ajaydiwakar.nl@gmail.com');
        $gateway->setSecret('237359');


        $formData = array('number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
        $response = $gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => $formData))->send();

        if ($response->isRedirect()) {
            // redirect to offsite payment gateway
            dd('redirect');
            $response->redirect();
        } elseif ($response->isSuccessful()) {
            // payment was successful: update database
            dd($response);
            print_r($response);
        } else {
            // payment failed: display message to customer
            dd('failed', $response);
            echo $response->getMessage();
        }
    }
}
