<!-- Scan mobile modal -->
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable ">
        <div class="modal-content" id="box_scan">
            <div class="modal-body bg-white p-0 overflow-hidden position-relative">
                <div class="h-100 d-flex flex-column d-block d-sm-none">
                    <div id="box_camera_scanner" class="position-relative w-100 bg-light overflow-hidden"
                        style="height: 45%; display:none">
                        <canvas id="canvas" class="w-100" style="height: 500px"></canvas>
                        <div id="camera-loading" class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-1 text-muted">Đang tải camera...</p>
                        </div>
                        <div id="skeleton-loading"
                            class="position-absolute top-50 start-50 translate-middle text-center"
                            style="display: none;">
                            <div class="spinner-border text-light" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-1 text-light">Đang kiểm tra...</p>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="w-100 d-flex align-items-center justify-content-between">
                                    <h5 class="card-title fw-bold">Thông tin sản phẩm</h5>
                                    <button id="show_full_product_info_btn" class="btn btn-light ">
                                        <i class="fa-solid fa-chevron-up rotate-180"></i> Truy cập Camera
                                    </button>
                                </div>
                            </div>
                            <div class="position-relative card-body p-0 overflow-scroll d-flex flex-column"
                                style="height: 160px">
                                <table id="table-scanned" class="mb-0 table table-bordered">
                                    <thead class="position-sticky " style="top: -1px">
                                        <tr>
                                            <th scope="col">Tên sản phẩm</th>
                                            <th scope="col">Giá</th>
                                            <th scope="col">Số lượng</th>
                                            <th scope="col">Tùy chọn</th>
                                        </tr>
                                    </thead>
                                    <tbody class="body-scanned" id="body">

                                    </tbody>
                                </table>
                                <div class="flex-grow-1 d-flex align-items-center justify-content-center"
                                    id="empty_scanned_message">
                                    <h3 class="card-text fw-light">Trống</h3>
                                </div>
                            </div>
                            <div class="card-footer ">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0 card-title fw-bold">
                                        Tổng tiền:
                                    </h3>
                                    <h3 class="mb-0">
                                        <span id="total_amount">0đ</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('agent.orders.index') }}" type="button" class="btn btn-secondary">Đóng</a>
                <button name="submit" type="button" class="btn btn-primary" disabled>Xác nhận bán</button>
            </div>
        </div>
        {{--  --}}
        <div class="modal-content" id="box_create_success" style="display: none">
            <div class="modal-body bg-white p-0 overflow-hidden position-relative">
                <div class="h-100 ">
                    <div class="vector w-100 card border-0 border-bottom">
                        <div class="card-header d-flex flex-column align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="success-animation m-3 d-flex justify-content-center">
                                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                        <circle class="checkmark__circle" cx="26" cy="26" r="25"
                                            fill="none" />
                                        <path class="checkmark__check" fill="none"
                                            d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                                    </svg>
                                </div>
                                <p class="card-text mb-1">Tạo đơn hàng thành công </p>
                                <p id="total_amount_success" class="card-text fw-bold fs-3">0đ</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-100 card border-0">
                        <div class="card-body border-0">
                            <h3 class="mb-3 fw-bold fs-3">Thông tin đơn hàng</h3>
                            <div class="overflow-scroll position-relative" style="height: 550px">
                                <table class="table w-100">
                                    <thead class="position-sticky top-0">
                                        <tr class="bg-white">
                                            <th>Sản phẩm</th>
                                            <th>Giá</th>
                                            <th style="width: 50px">SL</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product_success_list" class="table align-middle">
                                        {{-- <tr>
                                            <td style="max-width:200px;">
                                                <div class="px-0">
                                                    <div class="d-flex gap-3 align-items-center flex-wrap">
                                                        <span>Áo Fit Xanh Blue</span>
                                                    </div>
                                                    <div class="">
                                                        <small>Color: M, Size: S</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>1.000.000</td>
                                            <td style="width: 50px">1</td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('agent.orders.index') }}" type="button" class="btn btn-secondary">Đóng</a>
                <button name="continue" type="button" class="btn btn-primary">Tiếp tục tạo</button>
            </div>
        </div>

    </div>

    <div class="d-block d-sm-none">
        <agent-create-customer
            :currency="'{{ get_application_currency()->symbol }}'"
            :zip_code_enabled="{{ (int) EcommerceHelper::isZipCodeEnabled() }}"
            {{-- :use_location_data="{{ (int) EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation() }}" --}}
            use_location_data="true"
            :is_tax_enabled={{ (int) EcommerceHelper::isTaxEnabled() }}
            :sub_amount_label="'{{ format_price(0) }}'"
            :tax_amount_label="'{{ format_price(0) }}'"
            :promotion_amount_label="'{{ format_price(0) }}'"
            :discount_amount_label="'{{ format_price(0) }}'"
            :shipping_amount_label="'{{ format_price(0) }}'"
            :total_amount_label="'{{ format_price(0) }}'"
            :agent_id = "'{{(int)request()->input('select_id')}}'"
        ></agent-create-customer>
    </div>


<style>

    .checkmark {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #4bb71b;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #4bb71b;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        position: relative;
        top: 5px;
        right: 5px;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #4bb71b;
        fill: #fff;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;

    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {

        0%,
        100% {
            transform: none;
        }

        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #4bb71b;
        }
    }
    .rotate-180{
        transform: rotate(-180deg);
    }
</style>

<script>
    var selectedCustomerId = null;
    $('#search_customer').on('input', function(){
        loadListCustomersForSearch(1, false, $(this).val());
    });

    $('#delete_customer').on('click', function(){
        $('#detail_customer').addClass('d-none');
        $('#search_customer').removeClass('d-none');
        selectedCustomerId = null;
    });

    $(document).ready(function() {
        var isSearchResultsShown = false;

        $('#search_customer').on('click', function(event) {
            event.stopPropagation();
            isSearchResultsShown = !isSearchResultsShown;
            if (isSearchResultsShown) {
                $('#searchResults').show();
                loadListCustomersForSearch(1, false, $('#search_customer').val());
            } else {
                $('#searchResults').hide();
            }
        });

        $(document).on('click', function() {
            if (isSearchResultsShown) {
                $('#searchResults').hide();
                isSearchResultsShown = false;
            }
        });

        $('#searchResults').on('click', function(event) {
            event.stopPropagation();
        });


        $(document).on('click', '.list-group-item-action', function() {
            console.log('Clicked on dynamically added list item');
            // Thêm mã xử lý của bạn ở đây
        });
    });


function loadListCustomersForSearch(page = 1, force = false, customer_keyword = '') {
    let context = {
        hidden_customer_search_panel: false,
        loading: true,
        customer_keyword: customer_keyword, // Sử dụng giá trị từ khóa từ tham số
        customers: {}
    };

    $('.textbox-advancesearch.customer')
        .closest('.box-search-advance.customer')
        .find('.panel')
        .addClass('active');

    if (_.isEmpty(context.customers) || force) {
        if (context.customerSearchRequest) {
            context.customerSearchRequest.abort();
        }

        $.ajax({
            url: route('showroom.customers.get-list-customers-for-search', {
                keyword: context.customer_keyword,
                page: page,
            }),
            method: 'GET',
            success: function(res) {
                context.customers = res.data;
                const resultsElement = document.getElementById('searchResults');
                resultsElement.innerHTML = ''; // Xóa kết quả cũ

                if (customer_keyword) {
                    const createNewCustomerHTML = `
                        <div class="list-group-item cursor-pointer" onclick="/* Chèn hàm để xử lý tạo khách hàng mới */">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img width="28" src="/vendor/core/plugins/ecommerce/images/next-create-customer.svg" alt="icon">
                                </div>
                                <div class="col">
                                    <span>Tạo khách hàng mới</span>
                                </div>
                            </div>
                        </div>
                    `;
                    resultsElement.innerHTML += createNewCustomerHTML;
                }

                if (res.data.data.length > 0) {
                // Thêm kết quả tìm kiếm vào danh sách
                    res.data.data.forEach(function(customer) {
                        const li = document.createElement('div');
                        li.classList.add('list-group-item', 'list-group-item-action');
                        li.setAttribute('data-id', customer.id);
                        li.setAttribute('data-name', customer.name);
                        li.setAttribute('data-vid', customer.vid);
                        li.setAttribute('data-avatarurl', customer.avatar_url);
                        const contentHTML = `
                            <div class="flexbox-grid-default flexbox-align-items-center">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar" style="background-image: url('${customer.avatar_url || 'đường dẫn tới hình ảnh mặc định'}');"></span>
                                    </div>
                                    <div class="col text-truncate">
                                        <div class="text-body d-block">${customer.name}</div>
                                        <div class="text-body d-block">${customer.vid}</div>
                                    </div>
                                </div>
                            </div>
                        `;

                        li.innerHTML = contentHTML;
                        resultsElement.appendChild(li);
                    });
                    bindClickEventToListItems();
                } else if (customer_keyword) {
                    // Hiển thị thông báo khi không tìm thấy khách hàng và có nhập text
                    const noResultHTML = `<div class="list-group-item">Không tìm thấy khách hàng!</div>`;
                    resultsElement.innerHTML += noResultHTML;
                }
                context.loading = false;
            },
            error: function(xhr, textStatus) {
                if (textStatus !== 'abort') {
                    context.loading = false;
                    // Xử lý lỗi ở đây
                }
            }
        });
    }
}

function bindClickEventToListItems() {
    // Sử dụng event delegation để gắn sự kiện click
    $('.list-group-item-action').on('click', function(){
        var customerId = $(this).data('id');
        var name = $(this).data('name');
        var vid = $(this).data('vid');
        var avatarUrl = $(this).data('avatarurl');

        $('#detail_customer .avatar').css('background-image', 'url("' + avatarUrl + '")');
        $('#detail_customer .mb-n1').eq(0).text(name);
        $('#detail_customer .mb-n1').eq(1).text(vid);

        $('#customer_id').data('id', customerId);
        $('#detail_customer').removeClass('d-none');
        $('#searchResults').hide();
        $('#search_customer').addClass('d-none');
    })
}
</script>
