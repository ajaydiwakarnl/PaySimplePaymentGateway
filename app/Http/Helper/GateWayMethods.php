<?php


namespace App\Http\Helper;
use App\Http\Helper\GatewayTest;

class GateWayMethods extends GatewayTest
{

    public function testPayment()
    {
        $gateWayTest = new GatewayTest();
        $gateWayTest->testPayment();
    }
}
