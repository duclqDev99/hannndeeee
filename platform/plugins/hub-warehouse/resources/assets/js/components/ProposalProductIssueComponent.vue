<template>
    <div class="row mb-3" id="agent" v-show="isAgent">
        <div class="row">
            <div class="col-lg-6 col-12">
                <label for="control-label required">Đại lý:</label>
                <select v-model="selectedAgent" :onchange="onAgentChange" ref="agentSelect" data-quantity="0" name="agent"
                    class="select_hub form-control select-search-full">
                    <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                        {{ agent.name }}
                    </option>
                </select>
            </div>
            <div class="col-lg-6 col-12">
                <label for="control-label required">Chọn kho:</label>
                <select v-model="selectedAgentWarehouse" data-quantity="0" ref="selectedAgentWarehouse"
                    name="warehouseAgent" :onchange="onWarehouseAgentChange"
                    class="select_hub_warehouse form-control select-search-full">
                    <option v-if="agentWarehouses.length === 0" value="0">Không có lựa chọn</option>
                    <option v-for="warehouse in agentWarehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name
                    }}
                    </option>
                </select>
            </div>
        </div>
    </div>
    <div class="row mb-3" id="showroom" v-show="isShowroom">
        <div class="row">
            <div class="col-lg-6 col-12">
                <label for="control-label required">Showroom:</label>
                <select v-model="selectedShowroom" :onchange="onShowroomChange" ref="showroomSelect" data-quantity="0"
                    name="showroom" class="select_hub form-control select-search-full">
                    <option v-if="showrooms.length == 0" value="0">Không có showroom</option>
                    <option v-for="showroom in showrooms" :key="showroom.id" :value="showroom.id">
                        {{ showroom.name }}
                    </option>
                </select>
            </div>
            <div class="col-lg-6 col-12">
                <label for="control-label required">Chọn kho:</label>
                <select v-model="selectedShowroomWarehouse" data-quantity="0" ref="refShowroomWarehouse"
                    name="warehouseShowroom" :onchange="onWarehouseShowroomChange"
                    class="select_hub_warehouse form-control select-search-full">
                    <option v-if="showroomWarehouses.length === 0" value="0">Không có lựa chọn</option>
                    <option v-for="warehouse in showroomWarehouses" :key="warehouse.id" :value="warehouse.id">{{
                        warehouse.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>
    <div class="row mb-3" id="warehouse" v-show="isWarehouse">
        <div class="col-lg-6 col-12">
            <label>Kho thành phẩm:</label>
            <div class="ui-select-wrapper form-group">
                <select v-model="selectedWarehouseProduct" data-quantity="0" name="warehouse_product"
                    ref="selectedWarehouseProduct" :onchange="onWarehouseProduct" id="select_warehouse_product"
                    class="select_warehouse_product form-control select-search-full">
                    <option v-if="allWarehouse.length === 0" value="0">Chọn kho</option>
                    <option v-for="warehouse in allWarehouse" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="row mb-3" id="hub" v-show="isHub">
        <div class="row">
            <div class="col-lg-6 col-12">
                <label for="control-label required">Hub:</label>
                <select v-model="selectedHub" :onchange="onHubChange" ref="hubSelect" data-quantity="0" name="hub"
                    class="select_hub form-control select-search-full">
                    <option v-if="hubs.length == 0" value="0">Không có hub</option>
                    <option v-for="hub in hubs" :key="hub.id" :value="hub.id" :disabled="hub.disabled">
                        {{ hub.name }}
                    </option>
                </select>
            </div>
            <div class="col-lg-6 col-12">
                <label for="control-label required">Chọn kho:</label>
                <select v-model="selectedHubWarehouse" data-quantity="0" name="warehouseHub" ref="selectedHubWarehouse"
                    :onchange="onHubWarehouse" class="select_hub_warehouse form-control select-search-full">
                    <option v-if="warehouses.length === 0" value="0">Không có lựa chọn</option>
                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="row mb-3" id="hub-stock-other" v-show="isWarehouseHubOther">
        <div class="col-lg-6 col-12">
            <label>Kho khác:</label>
            <div class="ui-select-wrapper form-group">
                <select v-model="selectedWarehouseHubOther" data-quantity="0" ref="selectedWarehouseHubOther"
                    id="warehouse_out" name="warehouse_out" :onchange="onWarehouseOther"
                    class="select_warehouse_out form-control select-search-full">
                    <option v-if="warehouseOther.length <= 1" selected value="0">Không có lựa chọn</option>
                    <option v-for="warehouse in warehouseOther" :key="warehouse.id" :value="warehouse.id"
                        :disabled="warehouse.disabled">{{ warehouse.name }}</option>
                </select>
            </div>
        </div>
    </div>

     <div class="row mb-3" id="agent" v-show="isTour">
    </div>

    <div class="row mb-3" id="sale" v-show="isSale">
        <div class="col-lg-6 col-12">
            <label>Chính sách bán hàng:</label>
            <div class="ui-select-wrapper form-group">
                <select v-model="selectedPolicy" data-quantity="0" ref="policyRef"  :onchange="changePolicy"
                id="policy_sale" name="policy_sale"
                class="policy_sale form-control select-search-full">
                <option v-if="policy.length <= 0" selected value="0">Không có chính sách</option>
                <option v-for="policies in policy" :key="policies.id" :disabled="policies.status.value == 'inactive'  || (policies.end_date && new Date(policies.end_date) <= new Date())" :value="policies.id"
                >
                    {{ policies.name }}
                </option>
            </select>
            </div>
        </div>
        <div>
            <div  v-if="selectedPolicy !== '0' && policiesChoose">
                <h4 >Thông tin chính sách giảm giá</h4>
                <div>
                    <div class="row">
                        <div class="col-lg-6 col-12 col-md-6">
                            <p><strong>Tên chính sách:</strong> {{ policiesChoose?.name }}</p>
                            <p><strong>Ngày bắt đầu:</strong> {{ policiesChoose?.start_date }}</p>
                            <p><strong>Số lượng:</strong> {{ policiesChoose?.quantity > 0 ?  policiesChoose?.quantity: 'Không giới hạn'}}</p>
                            <p><strong>Giảm: </strong>{{ policiesChoose.value }} {{ policiesChoose?.type_option  == 'amount' ? 'VNĐ' : '%'}}</p>
                        </div>
                        <div class="col-lg-6 col-12">
                            <p><strong>Mã chính sách:</strong> {{ policiesChoose?.code }}</p>
                            <p><strong>Ngày kết thúc:</strong> {{ policiesChoose?.end_date ?? 'Không giới hạn' }}</p>

                            <p>
                                Sản phẩm có <strong>{{ policiesChoose?.type_time == 'date_production' ? ' ngày sản xuất' : ' ngày nhập kho' }} </strong> sau
                                <strong>
                            {{ policiesChoose?.time_active }}
                            {{ policiesChoose?.type_date_active == 'date' ? ' ngày' : (policiesChoose?.type_date_active == 'month' ? 'tháng' : 'năm') }}</strong>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="items" :value="JSON.stringify(items)" />
    <input type="hidden" name="itemOdd" :value="JSON.stringify(filteredItemOdd)" />
    <!-- <div class="text-right">
            <button v-if="!preConfirmed" type="button" class="btn btn-primary" @click="confirm">Xuất lô</button>
            <button v-else type="button" class="btn btn-primary" @click="confirm">Xuất lẻ</button>
        </div> -->
    <div class="ui vertical segment">
        <div class="flexbox" v-if="!preConfirmed">

            <div class="flex-content">
                <div class="button-group" style="margin-bottom: 5px">
                    <!-- <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="items.length > 0"  >
                        Xóa tất cả
                    </button> -->
                </div>
                <div>
                    <multi-select :products="filterDataList" :items="items"  :batch="true"  :placeholder="`Chọn lô xuất`"  @update:batchs="handleUpdatedBatch">
                    </multi-select>
                </div>
            </div>
            <div class="flex-result" v-if="!preConfirmed">
                <table class="ui celled table"  v-if="!preConfirmed">
                    <thead>
                        <tr>
                            <th class="text-center">Tên sản phẩm</th>
                            <th class="text-center">Hình ảnh</th>
                            <th class="text-center">Mã sản phẩm</th>
                            <th class="text-center">Mã lô</th>
                            <th class="text-center">Số lượng sản phẩm</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td  class="align-middle text-center">{{ item.name }}
                            </td>
                            <td class="align-middle text-center">
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name" class="image-view">
                                <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"  class="image-view">
                            </td>
                            <td class="align-middle text-center">{{ item.sku }}
                                <input type="hidden" name="sku" :value="item.sku">
                            </td>
                            <td class="align-middle text-center">{{ item.batch_code }}
                            </td>
                            <td class="align-middle text-center">{{ item.quantity }} sản phẩm
                            </td>

                            <td class="text-center">
                                <button @click="removeItem(item)" class="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flexbox"  v-else>

            <div class="flex-content">
                <div class="button-group" style="margin-bottom: 5px">
                    <!-- <button type="button" @click="resetItemOdd()" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="itemOdd.length > 0">
                        Xóa tất cả
                    </button> -->
                </div>
                <div>
                    <multi-select :products="filterDataListOdd" :items="filteredItemOdd"   :placeholder="placeholderText" :parent="true" @update:products="handleUpdatedProducts"
                    @search:products="searchProducts"
                    ></multi-select>
                </div>
            </div>
            <div class="flex-result">
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th class="text-center">Tên sản phẩm</th>
                            <th class="text-center">Hình ảnh</th>
                            <th class="text-center">Mã sản phẩm</th>
                            <th class="text-center">Số lượng trong kho</th>
                            <th class="text-center">Số lượng xuất</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredItemOdd" :key="item.id">
                            <td  class="align-middle text-center">{{ item.name }}</td>
                            <td class="align-middle text-center">
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name" class="image-view">
                                <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"  class="image-view">
                            </td>
                            <td class="align-middle text-center">
                                {{ item.sku }}
                            </td>
                            <td class="align-middle text-center">{{ item.quantityStock }} sản phẩm </td>
                            <td class="align-middle text-center">
                                <input :name="`listProduct[${item.id}][quantity]`" type="number" class="form-control text-center" :max="item.quantityStock" placeholder="Nhập số lượng" min=1 v-model="item.quantity" @blur="checkQuantity(item, $event)" />
                                <input :name="`listProduct[${item.id}][quantityStock]`" :value="item.quantityStock" hidden />
                            </td>
                            <td class="text-center">
                                <button @click="removeItemOdd(item)" class="btn btn-danger">Xóa</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import { MultiListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";
import MultiSelect from '../../../../../shared-module/resources/assets/components/proposal/MutilSelect.vue';
export default {
    props: {
        list: {
            type: Object,
            default: () => ({}),
        },
    },

    data() {
        return {

            isShowroom: false,
            isWarehouseHubOther: false,
            isHub: false,
            isWarehouse: false,
            isTour: false,
            isAgent: true,
            isSale: false,
            agents: [],
            showrooms: this.list.showroom,
            hubs: [],
            selectedAgent: 0,
            selectedAgentWarehouse: 0,
            selectedShowroom: 0,
            selectedShowroomWarehouse: 0,
            showroomWarehouses: [],
            selectedHub: 0,
            agentWarehouses: [],
            warehouses: [],
            selectedWarehouse: 0,
            warehouseIssue: 0,
            hubIssue: 0,
            warehouseOther: [],
            selectedWarehouseHubOther: 0,
            allWarehouse: [],
            selectedWarehouseProduct: 0,
            selectedHubWarehouse: 0,
            radioInputs: {},
            dataList: [],
            radio: 0,
            items: [],
            itemOdd: [],
            dataListOdd: [],
            totalAmount: 0,
            caseStates: {
                // Đại lý
                '0': { isWarehouseHubOther: false, isHub: false, isWarehouse: false, isAgent: true, isShowroom: false, isTour: false, isSale: false },
                // Hub khác
                '1': { isWarehouseHubOther: false, isHub: true, isWarehouse: false, isAgent: false, isShowroom: false, isTour: false, isSale: false },
                // kho khác trong hub
                '2': { isWarehouseHubOther: true, isHub: false, isWarehouse: false, isAgent: false, isShowroom: false, isTour: false, isSale: false },
                // Kho khác thành phẩm
                '3': { isWarehouseHubOther: false, isHub: false, isWarehouse: true, isAgent: false, isShowroom: false, isTour: false, isSale: false },
                // Showroom
                '4': { isWarehouseHubOther: false, isHub: false, isWarehouse: false, isAgent: false, isShowroom: true, isTour: false, isSale: false },
                // Kho sale
                '5': { isWarehouseHubOther: false, isHub: false, isWarehouse: false, isAgent: false, isShowroom: false, isTour: true, isSale: true },
                // Xuất đi giải
                '6': { isWarehouseHubOther: false, isHub: false, isWarehouse: false, isAgent: false, isShowroom: false, isTour: true, isSale: false },
            },
            allWarehouseHub: {},
            allAgentWarehouse: {},
            allShowroomWarehouse: this.list.showroomWarehouse,
            preConfirmed: true,
            keySearch: "",
            policy: this.list.policy,
            selectedPolicy: this.list?.policy[0]?.id ?? 0,
            policiesChoose: this.list?.policy[0]
        };
    },

    async mounted() {
        this.radioInputs.nodes = await document.querySelectorAll('input[name="is_warehouse"]');
        await this.disableRadio()
        await $("form button[type='submit']").prop('disabled', true);
        if (this.list) {
            // set agent
            this.agents = this.list.agent;
            this.selectedAgent = this.list.agent[0].id
            this.allAgentWarehouse = this.list.agentWarehouse
            this.fetchWarehouseByAgent()
            // set showroom

            this.selectedShowroom = this.list.showroom[0].id
            this.fetchWarehouseByShowroom()

            this.allWarehouseHub = this.list.hubWarehouse
            this.selectedWarehouseProduct = this.list.hubWarehouse[0].id;
            this.hubs = this.list.hub
            this.selectedHub = this.hubs.length > 0 ? this.hubs[0].id : 0;
            this.fetchWarehouseByHub()


        }
        $("form button[type='submit']").prop('disabled', false);
        this.nonDisableRadio()
        const proposal_id = $('#proposal_id').val();
        if (proposal_id) {
            this.getProductInFormProposal(proposal_id);
        }
        else {
            await this.changeHubWarehouse()
        }



        this.radioInputs.nodes.forEach(radio => {
            radio.addEventListener('change', this.handleRadioChange);
        });

        $('#hub_id').change(this.changeHubWarehouse);

        $('#warehouse_issue_id').change(this.changeWarehouseReceipt);
        const form = $("form button[type='submit']");
        form.click(this.submitForm);
        toastr.options = {
            closeButton: true,
            positionClass: 'toast-bottom-right',
            showDuration: 1000,
            hideDuration: 1000,
            timeOut: 10000,
            extendedTimeOut: 1000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        }
    },
    methods: {
        handleUpdatedBatch(updatedProducts) {
            this.items.push({ ...updatedProducts });
            let dataList = this.dataList.filter(item => item.id !== updatedProducts.id);
            this.dataList = dataList
            this.dataListOdd = this.dataListOdd.map((item) => {
                updatedProducts.list_product.forEach((list_product) => {
                    if (list_product.product_id === item.id) {
                        item.quantityStock -= 1;
                    }
                });
                return item;
            });
            this.itemOdd = this.itemOdd.filter((item) => {
                updatedProducts.list_product.forEach((list_product) => {
                    if (list_product.product_id === item.id) {
                        item.quantityStock -= 1;
                    }
                });
                return item;
            });
        },
        handleUpdatedProducts(updatedProducts) {
            this.itemOdd.push({ ...updatedProducts });
            this.dataListOdd.forEach((item, index) => {
                if (item.id === updatedProducts.id) {
                    this.dataListOdd.splice(index, 1);
                }
                return item;
            });
        },
        confirm() {
            if (this.preConfirmed) {
                this.preConfirmed = false;

            } else {
                this.preConfirmed = true;
            }
        },
        removeItem(itemToRemove) {
            this.items = this.items.filter(item => item.id !== itemToRemove.id);
            this.dataList.push(itemToRemove);
            this.dataListOdd = this.dataListOdd.map((item) => {
                itemToRemove.list_product.forEach((list_product) => {
                    if (list_product.product_id === item.product_id) {
                        item.quantityStock += 1;
                    }
                });
                return item;
            });
            this.itemOdd = this.itemOdd.map((item) => {
                itemToRemove.list_product.forEach((list_product) => {
                    if (list_product.product_id === item.product_id) {
                        item.quantityStock += 1;
                    }
                });
                return item;
            });
        },
        removeItemOdd(itemToRemove) {
            this.itemOdd = this.itemOdd.filter(item => item.id !== itemToRemove.id);
            this.dataListOdd.push(itemToRemove);

        },
        disableRadio() {
            this.radioInputs.nodes.forEach(radio => {
                radio.disabled = true;
            });
        },
        nonDisableRadio() {
            this.radioInputs.nodes.forEach(radio => {
                radio.disabled = false;
            });
        },
        async fetchAllWarehouseHub() {
            try {
                const res = await axios.get(route('hub-stock.getAllWarehouse'));
                this.allWarehouseHub = res.data.data;
            } catch (error) {
                console.error('Error:', error);
            }
        },
        alertWarning(message) {
            toastr.clear();
            toastr['warning'](message);
        },
        validateSku() {
            for (const item of this.itemOdd) {
                if (!item.sku) {
                    return false; // SKU is null, validation fails
                }
            }
            return true;
        },
        submitForm(e) {
            e.preventDefault();
            const form = document.getElementById('botble-hub-warehouse-forms-proposal-hub-issue-form');
            let isValid = true;
            const selectedDate = $('#expected_date').val();
            const currentDate = new Date();
            const selectedDateParts = selectedDate.split('-');
            const selectedDateObject = new Date(
                parseInt(selectedDateParts[0]),
                parseInt(selectedDateParts[1]) - 1,
                parseInt(selectedDateParts[2]),
            );
            this.itemOdd = this.itemOdd.filter(item => item.quantityStock != 0);
            const yesterday = new Date(currentDate);
            yesterday.setDate(currentDate.getDate() - 1);

            if (selectedDateObject <= yesterday) {
                isValid = false;
                this.alertWarning('Ngày phải dự kiến là ngày hiện tại hoặc sau ngày hiện tại');
                return;
            }
            if (selectedDate == '') {
                isValid = false;
                this.alertWarning('Cần nhập ngày dự kiến xuất');
                return;
            }
            if (this.hubIssue == 0 || isNaN(this.hubIssue)) {
                isValid = false;
                this.alertWarning('Phải chọn hub xuất thành phẩm');
                return;
            }
            if (this.warehouseIssue == 0 || isNaN(this.warehouseIssue)) {
                isValid = false;
                this.alertWarning('Phải chọn kho xuất thành phẩm');
                return;
            }
            if ($('#title').val() == '') {
                isValid = false;
                this.alertWarning('Phải nhập mục đích sử dụng cho đơn đề xuất');
                return;
            }
            if (this.items.length === 0 && this.itemOdd.length === 0) {
                isValid = false;
                this.alertWarning('Cần chọn sản phẩm xuất');
                return;
            }
            if (this.radio == 0) {
                if (this.selectedAgentWarehouse == 0 || this.selectedAgentWarehouse == 0) {
                    isValid = false;
                    this.alertWarning('Phải chọn đại lý');
                    return;

                }
            }
            if (this.radio == 4) {
                if (this.selectedShowroom == 0 || this.selectedShowroomWarehouse == 0) {
                    isValid = false;
                    this.alertWarning('Phải chọn showroom');
                    return;
                }
            }
            if (this.radio == 5) {
                if (this.selectedPolicy == 0 || this.selectedPolicy == '' || !this.selectedPolicy) {

                    isValid = false;
                    this.alertWarning('Phải chọn chính sách');
                    return;

                }
            }
            if (this.radio == 1) {
                if (this.selectedHubWarehouse == 0 || this.selectedHub == 0) {
                    isValid = false;
                    this.alertWarning('Phải chọn kho HUB khác ');
                    return;

                }
            }
            if (this.radio == 2) {
                if (this.selectedWarehouseHubOther == 0) {
                    isValid = false;
                    this.alertWarning('Phải chọn kho khác');
                    return;

                }
            }
            if (this.radio == 3) {
                if (this.selectedWarehouseProduct == 0) {
                    isValid = false;
                    this.alertWarning('Phải chọn kho thành phẩm');
                    return;
                }
            }
            if (this.validateSku() == false) {
                isValid = false;
                this.alertWarning('Mã SKU không được để trống');
                return;
            }
            for (const item of this.itemOdd) {
                if (!this.validateQuantity(item)) {
                    isValid = false;
                    return;
                }
            }
            if (isValid) {
                $("form button[type='submit']").prop('disabled', true);
                form.submit();
            }
        },
        validateQuantity(item) {
            let quantity = parseInt(item.quantity);
            const quantityStock = parseInt(item.quantityStock);

            if (isNaN(quantity)) {
                this.alertWarning('Số lượng không được để trống');
                return false;
            }

            if (quantity < 1) {
                this.alertWarning('Số lượng phải lớn hơn 0');
                return false;
            }

            if (quantity > quantityStock) {
                quantity = quantityStock
            }

            return true;
        },
        onWarehouseAgentChange(event) {
            this.selectedAgentWarehouse = event.target.value;
        },
        onWarehouseProduct(event) {
            this.selectedWarehouseProduct = event.target.value;
        },
        onWarehouseShowroomChange(event) {
            this.selectedShowroomWarehouse = event.target.value;
        },
        onWarehouseOther(event) {
            this.selectedWarehouseHubOther = event.target.value;
        },
        onHubWarehouse(event) {
            this.selectedHubWarehouse = event.target.value;
        },
        async getProductInFormProposal(idProposal) {
            $("form button[type='submit']").prop('disabled', true);
            this.disableRadio();
            try {
                const res = await axios.get(route('proposal-hub-issue.proposal', { proposal_id: idProposal }));
                const data = res.data;
                const idWarehouse = data.proposal.warehouse_issue_id;
                this.hubIssue = data.hubIssue
                this.warehouseIssue = idWarehouse
                $('#hub_id').val(this.hubIssue).trigger('change');
                await this.changeHubWarehouse(idWarehouse);
                this.handleRadioChange(event, data.proposal.is_warehouse);
                this.getProductByWarehouse(idWarehouse, data.data);

                if (data.proposal.is_warehouse == 0) {
                    this.selectedAgent = data.warehouseReceipt.agent_id;
                    this.$refs.agentSelect.value = data.warehouseReceipt.agent_id;
                    await $(this.$refs.agentSelect).trigger('change');
                    this.selectedAgentWarehouse = data.warehouseReceipt.id
                    this.$refs.selectedAgentWarehouse.value = this.selectedAgentWarehouse;
                    $(this.$refs.selectedAgentWarehouse).trigger('change');
                } else if (data.proposal.is_warehouse == 4) {
                    this.selectedShowroom = data.warehouseReceipt.showroom_id;
                    this.$refs.showroomSelect.value = data.warehouseReceipt.showroom_id;
                    await $(this.$refs.showroomSelect).trigger('change');
                    this.selectedShowroomWarehouse = data.warehouseReceipt.id
                    this.updateSelectRef(this.$refs.refShowroomWarehouse, this.selectedShowroomWarehouse);

                    // this.fetchWarehouseByShowroom(data.warehouseReceipt.id);
                } else if (data.proposal.is_warehouse == 1) {
                    this.selectedHub = data.warehouseReceipt.hub_id;
                    this.$refs.hubSelect.value = data.warehouseReceipt.hub_id;
                    await $(this.$refs.hubSelect).trigger('change');
                    this.selectedHubWarehouse = data.warehouseReceipt.id
                    this.$refs.selectedHubWarehouse.value = this.selectedHubWarehouse;
                    $(this.$refs.selectedHubWarehouse).trigger('change');

                    // this.fetchWarehouseByHub(data.warehouseReceipt.id);
                } else if (data.proposal.is_warehouse == 2) {
                    this.selectedWarehouseHubOther = data.proposal.warehouse_id;
                }
                else if (data.proposal.is_warehouse == 5) {
                    this.$refs.policyRef.value = data.proposal.policies_id;
                    $(this.$refs.policyRef).trigger('change');

                } else {
                    this.selectedWarehouseProduct = data.proposal.warehouse_id;
                    this.$refs.selectedWarehouseProduct.value = data.proposal.warehouse_id;
                    await $(this.$refs.selectedWarehouseProduct).trigger('change');
                }
            } catch (error) {
                console.error('Error fetching product data for proposal:', error);
            } finally {
                $("form button[type='submit']").prop('disabled', false);
                this.nonDisableRadio()
            }
        },

        changeWarehouseReceipt() {
            const idWarehouse = $('#warehouse_issue_id').val();
            this.warehouseOther.forEach(warehouse => {
                warehouse.disabled = warehouse.id === this.warehouseIssue;
            });
            this.warehouseIssue = idWarehouse
            this.items = [];
            this.dataList = [];
            this.dataListOdd = [];
            this.itemOdd = [];
            this.getProductByWarehouse(idWarehouse);
            if (this.warehouseIssue == this.selectedWarehouseHubOther) {
                const firstEnabledWarehouse = this.warehouseOther.find(warehouse => !warehouse.disabled);
                // Gán giá trị cho selectedWarehouseHubOther
                this.selectedWarehouseHubOther = firstEnabledWarehouse ? firstEnabledWarehouse.id : 0;
                this.updateSelectRef(this.$refs.selectedWarehouseHubOther, this.selectedWarehouseHubOther);
            }
        }
        ,
        updateSelectRef(ref, value) {
            ref.value = value;
            $(ref).trigger('change');
        },
        changeHubWarehouse(idWarehouse = null) {
            const hubId = parseFloat($('#hub_id').val());
            this.hubIssue = hubId
            this.hubs.forEach(hub => {
                hub.disabled = hub.id === hubId;
            });
            if (this.selectedHub == this.hubIssue) {
                const firstEnabledHub = this.hubs.find(hub => !hub.disabled);
                if (firstEnabledHub) {
                    this.selectedHub = firstEnabledHub.id;
                } else {
                    this.selectedHub = this.hubs.length > 0 ? this.hubs[0].id : 0;
                }
                this.$refs.hubSelect.value = this.selectedHub;
                $(this.$refs.hubSelect).trigger('change');
            }
            // const data = res.data
            const dropdown = $('#warehouse_issue_id');
            dropdown.empty()
            const data = this.allWarehouseHub
            if (data.length > 0) {
                $.each(data, function (index, hub) {
                    if (hubId == hub.hub_id)
                        dropdown.append('<option value="' + hub.id + '">' + hub.name + '</option>');
                });
                if (idWarehouse) {
                    dropdown.val(idWarehouse);
                } else {
                    dropdown.val(data[0].id);
                }
                dropdown.append('<option value="0" disabled ></option>');
                this.changeWarehouseReceipt();
            }

            this.warehouseOther = this.allWarehouseHub.map(item => {
                if (hubId === item.hub_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                        'disabled': item.id == this.warehouseIssue
                    };
                }
            }).filter(item => item !== undefined);
            // Lấy phần tử đầu tiên không bị disable
            const firstEnabledWarehouse = this.warehouseOther.find(warehouse => !warehouse.disabled);

            // Gán giá trị cho selectedWarehouseHubOther
            this.selectedWarehouseHubOther = firstEnabledWarehouse ? firstEnabledWarehouse.id : 0;
            if (this.warehouseOther.length == 0) {
                dropdown.append('<option value="0">Vui lòng chọn hub khác - hub không có kho</option>');
            }

        },

        checkQuantity(item, event) {
            let newValue = parseFloat(event.target.value);
            const min = 1;
            const max = parseFloat(item.quantityStock);
            if (newValue < min) {
                item.quantity = min;
            } else if (newValue > max) {
                item.quantity = max;
            } else {
                item.quantity = newValue;
            }

        },





        fetchWarehouseByHub(idWarehouse = null) {

            const data = this.allWarehouseHub.map(item => {
                if (this.selectedHub == item.hub_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                    };
                }
            }).filter(item => item !== undefined);
            this.warehouses = data;
            if (data.length > 0) {
                if (idWarehouse) {
                    this.selectedHubWarehouse = idWarehouse;

                }
                else if (data.length > 0) {
                    this.selectedHubWarehouse = data[0].id;
                }

            }


        },
        fetchWarehouseByAgent(idWarehouse = null) {

            const data = this.allAgentWarehouse.map(item => {
                if (this.selectedAgent == item.agent_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                    };
                }
            }).filter(item => item !== undefined);
            this.agentWarehouses = data;
            if (data.length > 0) {
                if (idWarehouse) {
                    this.selectedAgentWarehouse = idWarehouse

                }
                else {
                    this.selectedAgentWarehouse = data[0].id
                }
            }

        },
        onHubChange(event) {
            this.selectedHub = event.target.value;
            this.selectedHubWarehouse = 0;
            this.fetchWarehouseByHub();
        },
        onShowroomChange(e) {
            this.selectedShowroom = e.target.value;
            this.selectedShowroomWarehouse = 0;
            this.fetchWarehouseByShowroom();

        },
        onAgentChange(event) {
            this.selectedAgent = event.target.value;
            this.fetchWarehouseByAgent();
        },
        nameProduct(item) {
            return `${item.name}`;
        },

        reset() {
            this.items = [];
        },
        resetItemOdd() {
            this.itemOdd = [];
        },
        getValueFromVueComponent() {
            return this.items;
        },
        handleRadioChange(event, val = null) {

            let value;
            if (val == null) {
                value = event.target.value
            }
            else {
                value = val.toString()
            }
            this.radio = value;
            const newState = this.caseStates[value];
            this.isWarehouseHubOther = newState.isWarehouseHubOther;
            this.isHub = newState.isHub;
            this.isWarehouse = newState.isWarehouse;
            this.isAgent = newState.isAgent;
            this.isShowroom = newState.isShowroom;
            this.isTour = newState.isTour;
            this.isSale = newState.isSale;
        },

        async fetchWarehouseByShowroom() {
            const data = this.allShowroomWarehouse.map(item => {
                if (this.selectedShowroom == item.showroom_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                    };
                }
            }).filter(item => item !== undefined);
            this.showroomWarehouses = data;
            if (data.length > 0) {
                this.selectedShowroomWarehouse = data[0].id
            }

        },
        getProductByWarehouse(id, data = null, type = null) {
            axios.get(route('hub-warehouse.getProductByWarehouse', { id: id, keySearch: this.keySearch }),
                {
                    params: {
                        model: 'Botble\\HubWarehouse\\Models\\QuantityProductInStock',
                        type: type
                    }
                }).then(response => {
                    this.dataListOdd = [];
                    this.dataListOdd = response.data.dataDetail.map(item => {
                        let color;
                        let size;
                        if (item) {
                            item?.product?.product_attribute.map(key => {
                                if (key.attribute_set_id == 1) {
                                    color = key.title;
                                }
                                else {
                                    size = key.title
                                }
                            });
                            const name = `${item?.product?.name} - (Màu: ${color} - Size: ${size})`;
                            return {
                                ...item,
                                'name': name,
                                'image': item?.product?.parent_product[0].image,
                                'id': item?.product_id,
                                'sku': item?.product?.sku,
                                'quantityStock': item?.quantity,
                                'is_batch': 0,
                                'quantity': 1,
                                'parent_product': item?.product?.parent_product[0],
                            }
                        }
                    })
                    if (data != null) {
                        data.forEach(item => {

                            const foundItem = this.dataListOdd.find(data => data.product_id === item.product.id);
                            if (foundItem) {
                                foundItem.quantity = item.quantity;
                                this.itemOdd.push(foundItem);
                            }
                            else {
                                const dataInsert = {
                                    ...item,
                                    'sku': item.sku,
                                    'id': item.product_id,
                                    'name': `${item.product_name} - (Màu: ${item.color} - Size: ${item.size})`,
                                    'quantity': item.quantity,
                                    'quantityStock': item?.product_stock?.quantity ? item?.product_stock?.quantity : 0,
                                    'is_batch': 0,
                                    'image': item.product?.parent_product[0]?.image

                                }
                                this.itemOdd.push(dataInsert);
                            }

                        });
                        let found = true;
                        data.forEach(item => {
                            if (item.is_batch == 1) {
                                found = true;
                                return;
                            }
                        });
                        this.preConfirmed = found


                    }

                });
        },
        updateItemOdd() {
            this.itemOdd = this.itemOdd.map(item => {
                // Nếu quantity > quantityStock, gán quantity bằng quantityStock
                if (item.quantity > item.quantityStock) {
                    item.quantity = item.quantityStock;
                }
                return item; // Trả về item sau khi đã thay đổi
            }).filter(item => item.quantityStock > 0); // Lọc ra các phần tử có quantityStock > 0
        },
        searchProducts(keySearch) {
            this.keySearch = keySearch;
            this.getProductByWarehouse(this.warehouseIssue);
        },
        changePolicy(event) {
            this.policiesChoose = [];
            this.policiesChoose = (this.policy.filter(item => item.id == event.target.value)[0]);
            this.selectedPolicy = event.target.value;
        },
    },
    components: {
        MultiListSelect,
        MultiSelect

    },
    computed: {
        placeholderText() {
            return this.dataListOdd.length == 0 ? 'Đã hết sản phẩm trong kho' : 'Chọn sản phẩm xuất';
        },
        filteredItemOdd() {
            return this.itemOdd.filter(item => item.quantityStock !== 0);
        },
        filterDataList() {
            this.items.forEach(item => {
                this.dataList = this.dataList.filter(dataItem => dataItem.id !== item.id);
            });

            return this.dataList;
        },
        filterDataListOdd() {
            this.itemOdd.forEach(item => {
                this.dataListOdd = this.dataListOdd.filter(dataItem => dataItem.product_id !== item.product_id || dataItem.quantityStock <= 0);
            });
            return this.dataListOdd;
        }
    },
};
</script>
<style>
.image-view {
    height: 40px;
    width: 40px;
}
</style>
