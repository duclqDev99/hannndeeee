<template>
  <div>
    <div class="ui fluid search dropdown selection multiple active visible" ref="dropdown" @click="focusSearchInput">
      <i class="dropdown icon"></i>
      <a v-for="selectedProduct in items" :key="selectedProduct.id" class="ui label transition visible">
        {{ selectedProduct.name }}
        <i class="delete icon" @click="removeSelected(selectedProduct)"></i>
      </a>
      <input ref="searchInput" class="search search-input" autocomplete="off" @focus="showDropdown" tabindex="0" name=""
        style="width: 80px;" @input="debouncedSearch($event)">

      <div class="text default" v-if="items.length === 0 || searchQuery === ''">
        Chọn thành phẩm nhập kho
      </div>

      <div v-if="dropdownVisible" class="menu visible" tabindex="-1"
        style="display: block; max-height: 200px; overflow-y: auto;">
        <div v-if="filteredProducts.length === 0" class="item">
          Đã hết sản phẩm.
        </div>
        <div v-for="product in filteredProducts" :key="product.id" @click="toggleSelected(product)" class="item">
          <img :src="`/storage/${product.image}`" alt="Product Image" width="50" height="50">
          {{ product.name }}
          - Số lượng: {{ product.quantityStock || 0 }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import debounce from 'lodash/debounce';

export default {
    props: {
        products: Array,
        items: Array,
    },
    data() {
        return {
            dropdownVisible: false,
            searchQuery: '',
            originalOrder: this.products.map(p => p.id),
        };
    },
    computed: {
        filteredProducts() {
            const lowerCaseQuery = this.searchQuery.toLowerCase();
            const uniqueIds = new Set();

            return this.products.filter(product => {
                const nameMatches = product.name && product.name.toLowerCase().includes(lowerCaseQuery);
                const skuMatches = product.sku && product.sku.toLowerCase().includes(lowerCaseQuery);
                const isNotInItems = !this.items.some(item => item.id === product.id);
                const isUniqueId = !uniqueIds.has(product.id);

                if ((nameMatches || skuMatches) && isNotInItems && isUniqueId) {
                    uniqueIds.add(product.id);
                    return true;
                }
                return false;
            });
        },
    },
    methods: {
        toggleSelected(product) {
            const itemsIndex = this.items.findIndex((selectedProduct) => selectedProduct.id == product.id);
            if (itemsIndex == -1) {
                this.items.push(product);
                const productIndex = this.products.findIndex((p) => p.id == product.id);
                if (productIndex !== -1) {
                    this.products.splice(productIndex, 1);
                }
            }
        },
        removeSelected(selectedProduct) {
            const index = this.items.findIndex((product) => product.id == selectedProduct.id);
            if (index !== -1) {
                this.items.splice(index, 1);
                this.products.push(selectedProduct);
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
            this.searchQuery = event.target.value;
        }, 300),
        focusSearchInput() {
            this.$refs.searchInput.focus();
        },
    },

    created() {
        this.originalOrder = this.products.map(product => product.id);
    },
    watch: {
        items(newItems, oldItems) {
            const removedItems = oldItems.filter(oldItem => !newItems.some(newItem => newItem.id == oldItem.id));
            removedItems.forEach(removedItem => {
                if (!this.products.some(product => product.id == removedItem.id)) {
                    this.products.push(removedItem);
                }
            });
            this.products.sort((a, b) => this.originalOrder.indexOf(a.id) - this.originalOrder.indexOf(b.id));
        }
    },
};
</script>

<style scoped>
/* Style for search input */
.search-input {
    position: relative;
    width: 200px;
}

.search-input .search.icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
}

.search-input input {
    padding-left: 30px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Additional styling as needed */
</style>

<style scoped>
/* Add your custom styling here */
.search-input {
    margin: 5px;
    width: 200px;
}

.search {
    width: 100%;
}
</style>
