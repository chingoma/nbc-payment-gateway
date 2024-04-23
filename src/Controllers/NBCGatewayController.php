<?php

namespace Lockminds\NBCPaymentGateway\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lockminds\NBCPaymentGateway\Helpers\NBCHelpers;
use Lockminds\NBCPaymentGateway\Models\NBCApiSetting;
use Lockminds\NBCPaymentGateway\Models\NBCRequest;
use Lockminds\NBCPaymentGateway\DTOs\RefundTransactionDTO;
use Mtownsend\XmlToArray\XmlToArray;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;

class NBCGatewayController extends Controller
{
    private NBCApiSetting $apiSetting;

    public function __construct()
    {
        $status = NBCApiSetting::first();
        $this->apiSetting = $status;
    }

    public function callback(Request $request): JsonResponse
    {
        try {

            $nbcRequest = NBCRequest::where('reference_id', $request->ReferenceID)->first();

            if (empty($nbcRequest)) {
                return response()->json([
                    'ResponseCode' => 'BILLER-18-0000-E',
                    'ResponseStatus' => false,
                    'ResponseDescription' => 'Callback failed',
                    'ReferenceID' => $request->ReferenceID]);
            }

            $nbcRequest->callback_date = now("Africa/Dar_es_Salaam");
            $nbcRequest->callback_status = $request->Status;
            $nbcRequest->callback_description = $request->Description;
            $nbcRequest->transaction_id = $request->MFSTransactionID;
            $nbcRequest->callback_amount = $request->Amount;

            if ($request->Status) {
                $nbcRequest->status = 'success';
            } else {
                $nbcRequest->status = 'failed';
            }

            $nbcRequest->save();

            return response()->json(['ResponseCode' => 'BILLER-18-0000-S',
                'ResponseStatus' => true,
                'ResponseDescription' => 'Callback successful',
                'ReferenceID' => $request->ReferenceID]);

        } catch (Throwable $throwable) {
            return response()->json([
                'ResponseCode' => 'BILLER-18-0000-E',
                'ResponseStatus' => false,
                'ResponseDescription' => 'Callback failed',
                'ReferenceID' => $request->ReferenceID]);
        }
    }

    public function wallet_to_account(Request $request): mixed
    {

        try {
            $requestData = (object) XmlToArray::convert($request->getContent());
            Log::info($request->getContent());
            if($requestData->CUSTOMERREFERENCEID != "INFOMP0B001"){
                $xml = <<<XML
<?xml version="1.0"?>
<COMMAND>
<TYPE>SYNC_BILLPAY_RESPONSE</TYPE>
<TXNID>$requestData->TXNID</TXNID>
<REFID>$requestData->CUSTOMERREFERENCEID</REFID>
<RESULT>TF</RESULT>
<ERRORCODE>error010</ERRORCODE>
<ERRORDESC>Invalid Customer Reference Number</ERRORDESC>
<MSISDN>$requestData->MSISDN</MSISDN>
<FLAG>N</FLAG>
<CONTENT/>
</COMMAND>
XML;
                return response()->xml($xml);
            }

            if($requestData->AMOUNT < 1000){
                $xml = <<<XML
<?xml version="1.0"?>
<COMMAND>
<TYPE>SYNC_BILLPAY_RESPONSE</TYPE>
<TXNID>$requestData->TXNID</TXNID>
<REFID>$requestData->CUSTOMERREFERENCEID</REFID>
<RESULT>TF</RESULT>
<ERRORCODE>error015</ERRORCODE>
<ERRORDESC>Amount too low. Try a larger amount</ERRORDESC>
<MSISDN>$requestData->MSISDN</MSISDN>
<FLAG>N</FLAG>
<CONTENT/>
</COMMAND>
XML;
                return response()->xml($xml);
            }

            if($requestData->AMOUNT > 1000){
                $xml = <<<XML
<?xml version="1.0"?>
<COMMAND>
<TYPE>SYNC_BILLPAY_RESPONSE</TYPE>
<TXNID>$requestData->TXNID</TXNID>
<REFID>$requestData->CUSTOMERREFERENCEID</REFID>
<RESULT>TF</RESULT>
<ERRORCODE>error014</ERRORCODE>
<ERRORDESC>Amount too high. Try a smaller amount</ERRORDESC>
<MSISDN>$requestData->MSISDN</MSISDN>
<FLAG>N</FLAG>
<CONTENT/>
</COMMAND>
XML;
                return response()->xml($xml);
            }

            $xml = <<<XML
<?xml version="1.0"?>
<COMMAND>
<TYPE>SYNC_BILLPAY_RESPONSE</TYPE>
<TXNID>$requestData->TXNID</TXNID>
<REFID>$requestData->CUSTOMERREFERENCEID</REFID>
<RESULT>TS</RESULT>
<ERRORCODE>error000</ERRORCODE>
<ERRORDESC>Successful transaction</ERRORDESC>
<MSISDN>$requestData->MSISDN</MSISDN>
<FLAG>Y</FLAG>
<CONTENT/>
</COMMAND>
XML;
            return response()->xml($xml);

        } catch (Throwable $throwable) {
            $xml = <<<XML
<?xml version="1.0"?>
<COMMAND>
<TYPE>SYNC_BILLPAY_RESPONSE</TYPE>
<TXNID>$requestData->TXNID</TXNID>
<REFID>$requestData->CUSTOMERREFERENCEID</REFID>
<RESULT>TF</RESULT>
<ERRORCODE>error100</ERRORCODE>
<ERRORDESC>Unhandled exeption</ERRORDESC>
<MSISDN>$requestData->MSISDN</MSISDN>
<FLAG>N</FLAG>
<CONTENT/>
</COMMAND>
XML;
            return response()->xml($xml);
        }
    }

    public function transactions(Request $request): JsonResponse
    {
        try {
            $transactions = NBCRequest::latest()->paginate();

            return response()->json($transactions);
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable);
        }
    }

    public function create_token(Request $request): JsonResponse
    {
        if ($this->apiSetting->environment == 'test') {
            $endpoint = $this->apiSetting->base_url.'InformatsTechnologies2DM-GetToken';
        } else {
            $endpoint = $this->apiSetting->base_url.'token';
        }

        try {
            $payload = ['form_params' => [
                'username' => $this->apiSetting->username,
                'password' => $this->apiSetting->password,
                'grant_type' => $this->apiSetting->grant_type],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'text/xml',
            ])->asForm()->send('POST', $endpoint, $payload)->onError(function ($error) {
                return response()->json($error);
            });

            $body = (object) json_decode($response->body());

            if (empty($body->error_description)) {
                $newSettings = NBCApiSetting::find($this->apiSetting->id);
                $newSettings->access_token = $body->access_token;
                $newSettings->save();
                $this->apiSetting = $newSettings;

                return response()->json($body);
            } else {
                return $this->onErrorResponse($body->error_description);
            }
        } catch (\Exception $exception) {
            return $this->onErrorResponse($exception);
        }
    }

    public function push_bill_pay(Request $request): JsonResponse
    {
        $requestData = $request;//CreateRequestDTO::fromRequest($request);

        if ($this->apiSetting->environment == 'test') {
            $endpoint = $this->apiSetting->base_url.'InformatsTechnologies2DM-PushBillpay';
        } else {
            $endpoint = $this->apiSetting->base_url.'API/BillerPayment/BillerPay';
        }

        try {
            $systemDate = NBCHelpers::systemDateTime();
            $nbcRequest = new NBCRequest();
            $nbcRequest->date = $systemDate['timely'];
            $nbcRequest->amount = floatval($requestData->amount);
            $nbcRequest->customer_id = $requestData->id;
            $nbcRequest->customer_msisdn = $requestData->customer_msisdn;
            $nbcRequest->access_name = $this->apiSetting->access_name;
            $nbcRequest->biller_msisdn = $this->apiSetting->msisdn;
            $nbcRequest->remarks = $requestData->remark;
            $nbcRequest->save();

            $payload = json_encode([
                'CustomerMSISDN' => $requestData->customer_msisdn,
                'BillerMSISDN' => $this->apiSetting->msisdn,
                'Amount' => $requestData->amount,
                'Remarks' => $requestData->remarks,
                'ReferenceID' => $this->apiSetting->biller_code.$nbcRequest->id,
            ]);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'username' => $this->apiSetting->username,
                'password' => $this->apiSetting->password,
                'Authorization' => 'Bearer '.$this->apiSetting->access_token,
            ])->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return response()->json($error);
                });

            $body = (object) json_decode($response->body());

            if (! empty($body->Message)) {
                return $this->onErrorResponse($body->Message);
            }

            if ($body->ResponseStatus) {
                $nbcRequest->transaction_date = now("Africa/Dar_es_Salaam")->toDateTimeString();
                $nbcRequest->status = 'submitted';
                $nbcRequest->response_status = $body->ResponseStatus;
                $nbcRequest->response_code = $body->ResponseCode;
                $nbcRequest->response_description = $body->ResponseDescription;
                $nbcRequest->reference_id = $body->ReferenceID ?? '';
                $nbcRequest->save();

                return response()->json($body);
            } else {
                return $this->onErrorResponse($body->ResponseDescription);
            }

        } catch (\Exception $exception) {
            return $this->onErrorResponse($exception);
        }
    }

    public function account_to_wallet(Request $request): JsonResponse
    {
        $requestData = $request;//AccountToWalletDTO::fromRequest($request);

        if ($this->apiSetting->environment == 'test') {
            $endpoint = $this->apiSetting->base_url.'InformatsTechnologies2Crdb';
        } else {
            $endpoint = $this->apiSetting->base_url.'API/BillerPayment/BillerPay';
        }

        try {
            $systemDate = NBCHelpers::systemDateTime();
            $nbcRequest = new NBCRequest();
            $nbcRequest->date = $systemDate['timely'];
            $nbcRequest->type = 'receipt';
            $nbcRequest->amount = floatval($requestData->amount);
            $nbcRequest->customer_id = $requestData->id;
            $nbcRequest->customer_msisdn = $requestData->customer_msisdn;
            $nbcRequest->access_name = $this->apiSetting->access_name;
            $nbcRequest->biller_msisdn = $this->apiSetting->msisdn;
            $nbcRequest->remarks = $requestData->remark;
            $nbcRequest->save();

            $payloadRaw = [
                'TYPE' => $this->apiSetting->account_to_wallet_type,
                'REFERENCEID' => $this->apiSetting->account_to_wallet_type.rand(11111,99999),
                'MSISDN' => "25566000205",
                'PIN' => $this->apiSetting->brand_pin,
                'MSISDN1' => $requestData->customer_msisdn,
                'AMOUNT' => floatval($requestData->amount),
                'SENDERNAME' => $requestData->sender_name,
                'BRAND_ID' => $this->apiSetting->brand_id,
                'LANGUAGE1' => $requestData->language1,
            ];

            $payload = ArrayToXml::convert($payloadRaw, 'COMMAND');

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml;charset=UTF-8',
                'Accept' => 'text/xml',
                'username' => $this->apiSetting->username,
                'password' => $this->apiSetting->password,
                'Authorization' => 'Bearer '.$this->apiSetting->access_token,
            ])->send('POST', $endpoint, [
                'body' => $payload,
            ])->onError(function ($error) {
                return response()->json($error);
            });

            $body = (object) XmlToArray::convert($response->body());

            if (! empty($body->TXNID)) {
//                $nbcRequest->transaction_date = now(env('TIMEZONE'))->toDateTimeString();
//                $nbcRequest->status = 'submitted';
//                $nbcRequest->response_status = $body->ResponseStatus;
//                $nbcRequest->response_code = $body->ResponseCode;
//                $nbcRequest->response_description = $body->ResponseDescription;
//                $nbcRequest->reference_id = $body->ReferenceID ?? '';
//                $nbcRequest->save();

                return response()->json($body);
            } else {
                $nbcRequest->status = 'submitted';
                $nbcRequest->response_status = 'failed';
                $nbcRequest->response_code = $body->TXNSTATUS;
                $nbcRequest->response_description = $body->MESSAGE;
                $nbcRequest->reference_id = '';
                $nbcRequest->save();

                return response()->json($body);
            }

        } catch (\Exception $exception) {
            return $this->onErrorResponse($exception);
        }
    }

    public function w2a(Request $request): JsonResponse
    {

        $requestData = $request;//AccountToWalletDTO::fromRequest($request);

        if ($this->apiSetting->environment == 'test') {
            $endpoint = $this->apiSetting->base_url.'InformatsTechnologies2Crdb';
        } else {
            $endpoint = $this->apiSetting->base_url.'API/BillerPayment/BillerPay';
        }

        try {
            $systemDate = NBCHelpers::systemDateTime();
            $nbcRequest = new NBCRequest();
            $nbcRequest->date = $systemDate['timely'];
            $nbcRequest->type = 'receipt';
            $nbcRequest->amount = floatval($requestData->amount);
            $nbcRequest->customer_id = $requestData->id;
            $nbcRequest->customer_msisdn = $requestData->customer_msisdn;
            $nbcRequest->access_name = $this->apiSetting->access_name;
            $nbcRequest->biller_msisdn = $this->apiSetting->msisdn;
            $nbcRequest->remarks = $requestData->remark;
            $nbcRequest->save();

            $payloadRaw = [
                'TYPE' => $this->apiSetting->wallet_to_account_type,
                'MSISDN' => $requestData->customer_msisdn,
                'AMOUNT' => floatval($requestData->amount),
                'COMPANYNAME' => $this->apiSetting->account_name,
                'CUSTOMERREFERENCEID' => $this->apiSetting->wallet_to_account_type.rand(11111,99999),
                'SENDERNAME' => $requestData->sender_name
            ];

            $payload = ArrayToXml::convert($payloadRaw, 'COMMAND');

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml;charset=UTF-8',
                'Accept' => 'text/xml',
                'username' => $this->apiSetting->username,
                'password' => $this->apiSetting->password,
                'Authorization' => 'Bearer '.$this->apiSetting->access_token,
            ])->send('POST', $endpoint, [
                'body' => $payload,
            ])->onError(function ($error) {
                return response()->json($error);
            });

            $body = (object) XmlToArray::convert($response->body());

            if (! empty($body->TXNID)) {
//                $nbcRequest->transaction_date = now(env('TIMEZONE'))->toDateTimeString();
//                $nbcRequest->status = 'submitted';
//                $nbcRequest->response_status = $body->ResponseStatus;
//                $nbcRequest->response_code = $body->ResponseCode;
//                $nbcRequest->response_description = $body->ResponseDescription;
//                $nbcRequest->reference_id = $body->ReferenceID ?? '';
//                $nbcRequest->save();

                return response()->json($body);
            } else {
                $nbcRequest->status = 'submitted';
                $nbcRequest->response_status = 'failed';
                $nbcRequest->response_code = $body->TXNSTATUS;
                $nbcRequest->response_description = $body->MESSAGE;
                $nbcRequest->reference_id = '';
                $nbcRequest->save();

                return response()->json($body);
            }

        } catch (\Exception $exception) {
            return $this->onErrorResponse($exception);
        }

    }

    public function refund_transaction(Request $request): JsonResponse
    {
        $requestData = RefundTransactionDTO::fromRequest($request);
        $endpoint = $this->apiSetting->base_url.'API/Reverse/ReverseTransaction';
        try {
            $systemDate = NBCHelpers::systemDateTime();
            $nbcRequest = new NBCRequest();
            $nbcRequest->date = $systemDate['timely'];
            $nbcRequest->amount = floatval($requestData->amount);
            $nbcRequest->customer_id = $requestData->id;
            $nbcRequest->customer_msisdn = $requestData->customer_msisdn;
            $nbcRequest->access_name = $this->apiSetting->access_name;
            $nbcRequest->biller_msisdn = $this->apiSetting->msisdn;
            $nbcRequest->remarks = $requestData->remark;
            $nbcRequest->type = 'refund';
            $nbcRequest->save();

            $payload = json_encode([
                'CustomerMSISDN' => $requestData->customer_msisdn,
                'ChannelMSISDN' => $requestData->customer_msisdn.$this->apiSetting->biller_code,
                'ChannelPIN' => $this->apiSetting->msisdn,
                'Amount' => $requestData->amount,
                'MFSTransactionID' => $requestData->transaction_id,
                'PurchaseReferenceID' => $requestData->reference_id,
                'ReferenceID' => $this->apiSetting->biller_code.$nbcRequest->id,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'username' => $this->apiSetting->username,
                'password' => $this->apiSetting->password,
                'Authorization' => 'Bearer '.$this->apiSetting->access_token,
            ])->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return response()->json($error);
                });

            $body = (object) json_decode($response->body());

            if (! empty($body->Message)) {
                return $this->onErrorResponse($body->Message);
            }

            if ($body->ResponseStatus) {
                $nbcRequest->transaction_date = now("Africa/Dar_es_Salaam")->toDateTimeString();
                $nbcRequest->status = 'submitted';
                $nbcRequest->response_status = $body->ResponseStatus;
                $nbcRequest->response_code = $body->ResponseCode;
                $nbcRequest->response_description = $body->ResponseDescription;
                $nbcRequest->reference_id = $body->ReferenceID ?? '';
                $nbcRequest->save();

                return response()->json($body);
            } else {
                return $this->onErrorResponse($body->ResponseDescription);
            }

        } catch (\Exception $exception) {
            return $this->onErrorResponse($exception);
        }
    }
}
