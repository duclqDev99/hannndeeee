<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Warehouse\Supports\MaterialIssueHelper;
use Botble\Warehouse\Supports\MaterialReceiptHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MaterialIssueTemplatePdfController extends BaseController
{
    public function index(MaterialOutConfirm $invoiceHelper)
    {
        Assets::addStylesDirectly([
            'vendor/core/core/base/libraries/codemirror/lib/codemirror.css',
            'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.css',
            'vendor/core/core/setting/css/setting.css',
        ])
            ->addScriptsDirectly([
                'vendor/core/core/base/libraries/codemirror/lib/codemirror.js',
                'vendor/core/core/base/libraries/codemirror/lib/css.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/anyword-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/css-hint.js',
                'vendor/core/core/setting/js/setting.js',
            ]);

        $content = $invoiceHelper->getInvoiceTemplate();
        $variables = $invoiceHelper->getVariables();

        return view('plugins/warehouse::material-template-pdf.material-issue-edit-template', compact('content', 'variables'));
    }

    public function update(Request $request, BaseHttpResponse $response)
    {
        BaseHelper::saveFileData(storage_path('app/templates/material-issue.tpl'), $request->input('content'), false);

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function reset(BaseHttpResponse $response)
    {
        File::delete(storage_path('app/templates/material-issue.tpl'));

        return $response->setMessage(trans('core/setting::setting.email.reset_success'));
    }

    public function preview(MaterialIssueHelper $materialHelper)
    {
        $material = $materialHelper->getDataForPreview();

        return $materialHelper->streamInvoice($material);
    }
}
