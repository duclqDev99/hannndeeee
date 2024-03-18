<template>
    <div class="ui vertical segment">
        <!-- <div class="text-right">
            <button v-if="!preConfirmed" type="button" class="btn btn-primary" @click="confirm">Chọn lô</button>
            <button v-else type="button" class="btn btn-primary" @click="confirm">Chọn lẻ</button>
        </div> -->
        <input type="hidden" name="items" :value="JSON.stringify(items)" />
    <input type="hidden" name="itemOdd" :value="JSON.stringify(filteredItemOdd)" />
        <div class="flexbox" v-if="!preConfirmed">
            <div class="flex-content">
                Chọn lô nhập kho:
            </div>
            <div class="flex-content">
                <div class="button-group" style="margin-bottom: 5px">
                    <!-- <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="items.length > 0">
                        Xóa tất cả
                    </button> -->
                </div>
                <div>
                    <multi-select :products="filterDataList" :items="items"  :batch="true"  :placeholder="`Chọn lô nhập`"  @update:batchs="handleUpdatedBatch"

                    >
                    </multi-select>
                </div>
            </div>
            <div class="flex-result">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Tên sản phẩm</th>
                            <th class="text-center">Hình ảnh</th>
                            <th class="text-center">Mã sản phẩm</th>
                            <th class="text-center">Mã lô</th>
                            <th class="text-center">Số lượng sản phẩm trong lô</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td class="align-middle text-center">{{ item.name }}</td>
                            <td class="align-middle text-center">
                                <img v-if="item.image" :src="'/storage/' + item.image" alt="Image" class="image-view">
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
                Chọn thành phẩm lẻ:
            </div>
            <div class="flex-content">
                <div class="button-group" style="margin-bottom: 5px">
                    <!-- <button type="button" @click="resetItemOdd()" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="itemOdd.length > 0">
                        Xóa tất cả
                    </button> -->
                </div>
                <div>
                    <multi-select :products="filterDataListOdd" :items="filteredItemOdd"   :placeholder="`Chọn thành phẩm lẻ`" :parent="true" @update:products="handleUpdatedProducts"
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
                            <th class="text-center">Số lượng sản phẩm còn lại trong HUB</th>
                            <th class="text-center">Số lượng nhập</th>
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
                            <td class="align-middle text-center">{{ item.sku }}
                                <input type="hidden" name="sku" :value="item.sku">
                            </td>
                            <td class="align-middle text-center">{{ item.quantityStock }} sản phẩm </td>
                            <td class="align-middle text-center">
                                <input :name="`quantityBatch[${item.id}][quantity]`" type="number" class="form-control text-center" :max="item.quantityStock" placeholder="Nhập số lượng" min=1 v-model="item.quantity" @blur="checkQuantity(item, $event)" />
                                <input :name="`quantityBatch[${item.id}][quantityStock]`" :value="item.quantityStock" hidden />
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
            dataList: [],
            radio: 0,
            items: [],
            itemOdd: [],
            dataListOdd: [],
            warehouseReceipt: 0,
            showroomReceipt: 0,
            quantityErrors: {},
            preConfirmed: true,
            keySearch: "",
            allShowroomWarehouse: {},
            proposal: ''
        };
    },
    async mounted() {
        await this.fetchAllWarehouseShowroom()
        this.proposal_id = $('#proposal_id').val();
        if (this.proposal_id) {
            this.getProductInFormProposal();
        }
        else {
            this.changeAgentWarehouse();

        }
        const submitButton = $("form button[type='submit']");
        submitButton.click(this.submitForm);
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
        $('#showroom_id').change(this.changeAgentWarehouse);
        $('#warehouse_receipt_id').change(this.changeWarehouseReceipt);

    },
    methods: {
        checkQuantity(item, event) {
            let newValue = parseFloat(event.target.value);
            const min = 1;
            const max = parseFloat(item.quantityStock);
            if (newValue < min) {
                item.quantity = min;
                this.alertWarning('Phải là số dương');
            } else if (newValue > max) {
                item.quantity = max;
                this.alertWarning('Không vượt quá số lượng trong HUB');
            } else {
                item.quantity = newValue;
            }

        },
        confirm() {
            if (this.preConfirmed) {
                this.preConfirmed = false;

            } else {
                this.preConfirmed = true;
            }
        },
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
        changeWarehouseReceipt() {
            const warehouseIssue = parseFloat($('#warehouse_receipt_id').val());
            this.warehouseReceipt = warehouseIssue;
        },
        validateQuantity(item, quantity) {
            const parsedQuantity = parseInt(quantity, 10);

            if (isNaN(parsedQuantity) || parsedQuantity < 1) {
                item.quantity = 1;
                this.alertWarning('Phải là số dương');
            } else {
                item.quantity = parsedQuantity;
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
        submitForm(e) {
            e.preventDefault();
            const form = document.getElementById('botble-showroom-forms-proposal-showroom-receipt-form');
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
            if (this.showroomReceipt == 0) {
                isValid = false;
                this.alertWarning('Phải chọn showroom');
                return;
            }
            if (this.warehouseReceipt == 0) {
                isValid = false;
                this.alertWarning('Phải chọn kho nhận ');
                return;
            }
            if ($('#title').val() == '') {
                isValid = false;
                this.alertWarning('Phải nhập mục đích nhập kho');
                return;
            }
            if (this.items.length === 0 && this.filteredItemOdd.length === 0) {
                isValid = false;
                this.alertWarning('Cần chọn sản phẩm nhập');
                return;
            }
            this.filteredItemOdd.forEach(item => {
                if (item.quantity <= 0 || isNaN(item.quantity)) {
                    isValid = false;
                    this.alertWarning('Vui lòng nhập số lượng');
                    return;
                }
                if (item.quantity > item.quantityStock) {
                    isValid = false;
                    this.alertWarning('Vượt quá số lượng trong kho');
                    return;
                }
            });
            if (isValid) {
                $("form button[type='submit']").prop('disabled', true);
                form.submit();
            }
        },
        fetchDataProduct() {
            $("form button[type='submit']").prop('disabled', true);

            axios.get(route('showroom.products.get-all-product-parent'))
                .then(res => {
                    this.dataList = res.data.data.map(item => {
                        return {
                            ...item,
                            name: `${item.name}`,
                            image: item.images[0],
                            sku: item.sku,
                            quantity: 1,
                            quantityStock: 0

                        }
                    })
                    $("form button[type='submit']").prop('disabled', false);

                })
        },
        async changeAgentWarehouse() {
            console.log(334, 'changeAgentWarehouse');
            this.showroomReceipt = $('#showroom_id').val();
            this.reset();
            if (this.proposal_id =='') {
                await this.fetchProductByHub();
            }
            else {
                this.proposal_id = ''
            }
            this.fetchWarehouseByAgent();
        },
        reset() {
            this.dataList = []
            this.dataListOdd = []
            this.items = []
            this.itemOdd = []
        },
        fetchProductByHub(dataProposal = null) {
            $("form button[type='submit']").prop('disabled', true);

            axios.get(route('proposal-showroom-receipt.get-product-in-hub', { id: this.showroomReceipt, keySearch: this.keySearch }))
                .then(response => {


                    this.dataListOdd = response.data.dataDetail.map(item => {
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
                        const name = `${item.product.name} - (Màu: ${color} - Size: ${size})`;
                        return {
                            ...item,
                            'name': name,
                            'image': item.product.parent_product[0].image,
                            'id': item.product.id,
                            'sku': item.product.sku,
                            'quantityStock': item.quantity,
                            'is_batch': 0,
                            'quantity': 1,
                            'parent_product': item.product.parent_product[0],
                        }
                    })
                    if (dataProposal != null) {
                        dataProposal.forEach(item => {
                            const dataInsert = {
                                ...item,
                                'sku': item.product.sku,
                                'id': item.product.id,
                                'name': `${item.product.name} `,
                                'quantity': item.quantity,
                                'quantityStock': item?.product_hub_stock?.quantity ? item?.product_hub_stock?.quantity : 0,
                                'is_batch': 0,
                                'image': item.product.parent_product[0].image

                            }
                            this.itemOdd.push(dataInsert
                            );

                        });

                    }
                    $("form button[type='submit']").prop('disabled', false);
                })
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
        fetchWarehouseByAgent(idWarehouse = null) {
            $("form button[type='submit']").prop('disabled', true);
            const dropdown = $('#warehouse_receipt_id');
            dropdown.empty();

            const data = this.allShowroomWarehouse
                .filter(item => this.showroomReceipt == item.showroom_id)
                .map(item => ({
                    id: item.id,
                    name: item.name
                }));

            if (data.length > 0) {
                data.forEach(agentWarehouse => {
                    dropdown.append('<option value="' + agentWarehouse.id + '">' + agentWarehouse.name + '</option>');
                });

                if (idWarehouse) {
                    this.warehouseReceipt = idWarehouse;
                } else {
                    this.warehouseReceipt = data[0].id;
                }
                dropdown.val(this.warehouseReceipt);
            } else {
                dropdown.append('<option value="0">Vui lòng chọn showroom khác - showroom không có kho</option>');
            }

            $("form button[type='submit']").prop('disabled', false);
        }
        ,
        async getProductInFormProposal() {
            $("form button[type='submit']").prop('disabled', true);

            try {
                const res = await axios.get(route('proposal-showroom-receipt.proposal', { id: this.proposal_id }));
                const data = res.data;

                // Cập nhật dữ liệu trong Vue instance
                this.showroomReceipt = data.showroom;
                // $('#showroom_id').val(this.showroomReceipt)
                $('#showroom_id').val(data.showroom).trigger('change');
                console.log(464, 'trigger');
                this.fetchProductByHub(data.data)
                this.fetchWarehouseByAgent(data.proposal.warehouse_receipt_id)
            } catch (error) {
                // Xử lý lỗi nếu cần thiết
            } finally {
                $("form button[type='submit']").prop('disabled', false); // Bật nút submit sau khi kết thúc yêu cầu
            }
        },
        searchProducts(keySearch) {
            console.log(473, keySearch);
            if (keySearch && keySearch != '') {
                this.keySearch = keySearch;
                this.fetchProductByHub();
                console.log(478, keySearch);
            }
        },
        async fetchAllWarehouseShowroom() {

            try {
                const res = await axios.get(route('showroom-warehouse.get-all-warehouse-showroom'));
                this.allShowroomWarehouse = res.data.data;
            } catch (error) {
                console.error('Error:', error);
            }
        },

    },

    components: {
        MultiListSelect,
        MultiSelect

    },
    computed: {
        placeholderText() {
            return this.dataList.length === 0
                ? '(Danh sách rỗng)'
                : 'Chọn thành phẩm nhập kho';
        },
        filteredItemOdd() {
            return this.itemOdd.filter(item => item.quantityStock > 0);
        },
        filterDataList() {
            this.items.forEach(item => {
                this.dataList = this.dataList.filter(dataItem => dataItem.id !== item.id);
            });

            return this.dataList;
        },
        filterDataListOdd() {
            this.itemOdd.forEach(item => {
                this.dataListOdd = this.dataListOdd.filter(dataItem => dataItem.id !== item.id);
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
