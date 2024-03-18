import HubWarehouse from './components/ProposalIssueWarehouseComponent.vue'
import CreateProduct from './components/CreateProductComponent.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('hub-warehouse', HubWarehouse)
            app.component('warehouse-create-product', CreateProduct)

        },
    })
}


