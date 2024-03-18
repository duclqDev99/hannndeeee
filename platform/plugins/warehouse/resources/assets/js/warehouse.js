class WidgetManagement {
    init() {
        let listWidgets = [
            {
                name: 'wrap-widgets',
                pull: 'clone',
                put: false,
            },
        ]

        $.each($('.sidebar-item'), () => {
            listWidgets.push({ name: 'wrap-widgets', pull: true, put: true })
        })

        listWidgets.forEach((groupOpts, i) => {
            Sortable.create(document.getElementById('wrap-widget-' + (i + 1)), {
                sort: i !== 0,
                group: groupOpts,
                delay: 0, // time in milliseconds to define when the sorting should start
                disabled: false, // Disables the sortable if set to true.
                store: null, // @see Store
                animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                handle: '.widget-handle',
                ghostClass: 'sortable-ghost', // Class name for the drop placeholder
                chosenClass: 'sortable-chosen', // Class name for the chosen item
                dataIdAttr: 'data-id',

                forceFallback: false, // ignore the HTML5 DnD behaviour and force the fallback to kick in
                fallbackClass: 'sortable-fallback', // Class name for the cloned DOM Element when using forceFallback
                fallbackOnBody: false, // Appends the cloned DOM Element into the Document's Body

                scroll: true, // or HTMLElement
                scrollSensitivity: 30, // px, how near the mouse must be to an edge to start scrolling.
                scrollSpeed: 10, // px

                // Changed sorting within list
                onUpdate: (evt) => {
                    if (evt.from !== evt.to) {
                        saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    saveWidget($(evt.item).closest('.sidebar-item'))
                },
                onAdd: (evt) => {
                    if (evt.from !== evt.to) {
                        saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    saveWidget($(evt.item).closest('.sidebar-item'))

                    const inputRequestPro = evt.item.querySelector('.group__request-product');
                    const inputRequestQuan = evt.item.querySelector('.group__request-quantity');
                    const inputRequestUnit = evt.item.querySelector('.group__request-unit');

                    let dataBatch = evt.to.dataset.batch;

                    inputRequestPro.setAttribute('name', `batch[${dataBatch}][product_id][]`)
                    inputRequestQuan.setAttribute('name', `batch[${dataBatch}][quantity][]`)
                    inputRequestUnit.setAttribute('name', `batch[${dataBatch}][unit][]`)
                },
            })
        })

        let widgetWrap = $('#wrap-widgets')
        widgetWrap.on('click', '.widget-control-delete', (event) => {
            event.preventDefault()
            let _self = $(event.currentTarget)

            let widget = _self.closest('li')
            _self.addClass('button-loading')

            widget.remove()
        })

        widgetWrap.on('click', '#added-widget .widget-handle', (event) => {
            let _self = $(event.currentTarget)
            _self.closest('li').find('.widget-content').slideToggle(300)
            _self.find('.fa').toggleClass('fa-caret-up')
            _self.find('.fa').toggleClass('fa-caret-down')
        })

        widgetWrap.on('click', '#added-widget .sidebar-header', (event) => {
            let _self = $(event.currentTarget)
            _self.closest('.sidebar-area').find('> ul').slideToggle(300)
            _self.find('.fa').toggleClass('fa-caret-up')
            _self.find('.fa').toggleClass('fa-caret-down')
        })

        Botble.callScroll($('.list-page-select-widget'));
    }
}

let saveWidget = (parentElement) => {
    if (parentElement.length > 0) {
        let items = []
        $.each(parentElement.find('li[data-id]'), (index, widget) => {
            items.push($(widget).find('form').serialize())
        })

        Botble.showNotice('success', BotbleVariables.languages.notices_msg.success_request)
    }
}

$(document).ready(() => {
    new WidgetManagement().init();

    let selectBranch = $('.select-branch');
    let inputBranch = $('#input__branch');

    inputBranch.val(selectBranch.val());

    selectBranch.on('change', (event) => {
        event.preventDefault();

        let selectBranch = $('.select-branch');
        let inputBranch = $('#input__branch');
    
        inputBranch.val(selectBranch.val());
    })

    //Add batch
    const btnInsertBatch = $('#btn__insert-batch');
    btnInsertBatch.on('click', (event) => {
        event.preventDefault();

        const batchList = document.querySelector('.batch__list');

        const allBatch = batchList.querySelectorAll('.sidebar-item');
        
        let divInsert = `
        <div class="sidebar-item">
            <div class="sidebar-area">
                <div class="sidebar-header">
                    <h3 class="text-break position-relative pe-3">
                        Lô hàng ${allBatch.length+1}
                        <span class="position-absolute end-0 top-0 me-1">
                            <i class="fa fa-caret-down"></i>
                        </span>
                    </h3>
                    <p>Thêm mặt hàng</p>
                </div>
                <ul id="wrap-widget-${allBatch.length+2}" data-batch="${allBatch.length+1}">
                </ul>
            </div>
        </div>
        `;

        batchList.insertAdjacentHTML('beforeend', divInsert);

        Sortable.create(document.getElementById('wrap-widget-' + (allBatch.length+2)), {
            group: 'wrap-widgets',
            delay: 0, // time in milliseconds to define when the sorting should start
            disabled: false, // Disables the sortable if set to true.
            store: null, // @see Store
            animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
            handle: '.widget-handle',
            ghostClass: 'sortable-ghost', // Class name for the drop placeholder
            chosenClass: 'sortable-chosen', // Class name for the chosen item
            dataIdAttr: 'data-id',

            forceFallback: false, // ignore the HTML5 DnD behaviour and force the fallback to kick in
            fallbackClass: 'sortable-fallback', // Class name for the cloned DOM Element when using forceFallback
            fallbackOnBody: false, // Appends the cloned DOM Element into the Document's Body

            scroll: true, // or HTMLElement
            scrollSensitivity: 30, // px, how near the mouse must be to an edge to start scrolling.
            scrollSpeed: 10, // px

            // Changed sorting within list
            onUpdate: (evt) => {
                if (evt.from !== evt.to) {
                    saveWidget($(evt.from).closest('.sidebar-item'))
                }
                saveWidget($(evt.item).closest('.sidebar-item'))
            },
            onAdd: (evt) => {
                if (evt.from !== evt.to) {
                    saveWidget($(evt.from).closest('.sidebar-item'))
                }
                saveWidget($(evt.item).closest('.sidebar-item'))

                const inputRequestPro = evt.item.querySelector('.group__request-product');
                const inputRequestQuan = evt.item.querySelector('.group__request-quantity');
                const inputRequestUnit = evt.item.querySelector('.group__request-unit');

                let dataBatch = evt.to.dataset.batch;

                inputRequestPro.setAttribute('name', `batch[${dataBatch}][product_id][]`)
                inputRequestQuan.setAttribute('name', `batch[${dataBatch}][quantity][]`)
                inputRequestUnit.setAttribute('name', `batch[${dataBatch}][unit][]`)
            },
        })
    });

    let listItemProduct = document.querySelector('ul#wrap-widget-1');
    const itemProduct = listItemProduct?.querySelectorAll('li');

    const widgetSearch = $('#widget__search');
    widgetSearch.on('keyup', (event) => {
        let _self = $(event.currentTarget)

        itemProduct.forEach(item => {
            let nameProduct = item.querySelector('.widget-name').textContent;
            let productCode = item.dataset.code;

            if(nameProduct.includes(_self.val()) || productCode.includes(_self.val()))
            {
                item.style.display = 'block'
            }else{
                item.style.display = 'none';
            }
        })
    })

})
