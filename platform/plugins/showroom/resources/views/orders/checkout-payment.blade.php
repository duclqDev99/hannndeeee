<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thanh toán sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css"
        integrity="sha512-Ez0cGzNzHR1tYAv56860NLspgUGuQw16GiOOp/I2LuTmpSK9xDXlgJz3XN4cnpXWDmkNBKXR/VDMTCnAaEooxA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/vendor/core/plugins/shared-module/css/loading-admin.css">

    <style>

        body {
            background-color: #f1f3f7;
            margin: 0;
            padding: 0;
            margin-top: 20px;
        }
        #screen_qr{
            display: none;
            text-align: center;
        }
        #qr_img{
            border: unset;
        }
        .avatar-lg {
            height: 5rem;
            width: 5rem;
        }

        .font-size-18 {
            font-size: 18px !important;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        a {
            text-decoration: none !important;
        }

        .w-xl {
            min-width: 160px;
        }

        .card {
            margin-bottom: 24px;
            -webkit-box-shadow: 0 2px 3px #e4e8f0;
            box-shadow: 0 2px 3px #e4e8f0;
        }

        .card {
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid #eff0f2;
            border-radius: 1rem;
        }

        .wrap-img{
            max-width: 200px;
            margin: 0 auto;
            border: unset;
        }

        .widget-payment {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 25px 0;
            text-align: center;
        }

        .widget-payment .info h4 {
            font-size: 1.2em;
            text-transform: uppercase;
        }

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
            margin: 0 auto;
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

        .main-payment {
            position: relative;
            z-index: 2;
        }
        .loader-container {
            background: rgba(0, 0, 0, 0.8);
        }

        .fileDropBox {
            width: 100%;
            line-height: 10em;
            border: 1px dashed gray;
            text-align: center;
            color: gray;
            border-radius: 7px;
        }

        .products_payment{
            max-height: 800px;
            overflow-y: hidden;
        }
        
        .products_payment::-webkit-scrollbar-track
        {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            background-color: #F5F5F5;
            border-radius: 10px;
        }

        .products_payment::-webkit-scrollbar
        {
        	width: 10px;
	        background-color: #F5F5F5;
        }

        .products_payment::-webkit-scrollbar-thumb
        {
            border-radius: 10px;
	        background-image: -webkit-gradient(linear,
									   left bottom,
									   left top,
									   color-stop(0.44, rgb(138, 150, 177)));
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="heading">
                <h4>Danh sách sản phẩm</h4>
            </div>
            <div class="col-xl-8 col-lg-8 products_payment">
                <div class="fileDropBox">Trống!!</div>
            </div>

            <div class="col-xl-4 col-lg-4">
                <div class="mt-5 mt-lg-0">
                    <div class="card border shadow-none">
                        <div class="card-header bg-transparent border-bottom py-3 px-4">
                            <h5 class="font-size-16 mb-0">Thông tin thanh toán <span class="float-end"></span></h5>
                        </div>
                        <div class="card-body p-4 pt-2">

                            <div class="table-responsive bill_info">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Tổng giá sản phẩm :</td>
                                            <td class="text-end"><span class="refresh" id="child_sub_amount_label"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Giảm giá : </td>
                                            <td class="text-end"><span class="refresh" id="child_promotion_amount_label"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Số tiền khuyến mãi : </td>
                                            <td class="text-end"><span class="refresh" id="child_discount_amount_label"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Tổng thuế :</td>
                                            <td class="text-end"><span class="refresh" id="child_tax_amount_label"></span></td>
                                        </tr>
                                        <tr class="bg-light">
                                            <th>Tổng tiền :</th>
                                            <td class="text-end">
                                                <span class="refresh" id="child_total_amount_label" class="fw-bold"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- end table-responsive -->
                        </div>
                    </div>
                </div>
                <div class="mt-5 mt-lg-0">
                    <div class="card border shadow-none">
                        <div class="card-header bg-transparent border-bottom py-3 px-4">
                            <h5 class="font-size-16 mb-0">Mã QR thanh toán</h5>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <div class="fileDropBox">Trống!!</div>
                            <div id="screen_qr" class="card shadow-none" style="border: none;">
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <!-- end row -->

    </div>

    <script>


        document.addEventListener('DOMContentLoaded', function(){
            const channel = new BroadcastChannel('order-customer-channel');
            const qrChannel = new BroadcastChannel('QR-customer-channel');
            const qrSuccessChannel = new BroadcastChannel('QR-customer-channel-success');
            const screenQR = document.querySelector('#screen_qr')
            let timeoutId;
            resetTimer();
            qrChannel.onmessage = (event) => {
                console.log(event)
                console.log('qrChannel')
                resetTimer();
                const elDrop = document.querySelectorAll('.fileDropBox');
                elDrop?.forEach(item => {
                    item.classList.add('d-none');
                })
                let qrImg = document.createElement('img');
                qrImg.src = event.data.qr_image;
                qrImg.classList.add('wrap-img');
                screenQR.style.display = 'block';
                screenQR.appendChild(qrImg);

            }
            channel.onmessage = (event) => {
                resetTimer();
                if(event.data.create){
                    console.log('tạo mới')
                    resetElementToBegin()
                }
                else{
                    let products = event.data.child_products
                    const elListProduct = document.querySelector('.products_payment');
                    elListProduct.innerHTML = ''
                    products.forEach(function(value, index) {
                        setListProductOnView(value)

                    });
                    setInfoAmount(event.data)
                    let listPro = elListProduct.querySelectorAll('.card');

                    if(listPro.length > 3){
                        elListProduct.style.overflowY = 'scroll';
                    }
                }
            };
            qrSuccessChannel.onmessage = (event) => {
                resetTimer();
                console.log(event)
                console.log('qrSuccessChannel')

                if(event.data.success == 1){
                    successPayment(screenQR)
                }
            }
            function resetTimer() {
                // Hủy hẹn giờ cũ
                clearTimeout(timeoutId);
                const loaderFrame = document.querySelector('.loader-container')
                if (loaderFrame) {
                    loaderFrame.remove();
                }
                // Thiết lập hẹn giờ mới
                timeoutId = setTimeout(() => {
                    let body = document.querySelector('body')
                    window.insertSvgLoading(body)
                }, 300000); 
            }
        })

        function successPayment(screenQR){
            screenQR.innerHTML = `
            <div class="widget-payment">
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>
                </div>
                <div class="info">
                    <h4>Xác nhận thanh toán thành công!!</h4>
                </div>
            </div>
            `;
            screenQR.classList.add('bg-white');
        }

        function setListProductOnView(product) {
            const elListProduct = document.querySelector('.products_payment');
            if (elListProduct) {
                let htmlProduct = `
                <div class="card border shadow-none">
                    <div class="card-body">

                        <div class="d-flex align-items-start border-bottom pb-3">
                            <div class="me-4">
                                <img src="${product.image_url}" alt=""
                                    class="avatar-lg rounded">
                            </div>
                            <div class="flex-grow-1 align-self-center overflow-hidden">
                                <div>
                                    <h5 class="text-truncate font-size-18"><a href="#"
                                            class="text-dark">${product.name}</a></h5>
                                    <p class="text-muted mb-0">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                    </p>
                                    <p class="mb-0 mt-1"><span class="fw-medium">${product.variation_attributes}</span></p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-2">
                                <ul class="list-inline mb-0 font-size-16">
                                    <li class="list-inline-item">
                                        <a href="#" class="text-muted px-1">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#" class="text-muted px-1">
                                            <i class="mdi mdi-heart-outline"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mt-3">
                                        <p class="text-muted mb-2">Đơn giá</p>
                                        <h5 class="mb-0 mt-2">${product.original_price_label}</h5>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mt-3">
                                        <p class="text-muted mb-2">Số lượng</p>
                                        <div class="d-inline-flex">
                                            ${product.select_qty}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mt-3">
                                        <p class="text-muted mb-2">Thành tiền</p>
                                        <h5>${product.total_price_label}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                `;
                elListProduct.insertAdjacentHTML('afterbegin', htmlProduct);
            }
        }

        function setInfoAmount(data){
            document.querySelector('#child_sub_amount_label').innerHTML = data.child_sub_amount_label
            document.querySelector('#child_tax_amount_label').innerHTML = data.child_tax_amount_label
            document.querySelector('#child_promotion_amount_label').innerHTML = data.child_promotion_amount_label
            document.querySelector('#child_discount_amount_label').innerHTML = data.child_discount_amount_label
            document.querySelector('#child_total_amount_label').innerHTML = data.child_total_amount_label
        }

        function resetElementToBegin(){
            const listProduct = document.querySelector('.products_payment');
            const screenQR = document.querySelector('#screen_qr');

            screenQR.innerHTML = '';
            if(listProduct)
            {
                const itemProduct = listProduct.querySelectorAll('.card');

                itemProduct?.forEach(item => {
                    item.remove();
                })
            }

            if(screenQR){
                screenQR.style.display = 'none';
            }

            const elDrop = document.querySelectorAll('.fileDropBox');
            elDrop?.forEach(item => {
                if(item.classList.contains('d-none')){
                    item.classList.remove('d-none');
                }
            })

            const elRefresh = document.querySelectorAll('.refresh');
            elRefresh?.forEach(item => {
                item.innerHTML = '';
            })
        }
        window.addEventListener('beforeunload', function (e) {
            // Thông điệp sẽ được hiển thị trong hộp thoại xác nhận
            var message = 'Bạn có chắc chắn muốn rời khỏi trang này?';

            // Đặt thông điệp cho sự kiện
            e.returnValue = message;

            // Trả về thông điệp
            return message;
        });
        window.insertSvgLoading = function (element) {
            let svg = `
                <div class="loader-container">
                    <div class="loader-frame">
                        <svg id="Layer_2" class="logo-loading" width="60" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 525.71 642.83">
                        <defs>
                            <style>
                                .cls-1 {
                                    stroke-width: 0px;
                                }

                                .cls-2 {
                                    stroke: #000;
                                    stroke-miterlimit: 10;
                                    stroke-width: 9px;
                                }
                            </style>
                        </defs>
                        <g id="Layer_1-2" data-name="Layer 1">
                            <g>
                                <path class="cls-2"
                                    d="m467.3,206.33l6.22-198.22-150.22,89.78h-121.78L50.42,8.1l8,198.22L5.08,349.44l219.56,288.89h78.22l217.78-288-53.33-144Zm-159.23,413.11h-87.11l-72-161.78-126.22-114.67,62.22-123.56,21.33,5.33-27.56-172.44,123.56,75.56,62.22-12.44,64.89,11.56,124.44-74.67-29.33,176,21.33-7.11,60.44,121.78-123.56,116.44-74.67,160Z" />
                                <polygon class="cls-1" points="110.6 109.22 127.78 214.7 171.04 148.92 110.6 109.22" />
                                <polygon class="cls-1" points="414.69 108.63 397.99 214.19 354.43 148.61 414.69 108.63" />

                                <polygon class="cls-1 eye"
                                    points="117.12 334.1 148.52 387.14 204.23 393.66 227.04 411.73 210.75 378.84 117.12 334.1" />
                                <polygon class="cls-1 eye"
                                    points="409.08 334.1 377.67 387.14 321.97 393.66 299.16 411.73 315.45 378.84 409.08 334.1" />
                                <polygon class="cls-1"
                                    points="224.08 524.4 231.42 554.66 262.86 579.55 295.13 553.24 302.25 524.4 264.52 534.16 224.08 524.4" />
                            </g>
                        </g>
                        </svg>
                    </div>
                    </div>
                        `;


            element.insertAdjacentHTML('beforeend', svg);
            let params = new URLSearchParams(window.location.search);
            if (params.get('theme') === 'dark') {

                let svgElements = document.querySelectorAll('.cls-1, .cls-2');
                svgElements.forEach(function (element) {
                element.style.fill = 'white';
                });
            }
        }
    </script>
</body>

</html>
