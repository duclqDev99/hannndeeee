<template>
    <div class="row">
        <div class="col-lg-3  col-md-6 col-sm-12">
            <label class="form-label">Loại hình kinh doanh: </label>
            <select class="form-select form-select" aria-label=".form-select-sm" v-model="selectOption1" @change="handleChangeSelect1">
                <option value="0">Chọn</option>
                <option value="1">Đại lý</option>
                <option value="2">Showroom</option>
                <option value="3">Hub</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12">
            <label class="form-label">{{this.labelSelect2}}: </label>
            <select class="form-select form-select" aria-label=".form-select-sm" v-model="selectOption2" @change="handleChangeSelect2" >
                <option value="0">Tất cả</option>
                <option v-for="option in dataSelectOption2" :key="option.id" :value="option.id">{{ option.name }}</option>
            </select>
        </div>

       <div class="col-lg-2 col-md-6 col-sm-12">
            <label class="form-label">Ngày bắt đầu: </label>
            <DatePicker
                v-model:value="selectedDate"
                @change="onchangeDate"
                range
                :shortcuts="dateShortcuts"
                inputClass="form-control"
            />
        </div>
    </div>
    <div>
        <ViewAgent v-if="selectOption1 == '1'" :data="dataAgent"/>
        <ViewShowroom v-if="selectOption1 == '2'" :data="dataShowroom"/>
        <ViewHub v-if="selectOption1 == '3'" :data="dataHub"/>

    </div>
</template>

<script>
import ViewAgent from './ViewAgent.vue';
import ViewShowroom from './ViewShowroom.vue';
import { ref, onMounted } from 'vue';
import Botble from '../../../../../../sales/resources/assets/js/utils';
import ViewHub from './ViewHub.vue';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';

export default {
    components: {
        ViewAgent,ViewShowroom,
        ViewHub,
        DatePicker
    },
    setup() {
        const startDate = ref(null);
        const endDate = ref(null);



        const dateShortcuts = [
        {
            text: 'Hôm Nay',
            onClick: () => {
            const today = new Date();
            startDate.value = today;
            endDate.value = today;
            return [today, today];
            },
        },
         {
            text: 'Tuần Này',
            onClick: () => {
                const today = new Date();
                const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1))); // Chủ nhật là ngày đầu tiên
                const lastDayOfWeek = new Date(firstDayOfWeek.getTime());
                lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);
                startDate.value = firstDayOfWeek;
                endDate.value = lastDayOfWeek;
                return [firstDayOfWeek, lastDayOfWeek];
            },
        },
        {
            text: 'Tháng Này',
            onClick: () => {
            const startOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
            const endOfMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);
            startDate.value = startOfMonth;
            endDate.value = endOfMonth;
            return [startOfMonth, endOfMonth];
            },
        },
        {
            text: '7 Ngày Qua',
            onClick: () => {
            const today = new Date();
            const last7Days = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 6);
            startDate.value = last7Days;
            endDate.value = today;
            return [last7Days, today];
            },
        },
        {
            text: '1 Tháng Qua',
            onClick: () => {
                const today = new Date();
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
                startDate.value = lastMonth;
                endDate.value = today;
                return [lastMonth, today];
            },
        },
        {
            text: '1 Năm Qua',
            onClick: () => {
                const today = new Date();
                const lastYear = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
                startDate.value = lastYear;
                endDate.value = today;
                return [lastYear, today];
            },
        },

        ];

        return { startDate, endDate , dateShortcuts };
    },
    props: {
        business_type_select:{
            type: Array,
            default: [],
        },
        data_agent:{
            type: Array,
            default: [],
        },
        data_showroom:{
            type: Array,
            default: [],
        },
        data_hub:{
            type: Array,
            default: [],
        },
        permission:{
            type: Array,
            default: [],
        },

    },

  data() {
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    return {
      selectedType: 'showroom', // Mặc định chọn showroom
      options: [], // Các lựa chọn cho select 2
      selectedOption: '', // Lựa chọn hiện tại của select 2
      selected: '', // Ngày được chọn
      selectOption1 : 0,
      selectOption2 : 0,
      dataSelectOption2: [],
      startDate: '',
      endDate: '',
      selectedDate: [lastMonth, today],
      labelSelect2: 'Tùy chọn',
      dataAgent : {
        revenue: 0,
        totalProduct:0,
        totalProductSold:0,
        permission: this.permission,
      },

      dataShowroom : {
        revenue: 0,
        totalProduct:0,
        totalProductSold:0,
        permission: this.permission,
      },


      dataHub : {
        permission: this.permission,
      }
    };
  },
  methods: {
    async handleChangeSelect1(event) {
        if(event.type){
            switch(this.selectOption1){
                case '1':
                    this.dataSelectOption2 = Object.entries(this.data_agent).map(([id, name]) => ({id, name}))
                    this.selectOption2 = 0;
                    this.labelSelect2 = 'Chọn đại lý';
                    //agent
                    const response = await axios.post(route('overview-report.get-data-report-of-agent'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });
                    this.dataAgent.revenue = this.formatCurrencyVND(response.data?.revenue ?? 0);
                    this.dataAgent.totalProduct = this.formatNumber(response.data?.totalProduct ?? 0);
                    this.dataAgent.totalProductSold = this.formatNumber(response.data?.totalProductSold ?? 0);
                    this.dataAgent.warehouses = response.data?.warehouses ?? [];
                    this.dataAgent.agentReceipts = response.data?.agentReceipts ?? [];
                    this.dataAgent.agentIssues = response.data?.agentIssues ?? [];
                    this.dataAgent.countsAgentIssueInStatus = response.data?.countsAgentIssueInStatus ?? [];
                    this.dataAgent.countsReceiptsInStatus = response.data?.countsReceiptsInStatus ?? [];
                    //agent

                    break;
                case '2':
                    this.dataSelectOption2 = Object.entries(this.data_showroom).map(([id, name]) => ({id, name}))
                    this.selectOption2 = 0;
                    this.labelSelect2 = 'Chọn showroom';

                     //showroom
                    const showroomResponse = await axios.post(route('overview-report.get-data-report-of-showroom'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });
                    this.dataShowroom.revenue = this.formatCurrencyVND(showroomResponse.data.revenue?.revenue ?? 0);
                    this.dataShowroom.revenueBankTransfer = this.formatCurrencyVND(showroomResponse.data.revenueBankTransfer?.revenue ?? 0);
                    this.dataShowroom.revenueCash = this.formatCurrencyVND(showroomResponse.data.revenueCash?.revenue ?? 0);
                    this.dataShowroom.taxAmount = this.formatCurrencyVND(showroomResponse.data.taxAmount?.revenue ?? 0);
                    this.dataShowroom.product = this.formatNumber(showroomResponse.data?.product ?? 0);
                    this.dataShowroom.customer = this.formatNumber(showroomResponse.data?.customer ?? 0);
                    this.dataShowroom.order = this.formatNumber(showroomResponse.data?.order ?? 0);
                    this.dataShowroom.getDataOrder = showroomResponse.data?.getDataOrder ?? [];
                    this.dataShowroom.warehouses = showroomResponse.data?.warehouses ?? [];
                    this.dataShowroom.showroomReceipts = showroomResponse.data?.showroomReceipts ?? [];
                    this.dataShowroom.showroomIssues = showroomResponse.data?.showroomIssues ?? [];
                    this.dataShowroom.countsIssueInStatus = showroomResponse.data?.countsIssueInStatus ?? [];
                    this.dataShowroom.countsReceiptsInStatus = showroomResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataShowroom.topSellingProducts = showroomResponse.data?.topSellingProducts ?? [];
                    this.dataShowroom.countProductIssue = this.formatNumber(showroomResponse.data?.countProductIssue ?? 0);
                    this.dataShowroom.countProductReceipt = this.formatNumber(showroomResponse.data?.countProductReceipt ?? 0);
                    this.dataShowroom.countProductSold = this.formatNumber(showroomResponse.data?.countProductSold ?? 0);
                    this.dataShowroom.countProductPendingSold = this.formatNumber(showroomResponse.data?.countProductPendingSold ?? 0);
                    this.dataShowroom.totalrFundedPointAmount = this.formatNumber(showroomResponse.data?.totalrFundedPointAmount?.fundedPointAmount ?? 0);
                    // this.dataShowroom.totalProductSold = this.formatNumber(showroomResponse.data.totalProductSold);
                    //showroom
                    break;
                case '3':
                    this.dataSelectOption2 = Object.entries(this.data_hub).map(([id, name]) => ({id, name}))
                    this.selectOption2 = 0;
                    this.labelSelect2 = 'Chọn hub';

                    const hubResponse = await axios.post(route('overview-report.get-data-report-of-hub'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });
                    this.dataHub.warehouses = hubResponse.data?.warehouses ?? [];
                    this.dataHub.hubReceipts = hubResponse.data?.hubReceipts ?? [];
                    this.dataHub.hubIssues = hubResponse.data?.hubIssues ?? [];
                    this.dataHub.countsIssueInStatus = hubResponse.data?.countsIssueInStatus ?? [];
                    this.dataHub.countsReceiptsInStatus = hubResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataHub.countProductIssue = this.formatNumber(hubResponse.data?.countProductIssue ?? 0);
                    this.dataHub.countProductReceipt = this.formatNumber(hubResponse.data?.countProductReceipt ?? 0);
                    this.dataHub.product = this.formatNumber(hubResponse.data?.product ?? 0);

                    break;
                default:
                    this.dataSelectOption2 = null,
                    this.selectOption2 = 0;
                    this.labelSelect2 = 'Tùy chọn';
            }
        }
    },

    async handleChangeSelect2(event) {
        if(event.type){
            switch(this.selectOption1){
                case '1':
                    const response = await axios.post(route('overview-report.get-data-report-of-agent'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });

                    this.dataAgent.revenue = this.formatCurrencyVND(response.data?.revenue ?? 0);
                    this.dataAgent.totalProduct = this.formatNumber(response.data?.totalProduct ?? 0);
                    this.dataAgent.totalProductSold = this.formatNumber(response.data?.totalProductSold ?? 0);
                    this.dataAgent.warehouses = response.data?.warehouses ?? [];
                    this.dataAgent.agentReceipts = response.data?.agentReceipts ?? [];
                    this.dataAgent.agentIssues = response.data?.agentIssues ?? [];
                    this.dataAgent.countsAgentIssueInStatus = response.data?.countsAgentIssueInStatus ?? [];
                    this.dataAgent.countsReceiptsInStatus = response.data?.countsReceiptsInStatus ?? [];
                    //agent
                    break;
                case '2':
                    const showroomResponse = await axios.post(route('overview-report.get-data-report-of-showroom'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });
                    this.dataShowroom.revenue = this.formatCurrencyVND(showroomResponse.data.revenue?.revenue ?? 0);
                    this.dataShowroom.revenueBankTransfer = this.formatCurrencyVND(showroomResponse.data.revenueBankTransfer?.revenue ?? 0);
                    this.dataShowroom.revenueCash = this.formatCurrencyVND(showroomResponse.data.revenueCash?.revenue ?? 0);
                    this.dataShowroom.taxAmount = this.formatCurrencyVND(showroomResponse.data.taxAmount?.revenue ?? 0);
                    this.dataShowroom.product = this.formatNumber(showroomResponse.data?.product ?? 0);
                    this.dataShowroom.customer = this.formatNumber(showroomResponse.data?.customer ?? 0);
                    this.dataShowroom.order = this.formatNumber(showroomResponse.data?.order ?? 0);
                    this.dataShowroom.getDataOrder = showroomResponse.data?.getDataOrder ?? [];
                    this.dataShowroom.warehouses = showroomResponse.data?.warehouses ?? [];
                    this.dataShowroom.warehouses = showroomResponse.data?.warehouses ?? [];
                    this.dataShowroom.showroomReceipts = showroomResponse.data?.showroomReceipts ?? [];
                    this.dataShowroom.showroomIssues = showroomResponse.data?.showroomIssues ?? [];
                    this.dataShowroom.countsIssueInStatus = showroomResponse.data?.countsIssueInStatus ?? [];
                    this.dataShowroom.countsReceiptsInStatus = showroomResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataShowroom.topSellingProducts = showroomResponse.data?.topSellingProducts ?? [];
                    this.dataShowroom.countProductIssue = this.formatNumber(showroomResponse.data?.countProductIssue ?? 0);
                    this.dataShowroom.countProductReceipt = this.formatNumber(showroomResponse.data?.countProductReceipt ?? 0);
                    this.dataShowroom.countProductSold = this.formatNumber(showroomResponse.data?.countProductSold ?? 0);
                    this.dataShowroom.countProductPendingSold = this.formatNumber(showroomResponse.data?.countProductPendingSold ?? 0);
                    this.dataShowroom.totalrFundedPointAmount = this.formatNumber(showroomResponse.data?.totalrFundedPointAmount?.fundedPointAmount ?? 0);
                    break;
                case '3':
                    const hubResponse = await axios.post(route('overview-report.get-data-report-of-hub'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : this.startDate,
                            endDate : this.endDate,
                        });
                    this.dataHub.warehouses = hubResponse.data?.warehouses ?? [];
                    this.dataHub.hubReceipts = hubResponse.data?.hubReceipts ?? [];
                    this.dataHub.hubIssues = hubResponse.data?.hubIssues ?? [];
                    this.dataHub.countsIssueInStatus = hubResponse.data?.countsIssueInStatus ?? [];
                    this.dataHub.countsReceiptsInStatus = hubResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataHub.countProductIssue = this.formatNumber(hubResponse.data?.countProductIssue ?? 0);
                    this.dataHub.countProductReceipt = this.formatNumber(hubResponse.data?.countProductReceipt ?? 0);
                    this.dataHub.product = this.formatNumber(hubResponse.data?.product ?? 0);
                    break;
            }
        }
    },
    async onchangeDate(event) {
        this.selectedDate = event;
        this.startDate = event[0];
        this.endDate = event[1];
        if(this.startDate > this.endDate){
            Botble.showNotice('warning', 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc','Cảnh báo!!!');
            this.startDate = this.endDate;
        }
        if(event){
            switch(this.selectOption1){
                case '1':
                    const response = await axios.post(route('overview-report.get-data-report-of-agent'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : event[0],
                            endDate : event[1],
                        });
                    this.dataAgent.revenue = this.formatCurrencyVND(response.data?.revenue ?? 0);
                    this.dataAgent.totalProduct = this.formatNumber(response.data?.totalProduct ?? 0);
                    this.dataAgent.totalProductSold = this.formatNumber(response.data?.totalProductSold ?? 0);
                    this.dataAgent.warehouses = response.data?.warehouses ?? [];
                    this.dataAgent.agentReceipts = response.data?.agentReceipts ?? [];
                    this.dataAgent.agentIssues = response.data?.agentIssues ?? [];
                    this.dataAgent.countsAgentIssueInStatus = response.data?.countsAgentIssueInStatus ?? [];
                    this.dataAgent.countsReceiptsInStatus = response.data?.countsReceiptsInStatus ?? [];
                    //agent
                    break;
                case '2':
                    const showroomResponse = await axios.post(route('overview-report.get-data-report-of-showroom'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : event[0],
                            endDate : event[1],
                        });
                    this.dataShowroom.revenue = this.formatCurrencyVND(showroomResponse.data.revenue?.revenue ?? 0);
                    this.dataShowroom.revenueBankTransfer = this.formatCurrencyVND(showroomResponse.data.revenueBankTransfer?.revenue ?? 0);
                    this.dataShowroom.revenueCash = this.formatCurrencyVND(showroomResponse.data.revenueCash?.revenue ?? 0);
                    this.dataShowroom.taxAmount = this.formatCurrencyVND(showroomResponse.data.taxAmount?.revenue ?? 0);
                    this.dataShowroom.product = this.formatNumber(showroomResponse.data?.product ?? 0);
                    this.dataShowroom.customer = this.formatNumber(showroomResponse.data?.customer ?? 0);
                    this.dataShowroom.order = this.formatNumber(showroomResponse.data?.order ?? 0);
                    this.dataShowroom.getDataOrder = showroomResponse.data?.getDataOrder ?? [];
                    this.dataShowroom.warehouses = showroomResponse.data?.warehouses ?? [];
                    this.dataShowroom.warehouses = showroomResponse.data?.warehouses ?? [];
                    this.dataShowroom.showroomReceipts = showroomResponse.data?.showroomReceipts ?? [];
                    this.dataShowroom.showroomIssues = showroomResponse.data?.showroomIssues ?? [];
                    this.dataShowroom.countsIssueInStatus = showroomResponse.data?.countsIssueInStatus ?? [];
                    this.dataShowroom.countsReceiptsInStatus = showroomResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataShowroom.topSellingProducts = showroomResponse.data?.topSellingProducts ?? [];
                    this.dataShowroom.countProductIssue = this.formatNumber(showroomResponse.data?.countProductIssue ?? 0);
                    this.dataShowroom.countProductReceipt = this.formatNumber(showroomResponse.data?.countProductReceipt ?? 0);
                    this.dataShowroom.countProductSold = this.formatNumber(showroomResponse.data?.countProductSold ?? 0);
                    this.dataShowroom.countProductPendingSold = this.formatNumber(showroomResponse.data?.countProductPendingSold ?? 0);
                    this.dataShowroom.totalrFundedPointAmount = this.formatNumber(showroomResponse.data?.totalrFundedPointAmount?.fundedPointAmount ?? 0);
                    break;
                case '3':
                    const hubResponse = await axios.post(route('overview-report.get-data-report-of-hub'),{
                            selectOption1 : this.selectOption1,
                            selectOption2 : this.selectOption2,
                            startDate : event[0],
                            endDate : event[1],
                        });
                    this.dataHub.warehouses = hubResponse.data?.warehouses ?? [];
                    this.dataHub.hubReceipts = hubResponse.data?.hubReceipts ?? [];
                    this.dataHub.hubIssues = hubResponse.data?.hubIssues ?? [];
                    this.dataHub.countsIssueInStatus = hubResponse.data?.countsIssueInStatus ?? [];
                    this.dataHub.countsReceiptsInStatus = hubResponse.data?.countsReceiptsInStatus ?? [];
                    this.dataHub.countProductIssue = this.formatNumber(hubResponse.data?.countProductIssue ?? 0);
                    this.dataHub.countProductReceipt = this.formatNumber(hubResponse.data?.countProductReceipt ?? 0);
                    this.dataHub.product = this.formatNumber(hubResponse.data?.product ?? 0);
                    break;
            }
     }
    },


    formatCurrencyVND(value) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
        }).format(value);
    },

    formatNumber(value, locale = 'en-US') {
        return new Intl.NumberFormat(locale, {
            style: 'decimal',
            maximumFractionDigits: 0,
        }).format(value);
    },


  },
  mounted() {
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());

    this.endDate = today.toISOString().split('T')[0];
    this.startDate = lastMonth.toISOString().split('T')[0];
  }
};
</script>
