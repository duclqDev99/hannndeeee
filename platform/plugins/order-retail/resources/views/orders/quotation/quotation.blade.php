@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    .group__list{
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 10px 0;
    }

    .group__list .item__material{
        flex: 0 0 100%;
        width: 100%;

        display: flex;
        flex-direction: row;
        gap: 10px;
        align-items: center
    }

    .modal-header{
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: start;
        -ms-flex-align: start;
        align-items: flex-start;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }

    button.close {
        padding: 0;
        background-color: transparent;
        border: 0;
        -webkit-appearance: none;
        padding: 1rem;
        margin: -1rem -1rem -1rem auto;
    }

    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }
</style>
@php 
$arrPriceAttach = [];
@endphp
<div class="widget__view row row-cards justify-content-center">
    <div class="col-lg-8 col-md-12">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="card ui-layout__item">
                        <div class="wrapper-content">
                            <div class="pd-all-20">
                                <div class="card-header flexbox-grid-default">
                                    <div class="flexbox-auto-right mr5">
                                        <label
                                            class="title-product-main text-no-bold">{{ __('Bản báo giá cho đơn hàng') }} <i class="fa-solid fa-minus"></i>
                                            <strong>{{ $order->order_code }}</strong></label>
                                            <div>Người tạo đơn: {{ $order->invoice_issuer_name }}  - Vào ngày: {{ date('d/m/Y', strtotime($order->created_at)) }}</div>
                                    </div>
                                </div>
                            </div>
                            @foreach($order->attachByType("Botble\OrderAnalysis\Models\OrderAnalysis")->get() as $key => $attach)
                            <div class="card-body pd-all-20 border-top-title-main">
                                <div class="title d-flex gap-3">
                                    <h4>Bản thiết kế {{$key+1}}</h4>
                                    <a class="btn_modal_information" href="#" data-attach="{{$attach->attach_id}}" data-toggle="modal" data-target="#product-information">Thông tin sản phẩm</a>
                                </div>
                                <div class="table-wrap">
                                    <table class="table-order table-divided table-vcenter card-table w-100">
                                        <tbody>
                                        @php
                                        $totalQuantity=0;
                                        $totalPrice=0;
                                        @endphp
                                            @foreach($attach->attachFile->analysisDetails as $index => $item)
                                                <tr class="item__product">
                                                    <td class="vertical-align-t">
                                                        <div class="wrap-img">
                                                            <img
                                                                class="thumb-image thumb-image-cartorderlist" style="max-width: 100px;"
                                                                src="{{ RvMedia::getImageUrl($item->material->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                alt="{{ $item->material->name }}"
                                                            >
                                                        </div>
                                                    </td>
                                                    <td class="p-3" style="width: 200px;min-width:200px;">
                                                        {{ $item->material->name }}
                                                    </td>
                                                    <td class="p-3" style="white-space: nowrap">
                                                        Mã: <strong>{{$item->material->code }}</strong>
                                                    </td>
                                                    <td class="p-3 text-start" style="white-space: nowrap">
                                                        Số lượng: {{ $item->quantity }} {{$item->material->unit}}
                                                    </td>
                                                    <td class="p-3">x</td>
                                                    <td class="p-3">
                                                        {{format_price($item->material->price)}}
                                                    </td>
                                                    <td class="p-3">=</td>
                                                    <td class="p-3">
                                                        {{format_price($item->material->price * $item->quantity)}}
                                                    </td>
                                                </tr>
                                                @php 
                                                $totalQuantity += $item->quantity;
                                                $totalPrice += $item->material->price * $item->quantity;
                                                @endphp
                                            @endforeach
                                            @php
                                            $arrPriceAttach[$key] = $totalPrice;
                                            @endphp
                                            <tr>
                                                <td colspan="6"></td>
                                                <td colspan="2" class="text-start h4" style="white-space: nowrap">
                                                    <strong>Tổng tiền: </strong> <span class="widget__total_quantity">{{format_price($totalPrice)}}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="card ui-layout__item">
                        <div class="wrapper-content">
                            <div class="pd-all-20">
                                <div class="card-header flexbox-grid-default">
                                    <div class="flexbox-auto-right mr5">
                                        <label
                                            class="title-product-main text-no-bold"><strong>{{ __('Tạo bản báo giá') }}</strong></label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pd-all-20 border-top-title-main">
                                <form action="{{route('order-quotation.create.store')}}" method="post" class="form-quotation">
                                    @csrf
                                    <input type="number" class="form-control" name="order_id" value="{{$order->id}}" hidden>
                                    <div class="form-group">
                                        <label for="">Tiêu đề đơn:</label>
                                        <input type="text" class="form-control" name="title" placeholder="Nhập tiêu đề đơn">
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            @foreach($order->attachByType("Botble\OrderAnalysis\Models\OrderAnalysis")->get() as $key => $attach)
                                                <div class="form-group item">
                                                    <input type="number" class="form-control" name="attach[{{$attach->id}}]" value="{{$attach->id}}" hidden>
                                                    <label for=""><strong>Báo giá cho thiết kế {{$key+1}}:</strong> </label>
                                                    <input type="number" name="quotation[{{$attach->id}}]price" data-id="{{$key}}" value="{{$arrPriceAttach[$key]}}" class="form-control">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="">Thời gian hiệu lực:</label>
                                                <input type="datetime-local" class="form-control" name="effective_time">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="">Thời hạn thanh toán:</label>
                                                <input type="date" class="form-control" name="effective_payment">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Chi phí vận chuyển (nếu có):</label>
                                        <input type="number" class="form-control" name="transport_costs">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Ghi chú:</label>
                                        <textarea name="description" class="form-control" placeholder="Ghi chú"></textarea>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancel-quotation">Huỷ đơn</button>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#approved-quotation" onclick="setPriceInModal()">Xác nhận</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="approved-quotation" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Xác nhận đơn báo giá</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="group-quotation">
            @foreach($order->attachByType("Botble\OrderAnalysis\Models\OrderAnalysis")->get() as $key => $attach)
                <div class="item" data-id="{{$key}}">
                    Bản báo giá {{$key+1}}: <span class="quo_price"></span>
                </div>
            @endforeach
            <div class="quo_total border-top my-3 h3">
                <div class="strong">Tổng tiền: <span class="total_price"></span></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary" onclick="submitFormQuotation('create')">Xác nhận</button>
        </div>
      </div>
    </div>
</div>
<!-- Modal cancel -->
<div class="modal fade" id="cancel-quotation" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="exampleModalLongTitle">Xác nhận huỷ đơn báo giá</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for=""><strong>Lý do:</strong></label>
            <textarea name="" class="form-control" rows="5" placeholder="Ghi rõ lý do"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-danger" onclick="submitFormQuotation('cancel')">Xác nhận</button>
        </div>
      </div>
    </div>
</div>

<!-- Modal information start -->
<div class="modal fade" id="product-information" tabindex="-1" role="dialog" aria-labelledby="information" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Xác nhận đơn báo giá</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="submitFormQuotation('create')">Đóng</button>
        </div>
      </div>
    </div>
</div>
<!-- Modal information end -->

<script>
    function setPriceInModal() {
        const modalQuotation = document.querySelector('#approved-quotation');
        const formQuotation = document.querySelector('.form-quotation');

        if(modalQuotation && formQuotation){
            const listItemModalQuo = modalQuotation.querySelectorAll('.group-quotation .item');
            const listItemFormQuo = formQuotation.querySelectorAll('.item');
            const totalPrice = modalQuotation.querySelector('.group-quotation .total_price');

            let amount = 0;
            listItemModalQuo.forEach(element => {
                let textModal = element.querySelector('.quo_price');
                listItemFormQuo.forEach(item => {
                    let idInput = item.querySelector('input[name*=quotation]');
                    if(idInput.getAttribute('data-id') == element.getAttribute('data-id')){
                        textModal.innerHTML = format_price(idInput.value);
                        amount += parseInt(idInput.value);
                    }
                })
            });

            totalPrice.innerHTML = format_price(amount)
        }
    }

    function format_price(price) {
        price = parseFloat(price);

        const formatted = price.toLocaleString('vi-VN', {
            style: 'currency',
            currency: 'VND'
        });
        return formatted;
    }

    function submitFormQuotation(type) {
        const formQuotation = document.querySelector('.form-quotation');

        if(formQuotation){
            if(type == 'cancel'){
                formQuotation.action = `{{route('order-quotation.cancel', $order->id)}}`;
            }else{
                formQuotation.action = `{{route('order-quotation.create.store')}}`
            }
            formQuotation.submit();
        }
    }

    function ajaxGetInforProductAttach(orderId, id){
        let data;
        $.ajax({
            url: '/api/v1/get-information-product-attach',
            type: 'post',
            async: false,
            data: {
                orderId: orderId,
                id: id,
                type: "Botble\\OrderAnalysis\\Models\\OrderAnalysis"
            },
            success: function (response) {
                return data = response.body;
            },
            error: function(error){
                console.log(error);
            }
        })
        return data;
    }

    function setContentModalInformationAttach(title, response){
        const modalContent = $('#product-information').find('.modal-content');

        if(modalContent){
            //Set title
            $(modalContent).find('.modal-title').text(title);
            
            //Set body
            $(modalContent).find('.modal-body').html(response);
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        const listBtnShowModalInformationAttach = document.querySelectorAll('.btn_modal_information');
        let orderId = document.querySelector('input[name="order_id"]')?.value;

        $(document).on('click', '.btn_modal_information', function(){
            let attachId = $(this).data('attach');
            let response = ajaxGetInforProductAttach(orderId, attachId);
            console.log(response);
            let title = "Thông tin chi tiết của sản phẩm";
            let contentModal = `
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <strong>Tên sản phẩm:</strong>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <span>${response.context.name}</span>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <strong>Mã sản phẩm:</strong>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <span>${response.context.code}</span>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <strong>Mô tả:</strong>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <span>${response.context.description}</span>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <strong>Người thiết kế:</strong>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <span>${response.owner.first_name} ${response.owner.last_name}</span>
                    </div>
                </div>
            `;

            //Cập nhật content cho modal
            setContentModalInformationAttach(title, contentModal)
        })

    })
</script>
@endsection