<?php

namespace Botble\Sales\Http\Controllers;

use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Sales\Tables\SalesTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sales\Forms\SalesForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Sales\Tables\HgfAdminOrderTable;

class HgfAdminController extends BaseController
{
    public function index(HgfAdminOrderTable $table)
    {
        PageTitle::setTitle(trans('Yêu cầu sản xuất'));

        return $table->renderTable();
    }


}
