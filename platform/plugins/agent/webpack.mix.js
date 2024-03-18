let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/agent-order-create.js', dist + '/js')
    .js(source + '/resources/assets/js/agent-product.js', dist + '/js')
    .js(source + '/resources/assets/js/agent-warehouse.js', dist + '/js')
    .js(source + '/resources/assets/js/report.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-agent-receipt.js', dist + '/js')
    .js(source + '/resources/assets/js/discount.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-agent-receipt.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-agent-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-agent-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-mobile.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/agent-order-create.js', source + '/public/js')
        .copy(dist + '/js/agent-product.js', source + '/public/js')
        .copy(dist + '/js/agent-warehouse.js', source + '/public/js')
        .copy(dist + '/js/report.js', source + '/public/js')
        .copy(dist + '/js/proposal-agent-receipt.js', source + '/public/js')
        .copy(dist + '/js/discount.js', source + '/public/js')
        .copy(dist + '/js/approve-agent-receipt.js', source + '/public/js')
        .copy(dist + '/js/proposal-agent-issue.js', source + '/public/js')
        .copy(dist + '/js/approve-agent-issue.js', source + '/public/js')
        .copy(dist + '/js/scan-mobile.js', source + '/public/js')
}

// const scripts = [
//     'edit-product.js',
//     'edit-product-collection.js',
//     'global-option.js',
//     'product-option.js',
//     'ecommerce-product-attributes.js',
//     'change-product-swatches.js',
//     'change-product-options.js',
//     'currencies.js',
//     'review.js',
//     'shipping.js',
//     'utilities.js',
//     'payment-method.js',
//     'customer.js',
//     'setting.js',
//     'order.js',
//     'order-incomplete.js',
//     'shipment.js',
//     'store-locator.js',
//     'report.js',
//     'dashboard-widgets.js',
//     'avatar.js',
//     'flash-sale.js',
//     'bulk-import.js',
//     'export.js',
//     'address.js',
//     'tax.js',
//     'invoice.js',
// ];

// scripts.forEach(item => {
//     mix.js(source + '/resources/assets/js/' + item, dist + '/js');
// });

// if (mix.inProduction()) {
//     scripts.forEach(item => {
//         mix.copy(dist + '/js/' + item, source + '/public/js');
//     });
// }

// const styles = [
//     'ecommerce.scss',
//     'ecommerce-product-attributes.scss',
//     'currencies.scss',
//     'review.scss',
//     'customer.scss',
//     'front-theme.scss',
//     'front-theme-rtl.scss',
//     'report.scss',
//     'order-return.scss',
//     'customer-admin.scss',
//     'widget.scss',
//     'front-review.scss',
//     'front-faq.scss',
// ];

// styles.forEach(item => {
//     mix.sass(source + '/resources/assets/sass/' + item, dist + '/css');
// });

// if (mix.inProduction()) {
//     styles.forEach(item => {
//         mix.copy(dist + '/css/' + item.replace('.scss', '.css'), source + '/public/css');
//     });
// }
