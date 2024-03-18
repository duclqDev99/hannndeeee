@extends(BaseHelper::getAdminMasterLayoutTemplate())
@php
    use Botble\OrderAnalysis\Models\OrderAnalysis;


    $departmentCode = 'r_01';
    $buttonContent = 'Nhận đơn';
    $labelDescription = 'Ghi chú nhận đơn';
    $placeholderDescription = 'Thêm ghi chú nhận đơn...';
    $orderRelationship = $order->orderDepartments->where('department_code', 'r_01')->first();
    switch ($orderRelationship->status) {
        case 'approved':
            $buttonContent = 'Gửi bản phân tích';
            $labelDescription = 'Mô tả bản phân tích';
            $placeholderDescription = 'Thêm mô tả cho bản phân tích...';
            break;
        case 'reject':
            $buttonContent = 'Gửi bản phân tích';
            $labelDescription = 'Mô tả bản phân tích';
            $placeholderDescription = 'Thêm mô tả cho bản phân tích...';
            break;
        case 'processing':
            $buttonContent = 'Hoàn thành';
            $labelDescription = 'Ghi chú hoàn thành hoặc chỉnh sửa';
            $placeholderDescription = 'Thêm ghi chú hoàn thành hoặc chỉnh sửa...';
            break;
        case 'completed':
            $buttonContent = 'Hoàn thành';
            break;
        default:
            # code...
            break;
    }
    $analyses = false;
    $attach = $order->attachs->where('attach_type', OrderAnalysis::class)->first();
    if($attach){
        $analyses = $attach->attachFile;
    }
    $histories = $order->histories->where('procedure_code_previous', $departmentCode);
    $historiesCount = $histories->count();

    $permissionAdmin = Auth::user()->hasPermission('order-analyses.completed');
    $permissionEdit = Auth::user()->hasPermission('order-analyses.edit');
@endphp

@section('content')
    <style>
        .sroll-history{
            height: 280px;
            overflow: scroll;
        }
    </style>
    <div id="main-order-content">
        @include('plugins/ecommerce::orders.partials.canceled-alert', compact('order'))

        <div class="row row-cards">
            <div class="col-md-9">
                <x-core::card class="mb-3">
                    <x-core::card.header class="justify-content-between">
                        <x-core::card.title>
                            {{ trans('plugins/sales::orders.order_information') }} {{ $order->code }}
                        </x-core::card.title>
                        @switch($orderRelationship->status)
                            @case('waiting')
                                <x-core::badge color="warning" class="d-flex align-items-center gap-1">
                                    <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                    {{ trans('Đang chờ duyệt') }}
                                </x-core::badge>
                                @break
                            @case('approved')
                                <x-core::badge color="primary" class="d-flex align-items-center gap-1">
                                    <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                    {{ trans('Đã duyệt') }}
                                </x-core::badge>
                                @break

                            @case('processing')
                                <x-core::badge color="info" class="d-flex align-items-center gap-1">
                                    <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                    {{ trans('Chờ duyệt bản thiết kế') }}
                                </x-core::badge>
                                @break
                            @case('completed')
                                <x-core::badge color="success" class="d-flex align-items-center gap-1">
                                    <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                    {{ trans('Hoàn thành') }}
                                </x-core::badge>
                                @break
                            @case('reject')
                                <x-core::badge color="danger" class="d-flex align-items-center gap-1">
                                    <x-core::icon name="ti ti-shopping-cart-check"></x-core::icon>
                                    {{ trans('Từ chối') }}
                                </x-core::badge>
                                @break
                            @default
                                @break
                        @endswitch
                    </x-core::card.header>

                    <x-core::table :hover="false" :striped="false">
                        <x-core::table.body>
                            @php
                                $totalPrice = 0;
                            @endphp
                            @foreach ($order->order_detail as $orderProduct)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell style="width: 80px">
                                        <img src="{{ isset($orderProduct->product->product_image) ? RvMedia::getImageUrl($orderProduct->product->product_image, 'thumb', false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage() }}"
                                            alt="{{ $orderProduct->product_name }}">
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell style="width: 45%">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <a href="javascript:void(0)" title="{{ $orderProduct->product_name }}"
                                                class="me-2">
                                                {{ $orderProduct->product_name }}
                                            </a>
                                            <p class="mb-0">({{ trans('plugins/sales::orders.sku') }}:
                                                <strong>{{ $orderProduct->product ? $orderProduct->product->sku : '' }}</strong>)
                                            </p>
                                        </div>

                                        @include(
                                            'plugins/ecommerce::themes.includes.cart-item-options-extras',
                                            ['options' => $orderProduct->product->options]
                                        )

                                        {!! apply_filters(ECOMMERCE_ORDER_DETAIL_EXTRA_HTML, null) !!}

                                    </x-core::table.body.cell>
                                    {{-- <x-core::table.body.cell>
                                        Giá thành: {{ format_price($orderProduct->product->price) }}
                                    </x-core::table.body.cell> --}}
                                    <x-core::table.body.cell>
                                        Số lượng đặt: {{ $orderProduct->quantity }}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                @php
                                    $totalPrice += $orderProduct->product->price * $orderProduct->quantity;
                                @endphp
                            @endforeach
                        </x-core::table.body>
                    </x-core::table>

                    <x-core::card.body>
                        <div class="row">
                            <div class="col-md-6 md-6">
                                <x-core::table :hover="false" :striped="false" class="table-borderless text-end">
                                    <x-core::table.body>
                                        <x-core::table.body.row>
                                            <x-core::table.body.cell>{{ trans('plugins/sales::orders.quantity') }}</x-core::table.body.cell>
                                            <x-core::table.body.cell>
                                                {{ $order->order_detail->sum('quantity') }}
                                            </x-core::table.body.cell>
                                        </x-core::table.body.row>
                                        {!! apply_filters('ecommerce_admin_order_extra_info', null, $order) !!}

                                        <x-core::table.body.row>
                                            <td colspan="2">
                                                <hr class="my-0">
                                            </td>
                                        </x-core::table.body.row>
                                    </x-core::table.body>
                                </x-core::table>
                                <x-core::form.textarea :label="trans('plugins/sales::orders.note')" name="description" :placeholder="trans('plugins/sales::orders.add_note')"
                                    :value="$order->description" class="textarea-auto-height" disabled />
                            </div>
                            {{-- //////////////////form confirm////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

                            <div class="col-md-6 md-6">
                                <form action="{{ route('order-analyses.edit', $order->id) }}" method="POST">
                                    @csrf
                                    @if ($analyses)
                                        <div class="mb-3 position-relative">
                                            <label class="form-label" for="mySelect">Xem bản phân tích</label>
                                            <a href="{{route('analyses.edit', $analyses->id)}}">
                                                <span class="badge bg-primary text-primary-fg d-flex align-items-center gap-1">
                                                    <i class="fa-solid fa-eye"></i> {{$analyses->name}}
                                                </span>
                                            </a>
                                        </div>
                                    @endif

                                    @if ($orderRelationship->status != 'waiting' && $orderRelationship->status != 'completed')
                                        <x-core::form.select
                                            :label="trans('Bản phân tích')"
                                            name="analysis_id"
                                            data-type="state"
                                            {{-- :data-url="route('ajax.states-by-country')" --}}
                                            :searchable="true"
                                            :analysis-id="$analyses ? $analyses->id : $analyses"
                                            required
                                        >
                                                <option value="">-- chọn bản phân tích cho đơn hàng --</option>
                                                @foreach ($orderAnalysis as $analysis)
                                                    <option value="{{$analysis->id}}">{{$analysis->name}}</option>
                                                @endforeach
                                        </x-core::form.select>
                                    @endif
                                    {{-- <div class="mb-3 position-relative">
                                        <label class="form-label required" for="mySelect"> Ngày dự kiến hoàn thành</label>
                                        <input type="date" id="dateSuccess" name="dateSuccess" class="form-control" aria-describedby="dateSuccess">
                                      </div> --}}
                                    {{-- @dd($orderRelationship->expected_date) --}}
                                    <x-core::form.text-input
                                        :label="trans('Ngày dự kiến hoàn thành')"
                                        type="datetime-local"
                                        name="expected_date"
                                        :placeholder="trans('Ngày dự kiến hoàn thành')"
                                        required
                                        :value="$orderRelationship->expected_date"
                                    />


                                    @if ($orderRelationship->status != 'completed')
                                        <x-core::form.textarea :label="$labelDescription" name="descriptionForm" :placeholder="$placeholderDescription"
                                        {{-- :value="$order->description" --}} class="textarea-auto-height" rows="3"/>
                                    @endif
                                    <input type="text" name="statusSubmit" hidden value="{{$orderRelationship->status}}"/>


                                    @if ($orderRelationship->status != 'completed' && $orderRelationship->status != 'processing' && !$permissionAdmin)
                                        <x-core::button type="submit" name="successAnalyses"  value="{{$orderRelationship->status}}" class=" btn-success">
                                            {{ $buttonContent }}
                                        </x-core::button>
                                    @endif
                                    @if ($analyses != false && $orderRelationship->status == 'approved' && !$permissionAdmin)
                                        <x-core::button type="submit" name="editAnalyses"  value="{{$orderRelationship->status}}" class="btn-primary" >
                                            Chỉnh sửa
                                        </x-core::button>
                                    @endif
                                    @if (is_in_admin(true) && Auth::user()->hasPermission('order-analyses.completed') && $orderRelationship->status == 'processing')
                                        <x-core::button type="submit" name="completedAnalyses"  value="{{$orderRelationship->status}}" class="btn-success" >
                                            Hoàn thành
                                        </x-core::button>

                                        <x-core::button type="submit" name="rejectAnalyses"  value="{{$orderRelationship->status}}" class="btn-danger" >
                                           Từ Chối
                                        </x-core::button>
                                    @endif
                                </form>
                            </div>

                        </div>

                    </x-core::card.body>

                    <div class="list-group list-group-flush">
                        @if ($order->status != Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED || $order->is_confirmed)
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div class="text-uppercase">
                                    <x-core::icon name="ti ti-check" @class(['text-success' => $order->status]) />
                                    @if ($order->status == Botble\Sales\Enums\OrderStatusEnum::PENDING)
                                        {{ trans('plugins/sales::orders.order_was_confirmed') }}
                                    @else
                                        {{ trans('plugins/sales::orders.confirm_order') }}
                                    @endif
                                </div>
                                @if ($order->status == Botble\Sales\Enums\OrderStatusEnum::PENDING)
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('purchase-order.confirm', $order) }}">
                                            <input name="order_id" type="hidden" value="{{ $order->id }}">
                                            <x-core::button type="button" color="info" class="btn-confirm-order">
                                                {{ trans('plugins/order-analysis::order-analysis.orders.accepted') }}
                                            </x-core::button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-core::card>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/sales::orders.type_order') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="position-relative box-search-advance customer">
                            <select id="type_order" class="form-control" disabled>
                                <option>{{ trans('plugins/sales::orders.' . $order->type_order) }}</option>
                            </select>
                            @if ($order->type_order != Botble\Sales\Enums\TypeOrderEnum::SAMPLE)
                                <div class="link_order mt-3">
                                    <div class="form-group">
                                        <label for="link_order" class="mb-2"><strong>Đơn hàng liên kết:</strong></label>
                                        <select id="link_order" class="form-control" disabled>
                                            <option>{{ $order->orderLink->order_code }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            <span class=" position-relative" style="padding-top: 5px; padding-right: 10px">
                                Lịch sử
                                @if($historiesCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="color: #fff">
                                        {{$historiesCount}}
                                    <span class="visually-hidden">unread messages</span>
                                  </span>
                                @endif

                              </span>
                        </x-core::card.title>
                        <div class="card-actions">
                            {{-- <button
                                type="button"
                                data-bs-toggle="tooltip"
                                data-placement="top"
                                title="Delete customer"
                                @click="removeCustomer()"
                                class="btn-action"
                            >
                                <span class="icon-tabler-wrapper icon-sm icon-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M18 6l-12 12" />
                                        <path d="M6 6l12 12" />
                                    </svg>
                                </span>
                            </button> --}}
                        </div>
                    </x-core::card.header>
                    {{-- //hhhhhhhhhhhhhhhhhhhh --}}
                    <x-core::card.body>
                        <div class="p-0">
                            <div class="col sroll-history">
                                <ul class="timeline">
                                    @foreach ($histories as $history)
                                        @switch($history->status)
                                            @case('waiting')
                                                <li class="completed">
                                                    <x-core::badge color="warning" class="d-flex align-items-center gap-1">
                                                        {{ trans('Chờ duyệt') }}
                                                    </x-core::badge>
                                                    <span>- Người gửi: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- Dự kiến hoàn thành: {{$history->created_at}}</span></br>
                                                    <span>- ghi chú: {{$history->description}}</span></br>
                                                </li>
                                                @break

                                            @case('approved')
                                                <li class="completed">
                                                    <x-core::badge color="primary" class="d-flex align-items-center gap-1">
                                                        {{ trans('Đã duyệt') }}
                                                    </x-core::badge>
                                                    <span>- Người duyệt: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- ghi chú: {{$history->description}}</span></br>
                                                    <!-- Thêm thông tin chi tiết ở đây nếu cần -->
                                                </li>
                                                @break
                                            @case('processing')
                                                <li class="completed">
                                                    <x-core::badge color="info" class="d-flex align-items-center gap-1">
                                                        {{ trans('Gửi thiết kế') }}
                                                    </x-core::badge>
                                                    <span>- Người gửi: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- Mô tả: {{$history->description}}</span></br>
                                                    <!-- Thêm thông tin chi tiết ở đây nếu cần -->
                                                </li>
                                                @break
                                            @case('completed')
                                                <li class="completed">
                                                    <x-core::badge color="success" class="d-flex align-items-center gap-1">
                                                        {{ trans('Hoàn thành') }}
                                                    </x-core::badge>
                                                    <span>- Người hoàn thành: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- ghi chú: {{$history->description}}</span></br>
                                                </li>
                                                @break
                                            @case('reject')
                                                <li class="completed">
                                                    <x-core::badge color="danger" class="d-flex align-items-center gap-1">
                                                        {{ trans('Từ chối') }}
                                                    </x-core::badge>
                                                    <span>- Người hoàn thành: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- ghi chú: {{$history->description}}</span></br>
                                                </li>
                                                @break
                                            {{-- trạng thái chỉnh sửa bản thiết kế --}}
                                            @default
                                                <li class="completed">
                                                    <x-core::badge color="Secondary" class="d-flex align-items-center gap-1">
                                                        {{ trans('Chỉnh sửa') }}
                                                    </x-core::badge>
                                                    <span>- Người sửa: {{$history->created_by_name}}</span></br>
                                                    <span>- Thời gian: {{$history->created_at}}</span></br>
                                                    <span>- ghi chú: {{$history->description}}</span></br>
                                                </li>
                                                @break
                                            {{-- editing --}}
                                        @endswitch
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </x-core::card.body>

                    @if ($order->status != Botble\Sales\Enums\OrderStatusEnum::COMPLETED)
                        <x-core::card.footer>
                            {{-- <div class="btn-list">
                            <x-core::button
                                tag="a"
                                :href="route('purchase-order.reorder', ['order_id' => $order->id])"
                            >
                                {{ trans('plugins/sales::orders.update_order') }}
                            </x-core::button>
                        </div> --}}
                        </x-core::card.footer>
                    @endif
                </x-core::card>


            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#mySelect').select2();
            let analysis_id = $('#analysis_id').attr('analysis-id');
            $('#analysis_id').val(analysis_id).trigger('change');
        });
    </script>
@endsection
