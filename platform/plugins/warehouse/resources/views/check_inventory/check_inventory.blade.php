
@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="container">
        <div class="row">
            <div class="group flexbox-annotated-section">
                <div class="col-md-9">
                    <div class="">
                        @foreach ($quantity_stocks as $quantity_stock)
                            @isset($quantity_stock)
                            @if($quantity_stock->planMaterial != null)
                                <table class="table payment-method-item">
                                    <tbody>
                                        <tr>
                                            <div class="row">
                                                <div class="col-3">
                                                    Ngày tạo: {{ $quantity_stock->created_at->format('Y-m-d') }}
                                                </div>
                                                <div class="col-3"> Tiêu đề: {{ $quantity_stock->planMaterial->title }}
                                                </div>
                                                <div class="col-3"> Người đề xuất: {{ $quantity_stock->planMaterial->users->last_name }}
                                                </div>
                                                <div class="col-3"> Người duyệt:{{ $quantity_stock->planMaterial->users_confirm->last_name}}
                                                </div>
                                            </div>

                                        </tr>
                                        <tr class="border-pay-row">
                                            <td class="border-pay-col text-uppercase text-center"><i class="fa fa-warehouse"></i>
                                                {{ $quantity_stock->planMaterial->type }}</td>


                                            <td style="width: 70%;">
                                                <span>Tên kho: {{ $quantity_stock->planMaterial->inventory->name }} </span>
                                            </td>

                                            <td class="border-left">
                                                Ngày dự kiến: {{ $quantity_stock->planMaterial->date_proposal }}
                                            </td>
                                        </tr>

                                        <tr class="bg-white">
                                            <td colspan="3">
                                                <div class="float-start" style="margin-top: 5px;">
                                                    <div class="payment-name-label-group">
                                                        <span class="payment-note v-a-t">
                                                            Trạng thái: <p
                                                                style="display: inline-block; text-transform: uppercase; margin: 0;"
                                                                class="@if ($quantity_stock->status == 'completed') badge badge-primary @else  badge badge-warning @endif">
                                                                {{ $quantity_stock->status  == 'completed' ? 'Xác nhận' : 'Chưa xác nhận'}}</p>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="float-end">
                                                    <a
                                                        class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger  ">Chi
                                                        tiết đơn đề xuất</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="payment-content-item hidden">
                                            <td class="border-left" colspan="3">

                                                <div class="col-sm-12 mt-2">
                                                    <div class="well bg-white">
                                                        <div class="row">
                                                            <div class="col-2"></div>
                                                            <div class="col-10">
                                                                <table class="table align-middle mb-0 bg-white">
                                                                    <thead class="bg-light">
                                                                        <tr>
                                                                            <th>ID</th>
                                                                            <th>Tên NVL</th>


                                                                            <th>Số lương</th>
                                                                            <th>Giá</th>
                                                                            <th>Tổng</th>

                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @php
                                                                            $total = 0;
                                                                            $total_quantity = 0;
                                                                        @endphp
                                                                        @foreach ($quantity_stock->materials as $detail)
                                                                            <tr>
                                                                                <td>{{ $detail->material->id }}</td>
                                                                                <td>
                                                                                    <div class="d-flex align-items-center">
                                                                                        <img src = "{{ RvMedia::getImageUrl($detail->material->image, 'post-small') }}"
                                                                                            width="80px"
                                                                                            style="width: 80px; height: 80px"
                                                                                            class="rounded-circle" />
                                                                                        <div class="ms-3">
                                                                                            <p class="fw-bold mb-1">
                                                                                                {{ $detail->material->name }}
                                                                                            </p>
                                                                                            <p class="text-muted mb-0">
                                                                                                {{ trans('plugins/warehouse::material.unit.' . $detail->material->unit) }}
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>


                                                                                <td>{{ $detail->quantity }}</td>
                                                                                <td>{{ format_price($detail->material->price) }}
                                                                                </td>
                                                                                <td>{{ format_price($detail->material->price * $detail->quantity) }}
                                                                                </td>
                                                                                @php
                                                                                    $total += $detail->material->price * $detail->quantity;
                                                                                    $total_quantity += $detail->quantity;
                                                                                @endphp
                                                                                <td>

                                                                                </td>
                                                                            </tr>
                                                                        @endforeach

                                                                    </tbody>

                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-8"></div>
                                                            <div class="col-2">
                                                                <div class="row">
                                                                    Tổng tiền
                                                                </div>
                                                                <div class="row">
                                                                    Tổng số lượng
                                                                </div>

                                                            </div>
                                                            <div class="col-2">
                                                                <div class="row">
                                                                    {{ format_price($total) }}
                                                                </div>
                                                                <div class="row">
                                                                    {{ format_price($total_quantity) }} NVL
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <br>
                                                <div class="col-12 bg-white text-end">

                                                @if ($quantity_stock->status === 'pending' && Auth::user()->hasPermission('check_inventory.edit'))
                                                    <button class="btn btn-info save-payment-item btn-text-trigger-save   "
                                                        id_proposal = "{{ $quantity_stock->id }}"
                                                        id_inventory ="{{ $quantity_stock->planMaterial->inventory_id }}"
                                                        type="button">Xác nhận</button>
                                                @else
                                                <button class="btn btn-basic"
                                                id_proposal="{{$quantity_stock->id}}" id_inventory="{{$quantity_stock->planMaterial->inventory_id }}"
                                                type="button"><i class="fa fa-eye" aria-hidden="true"></i></button>
                                                @endif

                                                </div>
                                                {!! Form::close() !!}
                                            </td>
                                        </tr>
                                        <tr class="payment-content-item hidden">




                                        </tr>
                                    </tbody>

                                </table>
                            @endif
                            @endisset
                        @endforeach

                    </div>
                </div>
            </div>

            <div class="group">
                <div class="col-md-3"></div>
                <div class="col-md-9">

                </div>
            </div>
        </div>
    </div>
@endsection
