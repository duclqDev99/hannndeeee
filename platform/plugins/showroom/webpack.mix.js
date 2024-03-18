let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .css(source + '/resources/assets/css/exchange-goods.css', dist + '/css')
    .js(source + '/resources/assets/js/showroom-order-create.js', dist + '/js')
    .js(source + '/resources/assets/js/showroom-product.js', dist + '/js')
    .js(source + '/resources/assets/js/showroom-warehouse.js', dist + '/js')
    .js(source + '/resources/assets/js/report.js', dist + '/js')
    .js(source + '/resources/assets/js/order.js', dist + '/js')
    .js(source + '/resources/assets/js/showroom-user.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-showroom-receipt.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-showroom-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-showroom-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/payment-customer.js', dist + '/js')
    .js(source + '/resources/assets/js/check-qr-showroom.js', dist + '/js')
    .js(source + '/resources/assets/js/exchange-goods.js', dist + '/js')
    .js(source + '/resources/assets/js/scanner.js', dist + '/js')

    .js(source + '/resources/assets/js/confirm-return-product.js', dist + '/js')
    .js(source + '/resources/assets/js/showroom-detail-product.js', dist + '/js')
    
if (mix.inProduction()) {
    mix
        .copy(dist + '/css/exchange-goods.css', source + '/public/css')
        .copy(dist + '/js/showroom-order-create.js', source + '/public/js')
        .copy(dist + '/js/showroom-product.js', source + '/public/js')
        .copy(dist + '/js/showroom-warehouse.js', source + '/public/js')
        .copy(dist + '/js/report.js', source + '/public/js')
        .copy(dist + '/js/order.js', source + '/public/js')
        .copy(dist + '/js/showroom-user.js', source + '/public/js')
        .copy(dist + '/js/proposal-showroom-receipt.js', source + '/public/js')
        .copy(dist + '/js/proposal-showroom-issue.js', source + '/public/js')
        .copy(dist + '/js/approve-showroom-issue.js', source + '/public/js')
        .copy(dist + '/js/payment-customer.js', source + '/public/js')
        .copy(dist + '/js/check-qr-showroom.js', source + '/public/js')
        .copy(dist + '/js/exchange-goods.js', source + '/public/js')
        .copy(dist + '/js/scanner.js', source + '/public/js')
        .copy(dist + '/js/confirm-return-product.js', source + '/public/js')
        .copy(dist + '/js/showroom-detail-product.js', source + '/public/js')
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
