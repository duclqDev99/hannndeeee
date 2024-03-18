import AddAnalysisProduct from './components/add-analysis-product.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('add-analysis-product', AddAnalysisProduct)
        },
    })
}
