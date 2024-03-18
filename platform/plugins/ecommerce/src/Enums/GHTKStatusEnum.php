<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

class GHTKStatusEnum extends Enum
{
    public const CANCEL = 'cancel';

    public const WAITING_PENDING = 'waiting_pending';

    public const HAS_RECEIVED = 'has_received';

    public const RECEIVED_THE_GOODS  = 'received_the_goods';

    public const DELIVERING = 'delivering';

    public const DELIVERED = 'delivered';

    public const FOR_CONTROL = 'for_control';

    public const NOT_GET_THE_GOODS = 'not_get_the_goods';

    public const DELAY_GET_THE_GOODS = 'delay_get_the_goods';

    public const NOT_DELIVER_GOODS = 'not_deliver_goods';

    public const DELAY_DELIVERY = 'delay_delivery';

    public const REPAYMENT_DEBT_HAS_BEEN_CHECKED = 'repayment_debt_has_been_checked';

    public const PICKING_UP_GOODS = 'picking_up_goods';

    public const REIMBURSEMENT_ORDER = 'reimbursement_order';

    public const RETURNING_THE_GOODS = 'returning_the_goods';

    public const RETURNED_THE_GOODS = 'returned_the_goods';

    public const SHIPPER_HAS_RECEIVED_THE_GOODS = 'shipper_has_received_the_goods';

    public const SHIPPER_DID_NOT_RECEIVE_THE_GOODS = 'shipper_did_not_receive_the_goods';

    public const SHIPPER_NOTI_DELAY_GET_THE_GOODS = 'shipper_noti_delay_get_the_goods';

    public const SHIPPER_NOTI_DELIVERED = 'shipper_noti_delivered';

    public const SHIPPER_NOTI_NOT_DELIVERY = 'shipper_noti_not_delivery';

    public const SHIPPER_NOTI_DELAY_DELIVERED = 'shipper_noti_delay_delivered';

    public static $langPath = 'plugins/ecommerce::shipping.ghtk_statuses';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::HAS_RECEIVED,
            self::DELIVERING,
            self::DELIVERED,
            self::PICKING_UP_GOODS,
            self::SHIPPER_HAS_RECEIVED_THE_GOODS,
            self::SHIPPER_NOTI_DELAY_GET_THE_GOODS,
            self::SHIPPER_NOTI_DELIVERED,
            self::SHIPPER_NOTI_DELAY_DELIVERED,
            self::RECEIVED_THE_GOODS
            => 'info',

            self::DELIVERED,
            self::FOR_CONTROL,
            self::REPAYMENT_DEBT_HAS_BEEN_CHECKED
            => 'success',

            self::DELAY_GET_THE_GOODS,
            self::DELAY_DELIVERY,
            self::RETURNED_THE_GOODS,
            self::RETURNING_THE_GOODS,
            self::RETURNED_THE_GOODS,
            self::SHIPPER_DID_NOT_RECEIVE_THE_GOODS,
            => 'warning',

            self::CANCEL,
            self::NOT_GET_THE_GOODS,
            self::NOT_DELIVER_GOODS,
            self::SHIPPER_DID_NOT_RECEIVE_THE_GOODS,
            self::SHIPPER_NOTI_NOT_DELIVERY
            => 'danger',
            
            default => 'primary',
        };

        return BaseHelper::renderBadge($this->label(), $color);
    }
}
