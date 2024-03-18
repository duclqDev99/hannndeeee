<a
    class="editable status"
    data-type="select"
    data-source="{{ route('stock.list.json') }}"
    data-pk="{{ $item->id }}"
    data-url="{{ route('stock.assign') }}"
    data-value="{{ $role }}"
    data-title="{{ trans('core/acl::users.assigned_role') }}"
    href="#"
>
    {{ $role }}
</a>