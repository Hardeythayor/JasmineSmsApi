<?php

namespace Database\Seeders;

use App\Models\SmsGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmsGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sms_gateways = array(
			array(
				'name' => "vonage",
				'credentials' => [
                    'api_key' => 'fd108944',
                    'api_secret' => 'go12f6huKjSRblf5',
                ],
				'status' => 'active'
			),
			array(
				'name' => "eims",
				'credentials' => [
                    'url' => 'http://43.249.30.190:20003',
                    "account" => "0223-C0007",
                    "password" => "3FB1F4D7",
                ],
				'status' => 'active'
			),
            array(
				'name' => "easy_sms",
				'credentials' => [
                    'url' => 'https://restapi.easysendsms.app/v1/rest/sms/send',
                    "account" => "jasmqlqlqnvo12025",
                    "password" => "jasminekali22",
                    'api_key' => 'me71rygjp14ghaprgurg2hvdy6vhiyfe'
                ],
				'status' => 'active'
			),
		);
		foreach ($sms_gateways as $gateway) {
			$service = SmsGateway::updateOrCreate($gateway);
		}
    }
}
