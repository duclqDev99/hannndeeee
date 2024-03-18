<template>
    <div class="ui vertical segment">
        <div class="flexbox">
            <div class="flex-content">
                Chọn thành phẩm trả hàng:
            </div>
            <div class="flex-content">
                <!-- <div class="button-group" style="margin-bottom: 5px">
                    <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="items.length > 0">
                        Xóa tất cả
                    </button>
                </div> -->
                <div>
                    <!-- <multi-list-select :list="dataList" option-value="id" option-text="name"
                        :placeholder="placeholderText" :selected-items="items"
                        @select="onSelect"></multi-list-select> -->
                    <mutil-select :products="filterDataList" @select="onSelect" :warehouse="true" :parent="true" :placeholder="placeholderText" :items="items" @update:products="handleUpdatedProducts"

                    @search:products="searchProducts"

                    >
                    </mutil-select>
                </div>
            </div>
            <div class="flex-result">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center"> Tên sản phẩm</th>
                            <th class="text-center"> Hình ảnh</th>
                            <th class="text-center">Mã sản phẩm</th>
                            <th class="text-center">Số lượng tồn kho</th>
                            <th class="text-center">Số lượng yêu cầu</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
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
                                <input :name="`product[${item.id}][quantity]`" type="number"
                                    class="form-control text-center" :max="item.quantityStock" placeholder="Nhập số lượng"
                                    min=1 v-model="item.quantity" @blur="checkQuantity(item, $event)" />
                                <input :name="`product[${item.id}][quantityStock]`" :value="item.quantityStock" hidden />
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
import "vue-search-select/dist/VueSearchSelect.css";
import MutilSelect from '../../../../../shared-module/resources/assets/components/proposal/MutilSelect.vue';

export default {
    props: {
        data: {
            type: Object,
            default: () => ({}),
        },
    },

    data() {
        return {
            dataList: [],
            items: [],
            warehouseIssue: 0,
            showRoomIssue: 0,
            allShowroomWarehouse: {},
            keySearch: "",
            form: null
        };
    },
    async mounted() {
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
        await this.fetchAllWarehouseShowroom();
        await this.changeAgentWarehouse()
        const submitButton = $("form button[type='submit']");
        submitButton.click(this.submitForm);
        $('#showroom_id').change(this.changeAgentWarehouse);
        $('#warehouse_issue_id').change(this.changeWarehouseIssue);
        const proposal_id = $('#proposal_id').val();
        if (proposal_id) {
            this.getProductInFormProposal(proposal_id);
        }
    },

    methods: {
        handleUpdatedProducts(updatedProducts) {
            this.items.push({ ...updatedProducts });
            this.dataList.forEach((item, index) => {
                if (item.id === updatedProducts.id) {
                    this.dataList.splice(index, 1);
                }
                return item;
            });
        },
        removeItem(itemToRemove) {
            this.items = this.items.filter(item => item.id !== itemToRemove.id);
            this.dataList.push(Object.assign({}, itemToRemove));

        },
        async fetchAllWarehouseShowroom() {
            try {
                $("form button[type='submit']").prop('disabled', true);
                const res = await axios.get(route('showroom-warehouse.get-all-warehouse-showroom'));
                this.allShowroomWarehouse = res.data.data;
            } catch (error) {
                console.error('Đã xảy ra lỗi:', error);
            } finally {
                $("form button[type='submit']").prop('disabled', false);
            }
        }
        ,
        async getProductInFormProposal(idProposal) {
            try {
                $("form button[type='submit']").prop('disabled', true);
                const res = await axios.get(route('showroom-proposal-issue.proposal', { id: idProposal }));
                const data = res.data;
                this.showRoomIssue = data.showroom;
                $('#showroom_id').val(data.showroom).trigger('change');
                await this.fetchWarehouseByAgent(data.warehouseIssue, data.data);
            } catch (error) {
                console.error("Error in getProductInFormProposal:", error);
            }
            finally {
                $("form button[type='submit']").prop('disabled', false);
            }
        },
        checkQuantity(item, event) {
            let newValue = parseFloat(event.target.value);
            const min = 1;
            const max = parseFloat(item.quantityStock);
            if (newValue < min) {
                item.quantity = min;
                this.alertWarning('Số lượng không thể âm')
            } else if (newValue > max) {
                item.quantity = item.quantityStock;
                this.alertWarning('Số lượng vượt quá trong kho')
            } else {
                item.quantity = newValue;
            }

        },
        changeWarehouseIssue(data = null) {
            $("form button[type='submit']").prop('disabled', true);
            this.dataList = []
            this.item = []
            const warehouseIssue = parseFloat($('#warehouse_issue_id').val());
            this.warehouseIssue = warehouseIssue;
            this.getProductShowroom(data)
        },
        getProductShowroom(data = null) {
            this.dataList = []
            $("form button[type='submit']").prop('disabled', true);
            axios.get(route('showroom.products.get-product-in-showroom', { id: this.warehouseIssue, keySearch: this.keySearch }))
                .then(res => {
                    this.dataList = res.data.data.map(item => {
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
                        const name = `${item.name}|- (Màu: ${color} - Size: ${size})`;
                        return {
                            'image': item.images[0] ? item.images[0] : '',
                            'name': name,
                            'quantity': 1,
                            'parent_product': item.parent_product[0],
                            'sku': item.sku,
                            'quantityStock': item.product_showroom[0].pivot.quantity_qrcode,
                            'is_batch': 0,
                            'id': item.id,
                        };
                    });
                    if (data) {
                        data.forEach(item => {
                            const dataInsert = {
                                'image': item.image ? item.image : '',
                                'name': `${item.name} - (Màu: ${item.color} - Size: ${item.size})`,
                                'parent_product': item.parent_product[0],
                                'sku': item.sku,
                                'quantityStock': item.quantityStock,
                                'is_batch': 0,
                                'id': item.id,
                                'quantity' : item.quantity
                            }
                            this.items.push(dataInsert);
                        });
                    }
                    $("form button[type='submit']").prop('disabled', false);

                })
        },
        changeAgentWarehouse() {
            this.warehouseIssue = 0;
            this.dataList = []
            this.items = []
            this.showRoomIssue = $('#showroom_id').val();
            this.fetchWarehouseByAgent();
        },
        async fetchWarehouseByAgent(idWarehouse = null, dataProduct = null) {
            const dropdown = $('#warehouse_issue_id');
            dropdown.empty();
            const data = this.allShowroomWarehouse.map(item => {
                if (this.showRoomIssue == item.showroom_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                    };
                }
            }).filter(item => item !== undefined);
            if (data.length > 0) {
                data.forEach(warehouse => {
                    dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
                });
                if (idWarehouse) {
                    dropdown.val(idWarehouse);
                    this.warehouseIssue = idWarehouse;
                } else {
                    dropdown.val(data[0].id);
                    this.warehouseIssue = data[0].id
                }
                await this.changeWarehouseIssue(dataProduct)
            } else {
                dropdown.append('<option value="0">Vui lòng chọn showroom khác - showroom không có kho</option>');
            }




        },

        updateDropdown(res, idWarehouse, dataProduct) {
            const dropdown = $('#warehouse_issue_id');
            const data = res.data
            dropdown.empty();
            if (data.length > 0) {
                dropdown.append('<option value="0" selected>Chọn kho</option>');
            } else {
                dropdown.append('<option value="0">Vui lòng chọn Showroom khác - Showroom không có kho</option>');
            }

            data.forEach(agentWarehouse => {
                dropdown.append('<option value="' + agentWarehouse.id + '">' + agentWarehouse.name + '</option>');
            });
            if (idWarehouse) {
                dropdown.val(idWarehouse);
                this.warehouseIssue = idWarehouse;
            } else {
                dropdown.val(0);
            }
            dropdown.trigger('change');
            if (dataProduct) {
                this.items = dataProduct.map(item => ({
                    ...item,
                    id: item.id,
                    quantityStock: item.quantityStock,
                    name: ` ${item.name}| ${item.sku} - (Màu: ${item.color} - Size:  ${item.size})	`
                }));
            }


        },
        submitForm(e) {
            e.preventDefault();
            const form = document.getElementById('botble-showroom-forms-showroom-proposal-issue-form');
            let isValid = true;
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

            if (selectedDateObject <= yesterday) {
                isValid = false;
                this.alertWarning('Ngày phải dự kiến là ngày hiện tại hoặc sau ngày hiện tại');
                return;
            }
            if (this.showRoomIssue == 0) {
                isValid = false;
                this.alertWarning('Phải chọn showroom ');
                return;
            }
            if (this.warehouseIssue == 0) {
                isValid = false;
                this.alertWarning('Phải chọn kho trả hàng ');
                return;
            }
            if ($('#title').val() == '') {
                isValid = false;
                this.alertWarning('Phải nhập lý do trả hàng');
                return;
            }
            if (this.items.length === 0) {
                isValid = false;
                this.alertWarning('Cần chọn sản phẩm nhập');
                return;
            }
            this.items.forEach(item => {
                if (item.quantity > item.quantityStock) {
                    isValid = false;
                    this.alertWarning('Số lượng không được lớn hơn số lượng tồn kho');
                }
                if (item.quantity <= 0 || isNaN(item.quantity)) {
                    isValid = false;
                    this.alertWarning('Vui lòng nhập số lượng');
                    return;
                }
            });
            if (isValid) {
                $("form button[type='submit']").prop('disabled', true);
                form.submit();
            }
        },
        onSelect(selectedItems) {
            this.items = selectedItems.map((newItem) => {
                const existingItem = this.items.find((item) => item.id === newItem.id);
                if (existingItem) {
                    return { ...newItem, quantity: existingItem.quantity };
                } else {
                    return { ...newItem, quantity: 1 };
                }
            });
        },
        alertWarning(message) {
            toastr.clear();
            toastr['warning'](message);
        },
        searchProducts(keySearch) {
            this.keySearch = keySearch;
            this.getProductShowroom();
        },
    },
    computed: {
        placeholderText() {
            return this.dataList.length === 0
                ? ' (Danh sách rỗng)'
                : 'Chọn thành phẩm yêu cầu trả';
        },
        filterDataList() {
            this.items.forEach(item => {
                this.dataList = this.dataList.filter(dataItem => dataItem.id !== item.id);
            });

            return this.dataList;
        },
    },
    components: {
        MultiListSelect,
        MutilSelect
    },
}
</script>
<style>
.image-view {
    height: 40px;
    width: 40px;
}
</style>
