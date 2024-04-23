<?php

namespace Lockminds\NBCPaymentGateway\Commands;

use Illuminate\Console\Command;

class NBCPaymentGatewayCommand extends Command
{
    public $signature = 'nbc-payment-gateway';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
