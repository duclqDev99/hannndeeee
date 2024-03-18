<?php

use Botble\Base\Models\BaseModel;
use Botble\OrderRetail\Models\Order;
use Botble\OrderStepSetting\Models\Action;
use Doctrine\DBAL\Query\QueryBuilder;

if (!function_exists('get_action')) {
    function get_action(string $action_code, int $order_id): BaseModel|QueryBuilder|null
    {
        return Action::where('action_code', $action_code)
            ->whereHas('step.order', fn ($q) => $q->where('retail_orders.id',  $order_id))
            ->first() ?? null;
    }
}

if (!function_exists('updateOrderTotalAmount')) {
    function updateOrderTotalAmount(int $order_id)
    {
        $order = Order::find($order_id);
        if($order){
           $totalAmount = 0;
           foreach($order->products as $product){
               $totalAmount += ($product->qty * $product->price);
           }
           $order->update(['amount' => $totalAmount]);
        } 
    }
}

