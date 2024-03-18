$(document).ready(function () {
    // 👇️ using the function
    waitForElementToExist('.view-payment-for-customer').then(element => {
        $(document).find('.view-payment-for-customer').on('click', function () {
            var win = window.open(element.querySelector('span[data-url*="http"]').getAttribute('data-url'), '_blank');
            win.focus();
        });
    });
})

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
