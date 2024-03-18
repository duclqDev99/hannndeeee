

<template>
    <div class="w-100">
        <div class="row g-2" style="margin-top:10px">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-money-check-dollar fa-2x p-2 bg-success custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Doanh thu')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.revenue}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-money-check-dollar fa-2x p-2 bg-success custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Số tiền chuyển khoản')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.revenueBankTransfer}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-money-check-dollar fa-2x p-2 bg-success custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Số tiền tiền mặt')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.revenueCash}}</h2>
                    </div>
                </div>
            </div>
             <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-money-check-dollar fa-2x p-2 bg-success custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Tổng tiền thuế khách trả')}}
                        </h6>
                        <h2 class="card-title" style="font-size:1.8em;">{{data.taxAmount}}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2" style="margin-top:10px">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-right-to-bracket fa-2x p-2 bg-pink custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('sản phẩm nhập')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductReceipt}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-right-from-bracket fa-2x p-2 bg-pink custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('sản phẩm xuất')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductIssue}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-box-tissue fa-2x p-2 bg-pink custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('sản phẩm đã bán')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductSold}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-box-tissue fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('sản phẩm chờ bán')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductPendingSold}}</h2>
                    </div>
                </div>
            </div>


        </div>

        <div class="row g-2" style="margin-top:10px">
             <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fas fa-line-chart fa-2x p-2 bg-info custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Sản phẩm tồn hiện có')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.product}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                         <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-person-military-pointing fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Khách hàng')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.customer}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-cart-shopping fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Đơn hàng')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.order}}</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                            <i class="fa-solid fa-wallet fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Điểm hoàn ví')}}
                        </h6>
                        <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.totalrFundedPointAmount}}</h2>
                    </div>
                </div>
            </div>
        </div>

         <div class="row g-2" style="margin-top:10px;">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane d-flex justify-content-between align-items-center">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-receipt fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Phiếu nhập kho đang chờ')}}
                            </h6>
                            <div class="d-flex">
                                Tổng số lượng phiếu theo trạng thái ngày dự kiến:
                                <div class="dropdown ms-3">
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        :data-bs-title="noteToolipt(key)"
                                        v-for="(value, key) in totalExpectedDateReceipts" :key="key"
                                        :class="`btn bg-${key} badge text-${key}-fg me-1`"
                                        @click="filterDataReceipts(key)"
                                    >
                                        {{value}}
                                    </button>
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Tất cả"
                                        @click="filterDataReceipts('all')"
                                        :class="`btn bg-secondary badge text-secondary-fg me-1`"
                                    >
                                        Tất cả
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="scrollable-element" style="overflow: scroll; height:400px">
                            <table class="table table-hover mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Tiêu đề')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Người tạo đơn')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Mô Tả')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Ngày dự kiến')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Trạng Thái')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(value, key) in receiptsFilter" :key="key">
                                        <th scope="col">{{key + 1}}</th>
                                        <td scope="col">
                                            <a class="link-offset-2 link-underline link-underline-opacity-0 fw-bold" :href="getRouteConfirmViewInReceipt(value.id)" style="font-size: 18px;">{{value.title}}</a>
                                        </td>
                                        <td scope="col">{{value.invoice_issuer_name}}</td>
                                        <td scope="col">{{value.description}}</td>
                                        <td scope="col" :style="{ color: expectedDateColor(value.expected_date)}">{{value.expected_date}}</td>
                                        <td scope="col">
                                            <span class="badge bg-warning text-warning-fg">{{value.status.label}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- <div class="scrollable-element" style="overflow: scroll; height: 400px;">
                            <div class="pane mt-4 align-items-start" style="border-bottom: 0.5px dashed;" v-for="showroomReceipt in data.showroomReceipts" :key="showroomReceipt.id">
                                <div class="ms-3 h-100 d-flex flex-column">
                                    <p style="margin-bottom: 5px;">
                                        <a class="link-offset-2 link-underline link-underline-opacity-0 fw-bold" :href="getRouteConfirmViewInReceipt(showroomReceipt.id)" style="font-size: 18px;">{{showroomReceipt.title}}</a>
                                        <span class="small" style="float: inline-end; color:#F8E559">{{showroomReceipt.status.label}}</span>
                                    </p>
                                    <p class="mt-2 f75" style="margin: 0 0 3px 0 !important;">
                                        Người tạo đơn:{{showroomReceipt.invoice_issuer_name}}
                                    </p>
                                    <p class="mt-2 f75" style="margin: 0 0 3px 0 !important;">
                                        Ghi chú: {{showroomReceipt.description ?? '___' }}.
                                        <span class="small" :style="{ color: expectedDateColor(showroomReceipt.expected_date) , 'float': 'inline-end'}">Ngày dự kiến: {{ showroomReceipt.expected_date }}</span>
                                    </p>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane d-flex justify-content-between align-items-center">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-receipt fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Phiếu xuất kho đang chờ')}}
                            </h6>
                            <div class="d-flex">
                                Tổng số lượng phiếu theo trạng thái ngày dự kiến:
                                <div class="dropdown ms-3">
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        :data-bs-title="noteToolipt(key)"
                                        v-for="(value, key) in totalExpectedDate" :key="key"
                                        :class="`btn bg-${key} badge text-${key}-fg me-1`"
                                        @click="filterDataIssues(key)"
                                    >
                                        {{value}}
                                    </button>
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Tất cả"
                                        @click="filterDataIssues('all')"
                                        :class="`btn bg-secondary badge text-secondary-fg me-1`"
                                    >
                                        Tất cả
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="scrollable-element" style="overflow: scroll; height:400px">
                            <table class="table table-hover mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Tiêu đề')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Người tạo đơn')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Mô Tả')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Ngày dự kiến')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Trạng Thái')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(value, key) in issuesFilter" :key="key">
                                        <th scope="col">{{key + 1}}</th>
                                        <td scope="col">
                                            <a class="link-offset-2 link-underline link-underline-opacity-0 fw-bold" :href="getRouteConfirmViewInReceipt(value.id)" style="font-size: 18px;">{{value.title}}</a>
                                        </td>
                                        <td scope="col">{{value.invoice_issuer_name}}</td>
                                        <td scope="col">{{value.description}}</td>
                                        <td scope="col" :style="{ color: expectedDateColor(value.expected_date)}">{{value.expected_date}}</td>
                                        <td scope="col">
                                            <span class="badge bg-warning text-warning-fg">{{value.status.label}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2" style="margin-top:10px;">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane d-flex justify-content-between align-items-center">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-award fa-2x p-2 bg-secondary custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Sản phẩm bán chạy')}}
                            </h6>
                        </div>
                        <div class="scrollable-element mb-4" style="overflow: scroll">
                            <table class="table table-hover mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">Hình ảnh</th>
                                        <th scope="col">Tên sản phẩm</th>
                                        <th scope="col">Số lượng bán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <tr v-for="(value, key) in data.topSellingProducts" :key="key">
                                        <th scope="col">{{key + 1}}</th>
                                        <td>
                                            <img style="width:50px; height:50px" :src="value.images" class="img-thumbnail">
                                        </td>
                                        <td scope="col">{{value.name}}</td>
                                        <td scope="col">{{value.total}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-globe fa-2x p-2 bg-info custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Thống kê')}}
                            </h6>
                        </div>
                        <div class="pane mt-4 align-items-start" >
                            <div class="ms-3 d-flex flex-column">
                                <h3 class="fw-600" style="margin-bottom: 5px;">Phiếu xuất kho <span class="small" style="float: inline-end; color:green">Tổng số phiếu:{{totalIssues}}</span></h3>
                                <p class="mt-2 f75" style="margin: 0 0 3px 0 !important;">
                                </p>
                                <div  class="scrollable-element" style="overflow: scroll;height: 150px; border-bottom: 0.5px dashed; margin-top:5px">
                                    <div class="progress-group" v-for="(percentage, status) in percentagesIssues" :key="status">
                                        <div class="progress-group-header" style="display: flex;">
                                            <span class="title">{{status}} : {{data.countsIssueInStatus[status]}} </span>
                                            <span class="ms-auto fw-semibold">{{percentage}}%</span>
                                        </div>
                                        <div class="progress-group-bars">
                                            <div class="progress progress-thin" style="">
                                                <div class="progress-bar bg-warning-gradient"
                                                role="progressbar"
                                                aria-valuenow="90"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                                :style="{ width: percentage + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                            <div class="ms-3 d-flex flex-column">
                                <h3 class="fw-600" style="margin-bottom: 5px;">Phiếu nhập kho <span class="small" style="float: inline-end; color:green">Tổng số phiếu:{{totalReceipts}}</span></h3>
                                <p class="mt-2 f75" style="margin: 0 0 3px 0 !important;">
                                </p>
                                <div class="scrollable-element" style="overflow: scroll;height: 150px; border-bottom: 0.5px dashed; margin-top:5px">
                                    <div class="progress-group" v-for="(percentage, status) in percentagesReceipts" :key="status">
                                        <div class="progress-group-header" style="display: flex;">
                                            <span class="title">{{status}} : {{data.countsReceiptsInStatus[status]}} </span>
                                            <span class="ms-auto fw-semibold">{{percentage}}%</span>
                                        </div>
                                        <div class="progress-group-bars">
                                            <div class="progress progress-thin" style="">
                                                <div class="progress-bar bg-warning-gradient"
                                                role="progressbar"
                                                aria-valuenow="90"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                                :style="{ width: percentage + '%' }"
                                                ></div>
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

        <div class="row g-2" style="margin-top:10px;">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-house fa-2x p-2 bg-info custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Kho')}}
                            </h6>
                        </div>
                        <div class="scrollable-element" style="overflow: scroll; height: 400px;">
                            <table class="table table-hover mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">Tên</th>
                                        <th scope="col">Mô Tả</th>
                                        <th scope="col">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <tr v-for="(value, key) in data.warehouses" :key="key">
                                        <th scope="col">{{key + 1}}</th>
                                        <td scope="col">{{value.name}}</td>
                                        <td scope="col">{{value.description}}</td>
                                        <td scope="col">
                                            <span class="badge bg-success text-success-fg">{{value.status.label}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card card-boundary card4 card-body">
                        <div class="pane d-flex justify-content-between align-items-center">
                            <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                                <i class="fa-solid fa-receipt fa-2x p-2 bg-orange custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Đơn')}}
                            </h6>
                            <div class="d-flex">
                                Tổng số lượng phiếu theo trạng thái ngày dự kiến:
                                <div class="dropdown ms-3">
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Đã hoàn thành"
                                        :class="`btn bg-success badge text-success-fg me-1`"
                                        @click="filterDataOrder('success')"
                                    >{{countOrderInStatus['success']}}
                                    </button>

                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Đang xử lý"
                                        :class="`btn bg-warning badge text-warning-fg me-1`"
                                        @click="filterDataOrder('processing')"
                                    >{{countOrderInStatus['processing']}}
                                    </button>

                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Đã hủy"
                                        :class="`btn bg-danger badge text-danger-fg me-1`"
                                        @click="filterDataOrder('cancel')"
                                    >
                                    {{countOrderInStatus['cancel']}}
                                    </button>
                                    <button
                                        type="button"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-title="Tất cả"
                                        @click="filterDataOrder('all')"
                                        :class="`btn bg-secondary badge text-secondary-fg me-1`"
                                    >
                                        Tất cả: {{countOrderInStatus['all']}}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="scrollable-element" style="overflow: scroll; height:400px">
                            <table class="table table-hover mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Mã đơn')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Mô Tả')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Tổng tiền')}}</th>
                                        <th scope="col">{{capitalizeFirstLetterOfEachWord('Trạng Thái')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(value, key) in orderFilter" :key="key">
                                        <th scope="col">{{value.id}}</th>
                                        <td scope="col">
                                            <a class="link-offset-2 link-underline link-underline-opacity-0 fw-bold" :href="getRouteConfirmViewInReceipt(value.id)" style="font-size: 18px;">{{value.code}}</a>
                                        </td>
                                        <td scope="col">{{value.description}}</td>
                                        <td scope="col">{{formatPrice(value.amount)}}</td>
                                        <!-- <td scope="col" :style="{ color: expectedDateColor(value.expected_date)}">{{value.expected_date}}</td> -->
                                        <td scope="col">
                                            <span v-if="value.status.value == 'completed'" class=" badge bg-success text-success-fg">{{value.status.label}}</span>
                                            <span v-if="value.status.value == 'processing'" class="badge bg-warning text-warning-fg">{{value.status.label}}</span>
                                            <span v-if="value.status.value == 'canceled'" class="badge bg-danger text-danger-fg">{{value.status.label}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    components: {
    },
    props: {
        business_type_select:{
            type: Array,
            default: [],
        },
        data:{
            type: Array,
            default: [],
        },
    },

  data() {
    return {
        percentagesIssues:{},
        totalIssues:0,
        percentagesReceipts:{},
        totalReceipts:0,
        colorExpectedDate: '#000',
        totalExpectedDate: {
            'danger': 0,
            'warning': 0,
            'success': 0,
        },
        totalExpectedDateReceipts: {
            'danger': 0,
            'warning': 0,
            'success': 0,
        },
        receiptsFilter: this.data.showroomReceipts,
        issuesFilter: this.data.showroomIssues,
        orderFilter: this.data.getDataOrder,
        countOrderInStatus : {
            'success' : 0,
            'processing' : 0,
            'cancel' : 0,
            'all' : 0,
        },
    };
  },
  methods: {
        calculatePercentages() {
            this.percentagesIssues = {};
            this.totalIssues = 0;
            this.percentagesReceipts = {};
            this.totalReceipts = 0;
            if (this.data && this.data.countsIssueInStatus) {
                for (let status in this.data.countsIssueInStatus) {
                this.totalIssues += this.data.countsIssueInStatus[status];
                }
                for (let status in this.data.countsIssueInStatus) {
                this.percentagesIssues[status] = ((this.data.countsIssueInStatus[status] / this.totalIssues) * 100).toFixed(2);
                }
            }

            if (this.data && this.data.countsReceiptsInStatus) {
                for (let status in this.data.countsReceiptsInStatus) {
                this.totalReceipts += this.data.countsReceiptsInStatus[status];
                }
                for (let status in this.data.countsReceiptsInStatus) {
                this.percentagesReceipts[status] = ((this.data.countsReceiptsInStatus[status] / this.totalReceipts) * 100).toFixed(2);
                }
            }
        },
        expectedDateColor(expected_date) {
            let color = '#000000';
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const expectedDate = new Date(expected_date);
            expectedDate.setHours(0, 0, 0, 0);

            const diffTime =  expectedDate - today;
            const diffDays = diffTime / (1000 * 60 * 60 * 24);

            if (diffDays <= 0) {
                color = '#FF0000';
            } else if (diffDays > 0 && diffDays <= 3) {
                color = '#F76707';
            } else if (diffDays > 3) {
                color = '#00FF00';
            }
            return color;
        },

        totalExpectedDateColor() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            this.totalExpectedDate = {
                'danger': 0,
                'warning': 0,
                'success': 0,
            };
            this.totalExpectedDateReceipts = {
                'danger': 0,
                'warning': 0,
                'success': 0,
            };

            if (this.data.showroomIssues && this.data.showroomIssues.length > 0) {
                this.data.showroomIssues.forEach(element => {
                    const expectedDate = new Date(element.expected_date);
                    expectedDate.setHours(0, 0, 0, 0);

                    const diffTime = expectedDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);
                    if (diffDays <= 0) {
                        this.totalExpectedDate['danger'] += 1;
                    } else if (diffDays > 0 && diffDays <= 3) {
                        this.totalExpectedDate['warning'] += 1;
                    } else if (diffDays > 3) {
                        this.totalExpectedDate['success'] += 1;
                    }
                });
            }

            if (this.data.showroomReceipts && this.data.showroomReceipts.length > 0) {
                this.data.showroomReceipts.forEach(element => {
                    const expectedDate = new Date(element.expected_date);
                    expectedDate.setHours(0, 0, 0, 0);

                    const diffTime = expectedDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);
                    if (diffDays <= 0) {
                        this.totalExpectedDateReceipts['danger'] += 1;
                    } else if (diffDays > 0 && diffDays <= 3) {
                        this.totalExpectedDateReceipts['warning'] += 1;
                    } else if (diffDays > 3) {
                        this.totalExpectedDateReceipts['success'] += 1;
                    }
                });
            }
        },

         getRouteConfirmViewInReceipt(id){
            if(this.data.permission.showroomReceipt){
                return route('showroom-receipt.confirmView', id)
            }else{
                return '#'
            }
        },

        getRouteConfirmViewInIssue(id){
            if(this.data.permission.showroomIssue){
                return route('showroom-issue.confirmView', id)
            }else{
                return '#'
            }
        },

        capitalizeFirstLetterOfEachWord(string) {
            return string.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        noteToolipt(value) {
            let note = '___';
            if (value == 'danger') {
                note = 'Hôm nay hoặc đã quá hạn';
            } else if (value == 'warning') {
                note = 'Trong vòng 3 ngày tới';
            } else if (value == 'success') {
                note = 'Hơn 3 ngày';
            }
            return note;
        },

        filterDataReceipts(condition) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if(this.data.showroomReceipts){
                const filteredData = this.data.showroomReceipts.filter(receipt => {
                    const expectedDate = new Date(receipt.expected_date);
                    expectedDate.setHours(0, 0, 0, 0);

                    const diffTime = expectedDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);
                    switch (condition) {
                        case 'danger':
                            return diffDays <= 0;
                        case 'warning':
                            return diffDays > 0 && diffDays <= 3;
                        case 'success':
                            return diffDays > 3;
                        default:
                            return true;
                    }
                });
                this.receiptsFilter = filteredData;
            }
        },

        filterDataOrder(condition){
            let allOrder = this.data.getDataOrder;
            if (Array.isArray(allOrder)) {
                switch (condition) {
                    case 'success':
                        this.orderFilter = allOrder.filter(value => value.status.value == 'completed')
                        break;
                    case 'processing':
                        this.orderFilter = allOrder.filter(value => value.status.value == 'processing')
                        break;
                    case 'cancel':
                        this.orderFilter = allOrder.filter(value => value.status.value == 'cancel')
                        break;
                    default:
                        this.orderFilter = allOrder;
                }

            }
        },

        filterDataIssues(condition) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if(this.data.showroomIssues){
                const filteredData = this.data.showroomIssues.filter(receipt => {
                    const expectedDate = new Date(receipt.expected_date);
                    expectedDate.setHours(0, 0, 0, 0);

                    const diffTime = expectedDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);
                    switch (condition) {
                        case 'danger':
                            return diffDays <= 0;
                        case 'warning':
                            return diffDays > 0 && diffDays <= 3;
                        case 'success':
                            return diffDays > 3;
                        default:
                            return true;
                    }
                });
                this.issuesFilter = filteredData;
            }
        },

        formatPrice(value) {
            return new Intl.NumberFormat('vi-VN').format(parseFloat(value));
        },
  },
    watch: {
        'data': {
            deep: true,
            handler(newVal) {
                this.calculatePercentages();
                this.totalExpectedDateColor();
                this.receiptsFilter = this.data.showroomReceipts;
                this.issuesFilter = this.data.showroomIssues;
                this.orderFilter = this.data.getDataOrder;
                let allOrder = this.data.getDataOrder;
                this.countOrderInStatus = {
                    'success' : 0,
                    'processing' : 0,
                    'cancel' : 0,
                    'all' : 0,
                };
                if (Array.isArray(allOrder)) {
                    this.countOrderInStatus['all'] = allOrder.length;
                    allOrder.forEach(order => {
                        switch (order.status.value) {
                            case 'completed':
                                this.countOrderInStatus['success']++;
                                break;
                            case 'processing':
                                this.countOrderInStatus['processing']++;
                                break;
                            case 'cancel':
                                this.countOrderInStatus['cancel']++;
                                break;
                        }
                    });
                }
            }
        }
    },
   mounted() {
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
};
</script>
