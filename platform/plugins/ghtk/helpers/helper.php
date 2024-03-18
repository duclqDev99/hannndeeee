<?php

if (!function_exists('ghtk_match_status_id')) {
    function ghtk_match_status_id(int $ghtk_status_id): string|null
    {
        return match ($ghtk_status_id) {
            -1 => 'cancel',
            1 => 'waiting_pending',
            2 => 'has_received',
            3 => 'received_the_goods',
            4 => 'delivering',
            5 => 'delivered',
            6 => 'for_control',
            7 => 'not_get_the_goods',
            8 => 'delay_get_the_goods',
            9 => 'not_deliver_goods',
            10 => 'delay_delivery',
            11 => 'repayment_debt_has_been_checked',
            12 => 'picking_up_goods',
            13 => 'reimbursement_order',
            20 => 'returning_the_goods',
            21 => 'returned_the_goods',
            123 => 'shipper_has_received_the_goods',
            127 => 'shipper_did_not_receive_the_goods',
            128 => 'shipper_noti_delay_get_the_goods',
            45 => 'shipper_noti_delivered',
            49 => 'shipper_noti_not_delivery',
            410 => 'shipper_noti_delay_delivered',
            default => null
        };
        // status_id documentation link: https://docs.giaohangtietkiem.vn/#tr-ng-th-i-n-h-ng35
    }
}
