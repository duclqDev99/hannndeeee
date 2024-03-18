<?php

namespace Database\Seeders;

use Botble\Sales\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++){
            Customer::query()->create(
                [
                    'first_name' => fake()->firstName(), 
                    'last_name' => fake()->lastName(), 
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(), 
                    'avatar' => fake()->imageUrl($width = 600, $height = 600), 
                    'address' => fake()->address(), 
                    'dob' => fake()->date($format = 'Y-m-d', $max = '2000-01-01'), // Ngày sinh ngẫu nhiên trong khoảng từ '2000-01-01' trở về trước,
                    'level' => fake()->randomElement(['normal','special','vip']), 
                    'status' => fake()->randomElement(['published','pending','draft']), 
                    'gender' => fake()->randomElement(['Nam','Nữ','Khác']),
                ]
            );
        };
    }

}
