<template>
    <div class="ui vertical segment">
        <div class="flexbox">
            <div class="flex-content">
                Chọn thành phẩm nhập kho:
            </div>
            <div class="flex-content">
                <div class="button-group" style="margin-bottom: 5px">
                    <!-- <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="items.length > 0">
                        Xóa tất cả
                    </button> -->
                </div>
                <div>
                    <!-- <multi-list-select :list="dataList" option-value="id" option-text="name" :placeholder="placeholderText"
                        :selected-items="items" @select="onSelect"></multi-list-select> -->
                    <multi-select :products="filterDataListOdd" :items="filteredItemOdd"
                        :placeholder="`Chọn thành phẩm lẻ`" :parent="true" @update:products="handleUpdatedProducts"
                        @search:products="searchProducts"></multi-select>

                </div>
            </div>
            <input type="hidden" name="itemOdd" :value="JSON.stringify(filteredItemOdd)" />
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
                            <td class="align-middle text-center">{{ item.name }}</td>
                            <td class="align-middle text-center">
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name"
                                    class="image-view">
                                <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"
                                    class="image-view">
                            </td>
                            <td class="align-middle text-center">{{ item.sku }}
                                <input type="hidden" name="sku" :value="item.sku">
                            </td>
                            <td class="align-middle text-center">{{ item.quantityStock }} sản phẩm </td>
                            <td class="align-middle text-center">
                                <input :name="`quantityBatch[${item.id}][quantity]`" type="number"
                                    class="form-control text-center" :max="item.quantityStock"
                                    placeholder="Nhập số lượng" min=1 v-model="item.quantity"
                                    @blur="checkQuantity(item, $event)" />
                                <input :name="`quantityBatch[${item.id}][quantityStock]`" :value="item.quantityStock"
                                    hidden />
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
            warehouseReceipt: 0,
            agentReceipt: 0,
            quantityErrors: {},
            allAgentWarehouse: {},
            itemOdd: [],
            dataListOdd: [],
            keySearch: "",
            proposal_id:''
        };
    },
    async mounted() {
        await this.fetchWarehouseAgent();
        await this.changeAgentWarehouse();
        // await this.fetchDataProduct();
         this.proposal_id = $('#proposal_id').val();
        if ( this.proposal_id) {
            this.getProductInFormProposal(proposal_id);
        }
        $('#agent_id').change(this.changeAgentWarehouse);
        $('#warehouse_receipt_id').change(this.changeWarehouseReceipt);
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
        handleUpdatedProducts(updatedProducts) {
            this.itemOdd.push({ ...updatedProducts });
            this.dataListOdd.forEach((item, index) => {
                if (item.id === updatedProducts.id) {
                    this.dataListOdd.splice(index, 1);
                }
                return item;
            });
        },
        removeItem(itemToRemove) {
            this.items = this.items.filter(item => item.id !== itemToRemove.id);

            this.dataList.push(itemToRemove);

        },
        removeItemOdd(itemToRemove) {
            this.itemOdd = this.itemOdd.filter(item => item.id !== itemToRemove.id);
            this.dataListOdd.push(itemToRemove);
        },
        reset() {
            this.dataList = []
            this.dataListOdd = []
            this.items = []
            this.itemOdd = []
        },
        changeWarehouseReceipt() {
            const warehouseIssue = parseFloat($('#warehouse_receipt_id').val());
            this.warehouseReceipt = warehouseIssue;
        },
        validateQuantity(item, quantity) {
            const parsedQuantity = parseInt(quantity, 10); // Parse input as an integer

            if (isNaN(parsedQuantity) || parsedQuantity < 1) {
                item.quantity = 1;
                this.alertWarning('Phải là số dương');
            } else {
                item.quantity = parsedQuantity;
            }
        },
        onSelect(selectedItems) {
            this.items = selectedItems.map((newItem) => {
                const existingItem = this.items.find((item) => item.id == newItem.id);
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
            const form = document.getElementById('botble-agent-forms-proposal-agent-receipt-form');
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
            if (this.agentReceipt == 0 || this.agentReceipt == '') {
                isValid = false;
                this.alertWarning('Phải chọn đại lý ');
                return;
            }
            if (this.warehouseReceipt == 0 || this.warehouseReceipt == '') {
                isValid = false;
                this.alertWarning('Phải chọn kho nhận ');
                return;
            }
            if ($('#title').val() == '') {
                isValid = false;
                this.alertWarning('Phải nhập mục đích nhập kho');
                return;
            }
            if (this.itemOdd.length == 0) {
                isValid = false;
                this.alertWarning('Cần chọn sản phẩm nhập');
                return;
            }
            this.itemOdd.forEach(item => {
                if (item.quantity <= 0 || isNaN(item.quantity)) {
                    isValid = false;
                    this.alertWarning('Vui lòng nhập số lượng');
                    return;
                }
            });
            if (isValid) {
                form.submit();
                $("form button[type='submit']").prop('disabled', true);

            }
        },
        fetchDataProduct() {
            $("form button[type='submit']").prop('disabled', true);
            axios.get(route('agent.products.get-all-product-parent'))
                .then(res => {
                    this.dataList = res.data.data.map(item => {
                        return {
                            ...item,
                            image: item.images[0],
                            name: `${item.name}`,
                            quantity: 1,
                            sku: `${item.sku}`
                        }
                    })
                })
                .catch(error => {
                    // Handle errors here
                    console.error('Error:', error);
                })
                .finally(() => {
                    $("form button[type='submit']").prop('disabled', false);

                });
        },
        async changeAgentWarehouse() {
            this.agentReceipt = $('#agent_id').val();
            await this.fetchProductByHub();
            this.reset();
            this.fetchWarehouseByAgent();
        },
        fetchProductByHub(dataProposal = null) {
            $("form button[type='submit']").prop('disabled', true);

            axios.get(route('proposal-agent-receipt.get-product-in-hub', { id: this.agentReceipt, keySearch: this.keySearch }))
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
                            'image': item.product.images[0],
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

                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    $("form button[type='submit']").prop('disabled', false);

                });
        },
        async fetchWarehouseAgent() {
            try {
                $("form button[type='submit']").prop('disabled', true);
                const res = await axios.get(route('agent-warehouse.get-all-agent-warehouse'));
                this.allAgentWarehouse = res.data.data;
            } catch (error) {
                console.error('Error:', error);
            } finally {
                $("form button[type='submit']").prop('disabled', false);

            }
        },
        fetchWarehouseByAgent(idWarehouse = null) {
            const dropdown = $('#warehouse_receipt_id');
            dropdown.empty();

            const data = this.allAgentWarehouse.map(item => {
                if (this.agentReceipt == item.agent_id) {
                    return {
                        'id': item.id,
                        'name': item.name,
                    };
                }
            }).filter(item => item !== undefined);
            if (data.length > 0) {
                data.forEach(agentWarehouse => {
                    dropdown.append('<option value="' + agentWarehouse.id + '">' + agentWarehouse.name + '</option>');
                    if (idWarehouse) {
                        dropdown.val(idWarehouse);
                        this.warehouseReceipt = idWarehouse;
                    } else {
                        dropdown.val(data[0].id);
                        this.warehouseReceipt = data[0].id
                    }
                });
            } else {
                dropdown.append('<option value="0">Vui lòng chọn đại lý khác - đại lý không có kho</option>');
            }
        },
        getProductInFormProposal() {
            $("form button[type='submit']").prop('disabled', true);
            axios.get(route('proposal-agent-receipt.proposal', { id:  this.proposal_id }))
                .then(res => {
                    const data = res.data;
                    this.agentReceipt = data.agent;
                    $('#agent_id').val(data.agent).trigger('change');
                    this.fetchProductByHub(data.data)
                    this.fetchWarehouseByAgent(data.proposal.warehouse_receipt_id)

                }).catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    $("form button[type='submit']").prop('disabled', false);

                });

        },
        searchProducts(keySearch) {
            this.keySearch = keySearch;
            this.fetchProductByHub();
        },

    },

    components: {
        MultiListSelect,
        MultiSelect

    },
    computed: {
        placeholderText() {
            return this.dataList.length == 0
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
    }
};
</script>

<style>
.image-view {
    height: 40px;
    width: 40px;
}
</style>
