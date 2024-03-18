<?php

namespace Database\Seeders;

use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 15; $i++){
            $listCustomer = Customer::get();
            $index = rand(0, count($listCustomer));
            Order::query()->create(
                [
                    'order_code' => fake()->unique()->postcode(), 
                    'id_user' => $listCustomer[$index]->id, 
                    'username' => $listCustomer[$index]->name,
                    'email' => $listCustomer[$index]->email, 
                    'phone' => $listCustomer[$index]->phone, 
                    'invoice_issuer_name' => fake()->name(), 
                    'document_number' => fake()->swiftBicNumber(), 
                    'title' => fake()->text(25), 
                    'description' => fake()->text(50), 
                    'expected_date' => fake()->dateTimeBetween($startDate = 'now', $endDate = '+1 year'), 
                    'date_confirm' => fake()->dateTimeBetween($startDate = 'now', $endDate = '+1 year'),
                    'status' => fake()->randomElement(['published','pending','draft']),
                ]
            );
        };
    }

}
