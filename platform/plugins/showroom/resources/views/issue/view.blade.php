@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    @php
        use Botble\Base\Facades\BaseHelper;
        use Botble\WarehouseFinishedProducts\Models\ProductBatch;
    @endphp
    <style>
        .flexbox-grid-default {
            position: relative;
        }

        .card-notify-year {
            position: absolute;
            right: -15px;
            top: -15px;
            background: #ff4444;
            text-align: center;
            color: #fff;
            font-size: 14px;
            padding: 5px;
            padding-left: 30px;
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 10% 50%, 0% 0%);
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .card-notify-year.blue {
            background: rgb(74, 74, 236);
        }
    </style>
    <div class="widget__view row justify-content-center">
        @isset($productIssue)
            <div class="col-lg-6 col-md-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="flexbox-auto-right mr5">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                    <strong>{{ BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code)) }}
                                                    </strong>
                                                    - Người tạo:
                                                    <strong>{{ $productIssue->invoice_issuer_name }}</strong>
                                                </label>
                                                <div>
                                                    Ngày tạo: {{ date('d-m-Y', strtotime($productIssue->created_at)) }}
                                                </div>
                                                <div>
                                                    Nhập từ: <strong>{{ $productIssue->warehouse->name }} -
                                                        {{ $productIssue->warehouse->hub->name }}
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        {{ $productIssue->warehouse_name }} -
                                                        {{ $productIssue->warehouseIssue->showroom->name }}
                                                    </strong>

                                                </div>
                                                <div>

                                                </div>
                                                <div>
                                                    Mục đích: {{ $productIssue->title }}
                                                </div>
                                            </div>

                                            @if ($productIssue->status == \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::PENDING)
                                                <span class="card-notify-year blue">Phiếu chờ nhập kho</span>
                                            @elseif($productIssue->status == \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::DENIED)
                                                <span class="card-notify-year red">Từ chối nhập kho</span>
                                            @else
                                                <span class="card-notify-year red">Phiếu đã nhập kho</span>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">


                                        <div id="table-wrapper" class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table" width="100%"
                                                id="table-content">
                                                <tbody>

                                                    @foreach ($productIssue->productIssueDetail as $orderProduct)
                                                        <tr class="item__product">
                                                            <td class="width-60-px min-width-60-px vertical-align-t"
                                                                width="20%" style="margin:20px">
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        width="100px" height="100px"
                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                </div>
                                                            </td>

                                                            <td class="pl5 p-r5 text-center " width="30%">
                                                                {{ $orderProduct->product_name }}
                                                                <br> (Màu: {{ $orderProduct->color }} - Size:
                                                                {{ $orderProduct->size }} )

                                                                <input type="text" class="widget__price"
                                                                    name="product[{{ $orderProduct->id }}][attr]"
                                                                    value="{{ $orderProduct->attribute }}" hidden="">
                                                            </td>
                                                            <td class="pl5 text-center" width="25%">
                                                                <div class="inline_block">
                                                                    <input type="text" class="widget__batch"
                                                                        name="product[{{ $orderProduct->id }}][sku]"
                                                                        value="{{ $orderProduct->sku }}" hidden>
                                                                    <span>Mã: {{ $orderProduct->sku }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5" width="20%">
                                                                <span style="white-space: nowrap;">Số lượng:
                                                                    {{ $orderProduct->quantity }} SP</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                    <div class="card-body pd-all-20 p-none-t">
                                        <div class="flexbox-grid-default">
                                            <div class="flexbox-auto-right pl5">
                                                <div class="row">

                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3 w-100">
                                                            <label class="text-title-field">Ghi chú</label>
                                                            <textarea class="form-control textarea-auto-height" name="description" rows="4" placeholder="Ghi chú"
                                                                disabled="">{{ $productIssue->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="py-3">
                                                            <label class="text-title-field">Ngày dự kiến</label>
                                                            @php
                                                                $inputDate = $productIssue->expected_date;
                                                                $formattedDate = date('d-m-Y', strtotime($inputDate));
                                                            @endphp
                                                            <input class="form-control flatpickr-input" data-date-format="d-m-Y"
                                                                v-pre="1" placeholder="d-m-Y" data-input=""
                                                                readonly="readonly" name="expected_date" type="text"
                                                                value="{{ $formattedDate }}" id="expected_date"
                                                                aria-invalid="false" aria-describedby="expected_date-error"
                                                                disabled>

                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($productIssue->status != \Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum::PENDING)
                                <div class="card-footer text-center"
                                    style="background-color: #F8F8F8; border-radius: 0px 0px 10px 10px; padding: 10px">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-bs-whatever="@mdo">
                                        In phiếu xuất kho
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            {{ trans('plugins/warehouse::bill_export.title_form_out') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="myForm" action="{{ route('showroom-issue.export-file') }}"
                            method="POST">
                            @csrf
                            <input type="text" class="form-control" id="id" value="{{ $productIssue->id }}"
                                name="id" hidden="hidden">
                            <input type="text" class="form-control" id="type_bill"
                                value="{{ trans('plugins/warehouse::bill_export.title_form_out') }}" name="type_bill"
                                hidden="hidden">
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.proposal_name') }}:</label>
                                <input type="text" class="form-control" id="proposal_name"
                                    value="{{ $productIssue->invoice_issuer_name }}" name="proposal_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.receiver_name') }}:</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.storekeeper_name') }}:</label>
                                <input type="text" class="form-control" id="storekeeper_name" name="storekeeper_name"
                                    value="{{ auth()->user()->last_name . ' ' . auth()->user()->first_name }}">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.chief_accountant_name') }}:</label>
                                <input type="text" class="form-control" id="chief_accountant_name"
                                    name="chief_accountant_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.manager_name') }}:</label>
                                <input type="text" class="form-control" id="manager_name" name="manager_name">
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name"
                                    class="col-form-label required">{{ trans('plugins/warehouse::bill_export.today') }}:</label>
                                <input type="date" class="form-control" id="today" value="{{ date('Y-m-d') }}"
                                    name="today">
                            </div>
                            <div style="float: right">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" id="print"
                                    name="button_type" value="print"> <i class="fas fa-print"></i>
                                    {{ __('In phiếu xuất kho') }}</button>
                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" id="download"
                                    name="button_type" value="download"><i class="fas fa-download"></i>
                                    {{ __('Tải phiếu xuất kho') }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
        @endisset
        @isset($actualIssue)
            <div class="col-lg-6 col-md-12">
                <div class="ui-layout">
                    <div class="flexbox-layout-sections">
                        <div class="flexbox-layout-section-primary mt20">
                            <div class="card ui-layout__item">
                                <div class="wrapper-content">
                                    <div class="pd-all-20">
                                        <div class="card-header flexbox-grid-default">
                                            <div class="w-75">
                                                <label $class="title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                    <strong>{{ BaseHelper::clean(get_proposal_receipt_product_code($productIssue->receipt_code)) }}
                                                    </strong> - Người nhập kho {{ $productIssue->invoice_confirm_name }}
                                                </label>
                                                <div>
                                                    Ngày nhập: {{ date('d-m-Y', strtotime($actualIssue->created_at)) }}
                                                </div>
                                            </div>
                                            <span class="card-notify-year blue">Phiếu thực nhập</span>

                                        </div>
                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="card meta-boxes mb-3" id="gallery_wrap">
                                            <div class="card-header">
                                                <h4 class="card-title">
                                                    Danh sách thực nhập kho
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="table-wrap">
                                            <table class="table-order table-divided table-vcenter card-table w-100">
                                                <tbody>
                                                    @php
                                                        $totalActual = 0;
                                                    @endphp
                                                    @foreach ($actualIssue->autualDetail as $orderProduct)
                                                        <tr class="item__product">
                                                            <td class="vertical-align-t" width="15%">
                                                                <div class="wrap-img">
                                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                                        width="100px"
                                                                        src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                        alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5 text-center" width="20%">
                                                                {{ $orderProduct->product_name }}
                                                                <div>
                                                                    @php
                                                                        echo '(Màu: ' . $orderProduct->color . ' Size: ' . $orderProduct->size . ' )';
                                                                    @endphp

                                                                </div>
                                                            </td>
                                                            <td class="pl5 p-r5 text-center "width="30%">
                                                                Mã:
                                                                <strong>{{ $orderProduct->sku }}</strong></span>
                                                            </td>
                                                            <td class="pl5 p-r5 text-center">
                                                                Số lượng thành phẩm: {{ $orderProduct->quantity }} SP
                                                            </td>
                                                            @php
                                                                $totalActual += $orderProduct->quantity;
                                                            @endphp
                                                        </tr>
                                                    @endforeach

                                                    <tr>
                                                        <td colspan="5" class="text-end h5">
                                                            <strong>Tổng số lượng: </strong> <span
                                                                class="widget__amount">{{ $totalActual }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <div class="pd-all-20 p-none-t border-top-title-main">
                                        <div class="card meta-boxes mb-3">
                                            <div class="card-header">
                                                <h4 class="card-title">
                                                    Hình ảnh đính kèm
                                                </h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="list-photos-gallery">
                                                    <div class="row g-2">

                                                        @if ($actualIssue->image)
                                                            @php
                                                                $images = json_decode($actualIssue->image);
                                                            @endphp

                                                            @foreach ($images as $image)
                                                                <div
                                                                    class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                    <div class="custom-image-box mage-box">
                                                                        <img class="preview-image default-image"
                                                                            src="{{ RvMedia::getImageUrl($image, 'thumb') }}"
                                                                            alt="Preview image">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div
                                                                class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                                <div class="custom-image-box mage-box">
                                                                    <img class="preview-image default-image"
                                                                        src="{{ RvMedia::getDefaultImage() }}"
                                                                        alt="Preview image">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endisset
    </div>
    <script>
        $(document).ready(function() {
            $(".batch-header").click(function() {
                var batchId = $(this).data("batch-id");
                var detailsRow = $("#batch-details-" + batchId);
                detailsRow.toggle();
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const widget = document.querySelectorAll('.widget__view .ui-layout__item');



            if (widget && widget.length === 2) {
                const wp_porposal = widget[0];
                const wp_receipt = widget[1];

                const itemHighlightPorposal = wp_porposal.querySelectorAll('.check__highlight');
                const itemHighlightReceipt = wp_receipt.querySelectorAll('.check__highlight');

                for (let index = 0; index < itemHighlightPorposal.length; index++) {
                    if (itemHighlightPorposal[index].textContent !== itemHighlightReceipt[index].textContent) {
                        itemHighlightPorposal[index].style.color = 'red';
                        itemHighlightPorposal[index].style.fontSize = '1.2em';
                        itemHighlightPorposal[index].style.fontWeight = 'bold';
                        itemHighlightPorposal[index].style.textDecoration = 'underline';

                        itemHighlightReceipt[index].style.color = 'red';
                        itemHighlightReceipt[index].style.fontSize = '1.2em';
                        itemHighlightReceipt[index].style.fontWeight = 'bold';
                        itemHighlightReceipt[index].style.textDecoration = 'underline';
                    }
                }
            }

        })
    </script>
@stop
