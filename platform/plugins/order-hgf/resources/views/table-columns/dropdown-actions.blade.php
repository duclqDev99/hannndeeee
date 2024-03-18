@php
    $step = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_ORDER, $item->id);
@endphp

<div class="table-actions">
    <div class="dropdown">
        <button class="btn dropdown-toggle" type="button" id="dropdown-actions-57e516785256609e1606c1598309234b-120"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Hành động
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            @if (auth()->user()->hasPermission('hgf.admin.purchase-order.index'))
                <li>
                    <a href="{{ route('hgf.admin.purchase-order.show', ['order' => $item->id]) }}"
                        class="dropdown-item d-inline-flex gap-2 align-items-center "
                        data-action="{{ \Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_ORDER }}"
                        data-status="confirmed" data-type="next" data-order-id="{{ $item->id }}" href="#">
                        <span class="icon-tabler-wrapper">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        Xem chi tiết
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasPermission('hgf.admin.purchase-order.confirm'))
                @if ($step->status == 'pending')
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ \Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_ORDER }}"
                            data-status="confirmed" data-type="next" data-order-id="{{ $item->id }}" href="#">
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="21"
                                    viewBox="0 0 19 21" fill="none">
                                    <path
                                        d="M18.0249 3.3544L6.90644 17.3987L1.01575 11.0209L2.15099 9.85906L6.81586 14.9097L16.8085 2.28742L18.0249 3.3544Z"
                                        fill="black" />
                                </svg>
                            </span>
                            Phê duyệt YCSX
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ \Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_ORDER }}"
                            data-status="canceled" data-type="prev" data-order-id="{{ $item->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M8.38095 17.3168C13.0096 17.3168 16.7619 13.5645 16.7619 8.93581C16.7619 4.30713 13.0096 0.554855 8.38095 0.554855C3.75228 0.554855 0 4.30713 0 8.93581C0 13.5645 3.75228 17.3168 8.38095 17.3168ZM3.35238 9.7739H13.4095V8.09771H3.35238V9.7739Z"
                                    fill="black" />
                            </svg>
                            Từ chối YCSX
                        </button>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</div>



<style>
    .table .dropdown,
    .table .btn-group,
    .table .btn-group-vertical {
        position: static;
    }
</style>
