<a
    class="editable"
    data-type="select"
    data-source="{{ route('receipt-purchase-goods.list.json') }}"
    data-pk="{{ $item->id }}"
    data-url="{{ route('receipt-purchase-goods.assign') }}"
    data-value="{{ $role }}"
    data-title="{{ trans('core/acl::users.assigned_role') }}"
    href="#"
>
    {{ $role }}
</a>