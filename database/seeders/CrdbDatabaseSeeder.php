<?php

namespace Lockminds\CrdbPaymentGateway\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CrdbDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CrdbSettingsSeeder::class);
    }
}
