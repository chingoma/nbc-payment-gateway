<?php
namespace Lockminds\NBCPaymentGateway\Database;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Lockminds\NBCPaymentGateway\Models\NBCApiSetting;

class CrdbSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("nbc_api_settings")->truncate();
        $settings = new NBCApiSetting();
        $settings->access_name = 'nbctest';

        $settings->account_name = 'Informats Technologies LTD';
        $settings->msisdn = '25565877788';
        $settings->biller_code = 'IND';
        $settings->base_url = 'https://sal-accessgwtest.nbc.co.tz:8443/';
        $settings->username = 'InformatsTechnologiesLTD';

        $settings->password = 'tYEVuMl';
        $settings->grant_type = 'password';
        $settings->environment = 'test';
        $settings->access_token = 'WAdGo_-pCIC2nwRuXoUf7PRA5g3tBw2WaQI2hjxudS5FmDkDIQzdxY2vLE74kE3eF0e9nczqsjjUq7lEqm8CdUxJf53IdXuJbLYIq2812i_eTcm5dFjMfAZScuTtUW8yQO94C7Er9JB1G7UM6W8wQ5sugEQFPI6nVQyzB3sivkVFSXBfRz_gbNAwEJfFYoLvR-93JbMgNomSTgRwro4m-5b-QSb_1ufp5cAfBvRaHaQ';
        $settings->brand_id = '3438';
        $settings->brand_pin = '0205';
        $settings->language = 'en';
        $settings->account_to_wallet_type = 'REQMFICI';
        $settings->save();
    }
}

