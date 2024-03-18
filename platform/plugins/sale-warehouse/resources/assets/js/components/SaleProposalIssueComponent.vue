<template>
    <input type="hidden" name="itemOdd" :value="JSON.stringify(filteredItemOdd)" />
    <div class="ui vertical segment">

            <div class="flex-content">

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
                            <td class="align-middle text-center">{{ item.sku }}
                                <input type="hidden" name="sku" :value="item.sku">
                            </td>
                            <td class="align-middle text-center">{{ item.quantityStock }} sản phẩm </td>
                            <td class="align-middle text-center">
                                <input :name="`product[${item.id}][quantity]`" type="number" class="form-control text-center" :max="item.quantityStock" placeholder="Nhập số lượng" min=1 v-model="item.quantity" @blur="checkQuantity(item, $event)" />
                                <input :name="`product[${item.id}][quantityStock]`" :value="item.quantityStock" hidden />
                            </td>
                            <td class="text-center">
                                <button @click="removeItemOdd(item)" class="btn btn-danger">Xóa</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

</template>
<script>
import axios from 'axios';
import { MultiListSelect } from "vue-search-select";
import "vue-search-select/dist/VueSearchSelect.css";
import MultiSelect from '../../../../../shared-module/resources/assets/components/proposal/MutilSelect.vue';
export default {
    data() {
        return {


            warehouseIssue: 0,
            saleIssue: 0,
            radioButton: null,
            dataList: [],
            radio: 0,
            items: [],
            itemOdd: [],
            dataListOdd: [],
            totalAmount: 0,
            keySearch: "",
            allSaleWarehouseChild: {}
        };
    },

    async mounted() {
        $("form button[type='submit']").prop('disabled', false);
        await this.fetchAllSaleWarehouseChild();
        const proposal_id = $('#proposal_id').val();
        if (proposal_id) {
            this.getProductInFormProposal(proposal_id);
        }
        else {
            await this.changeSaleWarehouse()
        }

        const radioButtons = document.querySelectorAll('input[name="is_warehouse"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', this.handleRadioChange);
        });

        $('#sale_id').change(this.changeSaleWarehouse);

        $('#warehouse_issue_id').change(this.changeWarehouseIssue);
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

        handleUpdatedProducts(updatedProducts) {
            this.itemOdd.push({ ...updatedProducts });
            this.dataListOdd.forEach((item, index) => {
                if (item.id === updatedProducts.id) {
                    this.dataListOdd.splice(index, 1);
                }
                return item;
            });
        },


        removeItemOdd(itemToRemove) {
            this.itemOdd = this.itemOdd.filter(item => item.id !== itemToRemove.id);
            this.dataListOdd.push(itemToRemove);

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
            const form = document.getElementById('botble-sale-warehouse-forms-sale-proposal-issue-form');
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
            if (this.saleIssue == 0 || isNaN(this.saleIssue)) {
                isValid = false;
                this.alertWarning('Phải chọn kho sale xuất thành phẩm');
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
            if (this.itemOdd.length === 0) {
                isValid = false;
                this.alertWarning('Cần chọn sản phẩm xuất');
                return;
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

        async getProductInFormProposal(idProposal) {
            $("form button[type='submit']").prop('disabled', true);
            try {
                const res = await axios.get(route('sale-proposal-issue.proposal', { id: idProposal }));
                const data = res.data;
                console.log(data);
                const idWarehouse = data.proposal.warehouse_issue_id;
                this.saleIssue = data.saleWarehouse
                this.warehouseIssue = idWarehouse
                $('#sale_id').val(this.saleIssue).trigger('change');
                await this.changeSaleWarehouse(idWarehouse);
                // this.handleRadioChange(event, data.proposal.is_warehouse);
                this.getProductByWarehouse(idWarehouse, data.data);

            } catch (error) {
                console.error('Error fetching product data for proposal:', error);
            } finally {
                $("form button[type='submit']").prop('disabled', false);
            }
        },
        getProductByWarehouse(id, data = null, type = null) {
            axios.get(route('hub-warehouse.getProductByWarehouse', { id: id, }),
                {
                    params: {
                        keySearch: this.keySearch,
                        model: 'Botble\\SaleWarehouse\\Models\\SaleProduct',
                        type: type
                    }
                }).then(response => {
                    this.dataListOdd = []
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
                            let color;
                            let size;
                            item?.product?.product_attribute.map(key => {
                                if (key.attribute_set_id == 1) {
                                    color = key.title;
                                }
                                else {
                                    size = key.title
                                }
                            });
                            const dataInsert = {
                                ...item,
                                'sku': item.product.sku,
                                'id': item.product_id,
                                'name': `${item.product.name} - (Màu: ${color} - Size: ${size})`,
                                'quantity': item.quantity,
                                'quantityStock': item?.product_stock?.quantity ? item?.product_stock?.quantity : 0,
                                'is_batch': 0,
                                'image': item.product?.parent_product[0]?.image

                            }
                            this.itemOdd.push(dataInsert);
                        });
                    }

                });
        },
        changeWarehouseIssue() {
            const idWarehouse = $('#warehouse_issue_id').val();
            this.warehouseIssue = idWarehouse
            this.dataListOdd = [];
            this.itemOdd = [];
            this.getProductByWarehouse(this.warehouseIssue);
        }
        ,
        updateSelectRef(ref, value) {
            ref.value = value;
            $(ref).trigger('change');
        },
        async fetchAllSaleWarehouseChild() {
            try {
                const res = await axios.get(route('sale-warehouse-child.getAllWarehouse'));
                this.allSaleWarehouseChild = res.data.data;
            } catch (error) {
                console.error('Error:', error);
            }
        },
        changeSaleWarehouse(idWarehouse = null) {
            const saleWarehouse = parseFloat($('#sale_id').val());
            this.saleIssue = saleWarehouse;
            const dropdown = $('#warehouse_issue_id');
            dropdown.empty();
            const data = this.allSaleWarehouseChild;
            if (data.length > 0) {
                let optionAppended = false; // Biến để kiểm tra xem có option nào được thêm vào dropdown hay không
                $.each(data, function (index, sale) {
                    if (saleWarehouse == sale.sale_warehouse_id) {
                        dropdown.append('<option value="' + sale.id + '">' + sale.name + '</option>');
                        optionAppended = true; // Đánh dấu là đã thêm option vào dropdown
                    }
                });
                if (!optionAppended) { // Nếu không có option nào được thêm vào dropdown
                    dropdown.append('<option value="">Trong kho sale chưa có kho con</option>');
                }
                if (idWarehouse && dropdown.find('option[value="' + idWarehouse + '"]').length > 0) {
                    dropdown.val(idWarehouse);
                } else {
                    dropdown.val(dropdown.find('option:first').val());
                }
            } else {
                dropdown.append('<option value="">Trong kho sale chưa có kho con</option>');
            }
            this.changeWarehouseIssue();

        }
        ,

        checkQuantity(item, event) {
            let newValue = parseFloat(event.target.value);
            const min = 1;
            const max = parseFloat(item.quantityStock);
            if (newValue < min) {
                item.quantity = min;
                this.alertWarning('Số lượng đề xuất phải là số dương');

            } else if (newValue > max) {
                item.quantity = max;
                this.alertWarning('Số lượng đề xuất đã vượt quá số lượng trong kho');

            } else {
                item.quantity = newValue;
            }

        },

        updateItemOdd() {
            this.itemOdd = this.itemOdd.map(item => {
                if (item.quantity > item.quantityStock) {
                    item.quantity = item.quantityStock;
                }
                return item;
            }).filter(item => item.quantityStock > 0);
        },
        searchProducts(keySearch) {
            this.keySearch = keySearch;
            this.getProductByWarehouse(this.warehouseIssue)
        },
    },
    components: {
        MultiSelect

    },
    computed: {
        placeholderText() {
            return this.dataListOdd.length == 0 ? 'Đã hết sản phẩm trong kho' : 'Chọn sản phẩm xuất';
        },
        filteredItemOdd() {
            return this.itemOdd.filter(item => item.quantityStock !== 0);
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
