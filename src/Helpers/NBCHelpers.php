<?php

namespace Lockminds\NBCPaymentGateway\Helpers;

use Illuminate\Support\Carbon;

class NBCHelpers
{

    public static function systemDateTime($dateInput = null): array
    {
        if (empty($dateInput)) {
            $today = now("Africa/Dar_es_Salaam")->toDateString();
        } else {
            $today = date('Y-m-d', strtotime($dateInput));
        }

        $date = $today;
        $newDate = date(' l M, d Y', strtotime($date));
        $response['today'] = $date;
        $response['formatted'] = $newDate;
        $response['timely'] = Carbon::createFromFormat('Y-m-d', $date)->toDateTimeString();

        return $response;
    }

}
