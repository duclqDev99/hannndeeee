@php
    $sendQuotation = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SEND_QUOTATION, $item->order->id);
    $saleManagerConfirmQuotation = get_action(
        \Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION,
        $item->order->id,
    );
    $customerConfirm = get_action(
        \Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_CONFIRM_QUOTATION,
        $item->order->id,
    );

    $customerSign =
        $customerConfirm->status == 'confirmed'
            ? get_action(\Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_SIGN_CONTRACT, $item->order->id)
            : null;

@endphp

<div class="table-actions">
    <div class="dropdown">
        <button class="btn dropdown-toggle" type="button" id="dropdown-actions-57e516785256609e1606c1598309234b-120"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Hành động
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdown-actions-57e516785256609e1606c1598309234b-120" style="">

            @if (auth()->user()->hasPermission('retail.sale.quotation.index'))
                <li>
                    <a class="dropdown-item d-inline-flex gap-2 align-items-center"
                        href="{{ route('retail.sale.quotation.show', ['quotation' => $item->id]) }}">
                        <span class="icon-tabler-wrapper">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        Xem chi tiết
                    </a>
                </li>
            @endif

            @if ($saleManagerConfirmQuotation->status != 'confirmed')
                @if (auth()->user()->hasPermission('retail.sale.quotation.edit'))
                    <li>
                        <a class="dropdown-item d-inline-flex gap-2 align-items-center"
                            href="{{ route('retail.sale.quotation.edit', ['quotation' => $item->id]) }}">
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z">
                                    </path>
                                    <path d="M16 5l3 3"></path>
                                </svg>
                            </span>
                            Chỉnh sửa
                        </a>
                    </li>
                @endif
            @endif


            @if (auth()->user()->hasPermission('retail.sale.quotation.send'))
                @if ($sendQuotation->status == 'pending' || $sendQuotation->status == 'refused')
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SEND_QUOTATION }}"
                            data-status="sended" data-type="next" data-order-id="{{ $item->order->id }}">
                            <span class="icon-tabler-wrapper">
                                <i class="fa-solid fa-share-from-square"></i>
                            </span>
                            Gửi Sale Admin duyệt
                        </button>
                    </li>
                @endif
            @endif

            @if (auth()->user()->hasPermission('retail.sale.quotation.confirm'))
                @if ($saleManagerConfirmQuotation->status == 'pending')
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION }}"
                            data-status="confirmed" data-type="next" data-order-id="{{ $item->order->id }}">
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="21"
                                    viewBox="0 0 19 21" fill="none">
                                    <path
                                        d="M18.0249 3.3544L6.90644 17.3987L1.01575 11.0209L2.15099 9.85906L6.81586 14.9097L16.8085 2.28742L18.0249 3.3544Z"
                                        fill="black" />
                                </svg>
                            </span>
                            Phê duyệt báo giá
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION }}"
                            data-status="canceled" data-type="prev" data-order-id="{{ $item->order->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M8.38095 17.3168C13.0096 17.3168 16.7619 13.5645 16.7619 8.93581C16.7619 4.30713 13.0096 0.554855 8.38095 0.554855C3.75228 0.554855 0 4.30713 0 8.93581C0 13.5645 3.75228 17.3168 8.38095 17.3168ZM3.35238 9.7739H13.4095V8.09771H3.35238V9.7739Z"
                                    fill="black" />
                            </svg>
                            Từ chối báo giá
                        </button>
                    </li>
                @endif
            @endif

            @if (auth()->user()->hasPermission('retail.sale.quotation.sign_contact'))
                @if ($customerConfirm->status == 'pending' && $saleManagerConfirmQuotation->status == 'confirmed')
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                            data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_CONFIRM_QUOTATION }}"
                            data-status="confirmed" data-type="next" data-order-id="{{ $item->order->id }}">
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="21"
                                    viewBox="0 0 19 21" fill="none">
                                    <path
                                        d="M18.0249 3.3544L6.90644 17.3987L1.01575 11.0209L2.15099 9.85906L6.81586 14.9097L16.8085 2.28742L18.0249 3.3544Z"
                                        fill="black" />
                                </svg>
                            </span>
                            Khách đồng ý báo giá
                        </button>
                    </li>
                @endif
            @endif

            @if (auth()->user()->hasPermission('retail.sale.quotation.sign_contact'))
                @if ($customerConfirm->status == 'confirmed' && $customerSign?->status == 'pending')
                    <li>
                        <button class="dropdown-item d-inline-flex gap-2 align-items-center upload-contract-btn"
                            data-quotation-id="{{ $item->id }}" data-bs-toggle="modal"
                            data-bs-target="#uploadContractModal"
                            data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_SIGN_CONTRACT }}"
                            data-status="confirmed" data-type="next" data-order-id="{{ $item->order->id }}">
                            <span class="icon-tabler-wrapper">
                                <i class="fa-solid fa-file-contract"></i>
                            </span>
                            Xác nhận kí hợp đồng
                        </button>
                    </li>
                @endif
            @endif

            {{-- <li>
                <button class="dropdown-item d-inline-flex gap-2 align-items-center" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="19" viewBox="0 0 16 19"
                        fill="none">
                        <path
                            d="M5.01671 2.87715C4.98722 2.87703 4.95799 2.88281 4.93071 2.89415C4.90343 2.9055 4.87863 2.92218 4.85774 2.94325C4.83684 2.96432 4.82026 2.98936 4.80895 3.01694C4.79764 3.04451 4.79181 3.07408 4.79182 3.10394C4.79182 3.22903 4.89226 3.32982 5.01671 3.32982H8.31721C8.44165 3.32982 8.5421 3.22903 8.5421 3.10394C8.5421 3.07408 8.53628 3.04451 8.52496 3.01694C8.51365 2.98936 8.49707 2.96432 8.47618 2.94325C8.45528 2.92218 8.43048 2.9055 8.4032 2.89415C8.37592 2.88281 8.3467 2.87703 8.31721 2.87715H5.01671ZM8.31721 1.66941C8.96966 1.66941 9.51989 2.11039 9.68967 2.71336L11.623 2.71426C12.175 2.71785 12.6204 2.89875 12.8959 3.28843C13.1324 3.62321 13.2266 4.05339 13.2026 4.53666L13.2 16.2478C13.2373 16.7446 13.1448 17.1837 12.9084 17.5482C12.6311 17.9739 12.1786 18.1872 11.6177 18.1926H1.90199C1.22642 18.1656 0.725971 18.0045 0.413076 17.6445C0.122405 17.3106 -0.0047082 16.8525 0.000625226 16.2946V4.54836C-0.0109305 4.00839 0.137516 3.55301 0.461077 3.21643C0.795305 2.87085 1.28954 2.71786 1.9251 2.71336H3.64513C3.81402 2.11039 4.36425 1.66941 5.01671 1.66941H8.31721ZM8.31721 4.53756H5.01671C4.53137 4.53756 4.1038 4.29458 3.84602 3.92199H1.92866C1.58821 3.92379 1.39354 3.98499 1.32242 4.05879C1.24153 4.14248 1.19442 4.28558 1.19976 4.53666V16.2991C1.19709 16.5916 1.24598 16.7689 1.31531 16.848C1.36331 16.9029 1.56954 16.9695 1.92421 16.9839H11.6133C11.791 16.9821 11.8622 16.9479 11.903 16.8858C11.9839 16.7617 12.0213 16.5853 12.0008 16.2937L12.0017 4.50787C12.0142 4.24418 11.9777 4.07409 11.9164 3.98769C11.8933 3.95619 11.8133 3.92289 11.6177 3.92199H9.487C9.22922 4.29458 8.80166 4.53756 8.31632 4.53756H8.31721ZM14.4 0.192581C15.2835 0.192581 16 0.913445 16 1.8035V14.4767C16 15.3658 15.2835 16.0876 14.4 16.0876H12.6595V14.8789H14.3991C14.6204 14.8789 14.7991 14.699 14.7991 14.4767V1.8026C14.7986 1.69559 14.7562 1.59313 14.6813 1.51772C14.6063 1.4423 14.5048 1.40008 14.3991 1.40032H7.68253C7.46208 1.40032 7.28252 1.58031 7.28252 1.8026V1.94389H6.0825V1.8026C6.0825 0.913445 6.79896 0.192581 7.68253 0.192581H14.4ZM6.39629 10.768C6.72785 10.768 6.9963 11.0379 6.9963 11.3718C6.9963 11.7048 6.72785 11.9757 6.39629 11.9757H2.79534C2.71678 11.976 2.63892 11.9607 2.5662 11.9306C2.49349 11.9005 2.42734 11.8562 2.37154 11.8002C2.31574 11.7442 2.27138 11.6776 2.241 11.6043C2.21061 11.5309 2.19479 11.4523 2.19444 11.3727C2.19444 11.0388 2.46378 10.768 2.79534 10.768H6.39629ZM9.99812 8.35247C10.3288 8.35247 10.5981 8.62246 10.5981 8.95634C10.5981 9.28933 10.3288 9.56021 9.99812 9.56021H2.79534C2.71678 9.56057 2.63892 9.54525 2.5662 9.51514C2.49349 9.48503 2.42734 9.44072 2.37154 9.38473C2.31574 9.32874 2.27138 9.26217 2.241 9.18882C2.21061 9.11547 2.19479 9.03678 2.19444 8.95724C2.19444 8.62336 2.46378 8.35247 2.79534 8.35247H9.99812ZM9.99812 5.93699C10.3288 5.93699 10.5981 6.20698 10.5981 6.54086C10.5979 6.6204 10.5822 6.69911 10.5519 6.77251C10.5216 6.8459 10.4774 6.91254 10.4217 6.96861C10.3659 7.02469 10.2999 7.0691 10.2272 7.09932C10.1545 7.12954 10.0767 7.14497 9.99812 7.14473H2.79534C2.71678 7.14509 2.63892 7.12977 2.5662 7.09966C2.49349 7.06955 2.42734 7.02524 2.37154 6.96925C2.31574 6.91326 2.27138 6.84669 2.241 6.77334C2.21061 6.69999 2.19479 6.6213 2.19444 6.54176C2.19444 6.20788 2.46378 5.93699 2.79534 5.93699H9.99812Z"
                            fill="black" />
                    </svg>
                    Xem đơn hàng
                </button>
            </li> --}}
            {{-- <li>
                <button class="dropdown-item d-inline-flex gap-2 align-items-center" href="#">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Xem lịch sử
                </button>
            </li> --}}

        </div>
    </div>
</div>

<style>
    .table .dropdown,
    .table .btn-group,
    .table .btn-group-vertical {
        position: static;
    }
</style>
