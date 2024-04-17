<?php

namespace Lockminds\CrdbPaymentGateway\Commands;

use Illuminate\Console\Command;

class CrdbPaymentGatewayCommand extends Command
{
    public $signature = 'crdb-payment-gateway';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
