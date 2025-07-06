<?php

namespace Database\Seeders;

use App\Models\ThirdPartyNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThirdPartyNumbersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thirdparty_numbers = array(
			array(
				'label' => "skt",
				'phone' => '821086418560',
				'status' => 'active'
			),
			array(
				'label' => "kt",
				'phone' => '821068055831',
				'status' => 'active'
			),
            array(
				'label' => "lgu",
				'phone' => '821081324952',
				'status' => 'active'
			),
		);
		foreach ($thirdparty_numbers as $gateway) {
			$service = ThirdPartyNumber::updateOrCreate($gateway);
		}
    }
}
