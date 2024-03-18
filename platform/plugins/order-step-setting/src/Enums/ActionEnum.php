<?php

namespace Botble\OrderStepSetting\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PENDING()
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class ActionEnum extends Enum
{
    public static $langPath = 'plugins/sales::orders.statuses';
    
    // Retail steps
    // Step 1
    public const RETAIL_SALE_CREATE_ORDER = 'sale_create_order';
    public const RETAIL_SALE_REQUESTING_APPROVE_ORDER = 'retail_sale_requesting_approve_order';
    public const RETAIL_SALE_MANAGER_CONFIRM_ORDER = 'retail_sale_manager_confirm';
    public const HGF_ADMIN_CONFIRM_ORDER = 'hf_admin_confirm_order';

    // step 2
    public const RETAIL_SALE_CREATE_QUOTATION = 'retail_sale_create_quotation';
    public const RETAIL_SEND_QUOTATION = 'retail_send_quotation';
    public const RETAIL_SALE_MANAGER_CONFIRM_QUOTATION = 'retail_sale_manager_confirm_quotation';
    public const CUSTOMER_CONFIRM_QUOTATION = 'customer_confirm_quotation';

    // Step 3
    public const CUSTOMER_SIGN_CONTRACT = 'customer_sign_contact';
    public const CUSTOMER_DEPOSIT = 'customer_deposit';

    // Step 4
    public const RETAIL_SALE_CREATE_PRODUCTION = 'retail_sale_create_production';
    public const RETAIL_SALE_SEND_PRODUCTION = 'retail_sale_send_production';
    public const HGF_ADMIN_CONFIRM_PRODUCTION = 'hgf_admin_confirm_production';
    public const HGF_ADMIN_SHIPPING = 'hgf_admin_shipping';

    // Step 5
    public const RETAIL_SALE_CONFIRM_RECEIVE_PRODUCT = 'retail_sale_confirm_receive_product';
    public const ACCOUNTANT_CONFIRM_DEBT = 'accountant_confirm_debt';
}
