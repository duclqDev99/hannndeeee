<?php

namespace Botble\SharedModule\Forms\Settings;

use Botble\Setting\Forms\SettingForm;
use Botble\SharedModule\Http\Requests\TelegramChatIDSettingRequest;

class TelegramChatIDSettingForm extends SettingForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setSectionTitle('Setting chat ID telegram')
            ->setSectionDescription('Add chat ID')
            ->setFormOption('template', 'plugins/shared-module::settings.telegram-chat-id')
            ->setValidatorClass(TelegramChatIDSettingRequest::class)
            // ->add('tele_chat_id_hgf_order_noti', 'text',[
            //     'label' => 'Thông báo đơn hàng HGF',
            //     'value' => setting('tele_chat_id_hgf_order_noti','')
            // ])
            // ->add('tele_chat_id_hgf_accounting_department', 'text',[
            //     'label' => 'Thông báo cho bộ phận kế toán HGF',
            //     'value' => setting('tele_chat_id_hgf_accounting_department','')
            // ])
            // ->add('tele_chat_id_retail_order_noti', 'text',[
            //     'label' => 'Thông báo đơn hàng cho Retail',
            //     'value' => setting('tele_chat_id_retail_order_noti','')
            // ])
            // ->add('tele_chat_id_retail_accounting_department', 'text',[
            //     'label' => 'Thông báo cho bộ phận kế toán Retail',
            //     'value' => setting('tele_chat_id_retail_accounting_department','')
            // ])

            ->add('tele_chat_id_receipt_issue_material', 'text',[
                'label' => 'Thông báo xuất/nhập kho nguyên phụ liệu cho HGF',
                'value' => setting('tele_chat_id_receipt_issue_material','')
            ])
            ->add('tele_chat_id_order_retail', 'text',[
                'label' => 'Thông báo YCSX, đơn hàng, báo giá cho Retail',
                'value' => setting('tele_chat_id_order_retail','')
            ])
            ->add('tele_chat_id_order_hgf', 'text',[
                'label' => 'Thông báo YCSX, đơn hàng, báo giá cho HGF',
                'value' => setting('tele_chat_id_order_hgf','')
            ])
            ->add('tele_chat_id_receipt_issue', 'text',[
                'label' => 'Thông báo xuất/nhập kho thành phẩm, đại lý, HUB, showroom',
                'value' => setting('tele_chat_id_receipt_issue','')
            ])
            ->add('tele_chat_id_order_purchase', 'text',[
                'label' => 'Thông báo đơn hàng, thanh toán sản phẩm cho đại lý/showroom',
                'value' => setting('tele_chat_id_order_purchase','')
            ])
            ->add('tele_chat_id_error', 'text',[
                'label' => 'Thông báo error',
                'value' => setting('tele_chat_id_error','')
            ])
            ->add('tele_chat_id_ec_product', 'text',[
                'label' => 'Thông báo tạo/cập nhật sản phẩm',
                'value' => setting('tele_chat_id_ec_product','')
            ])
            ->add('tele_chat_id_report_daily', 'text',[
                'label' => 'Báo cáo hằng ngày',
                'value' => setting('tele_chat_id_report_daily','')
            ])
            ;
    }
}
