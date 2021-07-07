<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SquareConnect\Api\CustomersApi;
use SquareConnect\Api\PaymentsApi;
use SquareConnect\Api\RefundsApi;
use SquareConnect\ApiClient;
use SquareConnect\ApiException;
use SquareConnect\Configuration;
use SquareConnect\Model\CreateCustomerCardRequest;
use SquareConnect\Model\CreateCustomerRequest;
use SquareConnect\Model\CreatePaymentRequest;
use SquareConnect\Model\Money;
use SquareConnect\Model\RefundPaymentRequest;

class SquareController extends Controller
{
    public function __construct()
    {
        $accessToken = env('SQUARE_ACCESS_TOKEN', 'EAAAEA0Oy9027MEMUcIBer6UNmYpsgh069E9FMjaEZfGx48Jw2Ukbh-mfEFmMvPg');
        $this->locationId = env('SQUARE_LOCATION_ID', '90XTYB26P4HAP');
        $defaultApiConfig = new Configuration();
        $defaultApiConfig->setHost("https://connect.squareupsandbox.com");
        $defaultApiConfig->setAccessToken($accessToken);
        $this->defaultApiClient = new ApiClient($defaultApiConfig);
    }

    public function refund($paymentId)
    {
        if (!empty($this->defaultApiClient)) {
            $refundApi = new RefundsApi($this->defaultApiClient);
        }
        $body = new RefundPaymentRequest(); // \SquareConnect\Model\RefundPaymentRequest | An object containing the fields to POST for the request.  See the corresponding object definition for field details.
        $amountMoney = new Money();

        # Monetary amounts are specified in the smallest unit of the applicable currency.
        # This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
        $amountMoney->setAmount(100);
        $amountMoney->setCurrency("USD");

        $body->setPaymentId($paymentId);
        $body->setAmountMoney($amountMoney);
        $body->setIdempotencyKey(uniqid());
        $body->setReason('wrong order');
        try {
            $refundApi->refundPayment($body);

        } catch (Exception $e) {
            echo 'Exception when calling RefundsApi->refundPayment: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function addCustomer() {

        $name = "XXXX";
        $email = "xxxx@gmail.com";

        $customer = new CreateCustomerRequest();
        $customer->setGivenName($name);
        $customer->setEmailAddress($email);


        if (!empty($this->defaultApiClient)) {
            $customersApi = new CustomersApi($this->defaultApiClient);
        }

        try {
            $result = $customersApi->createCustomer($customer);
            $id = $result->getCustomer()->getId();
            return $id;

        } catch (Exception $e) {
            return "";
            Log::info($e->getMessage());
            //echo 'Exception when calling CustomersApi->createCustomer: ', $e->getMessage(), PHP_EOL;
        }

        return "";
    }

    public function addCard(Request $request)
    {
        $cardNonce = $request->nonce;

        if (!empty($this->defaultApiClient)) {
            $customersApi = new CustomersApi($this->defaultApiClient);
        }
        $customerId = $this->addCustomer();

        $body = new CreateCustomerCardRequest();
        $body->setCardNonce($cardNonce);

        $result = $customersApi->createCustomerCard($customerId, $body);

        $card_id = $result->getCard()->getId();
        $card_brand = $result->getCard()->getCardBrand();
        $card_last_four = $result->getCard()->getLast4();
        $card_exp_month = $result->getCard()->getExpMonth();
        $card_exp_year = $result->getCard()->getExpYear();

        $this->charge($customerId, $card_id);


        return redirect()->back();

    }

    public function charge($customerId, $cardId)
    {

        if (!empty($this->defaultApiClient)) {
            $payments_api = new PaymentsApi($this->defaultApiClient);
        }
        $payment_body = new CreatePaymentRequest();

        $amountMoney = new Money();

        # Monetary amounts are specified in the smallest unit of the applicable currency.
        # This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
        $amountMoney->setAmount(100);
        $amountMoney->setCurrency("USD");
        $payment_body->setCustomerId($customerId);
        $payment_body->setSourceId($cardId);
        $payment_body->setAmountMoney($amountMoney);
        if (!empty($this->locationId)) {
            $payment_body->setLocationId($this->locationId);
        }

        # Every payment you process with the SDK must have a unique idempotency key.
        # If you're unsure whether a particular payment succeeded, you can reattempt
        # it with the same idempotency key without worrying about double charging
        # the buyer.
        $payment_body->setIdempotencyKey(uniqid());

        try {
            $result = $payments_api->createPayment($payment_body);
            $transaction_id = $result->getPayment()->getId();
            return $transaction_id;
        } catch (ApiException $e) {
            echo "Exception when calling PaymentsApi->createPayment:";
            var_dump($e->getResponseBody());
        }

    }
}

