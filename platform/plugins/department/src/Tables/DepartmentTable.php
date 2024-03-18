<?php

namespace Botble\Department\Tables;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Department\Models\Department;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class DepartmentTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Department::class)
            ->addActions([
                EditAction::make()
                    ->route('department.edit'),
                DeleteAction::make()
                    ->route('department.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->collection(get_departments())
            ->editColumn('members_count', function ($item) {
                return User::whereRelation('department', 'department_code', $item->code)->count();
            })
            ->editColumn('operations', fn () => '');
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'code',
                'created_at',
                'status',
            ])->withCount('members');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('name')
                ->searchable(false)
                ->orderable(false),
            Column::make('code')
                ->searchable(false)
                ->orderable(false)
                ->title('Mã bộ phận'),
            Column::make('members_count')
                ->title('Số lượng thành viên')
                ->searchable(false)
                ->orderable(false),
        ];
    }

    public function buttons(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
