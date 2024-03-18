<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowroomPickAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('showroom_pick_addresses')->truncate();

        // GHTK
        DB::table('showroom_pick_addresses')->insert([
            [
                'showroom_code' => 'hcm',
                'service_type' => 'ghtk',
                'pick_name' => 'Showroom Handee Hồ Chí Minh',
                'pick_email' => null,
                'pick_address_id' => '14858895',
                'province_id' => null,
                'district_id' => null,
                'ward_id' => null,
            ],
            [
                'showroom_code' => 'hn',
                'service_type' => 'ghtk',
                'pick_name' => 'Showroom Handee Hà Nội',
                'pick_email' => null,
                'pick_address_id' => '17749767',
                'province_id' => null,
                'district_id' => null,
                'ward_id' => null,
            ],
            [
                'showroom_code' => 'dn',
                'service_type' => 'ghtk',
                'pick_name' => 'Showroom Handee Đà Nẵng',
                'pick_email' => null,
                'pick_address_id' => '17749765',
                'province_id' => null,
                'district_id' => null,
                'ward_id' => null,
            ]
        ]);

    }
}
