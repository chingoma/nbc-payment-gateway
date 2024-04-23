<?php

namespace Lockminds\NBCPaymentGateway\Controllers;

use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Lockminds\NBCPaymentGateway\Models\Transaction;

class NBCGatewayController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $transactions = Transaction::get();
            return response()->json([
                "data" => $transactions,
                "status" => 200,
                "message" => "OK"
            ]);
        }catch (\Throwable $exception){
            return response()->json([
                "status" => 202,
                "statusDesc" => $exception->getMessage()
            ]);
        }
    }

    public function create(Request $request): JsonResponse
    {
        ini_set('memory_limit', 2048000000);
        ini_set('max_execution_time', 3600);
        try {
//            \DB::beginTransaction();
            $types = ["PUSH","C2B","B2C"];
            $sex = ["male","female"];
            $customer_name = Factory::create()->name($sex[shuffle($sex)]);
            $gateways = ["TIGO","CRDB"];
            $gateway = $gateways[shuffle($gateways)];
//            Transaction::factory(1)->create([
//                "gateway" => $gateway,
//                "type" => ($gateway == "TIGO") ? $types[shuffle($types)] : "C2B",
//                "msisdn" => $request->msisdn,
//                "amount" => random_int(1000, 99999),
//                "expires_at" => now(getenv("TIMEZONE"))->addDays(random_int(1,7)),
//                "customer_name" => $customer_name,
//                "name" => $customer_name,
//            ]);
            $transaction = new Transaction();
            $transaction->gateway =  $gateways[shuffle($gateways)];
            $transaction->type = ($transaction->gateway == "TIGO") ? $types[shuffle($types)] : "C2B";
            $transaction->name = Factory::create()->name($sex[shuffle($sex)]);
            $transaction->msisdn = $request->msisdn;
            $transaction->customer_name = $transaction->name;
            $transaction->amount = random_int(1000, 99999);
            $transaction->expires_at = now(getenv("TIMEZONE"))->addDays(1);
            $transaction->save();
//            \DB::commit();
            return response()->json([
                "data" => [
                    "reference_number" => $transaction->reference_number,
                    "name" => $transaction->name,
                    "amount" => $transaction->amount,
                    "expires_at" => $transaction->expires_at,
                ],
//                "checksum" => sha1(getenv("TOKEN").$transaction->reference_number),
                "status" => 200,
                "message" => "Reference number created successfully"
            ]);
        }catch (\Throwable $exception){
            report($exception);
//            \DB::rollBack();
            return response()->json([
                "status" => 202,
                "statusDesc" => $exception->getMessage()
            ]);
        }
    }

    private function validateToken(Request $request):bool
    {
        $parser = new Parser(new JoseEncoder());

        $token = $parser->parse($request->token);

        $validator = new Validator();

        try {
            if(!$validator->validate($token, new RelatedTo("1201500"))) {
                return false;
            }

            return true;

        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    public function status(Request $request): JsonResponse
    {

        try {

            if(!$this->validateToken($request)){
                return response()->json([],400);
            }

            if($request->id != 39511710523172){
                return response()->json([],400);
            }

            return response()->json([]);


        }catch (\Exception $exception){
            report($exception);
            return response()->json([],400);
        }

    }

    public function verifyRequest(Request $request): JsonResponse
    {

        try {

            if(!$this->validateToken($request)){
                return response()->json([
                    "status" => 201,
                    "statusDesc" => "Invalid token"
                ]);
            }

            if(sha1($request->token.md5($request->paymentReference)) !== $request->checksum) {
                return response()->json([
                    "status" => 202,
                    "statusDesc" => "Invalid checksum"
                ]);
            }

            $transaction = Transaction::where("reference_number", $request->paymentReference)->first();


            if(empty($transaction)){
                return response()->json([
                    "status" => 204,
                    "statusDesc" => "Invalid payment reference number"
                ]);
            }

            if($transaction->status == "paid"){
                return response()->json([
                    "status" => 203,
                    "statusDesc" => "Payment reference number already paid"
                ]);
            }

            $now = now(getenv('TIMEZONE'));
            if($transaction->expires_at < $now){
                return response()->json([
                    "status" => 205,
                    "statusDesc" => "Payment reference number has expired"
                ]);
            }

            return response()->json([
                "status" => 200,
                "statusDesc" => "success",
                "data" => [
                    "payerName" => $transaction->name,
                    "amount" => $transaction->amount,
                    "amountType" => "FIXED",
                    "currency" => "TZS",
                    "paymentReference" => $transaction->reference_number,
                    "paymentType" => "01",
                    "paymentDesc" => $transaction->description,
                    "payerID" => $transaction->id
                ]
            ]);

        }catch (\Exception $exception){
            report($exception);
            return response()->json(["message" => "unhandled exception"],500);
        }

    }

    public function postPaymentRequest(Request $request): JsonResponse
    {

        try {

            if(!$this->validateToken($request)){
                return response()->json([
                    "status" => 201,
                    "statusDesc" => "Invalid token"
                ]);
            }

            if(sha1($request->token.$request->paymentReference) !== $request->checksum) {
                return response()->json([
                    "status" => 202,
                    "statusDesc" => "Invalid checksum"
                ]);
            }

            $transaction = Transaction::where("reference_number", $request->paymentReference)->first();


            if(empty($transaction)){
                return response()->json([
                    "status" => 204,
                    "statusDesc" => "Invalid payment reference number"
                ]);
            }

            $now = now(getenv('TIMEZONE'));
            if($transaction->expires_at < $now){
                return response()->json([
                    "status" => 205,
                    "statusDesc" => "Payment reference number has expired"
                ]);
            }

            if($transaction->status == "paid") {
                return response()->json([
                    "status" => 206,
                    "statusDesc" => "Duplicate entry",
                    "data" => [
                        "receipt" => $transaction->receipt
                    ]
                ]);
            }

            if($request->amount > 1000000){
                return response()->json([
                    "status" => 208,
                    "statusDesc" => "Customer Limit Exceeded"
                ]);
            }

            if($request->amount > $transaction->amount || $request->amount < $transaction->amount){
                return response()->json([
                    "status" => 210,
                    "statusDesc" => "TransacSon failed Reason:  You must pay exact amount"
                ]);
            }

            $transaction->status = "paid";
            $transaction->save();

            return response()->json([
                "status" => 200,
                "statusDesc" => "success",
                "data" => [
                    "receipt" => $transaction->receipt,
                ]
            ]);


        }catch (\Exception $exception){
            report($exception);
            return response()->json(["message" => "TransacSon failed Reason: ".$exception->getMessage()],301);
        }

    }
}
