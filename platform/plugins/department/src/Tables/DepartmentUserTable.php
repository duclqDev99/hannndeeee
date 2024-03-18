<?php

namespace Botble\Department\Tables;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Department\Models\Department;
use Botble\Department\Models\DepartmentUser;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class DepartmentUserTable extends TableAbstract
{

    protected $hasOperations = false;

    public function setup(): void
    {
        $this
            ->model(User::class)
            ->addActions([
                EditAction::make()
                    ->route('department-user.edit'),
                DeleteAction::make()
                    ->route('department-user.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('fullname', function (User $item) {
                return $item?->name;
            })
            ->editColumn('department_code', function (User $item) {
                $arrName = [];
                foreach ($item->department as $d) {
                    $arrName[] = get_departments()->where('code', $d->department_code)->first()?->name;
                }
                return implode(",", $arrName);

                $department = get_departments()->where('code', $item?->department_code)->first();
                return $department->name ?: '---';
            })
            ->editColumn('role', function (User $item) {
                $role = $item->roles->first();
                return $role ? $role->name : '';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->where([
                ['super_user',    '!=', 1],
                ['manage_supers', '!=', 1],
            ])
            ->with([
                'roles:id,name'
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('fullname')
                ->orderable(false)
                ->searchable(false)
                ->title('Họ tên'),
            Column::make('email')
                ->orderable(false)
                ->searchable(false),
            Column::make('phone')
                ->orderable(false)
                ->searchable(false)
                ->title('Số điện thoại'),
            Column::make('department_code')
                ->orderable(false)
                ->searchable(false)
                ->title('Bộ phận'),
            Column::make('role')
                ->orderable(false)
                ->searchable(false)
                ->title('Vài trò'),
        ];
    }

    public function buttons(): array
    {
        return [];
        return $this->addCreateButton(route('department-user.create'), 'department-user.create');
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
        return [
            'department_id' => [
                'title' => 'Bộ phận',
                'type' => 'select',
                'choices' => Department::pluck('name', 'id')->toArray(),
                // 'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
