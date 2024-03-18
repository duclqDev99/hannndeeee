let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/add-hub-user.js', dist + '/js')
    .js(source + '/resources/assets/js/add-agent-user.js', dist + '/js')
    .js(source + '/resources/assets/js/department-hub.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-hub-receipt.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-hub-receipt-form.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-hub-issue-form.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-product-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/agent-warehouse.js', dist + '/js')
    .js(source + '/resources/assets/js/detail-product.js', dist + '/js')
    .js(source + '/resources/assets/js/batch.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-product-receipt.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/add-hub-user.js', source + '/public/js')
        .copy(dist + '/js/add-agent-user.js', source + '/public/js')
        .copy(dist + '/js/department-hub.js', source + '/public/js')
        .copy(dist + '/js/proposal-hub-receipt.js', source + '/public/js')
        .copy(dist + '/js/proposal-hub-receipt-form.js', source + '/public/js')
        .copy(dist + '/js/proposal-hub-issue-form.js', source + '/public/js')
        .copy(dist + '/js/approve-product-issue.js', source + '/public/js')
        .copy(dist + '/js/agent-warehouse.js', source + '/public/js')
        .copy(dist + '/js/detail-product.js', source + '/public/js')
        .copy(dist + '/js/batch.js', source + '/public/js')
        .copy(dist + '/js/approve-product-receipt.js', source + '/public/js')
}
