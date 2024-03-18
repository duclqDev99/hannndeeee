<?php

namespace Botble\Department\Services;

use Botble\ACL\Events\RoleAssignmentEvent;
use Botble\ACL\Models\Role;
use Botble\ACL\Models\User;
use Botble\Department\Models\DepartmentUser;
use Botble\Support\Services\ProduceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateUserService implements ProduceServiceInterface
{
    public function __construct(protected ActivateUserService $activateUserService)
    {
    }

    public function execute(Request $request): DepartmentUser
    {
        $user = new DepartmentUser();
        $user->fill($request->input());
        $user->password = Hash::make($request->input('password'));
        $user->save();

        if (
            $this->activateUserService->activate($user) &&
            ($roleId = $request->input('role_id')) &&
            $role = Role::query()->find($roleId)
        ) {
            /**
             * @var Role $role
             */
            $role->users()->attach($user->getKey());

            event(new RoleAssignmentEvent($role, $user));
        }

        return $user;
    }
}
