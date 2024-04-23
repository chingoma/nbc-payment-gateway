<?php

namespace Lockminds\NBCPaymentGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Lockminds\NBCPaymentGateway\Traits\UuidForKey;
use OwenIt\Auditing\Contracts\Auditable;

abstract class MasterModel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;
}
