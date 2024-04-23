<?php

namespace Lockminds\NBCPaymentGateway\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function onSuccessResponseData($data): JsonResponse
    {
        return response()->json(['status' => true, 'data' => $data]);
    }

    protected function onSuccessResponse($message): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message]);
    }

    protected function onErrorResponse(string $message = '', string $code = ''): JsonResponse
    {
        return response()->json(['status' => false, 'code' => $code, 'message' => $message], 400);
    }

}
