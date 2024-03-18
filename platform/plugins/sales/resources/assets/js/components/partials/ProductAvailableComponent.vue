<template>
    <span>
        <span v-if="item.is_out_of_stock" class="text-danger">
            <small>&nbsp;({{ __('order.out_of_stock') }})</small>
        </span>
        <span v-else>
            <span v-if="item.with_storehouse_management">
                <span v-if="item.is_sample === true">
                    <strong><small>&nbsp;({{ __('order.product_sample') }})</small></strong>
                </span>
                <span v-else>
                    <strong><small>&nbsp;({{ __('order.product_ecomemerce') }})</small></strong>
                </span>
            </span>
        </span>
        <span class="text-info ps-1">({{ item.formatted_price }})</span>
    </span>
</template>

<script>
export default {
    props: {
        item: {
            type: Object,
            default: () => {},
            required: true,
        },
    },
}
</script>
