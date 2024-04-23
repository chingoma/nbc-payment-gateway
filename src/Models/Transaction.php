<?php

namespace Lockminds\NBCPaymentGateway\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends MasterModel
{
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'transaction_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d'
    ];
}
