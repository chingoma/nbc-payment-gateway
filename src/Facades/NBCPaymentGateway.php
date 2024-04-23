<?php

namespace Lockminds\NBCPaymentGateway\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lockminds\NBCPaymentGateway\NBCPaymentGateway
 */
class NBCPaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lockminds\NBCPaymentGateway\NBCPaymentGateway::class;
    }
}
