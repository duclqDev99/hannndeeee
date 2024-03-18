<a
    class="edit-not-quantity"
    data-type="select"
    data-source="{{ route('roles.list.json') }}"
    data-pk="{{ $item->id }}"
    data-url="{{ route('hub-stock.reduce-quantity') }}"
    data-value="{{ $item?->id ?: 0 }}"
    data-title="{{ trans('core/acl::users.assigned_role') }}"
    href="#"
>
    {{ $item?->quantity_not_qrcode ?: 0 }}
</a>
