<template>
    <div class="row mb-3" id="warehouse" v-show="isWarehouse">
        <div class="col-lg-6 col-12">
            <label>Kho thành phẩm:</label>
            <div class="ui-select-wrapper form-group">
                <select v-model="selectedWarehouseProduct" data-quantity="0" name="warehouse_product"
                    ref="selectedWarehouseProduct" id="select_warehouse_product"
                    class="select_warehouse_product form-control select-search-full" :onchange="handleSelectChange"
                    data-model="Botble\WarehouseFinishedProducts\Models\QuantityProductInStock">
                    <option v-if="allWarehouse.length == 0" value="0">Không có kho</option>
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

                    <option v-for="hub in hubs" :key="hub.id" :value="hub.id" :disabled="hub.disabled">
                        {{ hub.name }}
                    </option>
                </select>
            </div>
            <div class="col-lg-6 col-12">
                <label for="control-label required">Chọn kho:</label>
                <select v-model="selectedHubWarehouse" data-quantity="0" ref="selectedHubWarehouse" name="warehouseHub"
                    class="select_hub_warehouse form-control select-search-full"
                    data-model="Botble\HubWarehouse\Models\QuantityProductInStock" :onchange="handleSelectChange">
                    <option v-if="warehouses.length == 0" value="0">Không có lựa chọn</option>
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
                <select v-model="selectedWarehouseHubOther" data-quantity="0" id="warehouse_out"
                    ref="selectedWarehouseHubOther" name="warehouse_out" :onchange="handleSelectChange"
                    class="select_warehouse_out form-control select-search-full"
                    data-model="Botble\HubWarehouse\Models\QuantityProductInStock">
                    <option v-if="warehouseOther.length <= 1" value="0">Không có lựa chọn</option>
                    <option v-for="warehouse in warehouseOther" :key="warehouse.id" :value="warehouse.id"
                        :disabled="warehouse.disabled">{{ warehouse.name }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ui vertical segment">
        <div class="flexbox">
            <div class="flex-content">
                Chọn thành phẩm nhập kho:
            </div>
            <div class="flex-content">
                <!-- <div class="button-group" style="margin-bottom: 5px">
                    <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="caseStates[radio].items.length > 0">
                        Xóa tất cả
                    </button>
                </div> -->
                <div>
                    <!-- <multi-list-select
                        :list="dataList"
                        option-value="id"
                        option-text="name"
                        :custom-text="nameProduct"
                        :placeholder="placeholderText"
                        :selected-items="items"
                        @select="onSelect"
                        >


                    </multi-list-select>  -->
                    <multi-select :products="filteredProducts" @select="onSelect" :placeholder="placeholderText"
                        :items="caseStates[radio].items" :radio="radio"  @update:products="handleUpdatedProducts"
                        @search:products="searchProducts">
                    </multi-select>
                </div>
            </div>
            <div class="flex-result">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">Tên sản phẩm</th>
                            <th class="text-center">Hình ảnh</th>
                            <th class="text-center">Mã sản phẩm</th>
                            <th v-show="!inventory"  class="text-center">Số lượng sản phẩm trong kho</th>
                            <th v-show="inventory" class="text-center">Số lượng sản phẩm trong xuất</th>
                            <th class="text-center">Số lượng nhập</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in caseStates[radio].items" :key="item.id">
                            <td class="align-middle text-center">{{ item.name }}
                            </td>
                            <td class="align-middle text-center">
                                <img v-if="item.image" :src="'/storage/' + item.image" alt="Image" class="image-view">
                                <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"
                                    class="image-view">
                            </td>
                            <td class="align-middle text-center">{{ item.sku }}
                                <input type="hidden" name="sku" :value="item.sku">

                            </td>

                            <td class="align-middle text-center">{{ item.quantityStock }} sản phẩm</td>
                            <td class="text-center">
                                <input type="hidden" :value="item.is_batch" :name="`quantityBatch[${item.id}][is_batch]`">
                                <input :name="`quantityBatch[${item.id}][quantity]`" type="number"
                                    class="form-control text-center" :max="item.quantityStock" placeholder="Nhập số lượng"
                                    min=1 v-model="item.quantity" @blur="checkQuantity(item, $event)" />
                                <input :name="`quantityBatch[${item.id}][quantityStock]`" :value="item.quantityStock"
                                    hidden />
                            </td>
                            <td class="text-center">
                                <button @click="removeItem(item)" class="btn btn-danger">Xóa</button>
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
import MultiSelect from '../../../../../shared-module/resources/assets/components/proposal/MutilSelect.vue';
import "vue-search-select/dist/VueSearchSelect.css";
export default {
    props: {
        data: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            selectedValue: 'batch',
            inventory: true,
            isWarehouseHubOther: false,
            isHub: false,
            isWarehouse: true,
            hubs: [],
            selectedHub: 0,
            warehouses: [],
            selectedWarehouse: 0,
            warehouseOther: [],
            selectedWarehouseHubOther: 0,
            allWarehouse: [],
            selectedWarehouseProduct: 0,
            selectedHubWarehouse: 0,
            radioButton: null,
            dataList: [],
            items: [],
            totalAmount: 0,
            caseStates: {
                '0': { isWarehouseHubOther: false, isHub: false, isWarehouse: true, dataList: [], items: [], inventory: true },
                '1': { isWarehouseHubOther: false, isHub: true, isWarehouse: false, dataList: [], items: [], inventory: true },
                '2': { isWarehouseHubOther: true, isHub: false, isWarehouse: false, dataList: [], items: [], inventory: true },
                '3': { isWarehouseHubOther: false, isHub: false, isWarehouse: false, dataList: [], items: [], inventory: false },
            },
            radio: 0,
            warehouseReceipt: 0,
            hubReceipt: 0,
            allWarehouseHub: {},
            isAllWarehouseHubFetched: false,
            keySearch: "",
            radioButtons: [],
            proposal_id: ''
        };
    },
    async mounted() {
        this.proposal_id = $('#proposal_id').val();
        await $("form button[type='submit']").prop('disabled', true);
        await this.fetchWarehouse();
        await this.fetchAllWarehouseHub();
        await this.fetchHubs();
        if (this.proposal_id) {
            this.getProductInFormProposal();
        }
        else {
            this.changeHubWarehouse();
        }
        this.radioButtons = document.querySelectorAll('input[name="is_warehouse"]');
        this.radioButtons.forEach(radio => {
            radio.addEventListener('change', this.handleRadioChange);
        });

        $('#hub_id').change(this.changeHubWarehouse);
        $('#warehouse_receipt_id').change(this.changeWarehouseReceipt);
        $("form button[type='submit']").click(this.submitForm)
        $("form button[type='submit']").prop('disabled', false);
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
    watch: {

        radio(newValue) {
            this.updateStateFromNewValue(newValue);
        },
    },
    methods: {
        handleUpdatedProducts(updatedProducts) {
            console.log(updatedProducts);
            const newState = this.caseStates[this.radio]
            newState.items.push({ ...updatedProducts });
            newState.dataList.forEach((item, index) => {
                if (item.id === updatedProducts.id) {
                    newState.dataList.splice(index, 1);
                }
                return item;
            });
        },
        updateStateFromNewValue(value) {
            const val = parseFloat(value)
            const newState = this.caseStates[val];
            Object.keys(newState).forEach(key => {
                this[key] = newState[key];
            });
            if (newState.dataList.length == 0 && newState.items.length == 0) {
                switch (val) {
                    case 0:
                        this.handleWarehouseChange();
                        break;
                    case 1:
                        this.handleHubChange();
                        break;
                    case 2:
                        this.handleWarehouseHubOtherChange();
                        break;
                    case 3:

                        this.fetchAllProduct();
                        break;
                    // Xem xét thêm default case nếu cần
                }
            }


        },
        removeItem(itemToRemove) {
            const newState = this.caseStates[this.radio]
            newState.items = newState.items.filter(item => item.id !== itemToRemove.id);
            newState.dataList.push(Object.assign({}, itemToRemove));

        },
        updateSelectedDropdowns() {
            const modelMap = {
                '0': 'Botble\\WarehouseFinishedProducts\\Models\\QuantityProductInStock',
                '1': 'Botble\\HubWarehouse\\Models\\QuantityProductInStock',
                '2': 'Botble\\HubWarehouse\\Models\\QuantityProductInStock'
            };
            const selectedIdMap = {
                '0': this.selectedWarehouseProduct,
                '1': this.selectedHubWarehouse,
                '2': this.selectedWarehouseHubOther
            };
            const model = modelMap[this.radio];
            const idWarehouse = selectedIdMap[this.radio];
            if (idWarehouse > 0) {

                this.getProductByWarehouse(model, idWarehouse, null);
            }
        },
        alertWarning(message) {
            toastr.clear();
            toastr['warning'](message);
        },
        submitForm(e) {
            e.preventDefault();
            if (!this.validateForm()) return;

            $("form button[type='submit']").prop('disabled', true);
            const form = document.getElementById('botble-hub-warehouse-forms-proposal-hub-receipt-form');
            form.submit();

        },
        validateForm() {
            const selectedDate = $('#expected_date').val();
            const currentDate = new Date();
            const selectedDateParts = selectedDate.split('-');
            const selectedDateObject = new Date(
                parseInt(selectedDateParts[0]),
                parseInt(selectedDateParts[1]) - 1,
                parseInt(selectedDateParts[2]),
            );
            const yesterday = new Date(currentDate);
            yesterday.setDate(currentDate.getDate() - 1);
            const newState = this.caseStates[this.radio]

            if (selectedDateObject <= yesterday) {
                this.alertWarning('Ngày phải dự kiến là ngày hiện tại hoặc sau ngày hiện tại');
                return false;
            }
            if (selectedDate == '') {
                this.alertWarning('Vui lòng nhập ngày dự kiến');
                return false;
            }

            if ($('#title').val() == '') {
                this.alertWarning('Phải nhập mục đích nhập kho');
                return false;
            }

            switch (this.radio) {
                case '0':
                    if (this.selectedWarehouseProduct == 0) {
                        this.alertWarning('Phải chọn kho thành phẩm');
                        return false;
                    }
                    break;
                case '1':
                    if (this.selectedHubWarehouse == 0 || this.selectedHub == 0) {
                        this.alertWarning('Phải chọn kho HUB khác ');
                        return false;
                    }
                    break;
                case '2':
                    if (this.selectedWarehouseHubOther == 0) {
                        this.alertWarning('Phải chọn kho khác trong HUB');
                        return false;
                    }
                    break;
            }

            if (newState.items.length == 0) {
                this.alertWarning('Cần chọn sản phẩm nhập');
                return false;
            }

            if (!this.validateSku()) {
                this.alertWarning('Mã SKU không được để trống');
                return false;
            }
            for (const item of newState.items) {
                if (!this.validateQuantity(item)) {
                    isValid = false;
                    return;
                }
            }
            return true;
        },
        validateSku() {
            const newState = this.caseStates[this.radio]

            return newState.items.every(item => !!item.sku);
        },
        validateQuantity(item) {
            const quantity = parseInt(item.quantity);
            const quantityStock = parseInt(item.quantityStock);

            if (isNaN(quantity)) {
                this.alertWarning('Số lượng không được để trống');
                return false;
            }

            if (quantity < 1) {
                this.alertWarning('Số lượng phải lớn hơn 0');
                return false;
            }

            if (quantityStock && quantity > quantityStock && this.radio != 3) {
                this.alertWarning('Số lượng vượt quá số lượng trong kho');
                return false;
            }

            return true;
        },
        // async fetchData(data, newState) {
        //     const { is_warehouse, warehouse_type, warehouse_id } = data.proposal;
        //     this.handleRadioChange(null, is_warehouse, data.proposal.is_batch);

        //     if (is_warehouse == 3) {
        //         this.inventory = false;
        //         this.fetchAllProduct();
        //         newState.items = data.data.map(item => this.createItemFromData(item));
        //     } else {
        //         this.fetchData(data, newState, warehouse_type, warehouse_id);
        //     }
        // },
        handleWarehouseHubOtherChange() {
            this.warehouseOther = this.getWarehouseOtherOptions();
            const firstEnabledWarehouse = this.warehouseOther.find(warehouse => !warehouse.disabled);

            if (this.warehouseReceipt == this.selectedWarehouseHubOther) {
                this.selectedWarehouseHubOther = firstEnabledWarehouse ? firstEnabledWarehouse.id : 0;
                this.clearDataListAndItems();
                this.updateSelectRef(this.$refs.selectedWarehouseHubOther, this.selectedWarehouseHubOther);
            } else if (firstEnabledWarehouse > 0 && this.radio == 2) {
                this.getProductByWarehouse('Botble\\HubWarehouse\\Models\\QuantityProductInStock', firstEnabledWarehouse);
            }
        },
        handleRadioChange(event, val = null) {
            const value = val == null ? event.target.value : val.toString();
            this.radio = value;
        },
        getProductInFormProposal() {
            $("form button[type='submit']").prop('disabled', true);
            axios.get(route('proposal-hub-receipt.proposal', { proposal_id: this.proposal_id }))
                .then(res => {
                    const data = res.data
                    const idHubReceipt = data.idHubReceipt;
                    const idWarehouse = data.proposal.warehouse_receipt_id;
                    $('#hub_id').val(idHubReceipt).trigger('change.select2');
                    this.changeHubWarehouse(idWarehouse)
                    this.handleRadioChange(event, data.proposal.is_warehouse, data.proposal.is_batch)

                    const newState = this.caseStates[this.radio];
                    if (data.proposal.is_warehouse == 3) {
                        this.inventory = false;
                        this.fetchAllProduct(data.data);
                    }
                    else {
                        this.fetchData(data, newState)

                    }
                    $("form button[type='submit']").prop('disabled', false);

                })
        },

        async fetchData(data, newState) {

            if (data.proposal.is_warehouse == 0) {
                console.log(data);
                this.selectedWarehouseProduct = data.proposal.warehouse_id;
            } else if (data.proposal.is_warehouse == 1) {
                this.selectedHub = data.warehouseIssue.hub_id;
                this.fetchWarehouseByHub(data.warehouseIssue.id);
            } else if (data.proposal.is_warehouse == 2) {
                this.selectedWarehouseHubOther = data.proposal.warehouse_id;
            }

            await this.getProductByWarehouse(data.proposal.warehouse_type, data.proposal.warehouse_id, (dataList) => {
                newState.items = data.data.map(item => {
                    const name = item.size == '' ? `${item.product_name}` : `${item.product_name}  (Màu: ${item.color} - Size: ${item.size})`;
                    const is_batch = item.attribute == '' ? 1 : 0;
                    const correspondingDataItem = dataList.find(dataItem => dataItem.id == item.product_id);
                    return {
                        ...item,
                        'sku': item.sku,
                        'id': item.product_id,
                        'name': name,
                        'quantity': item.quantity,
                        'quantityStock': correspondingDataItem ? correspondingDataItem.quantityStock : 0,
                        'is_batch': is_batch
                    };
                });

                newState.selectedValue = data.proposal.is_batch == 1 ? 'batch' : 'odd'
            });
        },
        changeWarehouseReceipt() {
            const warehouseReceipt = parseFloat($('#warehouse_receipt_id').val());
            this.warehouseReceipt = warehouseReceipt;
            if (this.proposal_id == '') {
                this.fetchAllProduct()
            }
            else {
                this.proposal_id = ''
            }
            if (this.radio == 2) {
                this.warehouseOther.forEach(warehouse => {
                    warehouse.disabled = warehouse.id == warehouseReceipt;
                });

                // Kiểm tra xem có ít nhất một kho không bị disable không
                const atLeastOneEnabledWarehouse = this.warehouseOther.some(warehouse => !warehouse.disabled);
                if (atLeastOneEnabledWarehouse) {
                    // Nếu có ít nhất một kho không bị disable, thực hiện các hành động
                    const firstEnabledWarehouse = this.warehouseOther.find(warehouse => !warehouse.disabled);
                    if (this.warehouseReceipt == this.selectedWarehouseHubOther) {
                        // Nếu giá trị mới trùng với selectedWarehouseHubOther, chọn kho đầu tiên không bị disable
                        this.selectedWarehouseHubOther = firstEnabledWarehouse ? firstEnabledWarehouse.id : 0;
                        console.log(this.selectedWarehouseHubOther);
                        this.$refs.selectedWarehouseHubOther.value = this.selectedWarehouseHubOther;
                        $(this.$refs.selectedWarehouseHubOther).trigger('change');
                    }
                } else {

                    // Nếu tất cả các kho đều bị disable, thực hiện các hành động khác nếu cần
                    console.log('Vui lòng chọn kho khác - kho không có');
                }
            }
        }
        ,
        changeHubWarehouse(idWarehouse = null) {
            const hubId = parseFloat($('#hub_id').val());
            this.hubReceipt = hubId
            this.hubs.forEach(hub => {
                hub.disabled = hub.id == hubId;
            });
            if (this.selectedHub == this.hubReceipt && this.radio == 1) {
                const firstEnabledHub = this.hubs.find(hub => !hub.disabled);

                if (firstEnabledHub) {
                    // If a non-disabled hub is found, set selectedHub to that value
                    this.selectedHub = firstEnabledHub.id;
                } else {
                    // If no non-disabled hub is found, set selectedHub to the first hub (even if it's disabled)
                    this.selectedHub = this.hubs.length > 0 ? this.hubs[0].id : 0;
                }

                // Set the value of the <select> element and trigger the 'change' event
                this.$refs.hubSelect.value = this.selectedHub;
                $(this.$refs.hubSelect).trigger('change');
            }
            // const data = res.data
            const dropdown = $('#warehouse_receipt_id');
            dropdown.empty()
            const data = this.allWarehouseHub

            if (data.length > 0) {
                $.each(data, function (index, hub) {
                    if (hubId == hub.hub_id)
                        dropdown.append('<option value="' + hub.id + '">' + hub.name + '</option>');
                });
                if (idWarehouse) {
                    this.warehouseReceipt = idWarehouse;
                    dropdown.val(idWarehouse);
                } else {
                    this.warehouseReceipt = data[0].id;
                    dropdown.val(data[0].id);
                }
                dropdown.append('<option value="0" disabled ></option>');
                this.changeWarehouseReceipt();
            }
            else {
                dropdown.append('<option value="0">Vui lòng chọn hub khác - hub không có kho</option>');
            }
            this.warehouseOther = this.allWarehouseHub.map(item => {
                if (hubId == item.hub_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                        'disabled': item.id == this.warehouseReceipt
                    };
                }
            }).filter(item => item !== undefined);
            // Lấy phần tử đầu tiên không bị disable
            const firstEnabledWarehouse = this.warehouseOther.find(warehouse => !warehouse.disabled);

            // Gán giá trị cho selectedWarehouseHubOther
            this.selectedWarehouseHubOther = firstEnabledWarehouse ? firstEnabledWarehouse.id : 0;

        },

        checkQuantity(item, event) {
            let newValue = parseFloat(event.target.value);
            const min = 1;
            const max = parseFloat(item.quantityStock);
            if (this.radio != 3) {
                if (newValue < min) {
                    item.quantity = min;
                    this.alertWarning('Số lượng không thể âm')
                } else if (newValue > max) {
                    item.quantity = max;
                    this.alertWarning('Số lượng vượt quá sản phẩm trong kho')
                } else {
                    item.quantity = newValue;
                }
            }

        },
        handleSelectChange(event) {
            const model = event.target.getAttribute('data-model');
            const id = event.target.value;
            const selectedRadioButton = document.querySelector('input[name="is_warehouse"]:checked');
            const radioButtonValue = parseInt(selectedRadioButton.value, 10);

            switch (radioButtonValue) {
                case 0:
                    this.selectedWarehouseProduct = id;
                    break;
                case 1:
                    this.selectedHubWarehouse = id;
                    break;
                case 2:
                    this.selectedWarehouseHubOther = id;
                    break;
                default:
                    break;
            }
            const newState = this.caseStates[this.radio]

            newState.dataList = [];
            newState.items = []
            this.getProductByWarehouse(model, id, null);
        },
        fetchWarehouse() {
            $("form button[type='submit']").prop('disabled', true);

            axios.get(route('warehouse-finished-products.getAllWarehouse'))
                .then(response => {
                    this.allWarehouse = response.data.data;
                    this.selectedWarehouseProduct = this.allWarehouse.length > 0 ? this.allWarehouse[0].id : 0;
                    if (this.radio == 0) {
                        this.getProductByWarehouse('Botble\\WarehouseFinishedProducts\\Models\\QuantityProductInStock', this.allWarehouse[0].id, null, 'batch');
                    }
                    $("form button[type='submit']").prop('disabled', false);


                })
        },
        async fetchAllWarehouseHub() {
            try {
                const res = await axios.get(route('hub-stock.getAllWarehouse'));
                this.allWarehouseHub = res.data.data;
            } catch (error) {
                console.error('Error:', error);
            }
        },


        fetchWarehouseByHub(idWarehouse = null) {

            const data = this.allWarehouseHub.map(item => {
                if (this.selectedHub == item.hub_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                        'disabled': item.id == this.warehouseReceipt
                    };
                }
            }).filter(item => item !== undefined);
            this.warehouses = data;
            if (idWarehouse) {
                this.selectedHubWarehouse = idWarehouse;
            }
            else if (data.length > 0 && this.radio == 1) {
                this.selectedHubWarehouse = data[0].id;
            }

        },
        onHubChange(event) {
            this.selectedHub = event.target.value;
            this.fetchWarehouseByHub();
        },
        nameProduct(item) {
            return `${item.name}`;
        },
        onSelect(selectedItems) {

            const newState = this.caseStates[this.radio];

            const updatedItems = selectedItems.map(newItem => {
                const existingItem = newState.items.find(item => item.id === newItem.id);
                return {
                    ...newItem,
                    quantity: existingItem ? existingItem.quantity : 1
                };
            });
            newState.items = updatedItems; // Update newState accordingly
        },
        reset() {
            const newState = this.caseStates[this.radio];

            newState.items = [];
        },

        fetchAllProduct(data = null) {
            this.radioButtons.forEach(item => item.disabled = true);
            axios.get(route('finished-products.getAllListProduct', { id: this.warehouseReceipt, search: this.keySearch }))
                .then(response => {
                    const newState = this.caseStates[3]
                    newState.dataList = response.data.product.map((item) => {
                        let color;
                        let size;
                        item.product_attribute.map(key => {
                            if (key.attribute_set_id == 1) {
                                color = key.title;
                            }
                            else {
                                size = key.title
                            }
                        });
                        const name = `${item.name} - (Màu: ${color} - Size: ${size})`;
                        return {
                            'image': item?.parent_product[0]?.image ? item.parent_product[0].image : '',
                            'sku': item.sku,
                            'name': name,
                            'id': item.id,
                            'is_batch': 0,
                            'parent_product': item.parent_product[0],
                            'quantityStock': item.quantity_in_stock
                        }
                    })
                    if (data) {
                        data.map(item => {
                                const dataInsert = {
                                    ...item,
                                    'sku': item.sku,
                                    'id': item.product_id,
                                    'name': `${item.product_name} - (Màu: ${item.color} - Size: ${item.size})`,
                                    'quantity': item.quantity,
                                    'quantityStock': item.product_stock.quantity,
                                    'is_batch': 0,
                                    'image': item.product.parent_product[0].image
                                }
                                newState.items.push(dataInsert
                                );

                        })
                    }
                    this.radioButtons.forEach(item => item.disabled = false);

                })
                .catch(error => {
                    console.error('Error fetching warehouses:', error.message);
                });
        }
        ,
        updateStateFromNewState(newState) {
            Object.assign(this, {
                isWarehouseHubOther: newState.isWarehouseHubOther,
                isHub: newState.isHub,
                isWarehouse: newState.isWarehouse,
                inventory: newState.inventory,
                dataList: newState.dataList.length == 0 ? [] : newState.dataList,
                items: newState.dataList.length == 0 ? [] : newState.items,
            });
        },
        handleWarehouseChange() {
            this.getProductByWarehouse('Botble\\WarehouseFinishedProducts\\Models\\QuantityProductInStock', this.selectedWarehouseProduct);
        },

        handleHubChange() {
            const firstEnabledHub = this.getFirstEnabledHub();
            const newState = this.caseStates[this.radio]

            if (this.selectedHub == this.hubReceipt) {
                newState.items = [];
            }

            this.selectedHub = firstEnabledHub ? firstEnabledHub.id : this.getDefaultHubId();
            this.updateSelectRef(this.$refs.hubSelect, this.selectedHub);
        },
        getFirstEnabledHub() {
            return this.hubs.find(hub => !hub.disabled) || (this.hubs.length > 0 ? this.hubs[0] : null);
        },

        getDefaultHubId() {
            return this.hubs.length > 0 ? this.hubs[0].id : 0;
        },

        getWarehouseOtherOptions() {
            return this.allWarehouseHub.map(item => {
                if (this.allWarehouseHub.length > 1 && this.hubReceipt == item.hub_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                        'disabled': item.id == this.warehouseReceipt
                    };
                } else if (this.allWarehouseHub.length <= 1) {
                    return { 'id': 0, 'name': 'Hết kho trong hub' };
                }
            }).filter(item => item !== undefined);
        },

        clearDataListAndItems() {
            const newState = this.caseStates[this.radio]
            newState.dataList = [];
            newState.items = [];
        },

        updateSelectRef(ref, value) {
            ref.value = value;
            $(ref).trigger('change');
        }
        ,
        fetchHubs() {
            axios.get(route('hub-warehouse.getHub'))
                .then(response => {
                    this.hubs = response.data.map(item => {
                        return {
                            'id': item.id,
                            'name': item.name,
                            'disabled': item.id == this.hubReceipt,
                        }
                    });
                    const firstEnabledHub = this.hubs.find(hub => !hub.disabled);

                    // Nếu tìm thấy, gán giá trị đầu tiên không bị disabled
                    if (firstEnabledHub) {
                        this.selectedHub = firstEnabledHub.id;
                    } else {
                        // Nếu không tìm thấy giá trị nào khả dụng, gán giá trị đầu tiên bất kỳ
                        this.selectedHub = this.hubs.length > 0 ? this.hubs[0].id : 0;
                    }
                    this.fetchWarehouseByHub();
                })
                .catch(error => {
                    console.error('Error fetching hubs:', error);
                });

        },
        getProductByWarehouse(model, id, callback = null, type = null) {
            $("form button[type='submit']").prop('disabled', true);
            axios.get(route('hub-warehouse.getProductByWarehouse', { id: id, }),
                {
                    params: {
                        model: model,
                        type: type,
                        search: this.keySearch
                    }
                }).then(response => {
                    const newState = this.caseStates[this.radio]

                    newState.dataList = response.data.dataDetail.map(item => {
                        let color;
                        let size;
                        item.product.product_attribute.map(key => {
                            if (key.attribute_set_id == 1) {
                                color = key.title;
                            }
                            else {
                                size = key.title
                            }
                        });
                        var name;
                        var is_batch;
                        // if (type == 'odd' && model == 'Botble\\WarehouseFinishedProducts\\Models\\WarehouseFinishedProducts') {
                        name = `${item.product.name}  - (Màu: ${color} - Size: ${size})`;
                        is_batch = 0
                        // }
                        // else {
                        //     name = `${item.product.name}`
                        //     is_batch = 1
                        // }

                        return {
                            'name': name,
                            'sku': item.product.sku,
                            'id': item.product.id,
                            'quantityStock': item.quantity,
                            'is_batch': is_batch,
                            'image': item.product.images[0],
                            'quantity': 1
                        }
                    })

                    if (callback != null) {
                        callback(newState.dataList);
                    }
                    $("form button[type='submit']").prop('disabled', false);
                });
        },
        searchProducts(keySearch) {
            this.keySearch = keySearch;
            if (this.radio == 0) this.handleWarehouseChange();
            else if (this.radio == 1) this.handleHubChange();
            else if (this.radio == 2) this.handleWarehouseHubOtherChange();
            else if (this.radio == 3) this.fetchAllProduct();
        }

    },
    components: {
        MultiListSelect,
        MultiSelect
    },
    computed: {
        placeholderText() {
            const newState = this.caseStates[this.radio]

            return newState.dataList.length == 0
                ? '(Danh sách rỗng)'
                : 'Chọn thành phẩm nhập kho ';
        },
        currentState() {
            return this.caseStates[this.radio];
        },
        filteredProducts() {
            const newState = this.caseStates[this.radio]
            return newState.dataList.filter(product => {
                const isNotInItems = !newState.items.some(item => item.id === product.id);

                if (isNotInItems) {

                    return true;
                }
                return false;
            });
        },
    }
};
</script>
<style>
.custom-option {
    display: flex;
    align-items: center;
}

.option-image {
    width: 30px;
    /* Customize image size */
    height: 30px;
    margin-right: 10px;
    /* Adjust spacing between image and text */
}

.image-view {
    height: 40px;
    width: 40px;
}
</style>
