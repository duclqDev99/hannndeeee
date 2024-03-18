<?php

namespace Botble\Sales\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Forms\FormBuilder;
use Botble\Sales\Forms\CustomerForm;
use Botble\Sales\Http\Requests\CustomerRequest;
use Botble\Sales\Models\Customer;
use Botble\Sales\Tables\CustomerTable;

class CustomerController extends BaseController
{
    public function index(CustomerTable $table)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.customer.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.create'));

        return $formBuilder->create(CustomerForm::class)->renderForm();
    }

    public function store(CustomerRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->create($request->input());

        event(new CreatedContentEvent(CUSTOMER_ORDER_MODULE_SCREEN_NAME, $request, $customer));

        return $response
            ->setPreviousUrl(route('customer-purchase.index'))
            ->setNextUrl(route('customer-purchase.edit', $customer->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Customer $customer, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $customer->name]));

        return $formBuilder->create(CustomerForm::class, ['model' => $customer])->renderForm();
    }

    public function update(Customer $customer, CustomerRequest $request, BaseHttpResponse $response)
    {
        $customer->fill($request->input());

        $customer->save();

        event(new UpdatedContentEvent(CUSTOMER_ORDER_MODULE_SCREEN_NAME, $request, $customer));

        return $response
            ->setPreviousUrl(route('customer-purchase.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Customer $customer, Request $request, BaseHttpResponse $response)
    {
        try {
            $customer->delete();

            event(new DeletedContentEvent(CUSTOMER_ORDER_MODULE_SCREEN_NAME, $request, $customer));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    
    public function postCreateCustomerWhenCreatingOrder(Request $request)
    {
        $requestData = $request->input();
        dd($requestData);

        $dataInsert = [
            'first_name' => $requestData['first_name'],
            'last_name' =>  $requestData['last_name'],
            'email' =>  $requestData['email'],
            'phone' =>  $requestData['phone'],
            'address' =>  $requestData['address'],
            'level' =>  'normal',
        ];
        $customer = Customer::query()->create($dataInsert);

        event(new CreatedContentEvent(CUSTOMER_ORDER_MODULE_SCREEN_NAME, $request, $customer));

        return $this
            ->httpResponse()
            ->setData(compact('customer'))
            ->withCreatedSuccessMessage();
    }
}
