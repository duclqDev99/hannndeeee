<?php

namespace Botble\SharedModule\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\SharedModule\Forms\Settings\TelegramChatIDSettingForm;
use Illuminate\Http\Request;
use Botble\Setting\Http\Controllers\SettingController;

class TelegramSettingController extends SettingController
{
    public function editChatId(){
        $this->pageTitle(trans('plugins/social-login::social-login.settings.title'));

        return TelegramChatIDSettingForm::create()->renderForm();
    }
    public function updateChatId(Request $request){
        $data = $request->only([
            'tele_chat_id_receipt_issue_material',
            'tele_chat_id_order_retail',
            'tele_chat_id_order_hgf',
            'tele_chat_id_receipt_issue',
            'tele_chat_id_order_purchase',
            'tele_chat_id_error',
            'tele_chat_id_ec_product',
            'tele_chat_id_report_daily',
        ]);
        $this->saveSettings($data);

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }
}
