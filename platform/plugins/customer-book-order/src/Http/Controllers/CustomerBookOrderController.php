<?php

namespace Botble\CustomerBookOrder\Http\Controllers;

use Botble\CustomerBookOrder\Http\Requests\CustomerBookOrderRequest;
use Botble\CustomerBookOrder\Models\CustomerBookOrder;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\CustomerBookOrder\Tables\CustomerBookOrderTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\CustomerBookOrder\Forms\CustomerBookOrderForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\DB;

class CustomerBookOrderController extends BaseController
{
    public function index(CustomerBookOrderTable $table)
    {
        PageTitle::setTitle(trans('plugins/customer-book-order::customer-book-order.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/customer-book-order::customer-book-order.create'));

        return $formBuilder->create(CustomerBookOrderForm::class)->renderForm();
    }

    public function store(CustomerBookOrderRequest $request, BaseHttpResponse $response)
    {
        $customerBookOrder = CustomerBookOrder::query()->create($request->input());

        event(new CreatedContentEvent(CUSTOMER_BOOK_ORDER_MODULE_SCREEN_NAME, $request, $customerBookOrder));

        return $response
            ->setPreviousUrl(route('customer-book-order.index'))
            ->setNextUrl(route('customer-book-order.edit', $customerBookOrder->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function storeFront(CustomerBookOrderRequest $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();

        try{
            // Lưu file vào thư mục storage
            $stringImg = '[' . implode(',', array_map(function ($image) {
                // Đường dẫn đến thư mục chứa ảnh trên máy chủ
                $directory = 'images/customer';
                $path = $image->storeAs($directory, $image->getClientOriginalName());
                return "'" . $directory . '/' . $image->getClientOriginalName() . "'";
            }, $request->file('image'))) . ']';

            $request->merge(['image' => $stringImg]);

            //Kiểm tra file có phải dạng ảnh không
            $customerBookOrder = CustomerBookOrder::query()->create($request->input());
            event(new CreatedContentEvent(CUSTOMER_BOOK_ORDER_MODULE_SCREEN_NAME, $request, $customerBookOrder));
        }catch(Exception $err){
            DB::rollBack();
            dd(123);
            return $response
                ->setError()
                ->setMessage(__('Đã có lỗi xảy ra!!'));
        }

        DB::commit();

        return $response
            ->setMessage(__('Bạn đã tạo đơn đặt hàng thành công!! Chúng tôi sẽ phản hồi sớm nhất đến bạn.'));
    }

    public function edit(CustomerBookOrder $customerBookOrder, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $customerBookOrder->name]));

        return $formBuilder->create(CustomerBookOrderForm::class, ['model' => $customerBookOrder])->renderForm();
    }

    public function update(CustomerBookOrder $customerBookOrder, CustomerBookOrderRequest $request, BaseHttpResponse $response)
    {
        $customerBookOrder->fill($request->input());

        $customerBookOrder->save();

        event(new UpdatedContentEvent(CUSTOMER_BOOK_ORDER_MODULE_SCREEN_NAME, $request, $customerBookOrder));

        return $response
            ->setPreviousUrl(route('customer-book-order.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(CustomerBookOrder $customerBookOrder, Request $request, BaseHttpResponse $response)
    {
        try {
            $customerBookOrder->delete();

            event(new DeletedContentEvent(CUSTOMER_BOOK_ORDER_MODULE_SCREEN_NAME, $request, $customerBookOrder));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
