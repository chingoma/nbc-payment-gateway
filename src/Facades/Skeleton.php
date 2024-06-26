<?php

namespace Lockminds\NBCPaymentGateway\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lockminds\NBCPaymentGateway\Skeleton
 */
class Skeleton extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lockminds\NBCPaymentGateway\Skeleton::class;
    }
}
