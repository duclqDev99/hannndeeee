<?php

namespace Botble\Department\Http\Controllers;

use Botble\Department\Http\Requests\DepartmentRequest;
use Botble\Department\Models\Department;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Department\Tables\DepartmentTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Department\Forms\DepartmentForm;
use Botble\Base\Forms\FormBuilder;

class DepartmentController extends BaseController
{
    public function index(DepartmentTable $table)
    {
        PageTitle::setTitle('Danh sách');
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/department::department.create'));

        return $formBuilder->create(DepartmentForm::class)->renderForm();
    }

    public function store(DepartmentRequest $request, BaseHttpResponse $response)
    {
        $department = Department::query()->create($request->input());

        event(new CreatedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $department));

        return $response
            ->setPreviousUrl(route('department.index'))
            ->setNextUrl(route('department.edit', $department->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Department $department, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $department->name]));

        return $formBuilder->create(DepartmentForm::class, ['model' => $department])->renderForm();
    }

    public function update(Department $department, DepartmentRequest $request, BaseHttpResponse $response)
    {
        $department->fill($request->input());

        $department->save();

        event(new UpdatedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $department));

        return $response
            ->setPreviousUrl(route('department.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Department $department, Request $request, BaseHttpResponse $response)
    {
        try {
            if ($department->members()->exists()) throw new \Exception('Bộ phận tồn tại thành viên, không thể xóa!');
            $department->delete();

            event(new DeletedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $department));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
