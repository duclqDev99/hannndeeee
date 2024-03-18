

<template>
    <div class="row g-2" style="margin-top:10px">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                        <i class="fas fa-line-chart fa-2x p-2 bg-info custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Sản phẩm tồn hiện có')}}
                    </h6>
                    <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.product}}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                        <i class="fa-solid fa-right-to-bracket fa-2x p-2 bg-pink custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Tổng sản phẩm đã nhập')}}
                    </h6>
                    <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductReceipt}}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-4  col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title ms-3 mb-2 fw-bold" style="margin: 0!important">
                        <i class="fa-solid fa-right-from-bracket fa-2x p-2 bg-pink custom-icon-card"></i> {{capitalizeFirstLetterOfEachWord('Tổng sản phẩm đã xuất')}}
                    </h6>
                    <h2 class="card-title mt-2" style="font-size:1.8em;">{{data.countProductIssue}}</h2>
                </div>
            </div>
        </div>
    </div>

     <div class="row g-2" style="margin-top:5px">
        <div class="col-lg-6 col-md-6 col-sm-12">
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
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-boundary card4 card-body">
                <div class="pane d-flex justify-content-between align-items-center">
                    <h6 class="card-title ms-3 mb-2 fw-600" style="margin: 0!important">
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

    <div class="row g-2" style="margin-top:5px">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-boundary card4 card-body">
                <div class="pane">
                    <h6 class="card-title ms-3 mb-2 fw-600" style="margin: 0!important">
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

        <div class="col-lg-6 col-md-6 col-sm-12">

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
                            <div class="scrollable-element" style="overflow: scroll; height: 150px; border-bottom: 0.5px dashed; margin-top:5px">
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
                        <h3 class="fw-600" style="margin-bottom: 5px;">Phiếu nhập kho<span class="small" style="float: inline-end; color:green">Tổng số phiếu:{{totalReceipts}}</span></h3>
                        <p class="mt-2 f75" style="margin: 0 0 3px 0 !important;">
                        </p>
                        <div class="scrollable-element" style="overflow: scroll; height: 150px; border-bottom: 0.5px dashed; margin-top:5px">
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

</template>

<script>

export default {
    components: {
    },
    props: {
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
            receiptsFilter: {},
            issuesFilter: {},
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

            if (this.data.hubIssues && this.data.hubIssues.length > 0) {
                this.data.hubIssues.forEach(element => {
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

            if (this.data.hubReceipts && this.data.hubReceipts.length > 0) {
                this.data.hubReceipts.forEach(element => {
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
            if(this.data.permission.hubReceipt){
                return route('hub-receipt.confirm', id)
            }else{
                return '#'
            }
        },

        getRouteConfirmViewInIssue(id){
            if(this.data.permission.hubIssue){
                return route('hub-issue.view-confirm', id)
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

            if(this.data.hubReceipts){
                const filteredData = this.data.hubReceipts.filter(receipt => {
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

        filterDataIssues(condition) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if(this.data.hubIssues){
                const filteredData = this.data.hubIssues.filter(receipt => {
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
        }
    },
    watch: {
        'data': {
            deep: true,
            handler(newVal) {
                this.calculatePercentages();
                this.totalExpectedDateColor();
                this.receiptsFilter = this.data.hubReceipts;
                this.issuesFilter = this.data.hubIssues;
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
