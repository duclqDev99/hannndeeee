import AddUserAgent from './components/add-agent-user.vue'
import CreateProduct from './components/CreateProductComponent.vue'


if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('add-agent-user', AddUserAgent)
            app.component('agent-create-product', CreateProduct)
        },
    })
}


