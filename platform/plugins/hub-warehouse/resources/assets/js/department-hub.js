$(document).ready(function () {

    function toggleElements(selectedValues) {
        const elements = {
            'retail_hub': '#main-order',
            'retail_warehouse_product': '#warehouse-user',
            'agent': '#agent-user',
            'show_room': '#showroom-user',
            'sale_warehouse': '#sale-user',
        };
        Object.keys(elements).forEach(key => {
            const element = $(elements[key]);
            if (selectedValues.includes(key)) {
                element.show().closest('.meta-boxes').show();
            } else {
                element.hide().closest('.meta-boxes').hide();
            }
        });
    }

    function handleSelectChange() {
        var selectedValues = $(this).val() || [];
        toggleElements(selectedValues);
    }

    $('select[name="department_id[][]"]').change(handleSelectChange).trigger('change');
});

function waitForElementToExist(selector) {
    return new Promise(resolve => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(() => {
            if (document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            subtree: true,
            childList: true,
        });
    });
}
const selectors = ['#agent-user', '#main-order', '#warehouse-user', '#showroom-user', '#sale-user'];

selectors.forEach(selector => {
    waitForElementToExist(selector).then(element => {
        $(element).hide();
        $(element).closest('.meta-boxes').hide();
    });
});
