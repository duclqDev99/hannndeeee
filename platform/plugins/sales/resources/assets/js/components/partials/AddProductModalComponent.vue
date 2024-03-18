<template>
    <ec-modal
        id="add-product-item"
        :title="__('order.add_new_product')"
        :ok-title="__('order.save')"
        :cancel-title="__('order.cancel')"
        :size="('modal-lg')"
        @shown="resetProductData()"
        @ok="$emit('create-product', $event, product)"
    >
        <div class="row">
            <div class="col-8 mb-3 position-relative">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" class="form-control" v-model="product.name" :placeholder="__('order.placeholder_product_name')"/>
            </div>
            <div class="col-4 mb-3 position-relative">
                <label class="form-label">Đơn vị tính:</label>
                <input type="text" class="form-control" v-model="product.unit" :placeholder="__('order.placeholder_unit')"/>
            </div>
            <div class="col-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.sku') }}</label>
                <input type="text" class="form-control" v-model="product.sku" :placeholder="__('order.placeholder_sku')"/>
            </div>
            <div class="col-6 mb-3 position-relative">
                <label class="form-label">Giá:</label>
                <input type="number" class="form-control" v-model="product.price" :placeholder="__('order.placeholder_price')"/>
            </div>
            <div class="col-6 mb-3 position-relative">
                <label class="form-label">Màu sắc:</label>
                <input type="text" class="form-control" v-model="product.color" :placeholder="__('order.placeholder_color')"/>
            </div>
            <div class="col-6 mb-3 position-relative">
                <label class="form-label">Kích thước:</label>
                <input type="text" class="form-control" v-model="product.size" :placeholder="__('order.placeholder_size')"/>
            </div>
            <div class="col-12 mb-3 position-relative">
                <label class="form-label">Thành phần:</label>
                <input type="text" class="form-control" v-model="product.ingredient" :placeholder="__('order.placeholder_ingredient')"/>
            </div>
            <div class="col-12 mb-3 position-relative">
                <label class="form-label">Mô tả chi tiết:</label>
                <textarea class="form-control" v-model="product.description" :placeholder="__('order.placeholder_description')"></textarea>
            </div>
            <div class="col-12 mb-3 position-relative">
                <label class="form-label">Hình ảnh:</label>
                <input accept="image/*" class="form-control" type="file" multiple @change="previewFiles($event)" />
                <div v-if="product.image.length">
                    <ul class="product-sample-imgs">
                        <li v-for="(image, index) in product.image" :key="index">
                            <img class="img-fluid" :src="image" alt="" />
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="position-relative" v-if="store && store.id">
            <label class="form-check-label">{{ __('order.store') }}: <strong class="text-primary">{{ store.name }}</strong></label>
        </div>
    </ec-modal>
</template>

<style lang="scss">
    .product-sample-imgs{
        list-style: none;
        display: grid;
        padding: 0;
        gap: 10px;
        grid-template-columns: auto auto auto;
    }
</style>

<script>
export default {
    props: {
        store: {
            type: Object,
            default: () => ({}),
        },
    },
    data: function () {
        return {
            product: {
                image: []
            },
        }
    },
    methods: {
        resetProductData: function () {
            this.product = {
                name: null,
                unit: null,
                price: 0,
                sku: null,
                color: null,
                size: null,
                price: null,
                ingredient: null,
                description: null,
                image: []
                // with_storehouse_management: false,
                // allow_checkout_when_out_of_stock: false,
                // quantity: 0,
            }
        },
        previewFiles(event) {
            const files = event.target.files;

            if (files) {
                // Reset the array before adding new files
                this.product.image = [];

                // Process each selected file
                for (let i = 0; i < files.length; i++) {
                    const reader = new FileReader();

                    reader.onload = (e) => {
                        // Add the image source to the array
                        this.product.image.push(e.target.result);
                    };

                    // Read the file as a data URL
                    reader.readAsDataURL(files[i]);
                }
            }
        }
    },
    mounted: function () {
        this.resetProductData()
    },
}
</script>
