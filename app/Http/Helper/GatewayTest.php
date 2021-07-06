<?php

namespace App\Http\Helper;

use Omnipay\Common\AbstractGateway;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use Omnipay\Paysimple\Gateway;

class GatewayTest
{
    public function testPayment(){
        /**
         * @var $gateway Gateway
         */
        $gateway = Omnipay::create('Paysimple');
        $gateway->setSecret('8989970');

        $formData = array('number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
        $response = $gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => $formData))->send();

        if ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
        } elseif ($response->isSuccessful()) {
            // payment was successful: update database
            print_r($response);
        } else {
            // payment failed: display message to customer
            echo $response->getMessage();
        }
    }

}
