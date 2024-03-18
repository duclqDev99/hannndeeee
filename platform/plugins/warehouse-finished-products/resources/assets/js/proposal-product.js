
$(document).ready(function () {
    const formMTproposal = $('form#botble-warehouse-forms-proposal-receipt-products-form');
    const formMTproposalNode = document.querySelector('form#botble-warehouse-forms-proposal-receipt-products-form');

    let ARR_STOCK;
    let OPTION_SELECT = [];
    let arrAllProduct;
    let arrAllProductOdd;
    let arrAllProductParent;
    let rowNumber = 1;
    let arrAllBatch;

    let ARR_PRODUCT_STOCK = [];//Mảng chứa tất cả id sản phẩm của kho được chọn
    let ARR_PRODUCT_SELECT_STOCK = [];//Mảng chứa id sản phẩm của các select đang được chọn ở phần nhập từ kho đến kho

    let ARR_PRODUCT_STOCK_ODD = [];
    let ARR_PRODUCT_SELECT_STOCK_ODD = [];

    let ARR_PRODUCT_INVENTORY = [];
    let ARR_PRODUCT_SELECT_INVENTORY = [];

    if (formMTproposal.is(':visible')) {
        const proposal_element = formMTproposalNode.querySelector('input#proposal_id');
        //Table in supplier and stock
        const tabPane = formMTproposalNode.querySelectorAll('.tab-content .tab-pane');
        const navLinks = formMTproposalNode.querySelectorAll('.nav.nav-tabs .nav-item');

        const inputValueTypePost = formMTproposalNode.querySelector('input[name=type_proposal]');

        let typeValueStock = 'stock';
        let typeValueStockOdd = 'stock-odd';
        let typeValueInventory = 'inventory';

        let urlApi = formMTproposal.find('input#url_api').val();
        arrAllProduct = getListProduct(urlApi);
        arrAllProductParent = getListProductParent(urlApi);
        arrAllBatch = getListBatch(urlApi);

        let arrStock = getListStock(urlApi);
        ARR_STOCK = arrAllBatch;

        navLinks.forEach((navItem, index) => {
            navItem.addEventListener('click', function(){
                if(index === 0)
                {
                    inputValueTypePost.value = typeValueStock;

                    const radioBtn = navItem.querySelector('input#radioStock');
                    radioBtn.checked = true;
                    getCodeWhenChangeOption(tabPane[0])
                }else if(index === 1){
                    inputValueTypePost.value = typeValueStockOdd;

                    const radioBtn = navItem.querySelector('input#radioStockOdd');
                    radioBtn.checked = true;
                    getCodeWhenChangeOption(tabPane[1])
                }else if(index === 2){
                    inputValueTypePost.value = typeValueInventory;

                    const radioBtn = navItem.querySelector('input#radioInventory');
                    radioBtn.checked = true;
                    getCodeWhenChangeOption(tabPane[2])
                }
            })
        })

        tabPane.forEach((table, index) => {
            let elTable = $(table);

            let parameter = '';

            const selectBranch = formMTproposal.find('select[name=warehouse_id]');
            let branchId = selectBranch.val();

            if(index === 0)
            {
                const selectBranchOfStock = elTable.find('select.select__product');
                parameter = 'stock';

                const selectStock = elTable.find('select.stock_detination');

                selectStock.html(setOptionChooseStock(arrStock, branchId));

                selectStock.select2();

                try{
                    for (const key in arrAllBatch) {
                        if(selectStock.val() == key)
                        {
                            if(Object.keys(arrAllBatch[key]).length > 0)
                            {
                                populateSelectOptions(elTable, selectBranchOfStock, arrAllBatch[key], parameter, false);
                                //Add list id của các sản phẩm của kho hiện tại vào mảng
                                getValueAndAddToArray(arrAllProductParent, 'id', ARR_PRODUCT_STOCK);
                            }
                        }
                    }
                }catch(err){
                    console.log(err);
                }

                //On change stock level 1
                selectBranch.on('change', (evt) => {
                    let branchId = selectBranch.val();
                    selectStock.html(setOptionChooseStock(arrStock, branchId));

                    try{
                        for (const key in arrAllBatch) {
                            if(selectStock.val() == key)
                            {
                                if(Object.keys(arrAllBatch[key]).length > 0)
                                {
                                    ARR_PRODUCT_SELECT_STOCK = [];
                                    populateSelectOptions(elTable, selectBranchOfStock, arrAllBatch[key], parameter, false);

                                    //Add list id của các sản phẩm của kho hiện tại vào mảng
                                    getValueAndAddToArray(arrAllProductParent, 'id', ARR_PRODUCT_STOCK);
                                }
                            }
                        }
                    }catch(err){
                        console.log(err);
                    }
                    //Thay đổi các option trong từng select sản phẩm khi thay đổi kho xuất
                    // changeAllOptionChild(table, selectBranchOfStock, selectStock.val(), arrAllBatch[selectStock.val()], parameter)

                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK);//Gọi hàm kiểm tra nguyên liệu được chọn

                    setCodeWhenChangeStock(table, arrAllBatch, selectStock.val(),ARR_PRODUCT_SELECT_STOCK);

                    resetRowTable(table)//Reset các hàng của table khi thay đổi option chọn kho
                })

                //On change stock level 2
                selectStock.on('change', (evt) => {
                    let branchId = selectStock.val();

                    try{
                        for (const key in arrAllBatch) {
                            if(branchId == key)
                            {
                                if(Object.keys(arrAllBatch[key]).length > 0)
                                {
                                    ARR_PRODUCT_SELECT_STOCK = [];
                                    populateSelectOptions(elTable, selectBranchOfStock, arrAllBatch[key], parameter, false);

                                    //Add list id của các sản phẩm của kho hiện tại vào mảng
                                    getValueAndAddToArray(arrAllProductParent, 'id', ARR_PRODUCT_STOCK);
                                }
                            }
                            
                        }
                    }catch(err){
                        console.log(err);
                    }
                    changeAllOptionChild(table, selectBranchOfStock, branchId, arrAllBatch, parameter)
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK);//Gọi hàm kiểm tra nguyên liệu được chọn

                    setCodeWhenChangeStock(table, arrAllBatch, branchId,ARR_PRODUCT_SELECT_STOCK);

                    resetRowTable(table)//Reset các hàng của table khi thay đổi option chọn kho
                })

                setCodeWhenChangeStock(table, arrAllBatch, selectStock.val(),ARR_PRODUCT_SELECT_STOCK);
                setOptionProductWhenChange(table, ARR_PRODUCT_STOCK);//Gọi hàm kiểm tra nguyên liệu được chọn
                const inputCheckQtyProduct = table.querySelector('input.input__quantity_product');

                const inputQty = table.querySelector('input.input__quantity');
                addEventForElement(inputCheckQtyProduct, selectStock, inputQty)
            }else if(index === 1){
                parameter = 'stock-odd';

                const selectBranchOfStock = elTable.find('select.select__product');

                const selectStock = elTable.find('select.stock_odd_detination');

                selectStock.html(setOptionChooseStock(arrStock, branchId));

                selectStock.select2();

                arrStock.forEach(stock => {
                    if(stock.products.length > 0)
                    {
                        if(stock.id == selectStock.val())
                        {
                            arrAllProductOdd = stock.products;
                            populateSelectOptions(elTable, selectBranchOfStock, stock.products, parameter, true);

                            //Add list id của các sản phẩm của kho hiện tại vào mảng
                            getValueAndAddToArray(stock.products, 'id', ARR_PRODUCT_STOCK_ODD);

                        }
                    }
                })

                //On change stock level 1
                selectBranch.on('change', (evt) => {
                    let branchId = selectBranch.val();
                    selectStock.html(setOptionChooseStock(arrStock, branchId));

                    arrStock.forEach(stock => {
                        if(stock.id == selectStock.val())
                        {
                            arrAllProductOdd = stock.products;
                            ARR_PRODUCT_SELECT_STOCK_ODD = [];
                            populateSelectOptions(elTable, selectBranchOfStock, stock.products, parameter, true);

                            //Add list id của các sản phẩm của kho hiện tại vào mảng
                            getValueAndAddToArray(stock.products, 'id', ARR_PRODUCT_STOCK_ODD);
                        }
                    })
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK_ODD);//Gọi hàm kiểm tra nguyên liệu được chọn

                    setMaterialWhenChangeStockOdd(table, arrAllProductOdd, selectStock.val(),ARR_PRODUCT_SELECT_STOCK_ODD);

                    resetRowTable(table)//Reset các hàng của table khi thay đổi option chọn kho
                })

                //On change stock level 2
                selectStock.on('change', (evt) => {
                    let branchId = selectStock.val();
                    
                    arrStock.forEach(stock => {
                        if(stock.id == branchId)
                        {
                            arrAllProductOdd = stock.products;
                            ARR_PRODUCT_SELECT_STOCK_ODD = [];
                            populateSelectOptions(elTable, selectBranchOfStock, stock.products, parameter,true);

                            //Add list id của các sản phẩm của kho hiện tại vào mảng
                            getValueAndAddToArray(stock.products, 'id', ARR_PRODUCT_STOCK_ODD);
                        }
                    })
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK_ODD);//Gọi hàm kiểm tra nguyên liệu được chọn

                    setMaterialWhenChangeStockOdd(table, arrAllProductOdd, selectStock.val(),ARR_PRODUCT_SELECT_STOCK_ODD);

                    resetRowTable(table)//Reset các hàng của table khi thay đổi option chọn kho
                })

                setMaterialWhenChangeStockOdd(table, arrAllProduct, selectStock.val(),ARR_PRODUCT_SELECT_STOCK_ODD);
                setOptionProductWhenChange(table, ARR_PRODUCT_STOCK_ODD);//Gọi hàm kiểm tra nguyên liệu được chọn
                const inputCheckQtyProduct = table.querySelector('input.input__quantity_product');

                const inputQty = table.querySelector('input.input__quantity');
                addEventForElement(inputCheckQtyProduct, selectStock, inputQty)
            }else if(index === 2){
                parameter = 'inventory';
                const selectBranchOfStock = elTable.find('select.select__product');

                populateSelectOptions(elTable, selectBranchOfStock, arrAllProduct, parameter, true);

                //Add các id của các sản phẩm vào mảng
                getValueAndAddToArray(arrAllProduct, 'id', ARR_PRODUCT_INVENTORY);

                setOptionProductWhenChange(table, ARR_PRODUCT_INVENTORY);//Gọi hàm kiểm tra nguyên liệu được chọn
                setMaterialWhenChangeInventory(table, ARR_PRODUCT_SELECT_INVENTORY)
            }

            $(elTable).find("#add_row").click(function () {
                if($(elTable).find("#addr0 select.select__product").hasClass("select2-hidden-accessible")){
                    $(elTable).find("#addr0 select.select__product").select2('destroy');
                }

                const newRow = $(elTable).find("#addr0").clone();
                // $(elTable).find("#addr0").find('select.select__product').select2(OPTION_SELECT);

                const newRowId = 'addr' + rowNumber;

                const proposal_element = formMTproposalNode.querySelector('input#proposal_id');

                let so_thu_tu = rowNumber + 1;

                let proposal_id = proposal_element.value;
                if(proposal_id != 0){
                    so_thu_tu = rowNumber;
                }

                newRow.html(newRow.html()).find('td:first-child').html(so_thu_tu);
                newRow.html(newRow.html()).find('select.select__product').prop('name', `${parameter}[product][${rowNumber+1}][name]`);
                newRow.html(newRow.html()).find('input.input__quantity').prop('name', `${parameter}[product][${rowNumber+1}][quantity]`);
                newRow.html(newRow.html()).find('input.input__sku').prop('name', `${parameter}[product][${rowNumber+1}][sku]`);
                newRow.html(newRow.html()).find('input.value_id__product').prop('name', `${parameter}[product][${rowNumber+1}][product_id]`);
                newRow.attr('id', newRowId);

                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                //Remove option product has selected
                newRow.html(newRow.html()).find('select.select__product option').each( function() {
                    ARR_PRODUCT_SELECT_STOCK.forEach(item => {
                        if($(this).attr('data-id') == item)
                        {
                            $(this).prop('disabled', true);
                        }
                    })
                });
                ARR_PRODUCT_SELECT_STOCK.push(newRow.html(newRow.html()).find('select.select__product').find(':selected').attr('data-id'));//Add new value

                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){

                    getCodeWhenChangeOption(newRow.get(0))
                })
                getCodeWhenChangeOption(newRow.get(0));
                
                $(elTable).find('#tab_logic').append(newRow);
                
                table.querySelector(`tr#${newRowId}`).style.display = 'table-row';
                setCodeWhenChangeStock(table, ARR_STOCK, formMTproposal.find('.tab-content select.stock_detination').val(),ARR_PRODUCT_SELECT_STOCK);

                const inputCheckQtyProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity_product');
                const selectProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' select.select__product');
                const inputQty = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity');

                const itemRow = table.querySelector(`#addr${rowNumber}`)

                let options = setOptionSearchWithCode([])

                // Kiểm tra xem phần tử có kết nối với Select2 hay không
                if (newRow.find('select.select__product').attr('data-select2-id') !== undefined) {
                    // console.log('Phần tử đã được kết nối với Select2');
                } else {
                    newRow.find('select.select__product').select2(options)
                }

                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                allSelect[0].style.width = '100%';
                if(allSelect?.length > 1)
                {
                    allSelect[1].style.display = 'none';
                }

                if(index === 0)
                {
                    addEventForElement(inputCheckQtyProduct, selectProduct, inputQty)
                }else if(index === 1){
                    const selectStock = elTable.find('select.stock_odd_detination');
                    arrAllProductOdd = stock.products;
                    setMaterialWhenChangeStockOdd(table, stock.products, selectStock.val(),ARR_PRODUCT_SELECT_STOCK_ODD);
                }else if(index === 2){
                    setMaterialWhenChangeInventory(table, ARR_PRODUCT_SELECT_INVENTORY)
                }
                rowNumber++;

                $(elTable).find("#addr0 select.select__product").select2(options);

                updateRowNumber(table);//Cập nhật số thứ tự các hàng của bảng

                //Gọi hàm set các option của từng select sản phẩm
                let isEdit = false;
                if(proposal_element && proposal_element.value != 0){
                    isEdit = true;
                }

                //Kiểm tra đang nhập kho từ kho khác hay nhà cung cấp
                if(parameter == 'stock'){
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK);
                }else if(parameter == 'stock-odd'){
                    // getCodeWhenChangeOption(newRow.get(0));
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK_ODD);
                }else if(parameter == 'inventory'){
                    setOptionProductWhenChange(table, ARR_PRODUCT_INVENTORY);
                }
            });

            $(elTable).on("click", ".delete-row", function () {
                let valueToRemove = $(this).closest('tr').find('select.select__product :selected').attr('data-id');
                // Tìm index của giá trị cần xoá trong mảng
                let indexToRemove = ARR_PRODUCT_SELECT_STOCK.indexOf(valueToRemove);

                // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                if (indexToRemove !== -1) {
                    
                     //Kiểm tra đang nhập kho từ kho khác hay nhà cung cấp
                    if(parameter == 'stock'){
                        // Xoá giá trị tại indexToRemove
                        ARR_PRODUCT_SELECT_STOCK.splice(indexToRemove, 1);
                    }else if(parameter == 'stock-odd'){
                        // Xoá giá trị tại indexToRemove
                        ARR_PRODUCT_SELECT_STOCK_ODD.splice(indexToRemove, 1);
                    }else if(parameter == 'inventory'){
                        ARR_PRODUCT_INVENTORY.splice(indexToRemove, 1);
                    }
                }

                $(elTable).find(this).closest('tr').remove();

                updateRowNumber(table);//Cập nhật số thứ tự các hàng của bảng

                //Gọi hàm set các option của từng select sản phẩm
                if(parameter == 'stock'){
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK);
                }else if(parameter == 'stock-odd'){
                    setOptionProductWhenChange(table, ARR_PRODUCT_STOCK_ODD);
                }else if(parameter == 'inventory'){
                    setOptionProductWhenChange(table, ARR_PRODUCT_INVENTORY);
                }
            });
        })

        //Validate for request
        const btnSubmitSaveExit = formMTproposalNode.querySelector('.btn-list button[value=save]');
        const btnSubmitSave = formMTproposalNode.querySelector('.btn-list button[value=apply]');

        let currentModelProposal = null;

        //Show option when edit
        if(proposal_element)
        {
            let proposal_id = proposal_element.value;
            if(proposal_id != 0)
            {
                currentModelProposal = getInfoProposalById(urlApi, proposal_id);
                //Chọn tab pane theo model đơn đề xuất
                if(currentModelProposal.is_warehouse == 'warehouse'){
                    const tableStockByStock = tabPane[0];
                    if(tableStockByStock)
                    {
                        const selectWarehouseDetination = tableStockByStock.querySelector('select.stock_detination');
                        // $(selectWarehouseDetination).val(currentModelProposal.wh_departure_id).trigger('change')
                        selectWarehouseDetination.value = currentModelProposal.wh_departure_id;

                        //Set body table
                        const tbodyTable = tableStockByStock.querySelector('table.table__delivery tbody');

                        if(tbodyTable)
                        {
                            //Add content
                            let parameter = 'stock';

                            rowNumber = 1;
                            currentModelProposal.proposal_detail.forEach((product, index) => {
                                const newRow = $($(tableStockByStock)).find("#addr0").clone();
                                const newRowId = 'addr' + (rowNumber);
                                const curTable = $($(tableStockByStock));

                                newRow.find('td:first-child').html(rowNumber);
                                newRow.find('select.select__product').prop('name', `${parameter}[product][${rowNumber+1}][name]`);
                                newRow.find('input.input__quantity').prop('name', `${parameter}[product][${rowNumber+1}][quantity]`);
                                newRow.find('input.input__sku').prop('name', `${parameter}[product][${rowNumber+1}][sku]`);
                                newRow.find('input.input__price').prop('name', `${parameter}[product][${rowNumber+1}][price]`);
                                newRow.find('input.value_id__product').prop('name', `${parameter}[product][${rowNumber+1}][product_id]`);
                                newRow.attr('id', newRowId);

                                newRow.find('select.select__product option').each(function(){
                                    if($(this).attr('data-id') == product.product_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.input__quantity').val(product.quantity);
                                        newRow.find('input.input__quantity_product').val($(this).attr('data-qty'));
                                    }
                                })

                                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                                getCodeWhenChangeOption(newRow.get(0));

                                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){

                                    getCodeWhenChangeOption(newRow.get(0))
                                })
                                curTable.find('#tab_logic').append(newRow);

                                setCodeWhenChangeStock(tableStockByStock, ARR_STOCK, formMTproposal.find('.tab-content select.stock_detination').val(),ARR_PRODUCT_SELECT_STOCK);

                                const inputCheckQtyProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity_product');
                                const selectProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' select.select__product');
                                const inputQty = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity');

                                const itemRow = tableStockByStock.querySelector(`#addr${rowNumber}`)

                                $($(tableStockByStock)).find(`#addr${rowNumber}`).find('select.select__product').select2()

                                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                                allSelect[0].style.width = '100%';
                                if(allSelect?.length > 1)
                                {
                                    allSelect[1].style.display = 'none';
                                }

                                addEventForElement(inputCheckQtyProduct, selectProduct, inputQty)
                                rowNumber++;

                            });
                            tbodyTable.querySelector('tr#addr0 select.select__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__sku').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.value_id__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__quantity').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0').style.display = 'none';

                            updateRowNumber(tableStockByStock);//Cập nhật số thứ tự các hàng của bảng
                            
                            setOptionProductWhenChange(tableStockByStock, ARR_PRODUCT_STOCK, true);
                        }
                    }
                }else if(currentModelProposal.is_warehouse == 'warehouse-odd'){
                    // Tạo và kích hoạt sự kiện click
                    var event = new Event('click');
                    navLinks[1].dispatchEvent(event);

                    // Thay đổi thuộc tính "aria-expanded" để mở collapse
                    navLinks[0].querySelector('button').setAttribute('aria-expanded', 'false');
                    navLinks[1].querySelector('button').setAttribute('aria-expanded', 'true');

                    navLinks[0].querySelector('button').setAttribute('aria-selected', 'false');
                    navLinks[1].querySelector('button').setAttribute('aria-selected', 'true');

                    navLinks[0].querySelector('button').classList.remove('active');
                    navLinks[1].querySelector('button').classList.add('active');

                    // Thêm class "show" để hiển thị nội dung của collapse
                    tabPane[0].classList.remove('show');
                    tabPane[0].classList.remove('active');
                    tabPane[1].classList.add('active');
                    tabPane[1].classList.add('show');

                    const tableStockByStock = tabPane[1];
                    if(tableStockByStock)
                    {
                        //Add content
                        let parameter = 'stock-odd';

                        const selectWarehouseDetination = tableStockByStock.querySelector('select.stock_odd_detination');
                        selectWarehouseDetination.value = currentModelProposal.wh_departure_id;

                        //Set body table
                        const tbodyTable = tableStockByStock.querySelector('table.table__delivery tbody');

                        if(tbodyTable)
                        {
                            rowNumber = 1;
                            currentModelProposal.proposal_detail.forEach((product, index) => {
                                const newRow = $($(tableStockByStock)).find("#addr0").clone();
                                const newRowId = 'addr' + (rowNumber);
                                const curTable = $($(tableStockByStock));

                                newRow.find('td:first-child').html(rowNumber);
                                newRow.find('select.select__product').prop('name', `${parameter}[product][${rowNumber+1}][name]`);
                                newRow.find('input.input__quantity').prop('name', `${parameter}[product][${rowNumber+1}][quantity]`);
                                newRow.find('input.input__sku').prop('name', `${parameter}[product][${rowNumber+1}][sku]`);
                                newRow.find('input.input__price').prop('name', `${parameter}[product][${rowNumber+1}][price]`);
                                newRow.find('input.value_id__product').prop('name', `${parameter}[product][${rowNumber+1}][product_id]`);
                                newRow.attr('id', newRowId);

                                newRow.find('select.select__product option').each(function(){
                                    if($(this).attr('data-id') == product.product_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.input__quantity').val(product.quantity);
                                        newRow.find('input.input__quantity_product').val($(this).attr('data-qty'));
                                    }
                                })

                                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                                curTable.find('#tab_logic').append(newRow);

                                // setCodeWhenChangeStock(tableStockByStock, ARR_STOCK, formMTproposal.find('.tab-content select.stock_odd_detination').val(),ARR_PRODUCT_SELECT_STOCK_ODD);

                                const inputCheckQtyProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity_product');
                                const selectProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' select.select__product');
                                const inputQty = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity');

                                const itemRow = tableStockByStock.querySelector(`#addr${rowNumber}`)

                                $($(tableStockByStock)).find(`#addr${rowNumber}`).find('select.select__product').select2()

                                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                                allSelect[0].style.width = '100%';
                                if(allSelect?.length > 1)
                                {
                                    allSelect[1].style.display = 'none';
                                }

                                addEventForElement(inputCheckQtyProduct, selectProduct, inputQty)
                                rowNumber++;

                            });
                            tbodyTable.querySelector('tr#addr0 select.select__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__sku').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.value_id__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__quantity').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0').style.display = 'none';

                            updateRowNumber(tableStockByStock);//Cập nhật số thứ tự các hàng của bảng

                            setOptionProductWhenChange(tableStockByStock, ARR_PRODUCT_STOCK_ODD, true);
                        }
                    }
                }
                else{
                    // Tạo và kích hoạt sự kiện click
                    var event = new Event('click');
                    navLinks[2].dispatchEvent(event);

                    // Thay đổi thuộc tính "aria-expanded" để mở collapse
                    navLinks[0].querySelector('button').setAttribute('aria-expanded', 'false');
                    navLinks[2].querySelector('button').setAttribute('aria-expanded', 'true');

                    navLinks[0].querySelector('button').setAttribute('aria-selected', 'false');
                    navLinks[2].querySelector('button').setAttribute('aria-selected', 'true');

                    navLinks[0].querySelector('button').classList.remove('active');
                    navLinks[2].querySelector('button').classList.add('active');

                    // Thêm class "show" để hiển thị nội dung của collapse
                    tabPane[0].classList.remove('show');
                    tabPane[0].classList.remove('active');
                    tabPane[2].classList.add('active');
                    tabPane[2].classList.add('show');

                    //
                    const tableStockByProcessingHouse = tabPane[2];
                    if(tableStockByProcessingHouse)
                    {
                        //Set body table
                        const tbodyTable = tableStockByProcessingHouse.querySelector('table.table__delivery tbody');
                        if(tbodyTable)
                        {
                            //Add content
                            let parameter = 'inventory';

                            rowNumber = 1;
                            currentModelProposal.proposal_detail.forEach((product, index) => {
                                const newRow = $($(tableStockByProcessingHouse)).find("#addr0").clone();
                                const newRowId = 'addr' + (rowNumber);
                                const curTable = $($(tableStockByProcessingHouse));

                                newRow.find('td:first-child').html(rowNumber);
                                newRow.find('select.select__product').prop('name', `${parameter}[product][${rowNumber+1}][name]`);
                                newRow.find('input.input__quantity').prop('name', `${parameter}[product][${rowNumber+1}][quantity]`);
                                newRow.find('input.input__sku').prop('name', `${parameter}[product][${rowNumber+1}][sku]`);
                                newRow.find('input.input__price').prop('name', `${parameter}[product][${rowNumber+1}][price]`);
                                newRow.find('input.value_id__product').prop('name', `${parameter}[product][${rowNumber+1}][product_id]`);
                                newRow.attr('id', newRowId);

                                newRow.find('select.select__product option').each(function(){
                                    if($(this).attr('data-id') == product.product_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.input__quantity').val(product.quantity);
                                        newRow.find('input.input__quantity_product').val($(this).attr('data-qty'));
                                    }
                                })

                                ARR_PRODUCT_SELECT_INVENTORY.push(product.product_id);//Add new value

                                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                                getCodeWhenChangeOption( newRow.get(0));

                                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){
                                    getCodeWhenChangeOption(newRow.get(0))
                                })
                                curTable.find('#tab_logic').append(newRow);

                                const itemRow = tableStockByProcessingHouse.querySelector(`#addr${rowNumber}`)

                                $($(tableStockByProcessingHouse)).find(`#addr${rowNumber}`).find('select.select__product').select2()

                                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                                allSelect[0].style.width = '100%';
                                if(allSelect?.length > 1)
                                {
                                    allSelect[1].style.display = 'none';
                                }

                                rowNumber++;
                            });
                            tbodyTable.querySelector('tr#addr0 select.select__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__sku').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.value_id__product').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0 input.input__quantity').setAttribute('name', '');
                            tbodyTable.querySelector('tr#addr0').style.display = 'none';

                            updateRowNumber(tableStockByProcessingHouse);//Cập nhật số thứ tự các hàng của bảng

                            setOptionProductWhenChange(tableStockByProcessingHouse, ARR_PRODUCT_INVENTORY, true);
                        }
                    }
                }
            }
        }

        btnSubmitSaveExit.addEventListener('click', function(){
            preventDefaultForm(formMTproposalNode);

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                if(currentModelProposal != null)
                {
                    if(currentModelProposal.is_warehouse == 'inventory')
                    {
                        const tableStockBySupplier = tabPane[2];
                        //Set body table
                        const tbodyTable = tableStockBySupplier.querySelector('table.table__delivery tbody');
                        tbodyTable.querySelector('tr#addr0')?.remove();
                    }
                }
            }

            checkValidate(urlApi, formMTproposalNode);
        });

        btnSubmitSave.addEventListener('click', function(){
            preventDefaultForm(formMTproposalNode)

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                if(currentModelProposal != null)
                {
                    if(currentModelProposal.is_warehouse == 'inventory')
                    {
                        const tableStockBySupplier = tabPane[2];
                        //Set body table
                        const tbodyTable = tableStockBySupplier.querySelector('table.table__delivery tbody');
                        tbodyTable.querySelector('tr#addr0')?.remove();
                    }
                }
            }

            checkValidate(urlApi, formMTproposalNode);
        });
    }

    function preventDefaultForm(form){
        form.onsubmit = () => {
            return false;
        }
    }

    function checkValidate(urlApi, form)
    {
        const formData = new FormData(form);

        // Chuyển FormData thành đối tượng JavaScript
        let data = {};
        formData.forEach((value, key) => {
            // Nếu key đã tồn tại trong đối tượng data, thì thêm value vào mảng
            setValueByPath(data, key, value);
        });
        // Gửi dữ liệu qua API sử dụng Fetch API
        $.ajax({
            url: `${urlApi}validate-proposal-receipt`,
            type: 'POST',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {
                'data': JSON.stringify(data),
            },
        }).done(function (response) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right',
                onclick: null,
                showDuration: 1000,
                hideDuration: 1000,
                timeOut: 10000,
                extendedTimeOut: 1000,
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut',
            }
            let messageHeader = response.msg

            if(response.error_code === 1)
            {
                toastr['error'](messageHeader, 'Thông báo')

                let timeOut = setTimeout(()=>{
                    const btnSubmitSaveExit = form.querySelector('.btn-list button[value=save]');
                    const btnSubmitSave = form.querySelector('.btn-list button[value=apply]');
                    if (btnSubmitSaveExit.classList.contains('disabled')) {
                        // Loại bỏ lớp 'example-class'
                        btnSubmitSaveExit.classList.remove('disabled');
                        btnSubmitSave.classList.remove('disabled');
                      }
                }, 200)
            }else{
                toastr['success'](messageHeader, 'Thông báo')

                form.submit()
            }

        });
    }

    function setValueByPath(obj, path, value) {
        const keys = path.replace(/\]/g, '').split('[');
        let currentObj = obj;

        for (let i = 0; i < keys.length; i++) {
            const key = keys[i];
            if (i === keys.length - 1) {
                // Nếu đây là key cuối cùng, gán giá trị
                currentObj[key] = value;
            } else {
                // Nếu không phải là key cuối cùng, tạo đối tượng nếu chưa tồn tại
                currentObj[key] = currentObj[key] || {};
                // Di chuyển đến đối tượng con tiếp theo
                currentObj = currentObj[key];
            }
        }
    }

    function getListProduct(urlApi) {
        let listProduct;
        $.ajax({
            url: `${urlApi}get-list-products`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {},
        }).done(function (response) {
            listProduct = response.body;
        });
        return listProduct;
    }

    function getListStock(urlApi) {
        let listStock;
        $.ajax({
            url: `${urlApi}get-products-in-stock`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {},
        }).done(function (response) {
            listStock = response.body;
        });
        return listStock;
    }

    function getListBatch(urlApi) {
        let listBatch;
        $.ajax({
            url: `${urlApi}get-batch-in-warehouse`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {},
        }).done(function (response) {
            listBatch = response.body;
        });
        return listBatch;
    }

    function getListProductParent(urlApi){
        let listProduct;
        $.ajax({
            url: `${urlApi}get-list-product-parent`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {},
        }).done(function (response) {
            listProduct = response.body;
        });
        return listProduct;
    }

    function populateSelectOptions(elTable, selectElement, data, parameter, hasStock) {
        selectElement.empty();

        if(typeof data !== 'undefined')
        {
            const inputElementQty = $(elTable).find('.input__quantity');
            const inputElementCode = $(elTable).find('.input__sku');
            const inputElementPrice = $(elTable).find('.input__price');
            const inputMaterialId = $(elTable).find('.value_id__product');
            const inputQuantityStock = $(elTable).find('input.input__quantity_product');

            if(hasStock){
                // selectElement.append('<option value="">Danh sách sản phẩm</option>');
                selectElement.prop('name', `${parameter}[product][1][name]`);

                inputElementQty.prop('name', `${parameter}[product][1][quantity]`);
                inputElementCode.prop('name', `${parameter}[product][1][sku]`);
                inputElementPrice.prop('name', `${parameter}[product][1][price]`);
                inputMaterialId.prop('name', `${parameter}[product][1][product_id]`);
            }
            let optionSelect = [];

            if(parameter == 'inventory'){
                $.each(data, function (index, product) {
                    try{
                        let color = '';
                        let size = '';

                        let arrAttribute = product.variation_product_attributes;
                        if(arrAttribute.length > 0)
                        {
                            if(arrAttribute.length === 1)
                            {
                                color = arrAttribute[0].color == null ? '' : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : '';
                            }else if(arrAttribute.length === 2){
                                color = arrAttribute[0].color == null ? arrAttribute[1].title : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : arrAttribute[1].title;
                            }
                        }

                        selectElement.append(`<option data-code="${product.sku}" data-id="${product.id}" value="${product.name}">${product.name} - ${color} - ${size}</option>`);
                        optionSelect.push({
                            'id': product.id,
                            'text': product.name,
                            "data-id": product.sku
                        });

                    }catch(err)
                    {
                        console.log(err);
                    }
                });
            }else if(parameter == 'stock'){
                for(const key in data){
                    try{
                        if(data[key]?.data?.product != '')
                        {
                            selectElement.append(`<option data-qty="${data[key].quantity}" data-code='${data[key].data.product.sku}' data-id="${data[key].data.product.id}" value="${data[key].data.product.name}">${data[key].data.product.name} </option>`);
                            optionSelect.push({
                                'id': data[key].data.product.id,
                                'text': data[key].data.product.name,
                                "data-id": data[key].data.product.sku
                            });
                        }

                    }catch(err)
                    {
                        console.log(err);
                    }
                }
            }else if(parameter == 'stock-odd'){
                $.each(data, function (index, product) {
                    try{
                        let color = '';
                        let size = '';

                        let arrAttribute = product.variation_product_attributes;
                        if(arrAttribute.length > 0)
                        {
                            if(arrAttribute.length === 1)
                            {
                                color = arrAttribute[0].color == null ? '' : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : '';
                            }else if(arrAttribute.length === 2){
                                color = arrAttribute[0].color == null ? arrAttribute[1].title : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : arrAttribute[1].title;
                            }
                        }

                        if(data[index].pivot)
                        {
                            selectElement.append(`<option data-qty="${product.pivot.quantity}" data-code="${product.sku}" data-id="${product.id}" value="${product.name}">${product.name} - ${color} - ${size}</option>`);
                            optionSelect.push({
                                'id': product.id,
                                'text': product.name,
                                "data-id": product.sku
                            });
                        }

                    }catch(err)
                    {
                        console.log(err);
                    }
                });
            }

            OPTION_SELECT = optionSelect

            let options = setOptionSearchWithCode([])

            const inputMaterialQty = $(elTable).find('.input__quantity_product');

            let curMaterialQty = selectElement.find(':selected').attr('data-qty');
            inputMaterialQty.val(curMaterialQty);
            
            selectElement.select2(options)

            let productCode = selectElement.find(':selected').attr('data-code');
            inputElementCode.val(productCode);

            if(parameter == 'stock-odd'){
                // Kiểm tra xem giá trị có trong mảng hay không
                if (!ARR_PRODUCT_SELECT_STOCK_ODD.includes(selectElement.find(':selected').attr('data-id'))) {
                    // Nếu giá trị không có trong mảng, thêm vào
                    ARR_PRODUCT_SELECT_STOCK_ODD.push(selectElement.find(':selected').attr('data-id'));
                }
            }
            else if(parameter == 'inventory'){
                // Kiểm tra xem giá trị có trong mảng hay không
                if (!ARR_PRODUCT_SELECT_INVENTORY.includes(selectElement.find(':selected').attr('data-id'))) {
                    // Nếu giá trị không có trong mảng, thêm vào
                    ARR_PRODUCT_SELECT_INVENTORY.push(selectElement.find(':selected').attr('data-id'));
                }
            }
            else{
                // Kiểm tra xem giá trị có trong mảng hay không
                if (!ARR_PRODUCT_SELECT_STOCK.includes(selectElement.find(':selected').attr('data-id'))) {
                    // Nếu giá trị không có trong mảng, thêm vào
                    ARR_PRODUCT_SELECT_STOCK.push(selectElement.find(':selected').attr('data-id'));
                }
            }
        }
    }

    function changeAllOptionChild(table, selectElement, stockId, data, parameter)
    {
        const trItem = table.querySelector('tbody tr');
        if(trItem)
        {
            if (data != null && stockId in data) {
                if(parameter == 'inventory' || parameter == 'stock-odd'){
                    $.each(data, function (index, product) {
                        let color = '';
                        let size = '';

                        let arrAttribute = product.variation_product_attributes;
                        if(arrAttribute.length > 0)
                        {
                            if(arrAttribute.length === 1)
                            {
                                color = arrAttribute[0].color == null ? '' : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : '';
                            }else if(arrAttribute.length === 2){
                                color = arrAttribute[0].color == null ? arrAttribute[1].title : arrAttribute[0].title;
                                size = arrAttribute[0].color == null ? arrAttribute[0].title : arrAttribute[1].title;
                            }
                        }

                        selectElement.append(`<option data-qty="${product.pivot.quantity}" data-code="${product.sku}" data-id="${product.id}" value="${product.name}">${product.name} - ${color} - ${size}</option>`);

                    });
                }else if(parameter == 'stock'){
                    for(const key in data){
                        try{
                            if(data[key]?.data?.product != '')
                            {
                                selectElement.append(`<option data-qty="${data[key].quantity}" data-code='${data[key].data.product.sku}' data-id="${data[key].data.product.id}" value="${data[key].data.product.name}">${data[key].data.product.name} </option>`);
                            }
                        }catch(err)
                        {
                            console.log(err);
                        }
                    }
                }
            } else {
                selectElement.empty();
                selectElement.append('<option disable>Không có sản phẩm</option>');
            }
            $(trItem).find('input.input__sku').val('');
            $(trItem).find('input.input__quantity_product').val('');
            $(trItem).find('input.input__quantity').val('');
        }
    }

    function getCodeByIdMaterial(list, id)
    {
        let code = [];

        for (let index = 0; index < list.length; index++) {
            const element = list[index];
            if(id == element.id)
            {
                code[0] = element.sku;
                code[1] = element.id;
                break;
            }

        }
        return code;
    }

    function getCodeByIdParentProduct(list, id)
    {
        let code = [];

        for(const pro_id in list){
            if(id == pro_id)
            {
                code[0] = list[pro_id].sku;
                code[1] = pro_id;
                break;
            }

        }

        return code;
    }

    function getCodeWhenChangeOption(itemRow)
    {
        let selectElement = $(itemRow).find(".select__product");

        let inputQuantityStock = $(itemRow).find('input.input__quantity_product');

        const inputMaterialId = $(itemRow).find('.value_id__product');

        let curMaterialId = selectElement.find(':selected').attr('data-id');

        const navLinks = formMTproposalNode.querySelectorAll('.nav.nav-tabs .nav-item');

        let codeOfMaterial;
        let idMaterial;


        navLinks?.forEach((navItem, index) => {
            const radioBtn = navItem.querySelector('input[name="tabRadio"]');

            if(radioBtn.checked){
                if(index === 0)
                {
                    codeOfMaterial = getCodeByIdParentProduct(arrAllProductParent, curMaterialId)[0];
                    idMaterial = getCodeByIdParentProduct(arrAllProductParent, curMaterialId)[1];
                }else if(index === 1){
                    codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
                    idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
                }else if(index === 2){
                    codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
                    idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
                }
            }
        })

        let curMaterialQty = selectElement.find(':selected').attr('data-qty');

        if(curMaterialQty && inputQuantityStock){
            inputQuantityStock.val(curMaterialQty);
        }

        let inputCode = $(itemRow).find(".input__sku");

        inputCode.val(codeOfMaterial);
        inputMaterialId.val(idMaterial);
    }

    function setOptionChooseStock(arrStock, branchId)
    {
        let arr = '';

        arrStock?.forEach(stock => {
            if(stock.id != branchId)
            {
                let note = '';
                if(stock.products.length === 0){
                    note = ' - Đã hết sản phẩm trong kho';
                }
                arr += `<option value="${stock.id}">${stock.name} ${note}</option>`
            }
        });

        return arr;
    }

    //Function check quantity in stock
    function addEventForElement(inputCheckQtyProduct, selectProduct, inputQty)
    {
        if(inputQty)
        {
            inputQty.addEventListener('keyup', function(event){
                event.preventDefault();

                if(inputCheckQtyProduct.value*1 < event.target.value*1)
                {
                    alert('Số lượng sản phẩm xuất đi lớn hơn số lượng trong kho!!')
                    event.target.value = inputCheckQtyProduct.value;
                }
            })
            inputQty.addEventListener('change', function(event){
                event.preventDefault();

                if(inputCheckQtyProduct.value*1 < event.target.value*1)
                {
                    alert('Số lượng sản phẩm xuất đi lớn hơn số lượng trong kho!!')
                    event.target.value = inputCheckQtyProduct.value;
                }
            })
        }
    }

    function setCodeWhenChangeStock(tableProposal, listStock, stockId, arrHasChoose)
    {
        if(tableProposal)
        {
            const groupTrRow = tableProposal.querySelectorAll('tbody tr');

            groupTrRow?.forEach(itemRow => {
                let selectElementNode = itemRow.querySelector('select.select__product');
                let inputQuantityStock = itemRow.querySelector('input.input__quantity_product');

                getCodeWhenChangeOption( itemRow)
                if(selectElementNode.options.length > 0)
                {
                    let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    let valueQty = getQuantityInStock(listStock, stockId, materialId);

                    if(typeof valueQty != 'undefined'){
                        inputQuantityStock.value = valueQty;
                    }

                    //Get pre value before change selected
                    let previousValue;

                    // Lấy giá trị trước khi thay đổi
                    $(selectElementNode).on("select2:open", function () {
                        previousValue = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    });

                      // Sự kiện "change" để xử lý khi giá trị thay đổi
                      $(selectElementNode).on("change", function () {
                        // Xử lý khi giá trị thay đổi
                        getCodeWhenChangeOption(itemRow)

                        let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;

                        let valueQty = getQuantityInStock(listStock, stockId, materialId)
                        inputQuantityStock.value = valueQty;

                        // Tìm index của giá trị cần xoá trong mảng
                        let indexToRemove = arrHasChoose.indexOf(previousValue);

                        // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                        if (indexToRemove !== -1) {
                            // Xoá giá trị tại indexToRemove
                            arrHasChoose.splice(indexToRemove, 1);

                            // Thêm giá trị mới vào mảng
                            arrHasChoose.push(materialId);
                        }

                        setOptionProductWhenChange(tableProposal, ARR_PRODUCT_STOCK)
                    })
                }else{
                    selectElementNode.innerHTML = '<option disable>Không có sản phẩm</option>'
                }

            })
        }
    }

    function setMaterialWhenChangeStockOdd(tableProposal, listStock, stockId, arrHasChoose){
        if(tableProposal)
        {
            const groupTrRow = tableProposal.querySelectorAll('tbody tr');

            groupTrRow?.forEach(itemRow => {
                let selectElementNode = itemRow.querySelector('select.select__product');
                let inputQuantityStock = itemRow.querySelector('input.input__quantity_product');

                getCodeWhenChangeOption( itemRow)
                if(selectElementNode.options.length > 0)
                {
                    let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    let valueQty = getQuantityInStock(listStock, stockId, materialId);

                    if(typeof valueQty != 'undefined'){
                        inputQuantityStock.value = valueQty;
                    }

                    //Get pre value before change selected
                    let previousValue;

                    // Lấy giá trị trước khi thay đổi
                    $(selectElementNode).on("select2:open", function () {
                        previousValue = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    });

                    // Sự kiện "change" để xử lý khi giá trị thay đổi
                    $(selectElementNode).on("change", function () {
                        // Xử lý khi giá trị thay đổi
                        getCodeWhenChangeOption(itemRow)

                        let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;

                        let valueQty = getQuantityInStock(listStock, stockId, materialId)
                        inputQuantityStock.value = valueQty;

                        // Tìm index của giá trị cần xoá trong mảng
                        let indexToRemove = arrHasChoose.indexOf(previousValue);

                        // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                        if (indexToRemove !== -1) {
                            // Xoá giá trị tại indexToRemove
                            arrHasChoose.splice(indexToRemove, 1);

                            // Thêm giá trị mới vào mảng
                            arrHasChoose.push(materialId);
                        }

                        setOptionProductWhenChange(tableProposal, ARR_PRODUCT_STOCK_ODD)
                    })
                }else{
                    selectElementNode.innerHTML = '<option disable>Không có sản phẩm</option>'
                }

            })
        }
    }

    function setMaterialWhenChangeInventory(table, arrHasChoose){
        const groupTrRow = table.querySelectorAll('tbody tr');

        groupTrRow?.forEach(itemRow => {
            let selectElementNode = itemRow.querySelector('select.select__product');

            if(selectElementNode.options.length > 0)
            {
                //Get pre value before change selected
                let previousValue;

                // Lấy giá trị trước khi thay đổi
                $(selectElementNode).on("select2:open", function () {
                    previousValue = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                });

                // Sự kiện "change" để xử lý khi giá trị thay đổi
                $(selectElementNode).on("change", function () {
                    // Xử lý khi giá trị thay đổi
                    let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;

                    // Tìm index của giá trị cần xoá trong mảng
                    let indexToRemove = arrHasChoose.indexOf(previousValue);

                    // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                    if (indexToRemove !== -1) {
                        // Xoá giá trị tại indexToRemove
                        arrHasChoose.splice(indexToRemove, 1);

                        // Thêm giá trị mới vào mảng
                        arrHasChoose.push(materialId);
                    }

                    setOptionProductWhenChange(table, ARR_PRODUCT_INVENTORY)
                })
            }else{
                selectElementNode.innerHTML = '<option disable>Không có sản phẩm</option>'
            }

        })
    }

    function getQuantityInStock(data, stockId, productId)
    {
        let qty;

        for(const key in data){
            const element = data[key];
            if(stockId == key)
            {
                for(const parent_product_id in element){
                    if(parent_product_id == productId)
                    {
                        qty = element[parent_product_id].quantity;
                        break;
                    }
                }
            }
        }

        return qty;
    }

    $(document).on('change', '.select__product', function () {
        currentElement = $(this);

        let selectElement = currentElement.closest('tr').find("select.select__product");
        const inputMaterialId = currentElement.closest('tr').find('input.value_id__product');
        const inputMaterialQty = currentElement.closest('tr').find('.input__quantity_product');
        const inputMaterialCode = currentElement.closest('tr').find('.input__sku');

        let curMaterialId = selectElement.find(':selected').attr('data-id');
        let curMaterialQty = selectElement.find(':selected').attr('data-qty');

        const navLinks = formMTproposalNode.querySelectorAll('.nav.nav-tabs .nav-item');

        let codeOfMaterial;
        let idMaterial;

        navLinks?.forEach((navItem, index) => {
            const radioBtn = navItem.querySelector('input[name="tabRadio"]');

            if(radioBtn.checked){
                if(index === 0)
                {
                    codeOfMaterial = getCodeByIdParentProduct(arrAllProductParent, curMaterialId)[0];
                    idMaterial = getCodeByIdParentProduct(arrAllProductParent, curMaterialId)[1];
                }else if(index === 1){
                    codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
                    idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
                }
                else if(index === 2){
                    codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
                    idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
                }
            }
        })

        inputMaterialCode.val(codeOfMaterial);
        inputMaterialId.val(idMaterial);
        inputMaterialQty.val(curMaterialQty);

        currentElement.closest('tr').find('.input__quantity').val('')
    });

    //Function get infomation of proposal purchase by id
    function getInfoProposalById(urlApi, id)
    {
        let modelProposal;
        $.ajax({
            url: `${urlApi}get-info-proposal-receipt-product/${id}`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            success: function (response) {
                if(response.error_code === 0){
                    modelProposal = response.body
                }
            },
            error: function(error){
                console.log(error);
            }
        });
        return modelProposal;
    }

    //Lấy các
    function arrayDifference(arr1, arr2) {
        // Lấy các giá trị chỉ xuất hiện trong arr1
        var difference1 = arr1.filter(value => !arr2.includes(value));

        // Lấy các giá trị chỉ xuất hiện trong arr2
        var difference2 = arr2.filter(value => !arr1.includes(value));

        // Kết hợp các giá trị khác nhau từ cả hai mảng
        var result = difference1.concat(difference2);

        return result;
    }

    function setOptionProductWhenChange(table, listProduct, isEdit = null)
    {
        const tr = table.querySelectorAll('tbody tr');

        let arrCurrentSelect = [];

        try{
            tr.forEach(item => {
                if($(item).css('display') !== 'none'){
                    const selectProduct = item.querySelector('select.select__product');
    
                    let mproductId = selectProduct.options[selectProduct.selectedIndex]?.dataset.id;
    
                    if(isEdit === true){
                        if($(item).attr('id') != 'addr0'){
                            arrCurrentSelect.push(mproductId*1);
                        }
                    }else{
                        arrCurrentSelect.push(mproductId*1);
                    }
                }
            });
            let differentValues = arrayDifference(arrCurrentSelect, listProduct);

            tr.forEach(item => {
                if($(item).css('display') !== 'none'){
                    const selectProduct = item.querySelector('select.select__product');
                    selectProduct.querySelectorAll('option').forEach(option => {
                        if (differentValues.includes(option.dataset.id*1)) {
                            option.disabled = false;
                        } else {
                            option.disabled = true;
                        }
                    })
                }
            });

        }catch(error){
            console.log(error);
        }
    }

    //Hàm lấy 1 value của key trong object và add nó vào 1 mảng
    function getValueAndAddToArray(obj, key, array) {
        // Kiểm tra xem key có tồn tại trong đối tượng không
        obj.forEach(item => {
            if (item.hasOwnProperty(key)) {
                // Lấy giá trị từ đối tượng và thêm vào mảng
                var value = item[key];
                array.push(value);
            }
        })
    }

    //Hàm set số thứ tự các hàng của table
    function updateRowNumber(table)
    {
        const trList = table.querySelectorAll('tbody tr');

        let dem = 1;

        trList?.forEach((item, index) => {
            if(item.style.display != 'none'){
                item.querySelector('td:first-child').innerHTML = dem;
                dem++;
            }
        })
    }

    //Reset row table khi change option chọn kho xuất và kho nhập
    function resetRowTable(table){
        const trList = table.querySelectorAll('tbody tr');
        trList?.forEach((item, index) => {
            if(index > 0){
                $(item).remove();//Xoas
            }
        })
    }

    function setOptionSearchWithCode(arr){
        let options = {
            width: '100%',
            data: arr,
            templateResult: function(data) {
                // Use templateResult to include both display text and hidden attribute in the search
                var $result = $("<span></span>");
                $result.text(data.text);
                return $result;
            },
            matcher: function(params, data) {
                // Customize the matcher function to search in both text and hidden attribute
                if ($.trim(params.term) === '') {
                    return data;
                }

                if (typeof data.text === 'undefined') {
                    return null;
                }

                var text = data.text.toLowerCase();
                var term = params.term.toLowerCase();
                var hidden = '';
                if (data.element && data.element.dataset && data.element.dataset.code) {
                    hidden = data.element.dataset.code.toLowerCase();
                }

                if (text.indexOf(term) > -1 || hidden.indexOf(term) > -1) {
                    return data;
                }

                return null;
            }
        }

        return options;
    }

    function checkKeyExistInArrayObj(arrObj, value)
    {
        let result = false;

        arrObj?.forEach(item => {
            if(item.id == value) {
                result = true;
                return;
            }
        });

        return result;
    }
});
