<?php

namespace Botble\Department\Http\Controllers;

use Botble\ACL\Models\Role;
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
use Botble\Department\Forms\DepartmentUserForm;
use Botble\Department\Http\Requests\DepartmentUserRequest;
use Botble\Department\Models\DepartmentUser;
use Botble\Department\Tables\DepartmentUserTable;
use Illuminate\Support\Facades\Hash;
use Botble\Department\Services\CreateUserService;

class DepartmentUserController extends BaseController
{
    public function index(DepartmentUserTable $table)
    {
        // PageTitle::setTitle(trans('plugins/department::department.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        // PageTitle::setTitle(trans('plugins/department::department.create'));
        PageTitle::setTitle(__('Thêm nhân viên bộ phận'));
        return $formBuilder->create(DepartmentUserForm::class)->renderForm();
    }

    public function store(DepartmentUserRequest $request, CreateUserService $service, BaseHttpResponse $response)
    {
        $form = DepartmentUserForm::create();
        $user = null;

        $form->saving(function () use ($service, $request, &$user) {
            $user = $service->execute($request);
        });

        // event(new CreatedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $departmentUser));

        return $response
            ->setPreviousUrl(route('department-user.index'))
            ->setNextUrl(route('department-user.edit', $user?->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(DepartmentUser $departmentUser, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $departmentUser->username]));

        return $formBuilder->create(DepartmentUserForm::class, ['model' => $departmentUser])->renderForm();
    }

    public function update(DepartmentUser $departmentUser, DepartmentUserRequest $request, BaseHttpResponse $response)
    {
        $departmentUser->fill($request->input());
        if ($request->role_id) {
            $departmentUser->roles()->sync([$request->role_id]);
        }
        $departmentUser->save();


        event(new UpdatedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $departmentUser));

        return $response
            ->setPreviousUrl(route('department-user.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(DepartmentUser $departmentUser, Request $request, BaseHttpResponse $response)
    {
        try {
            $departmentUser->delete();

            event(new DeletedContentEvent(DEPARTMENT_MODULE_SCREEN_NAME, $request, $departmentUser));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
