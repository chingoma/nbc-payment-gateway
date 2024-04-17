<?php

namespace Lockminds\CrdbPaymentGateway\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lockminds\CrdbPaymentGateway\CrdbPaymentGateway
 */
class CrdbPaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lockminds\CrdbPaymentGateway\CrdbPaymentGateway::class;
    }
}
