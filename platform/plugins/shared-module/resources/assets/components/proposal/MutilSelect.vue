<template>
    <div>
        <div class="ui fluid search dropdown selection multiple active visible" ref="dropdown"
            @click="focusSearchInput">
            <i class="dropdown icon"></i>
            <input ref="searchInput" class="search search-input" autocomplete="off" @focus="showDropdown" tabindex="0"
                name="" style="width: inherit;" @input="debouncedSearch($event)">

            <div class="text default" v-if="!dropdownVisible && search == ''">
                {{ placeholder ? placeholder : 'Chọn sản phẩm' }}
            </div>

            <div v-if="dropdownVisible" class="menu visible" tabindex="-1"
                style="display: block; max-height: 400px; overflow-y: auto;">
                <div v-if="groupedProducts.length === 0 || filteredProducts.length === 0" class="item">
                    Không tìm thấy sản phẩm.
                </div>
                <div v-if="radio == 3 || parent == true" v-for="(group, parentId) in groupedProducts" :key="parentId"
                    class="group-container">
                    <div class="list-group-item list-group-item-action item-not-selectable">
                        <div v-if="group.parentInfo" class="parent-option">
                            <img v-if="group.parentInfo.image" :src="`/storage/${group.parentInfo.image}`"
                                alt="Parent Image" width="50" height="50">
                            <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"
                                class="image-view">
                            {{ group.parentInfo.name }}
                        </div>

                        <div class="list-group list-group-flush">
                            <div v-for="product in group.products" :key="product.id" class="item">
                                <div class="list-group-item p-2 ml-10" v-if="product.quantityStock > 0 || radio == 3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <span>{{ product.name }}</span> -
                                            <span>
                                                <span class="text-success">Mã sản phẩm: {{ product.sku }}</span>
                                                <span v-if="warehouse"> Số lượng tồn kho:</span>
                                                <span v-else> Số lượng tồn kho trong HUB:</span>
                                                {{ product.quantityStock }} sản phẩm
                                            </span>
                                            <div style="display: none;"></div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="btn btn-outline-primary btn-sm" type="button"
                                                @click="toggleSelected(product)">
                                                <i class="icon-sm ti ti-plus"></i> Thêm
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="batch == true">
                    <div v-for="(group, productId) in groupedProductBatch" :key="productId">
                        <div class="item">
                            <div class="list-group-item p-2">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <span>{{ group[0].name }} </span> -
                                        <span>
                                            <span class="text-success">Mã sản phẩm: {{ group[0].sku }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            <div v-for="batch in group" :key="batch.id" class="item">
                                <div class="list-group-item p-2">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <span>
                                                <span class="text-success">
                                                    Mã lô: {{ batch.batch_code }}
                                                </span>
                                                - số lượng: {{ batch.quantity }}
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <button class="btn btn-outline-primary btn-sm" type="button"
                                                @click="toggleSelectedBatch(batch)">
                                                <i class="icon-sm ti ti-plus"></i> Thêm
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else v-for="product in filteredProducts" :key="product.id" class="item">
                    <div class="list-group-item p-2">
                        <div class="row align-items-center">
                            <div class="col">
                                <span>
                                    <img v-if="product.image" :src="`/storage/${product.image}`" alt="Product Image"
                                        width="50" height="50">
                                    <img v-else :src="'/vendor/core/core/base/images/placeholder.png'" alt="Image"
                                        class="image-view">
                                </span>
                                <span>{{ product.name }} - {{ product.batch_code }}</span> -
                                <span class="text-success">
                                    Mã sản phẩm: {{ product.sku }}
                                </span>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-primary btn-sm" type="button"
                                    @click="toggleSelected(product)">
                                    <i class="icon-sm ti ti-plus">
                                    </i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import debounce from 'lodash/debounce';

export default {
    props: {
        radio: String,
        products: Array,
        items: Array,
        placeholder: String,
        parent: Boolean,
        batch: Boolean,
        warehouse: Boolean
    },
    data() {
        return {
            dropdownVisible: false,
            searchQuery: '',
            search: ''
        };
    },
    computed: {
        filteredProducts() {
            const lowerCaseQuery = this.searchQuery.toLowerCase();
            return this.products.filter(product => {
                const nameMatches = product.name && product.name.toLowerCase().includes(lowerCaseQuery);
                const nameBatchCode = product.batch_code && product.batch_code.toLowerCase().includes(lowerCaseQuery);
                const skuMatches = product.sku && product.sku.toLowerCase().includes(lowerCaseQuery);
                if ((nameMatches || skuMatches) || nameBatchCode) {
                    return true;
                }
                return false;
            });
        },
        groupedProducts() {
            const grouped = {};
            const uniqueIds = new Set();

            this.products.forEach((product) => {
                const parentId = product?.parent_product?.id ?? '';
                if (!grouped[parentId]) {
                    grouped[parentId] = {
                        parentInfo: {
                            name: product?.parent_product?.name ?? '',
                            sku: product?.parent_product?.sku ?? '',
                            image: product?.parent_product?.images?.length > 0 ? product.parent_product.images[0] : '',
                        },
                        products: [],
                    };
                }

                const isUniqueId = !uniqueIds.has(product.id);
                if (isUniqueId) {
                    uniqueIds.add(product.id);
                    grouped[parentId].products.push(product);
                }
            });

            return grouped;
        },
        groupedProductBatch() {
            const grouped = {};
            // Group products by their product_id
            this.filteredProducts.forEach(product => {
                if (!grouped[product.product_id]) {
                    grouped[product.product_id] = [];
                }
                grouped[product.product_id].push(product);
            });
            return grouped;
        }

    },
    methods: {
        toggleSelected(product) {
            this.$emit('update:products', product);
            if (this.$refs.searchInput) {
                this.$refs.searchInput.blur();
            }
        },
        toggleSelectedBatch(batch) {
            this.$emit('update:batchs', batch);
            if (this.$refs.searchInput) {
                this.$refs.searchInput.blur();
            }
        },


        showDropdown() {
            this.dropdownVisible = true;
            document.addEventListener('click', this.handleDocumentClick);
        },

        hideDropdown() {
            this.dropdownVisible = false;
            document.removeEventListener('click', this.handleDocumentClick);
        },

        toggleDropdownVisibility() {
            this.showDropdown();
        },

        handleDocumentClick(event) {
            if (this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
                this.hideDropdown();
            }
        },

        debouncedSearch: debounce(function (event) {
            this.search = event.target.value;
            this.$emit('search:products', event.target.value);
        }, 800),
        focusSearchInput() {
            this.$refs.searchInput.focus();
        },
    },

    created() {
    },
    watch: {

    },
};
</script>

<style scoped>
/* Style for search input */
.parent-option {
    margin: 10px;
    display: flex;
    align-items: center;
}

.parent-option img {
    border-radius: 5px;
    width: 50px;
    height: 50px;
    object-fit: cover;
}

.parent-info {
    margin-left: 10px;
}

.btn-sm {
    position: relative;
    margin-top: 20px;
    margin-left: 20px;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease;

}

.btn-sm button {
    position: absolute;
    bottom: 8px;
    right: 8px;
    padding: 8px 16px;
    background-color: #ffffff;
    color: #206bc4;
    border: 1px solid #206bc4;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-sm button:hover {
    background-color: #206bc4;
    color: #ffffff;

}

item:hover {
    background-color: #3a3737;
    /* Change the background color on hover */

}

.item button:focus {
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}





.image-view {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

.group-container {

    /* Your existing styles for group-container */
    transition: background-color 0.3s ease;
    /* Add a smooth transition effect */
}

/* Add a new class for the hover effect */
.group-container:hover {
    background-color: #f0f0f0;
    /* Change the background color on hover */
}

/* Additional styling as needed */

/* Responsive design for smaller screens */
@media only screen and (max-width: 768px) {
    .item button {
        position: static;
        margin-top: 10px;
    }

    .parent-option {
        flex-direction: column;
        align-items: flex-start;
    }

    .parent-info {
        margin-left: 0;
        margin-top: 10px;
    }
}
</style>
