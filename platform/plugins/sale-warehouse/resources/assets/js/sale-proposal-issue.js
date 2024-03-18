import ProposalIssue from './components/SaleProposalIssueComponent.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('sale-proposal-issue', ProposalIssue)
        },
    })
}


