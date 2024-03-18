
$(document).ready(function () {
    const formMTproposal = $('form#botble-warehouse-forms-material-proposal-purchase-form');
    const formMTproposalNode = document.querySelector('form#botble-warehouse-forms-material-proposal-purchase-form');

    let ARR_STOCK;
    let OPTION_SELECT = [];
    let arrAllProduct;
    let rowNumber = 1;
    let so_thu_tu = 1;

    let ARR_MATERIAL_STOCK = [];//Mảng chứa tất cả id nguyên phụ liệu của kho được chọn
    let ARR_MATERIAL_SELECT_STOCK = [];//Mảng chứa id nguyên phụ liệu của các select đang được chọn ở phần nhập từ kho đến kho

    let ARR_MATERIAL_SUPPLIER = [];
    let ARR_MATERIAL_SELECT_SUPPLIER = [];

    if (formMTproposal.is(':visible')) {
        const proposal_element = formMTproposalNode.querySelector('input#proposal_id');
        //Table in supplier and stock
        const tabPane = formMTproposalNode.querySelectorAll('.tab-content .tab-pane');
        const navLinks = formMTproposalNode.querySelectorAll('.nav.nav-tabs .nav-item');

        const inputValueTypePost = formMTproposalNode.querySelector('input[name=type_proposal]');

        let typeValueStock = 'stock';
        let typeValueSupplier = 'supplier';
        let parameterEdit = '';

        navLinks.forEach((navItem, index) => {
            navItem.addEventListener('click', function(){
                if(index === 0)
                {
                    inputValueTypePost.value = typeValueStock;

                    const radioBtn = navItem.querySelector('input#radioStock');
                    radioBtn.checked = true;
                }else{
                    inputValueTypePost.value = typeValueSupplier;

                    const radioBtn = navItem.querySelector('input#radioSupplier');
                    radioBtn.checked = true;
                }
            })
        })

        tabPane.forEach((table, index) => {
            let elTable = $(table);

            let parameter = '';

            let urlApi = formMTproposal.find('input#url_api').val();
            arrAllProduct = getListProduct(urlApi);

            const selectBranch = formMTproposal.find('select#warehouse_id');
            let branchId = selectBranch.val();
            //
            const selectBranchOfStock = elTable.find('select.select__product');
            //

            if(index === 0)
            {
                parameter = 'stock';
                let arrStock = getListStock(urlApi);

                ARR_STOCK = arrStock;

                const selectStock = formMTproposal.find('.tab-content select[name=detination_wh_id]');

                selectStock.html(setOptionChooseStock(arrStock, branchId));
                
                arrStock.forEach(stock => {
                    if(stock.materials.length > 0)
                    {
                        if(stock.id == selectStock.val())
                        {
                            populateSelectOptions(elTable, selectBranchOfStock, stock.materials, parameter, true);
                            
                            changeAllOptionChild(table, stock.materials, parameter)
                            //Add list id của các nguyên phụ liệu của kho hiện tại vào mảng
                            getValueAndAddToArray(stock.materials, 'id', ARR_MATERIAL_STOCK);

                        }
                    }
                })

                //On change stock level 1
                selectBranch.on('change', (evt) => {
                    let branchId = selectBranch.val();
                    selectStock.html(setOptionChooseStock(arrStock, branchId));

                    arrStock.forEach(stock => {
                        // if(stock.materials.length > 0)
                        // {
                            if(stock.id == selectStock.val())
                            {
                                ARR_MATERIAL_SELECT_STOCK = [];
                                populateSelectOptions(elTable, selectBranchOfStock, stock.materials, parameter, true);

                                //Add list id của các nguyên phụ liệu của kho hiện tại vào mảng
                                getValueAndAddToArray(stock.materials, 'id', ARR_MATERIAL_STOCK);

                                //Thay đổi các option trong từng select nguyên phụ liệu khi thay đổi kho xuất
                                changeAllOptionChild(table, stock.materials)

                                setOptionMaterialWhenChange(table, ARR_MATERIAL_STOCK);//Gọi hàm kiểm tra nguyên liệu được chọn

                                resetRowTable(table)//Reset các hàng của table khi thay đổi kho chọn
                            }
                        // }
                    })
                })

                //On change stock level 2
                selectStock.on('change', (evt) => {
                    let branchId = selectStock.val();
                    arrStock.forEach(stock => {
                        if(stock.id == branchId)
                        {
                            ARR_MATERIAL_SELECT_STOCK = [];
                            populateSelectOptions(elTable, selectBranchOfStock, stock.materials, parameter,true);

                            //Add list id của các nguyên phụ liệu của kho hiện tại vào mảng
                            getValueAndAddToArray(stock.materials, 'id', ARR_MATERIAL_STOCK);

                            changeAllOptionChild(table, stock.materials);

                            setOptionMaterialWhenChange(table, ARR_MATERIAL_STOCK);//Gọi hàm kiểm tra nguyên liệu được chọn

                            resetRowTable(table)//Reset các hàng của table khi thay đổi kho chọn
                        }
                    })
                    
                })

                setCodeWhenChangeStock(arrAllProduct, arrStock, selectStock.val());
                const inputCheckQtyProduct = formMTproposalNode.querySelector('input.input__quantity_material');

                const inputQty = formMTproposalNode.querySelector('input.input__quantity');
                addEventForElement(inputCheckQtyProduct, selectStock, inputQty)
            }else{
                parameter = 'supplier';
                populateSelectOptions(elTable, selectBranchOfStock, arrAllProduct, parameter, true);

                //Add các id của các nguyên phụ liệu vào mảng
                getValueAndAddToArray(arrAllProduct, 'id', ARR_MATERIAL_SUPPLIER);

                setOptionMaterialWhenChange(table, ARR_MATERIAL_SUPPLIER);//Gọi hàm kiểm tra nguyên liệu được chọn

                setMaterialWhenChangeSupplier(table, ARR_MATERIAL_SELECT_SUPPLIER)
            }

            $(elTable).find("#add_row").click(function () {
                if($(elTable).find("#addr0 select.select__product").hasClass("select2-hidden-accessible")){
                    $(elTable).find("#addr0 select.select__product").select2('destroy');
                }

                const newRow = $(elTable).find("#addr0").clone();
                // $(elTable).find("#addr0").find('select.select__product').select2(OPTION_SELECT);

                const newRowId = 'addr' + rowNumber;

                const proposal_element = formMTproposalNode.querySelector('input#proposal_id');

                let proposal_id = proposal_element.value;
                if(proposal_id != 0){
                    so_thu_tu = rowNumber;
                }

                newRow.html(newRow.html()).find('td:first-child').html(so_thu_tu);
                newRow.html(newRow.html()).find('select.select__product').prop('name', `${parameter}[material][${rowNumber+1}][name]`);
                newRow.html(newRow.html()).find('select.select__agency').prop('name', `${parameter}[material][${rowNumber+1}][supplier_id]`);
                newRow.html(newRow.html()).find('input.input__quantity').prop('name', `${parameter}[material][${rowNumber+1}][quantity]`);
                newRow.html(newRow.html()).find('input.input__code').prop('name', `${parameter}[material][${rowNumber+1}][code]`);
                newRow.html(newRow.html()).find('input.input__unit').prop('name', `${parameter}[material][${rowNumber+1}][unit]`);
                newRow.html(newRow.html()).find('input.input__price').prop('name', `${parameter}[material][${rowNumber+1}][price]`);
                newRow.html(newRow.html()).find('input.value_id__material').prop('name', `${parameter}[material][${rowNumber+1}][material_id]`);
                newRow.attr('id', newRowId);

                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                //Remove option material has selected
                newRow.html(newRow.html()).find('select.select__product option').each( function() {
                    ARR_MATERIAL_SELECT_STOCK.forEach(item => {
                        if($(this).attr('data-id') == item)
                        {
                            $(this).prop('disabled', true);
                        }
                    })
                });
                ARR_MATERIAL_SELECT_STOCK.push(newRow.html(newRow.html()).find('select.select__product').find(':selected').attr('data-id'));//Add new value

                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){

                    getCodeWhenChangeOption(arrAllProduct,newRow.get(0))
                })
                getCodeWhenChangeOption(arrAllProduct, newRow.get(0));

                $(elTable).find('#tab_logic').append(newRow);

                table.querySelector(`tr#${newRowId}`).style.display = 'table-row';

                setCodeWhenChangeStock(arrAllProduct, ARR_STOCK, formMTproposal.find('.tab-content select[name=detination_wh_id]').val());

                const inputCheckQtyProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity_material');
                const selectProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' select.select__product');
                const inputQty = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity');

                const itemRow = table.querySelector(`#addr${rowNumber}`)

                // Kiểm tra xem phần tử có kết nối với Select2 hay không
                let options = setOptionSearchWithCode([])

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
                }else{
                    setMaterialWhenChangeSupplier(table, ARR_MATERIAL_SELECT_SUPPLIER)
                }
                rowNumber++;

                $(elTable).find("#addr0 select.select__product").select2(options);

                updateRowNumber(table);

                //Gọi hàm set các option của từng select nguyên phụ liệu
                let isEdit = false;
                if(proposal_element && proposal_element.value != 0){
                    isEdit = true;
                }

                //Kiểm tra đang nhập kho từ kho khác hay nhà cung cấp
                if(parameter == 'stock'){
                    setOptionMaterialWhenChange(table, ARR_MATERIAL_STOCK, isEdit);
                }else{
                    setOptionMaterialWhenChange(table, ARR_MATERIAL_SUPPLIER);
                }
            });

            $(elTable).on("click", ".delete-row", function () {
                let valueToRemove = $(this).closest('tr').find('select.select__product :selected').attr('data-id');
                // Tìm index của giá trị cần xoá trong mảng
                let indexToRemove = ARR_MATERIAL_SELECT_STOCK.indexOf(valueToRemove);

                // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                if (indexToRemove !== -1) {
                    // Xoá giá trị tại indexToRemove
                    ARR_MATERIAL_SELECT_STOCK.splice(indexToRemove, 1);
                }

                $(elTable).find(this).closest('tr').remove();

                updateRowNumber(table);

                //Gọi hàm set các option của từng select nguyên phụ liệu
                if(parameter == 'stock'){
                    setOptionMaterialWhenChange(table, ARR_MATERIAL_STOCK);
                }else{
                    setOptionMaterialWhenChange(table, ARR_MATERIAL_SUPPLIER);
                }
            });
        })

        //Validate for request
        const btnSubmitSaveExit = formMTproposalNode.querySelector('.btn-list button[value=save]');
        const btnSubmitSave = formMTproposalNode.querySelector('.btn-list button[value=apply]');

        let urlApi = formMTproposal.find('input#url_api').val();

        let currentModelProposal = null;

        //Show option when edit
        if(proposal_element)
        {
            let proposal_id = proposal_element.value;
            if(proposal_id != 0)
            {
                currentModelProposal = getInfoProposalById(urlApi, proposal_id);

                //Chọn tab pane theo model đơn đề xuất
                if(currentModelProposal.is_from_supplier === 1){
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

                    //
                    const tableStockBySupplier = tabPane[1];
                    if(tableStockBySupplier)
                    {
                        //Set body table
                        const tbodyTable = tableStockBySupplier.querySelector('table.table__delivery tbody');
                        if(tbodyTable)
                        {
                            //Add content
                            let parameter = 'supplier';

                            rowNumber = 1;
                            currentModelProposal.proposal_detail.forEach((mateial, index) => {
                                const newRow = $($(tableStockBySupplier)).find("#addr0").clone();
                                const newRowId = 'addr' + (rowNumber);
                                const curTable = $($(tableStockBySupplier));

                                newRow.find('td:first-child').html(rowNumber);
                                newRow.find('select.select__product').prop('name', `${parameter}[material][${rowNumber+1}][name]`);
                                newRow.find('select.select__agency').prop('name', `${parameter}[material][${rowNumber+1}][supplier_id]`);
                                newRow.find('input.input__quantity').prop('name', `${parameter}[material][${rowNumber+1}][quantity]`);
                                newRow.find('input.input__code').prop('name', `${parameter}[material][${rowNumber+1}][code]`);
                                newRow.find('input.input__unit').prop('name', `${parameter}[material][${rowNumber+1}][unit]`);
                                newRow.find('input.input__price').prop('name', `${parameter}[material][${rowNumber+1}][price]`);
                                newRow.find('input.value_id__material').prop('name', `${parameter}[material][${rowNumber+1}][material_id]`);
                                newRow.attr('id', newRowId);

                                newRow.find('select.select__product option').each(function(){
                                    if($(this).attr('data-id') == mateial.material_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.input__quantity').val(mateial.material_quantity);
                                        newRow.find('input.input__quantity_material').val($(this).attr('data-qty'));
                                    }
                                })

                                $(formMTproposal).find('select.supplier_id option').each(function(){
                                    if($(this).val() == mateial.supplier_id){
                                        $(this).prop('selected', true);
                                    }
                                })

                                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                                getCodeWhenChangeOption(arrAllProduct, newRow.get(0));

                                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){

                                    getCodeWhenChangeOption(arrAllProduct,newRow.get(0))
                                })
                                curTable.find('#tab_logic').append(newRow);

                                const itemRow = tableStockBySupplier.querySelector(`#addr${rowNumber}`)

                                let options = setOptionSearchWithCode(OPTION_SELECT)

                                $($(tableStockBySupplier)).find(`#addr${rowNumber}`).find('select.select__product').select2(options)

                                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                                allSelect[0].style.width = '100%';
                                if(allSelect?.length > 1)
                                {
                                    allSelect[1].style.display = 'none';
                                }

                                rowNumber++;
                            });
                            tbodyTable.querySelector('tr#addr0').style.display = 'none';

                            setOptionMaterialWhenChange(tableStockBySupplier, ARR_MATERIAL_SUPPLIER, true);
                        }
                    }
                }else{
                    const tableStockByStock = tabPane[0];
                    if(tableStockByStock)
                    {
                        const selectWarehouseDetination = tableStockByStock.querySelector('select[name=detination_wh_id]');
                        selectWarehouseDetination.value = currentModelProposal.wh_departure_id;

                        //Set body table
                        const tbodyTable = tableStockByStock.querySelector('table.table__delivery tbody');

                        if(tbodyTable)
                        {
                            //Add content
                            let parameter = 'stock';

                            ARR_STOCK.forEach(stock => {
                                if(stock.id == currentModelProposal.wh_departure_id)
                                {
                                    changeAllOptionChild(tableStockByStock, stock.materials, parameter)
                                }
                            })

                            rowNumber = 1;
                            currentModelProposal.proposal_detail.forEach((mateial, index) => {
                                const newRow = $($(tableStockByStock)).find("#addr0").clone();
                                const newRowId = 'addr' + (rowNumber);
                                const curTable = $($(tableStockByStock));

                                newRow.find('td:first-child').html(rowNumber);
                                newRow.find('select.select__product').prop('name', `${parameter}[material][${rowNumber+1}][name]`);
                                newRow.find('select.select__agency').prop('name', `${parameter}[material][${rowNumber+1}][supplier_id]`);
                                newRow.find('input.input__quantity').prop('name', `${parameter}[material][${rowNumber+1}][quantity]`);
                                newRow.find('input.input__code').prop('name', `${parameter}[material][${rowNumber+1}][code]`);
                                newRow.find('input.input__unit').prop('name', `${parameter}[material][${rowNumber+1}][unit]`);
                                newRow.find('input.input__price').prop('name', `${parameter}[material][${rowNumber+1}][price]`);
                                newRow.find('input.value_id__material').prop('name', `${parameter}[material][${rowNumber+1}][material_id]`);
                                newRow.attr('id', newRowId);

                                newRow.find('select.select__product option').each(function(){
                                    if($(this).attr('data-id') == mateial.material_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.input__quantity').val(mateial.material_quantity);
                                        newRow.find('input.input__quantity_material').val($(this).attr('data-qty'));
                                    }
                                })

                                newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                                getCodeWhenChangeOption(arrAllProduct, newRow.get(0));

                                newRow.get(0).querySelector('select.select__product').addEventListener('change', function(){

                                    getCodeWhenChangeOption(arrAllProduct,newRow.get(0))
                                })
                                curTable.find('#tab_logic').append(newRow);

                                setCodeWhenChangeStock(arrAllProduct, ARR_STOCK, formMTproposal.find('.tab-content select[name=detination_wh_id]').val());

                                const inputCheckQtyProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity_material');
                                const selectProduct = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' select.select__product');
                                const inputQty = document.querySelector('table.table__delivery tbody tr#addr'+(rowNumber)+' input.input__quantity');

                                const itemRow = tableStockByStock.querySelector(`#addr${rowNumber}`)

                                let options = setOptionSearchWithCode(OPTION_SELECT)

                                $($(tableStockByStock)).find(`#addr${rowNumber}`).find('select.select__product').select2(options)

                                const allSelect = itemRow?.querySelectorAll('.select2.select2-container');

                                allSelect[0].style.width = '100%';
                                if(allSelect?.length > 1)
                                {
                                    allSelect[1].style.display = 'none';
                                }

                                addEventForElement(inputCheckQtyProduct, selectProduct, inputQty)
                                rowNumber++;
                            });
                            tbodyTable.querySelector('tr#addr0').style.display = 'none';

                            setOptionMaterialWhenChange(tableStockByStock, ARR_MATERIAL_STOCK, true);
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
                    if(currentModelProposal.is_from_supplier === 1)
                    {
                        const tableStockBySupplier = tabPane[1];
                        //Set body table
                        const tbodyTable = tableStockBySupplier.querySelector('table.table__delivery tbody');
                        if(tbodyTable.querySelector('tr#addr0')){
                            tbodyTable.querySelector('tr#addr0').remove();
                        }
                    }else{
                        const tableStockByStock = tabPane[0];
                        //Set body table
                        const tbodyTable = tableStockByStock.querySelector('table.table__delivery tbody');
                        if(tbodyTable.querySelector('tr#addr0')){
                            tbodyTable.querySelector('tr#addr0').remove();
                        }
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
                    if(currentModelProposal.is_from_supplier === 1)
                    {
                        const tableStockBySupplier = tabPane[1];
                        //Set body table
                        const tbodyTable = tableStockBySupplier.querySelector('table.table__delivery tbody');
                        if(tbodyTable.querySelector('tr#addr0')){
                            tbodyTable.querySelector('tr#addr0').remove();
                        }
                    }else{
                        const tableStockByStock = tabPane[0];
                        //Set body table
                        const tbodyTable = tableStockByStock.querySelector('table.table__delivery tbody');
                        if(tbodyTable.querySelector('tr#addr0')){
                            tbodyTable.querySelector('tr#addr0').remove();
                        }
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
            url: `${urlApi}validate/receipt`,
            type: 'POST',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {
                'data': JSON.stringify(data)
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
            url: `${urlApi}material/list`,
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

    function getListAgency(urlApi) {
        let listAgency;
        $.ajax({
            url: `${urlApi}supplier/list`,
            type: 'get',
            async: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                // Thiết lập header Authorization với token
                xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
            },
            data: {},
        }).done(function (response) {
            listAgency = response.body;
        });
        return listAgency;
    }

    function getListStock(urlApi) {
        let listStock;
        $.ajax({
            url: `${urlApi}warehouse/material`,
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

    function populateSelectOptions(elTable, selectElement, data, parameter, hasStock) {
        selectElement.empty();

        if(typeof data !== 'undefined' && data.length > 0)
        {
            const inputElementQty = $(elTable).find('.input__quantity');
            const inputElementCode = $(elTable).find('.input__code');
            const inputElementUnit = $(elTable).find('.input__unit');
            const inputElementPrice = $(elTable).find('.input__price');
            const selectElementSupplier = $(elTable).find('.select__agency');
            const inputMaterialId = $(elTable).find('.value_id__material');
            const inputQuantityStock = $(elTable).find('input.input__quantity_material');

            if(hasStock){
                // selectElement.append('<option value="">Danh sách nguyên phụ liệu</option>');
                selectElement.prop('name', `${parameter}[material][1][name]`);

                inputElementQty.prop('name', `${parameter}[material][1][quantity]`);
                inputElementCode.prop('name', `${parameter}[material][1][code]`);
                inputElementUnit.prop('name', `${parameter}[material][1][unit]`);
                inputElementPrice.prop('name', `${parameter}[material][1][price]`);
                selectElementSupplier.prop('name', `${parameter}[material][1][supplier_id]`);
                inputMaterialId.prop('name', `${parameter}[material][1][material_id]`);
                inputMaterialId.val(data[0].id);

                if(data[0].pivot)
                {
                    inputQuantityStock.val(data[0].pivot.quantity);
                }
            }

            let optionSelect = [];

            $.each(data, function (index, material) {
                try{
                    if(data[index].pivot)
                    {
                        selectElement.append('<option data-qty="'+ data[index].pivot.quantity +'" data-code="' + material.code + '" data-unit="' + material.unit + '" data-id="' + material.id + '" value="' + material.name + '">' + material.name + '</option>');
                        optionSelect.push({
                            'id': material.id,
                            'text': material.name,
                            "data-id": material.code,
                            "data-code": material.code
                        });
                    }
                    else{
                        selectElement.append('<option data-qty="" data-code="' + material.code + '" data-unit="' + material.unit + '" data-id="' + material.id + '" value="' + material.name + '">' + material.name + '</option>');
                    }

                }catch(err)
                {
                    console.log(err);
                }
            });

            OPTION_SELECT = optionSelect

            let options = setOptionSearchWithCode([])

            if(parameter == 'supplier')
            {
                options.data = OPTION_SELECT
            }else{
                const inputMaterialQty = $(elTable).find('.input__quantity_material');

                let curMaterialQty = selectElement.find(':selected').attr('data-qty');
                inputMaterialQty.val(curMaterialQty);

            }
            selectElement.select2(options)

            let materialCode = selectElement.find(':selected').attr('data-code');
            let materialUnit = selectElement.find(':selected').attr('data-unit');
            inputElementCode.val(materialCode);
            inputElementUnit.val(materialUnit);

            if(parameter == 'supplier'){
                // Kiểm tra xem giá trị có trong mảng hay không
                if (!ARR_MATERIAL_SELECT_SUPPLIER.includes(selectElement.find(':selected').attr('data-id'))) {
                    // Nếu giá trị không có trong mảng, thêm vào
                    ARR_MATERIAL_SELECT_SUPPLIER.push(selectElement.find(':selected').attr('data-id'));
                }
            }else{
                // Kiểm tra xem giá trị có trong mảng hay không
                if (!ARR_MATERIAL_SELECT_STOCK.includes(selectElement.find(':selected').attr('data-id'))) {
                    // Nếu giá trị không có trong mảng, thêm vào
                    ARR_MATERIAL_SELECT_STOCK.push(selectElement.find(':selected').attr('data-id'));
                }
            }
        }
    }

    function changeAllOptionChild(table, data, parameter)
    {
        const trItem = table.querySelectorAll('tbody tr');

        if(trItem)
        {
            trItem.forEach((tr,index) => {
                if(data?.length > 0)
                {
                    let selectElement = $(tr).find('select.select__product');
                    let inputCode = $(tr).find('input.input__code');

                    selectElement.empty();
                    $.each(data, function (index, material) {
                        selectElement.append('<option data-qty="'+ material.pivot.quantity +'" data-code="' + material.code + '" data-id="' + material.id + '" value="' + material.name + '">' + material.name + '</option>');
                    });

                    let materialCode = selectElement.find(':selected').attr('data-code');
                    inputCode.val(materialCode);
                }else{
                    let selectElement = $(tr).find('select.select__product');
                    selectElement.empty();
                    selectElement.append('<option disable>Không có nguyên phụ liệu</option>')
                }
            })
        }
    }

    function getCodeByIdMaterial(list, id)
    {
        let code = [];

        for (let index = 0; index < list.length; index++) {
            const element = list[index];
            if(id == element.id)
            {
                code[0] = element.code;
                code[1] = element.id;
                code[2] = element.unit;
                break;
            }

        }
        return code;
    }

    function getCodeWhenChangeOption(arrAllProduct, itemRow)
    {
        let selectElement = $(itemRow).find(".select__product");

        // let inputQuantityStock = $(itemRow).find('input.input__quantity_material');

        const inputMaterialId = $(itemRow).find('.value_id__material');
        const inputMaterialUnit = $(itemRow).find('.input__unit');

        let curMaterialId = selectElement.find(':selected').attr('data-id');

        let codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
        let idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
        let materialUnit = getCodeByIdMaterial(arrAllProduct, curMaterialId)[2];

        let inputCode = $(itemRow).find(".input__code");

        inputCode.val(codeOfMaterial);
        inputMaterialId.val(idMaterial);
        inputMaterialUnit.val(materialUnit);
    }

    function setOptionChooseStock(arrStock, branchId)
    {
        let arr = '';
        arrStock?.forEach(stock => {
            if(stock.id != branchId)
            {
                if(stock.materials.length === 0)
                {
                    arr += `<option value="${stock.id}">${stock.name} - Đã hết nguyên phụ liệu</option>`
                }else{
                    arr += `<option value="${stock.id}">${stock.name}</option>`
                }
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

    function setCodeWhenChangeStock(arrAllProduct, listStock, stockId)
    {
        const tableProposal = document.querySelector('table#tab_logic');
        if(tableProposal)
        {
            const groupTrRow = tableProposal.querySelectorAll('tbody tr');

            groupTrRow?.forEach(itemRow => {
                let selectElementNode = itemRow.querySelector('select.select__product');
                let inputQuantityStock = itemRow.querySelector('input.input__quantity_material');

                getCodeWhenChangeOption(arrAllProduct, itemRow)
                if(selectElementNode.options.length > 0)
                {
                    let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    let valueQty = getQuantityInStock(listStock, stockId, materialId)

                    inputQuantityStock.value = valueQty;

                    //Get pre value before change selected
                    let previousValue;

                    // Lấy giá trị trước khi thay đổi
                    $(selectElementNode).on("select2:open", function () {
                        previousValue = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;
                    });

                      // Sự kiện "change" để xử lý khi giá trị thay đổi
                      $(selectElementNode).on("change", function () {
                        // Xử lý khi giá trị thay đổi
                        getCodeWhenChangeOption(arrAllProduct,itemRow)

                        let materialId = selectElementNode.options[selectElementNode.selectedIndex]?.dataset.id;


                        let valueQty = getQuantityInStock(listStock, stockId, materialId)
                        inputQuantityStock.value = valueQty;

                        // Tìm index của giá trị cần xoá trong mảng
                        let indexToRemove = ARR_MATERIAL_SELECT_STOCK.indexOf(previousValue);

                        // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
                        if (indexToRemove !== -1) {
                            // Xoá giá trị tại indexToRemove
                            ARR_MATERIAL_SELECT_STOCK.splice(indexToRemove, 1);

                            // Thêm giá trị mới vào mảng
                            ARR_MATERIAL_SELECT_STOCK.push(materialId);
                        }

                        setOptionMaterialWhenChange(tableProposal, ARR_MATERIAL_STOCK)
                    })
                }else{
                    selectElementNode.innerHTML = '<option disable>Không có nguyên phụ liệu</option>'
                }

            })
        }
    }

    function setMaterialWhenChangeSupplier(table, arrHasChoose){
        const groupTrRow = table.querySelectorAll('tbody tr');

        groupTrRow?.forEach(itemRow => {
            let selectElementNode = itemRow.querySelector('select.select__product');

            // getCodeWhenChangeOption(arrAllProduct, itemRow)
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

                    setOptionMaterialWhenChange(table, ARR_MATERIAL_SUPPLIER)
                })
            }else{
                selectElementNode.innerHTML = '<option disable>Không có sản phẩm</option>'
            }

        })
    }

    function getQuantityInStock(data, stockId, materialId)
    {
        let qty;

        for (let index = 0; index < data.length; index++) {
            const element = data[index];
            if(stockId == element.id)
            {
                for (let j = 0; j < element.materials.length; j++) {
                    const material = element.materials[j];
                    if(material.id == materialId)
                    {
                        qty = material.pivot.quantity;
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
        const inputMaterialId = currentElement.closest('tr').find('input.value_id__material');
        const inputMaterialUnit = currentElement.closest('tr').find('.input__unit');
        const inputMaterialQty = currentElement.closest('tr').find('.input__quantity_material');
        const inputMaterialCode = currentElement.closest('tr').find('.input__code');

        let curMaterialId = selectElement.find(':selected').attr('data-id');
        let curMaterialQty = selectElement.find(':selected').attr('data-qty');

        let codeOfMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[0];
        let idMaterial = getCodeByIdMaterial(arrAllProduct, curMaterialId)[1];
        let materialUnit = getCodeByIdMaterial(arrAllProduct, curMaterialId)[2];

        let inputCode = currentElement.closest('tr').find(".input__code");

        inputMaterialCode.val(codeOfMaterial);
        inputMaterialId.val(idMaterial);
        inputMaterialUnit.val(materialUnit);
        inputMaterialQty.val(curMaterialQty);

        currentElement.closest('tr').find('.input__quantity').val(0)
    });

    //Function get infomation of proposal purchase by id
    function getInfoProposalById(urlApi, id)
    {
        let modelProposal;
        $.ajax({
            url: `${urlApi}proposal/info/${id}`,
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

    function setOptionMaterialWhenChange(table, listMaterial, isEdit = null)
    {
        const tr = table.querySelectorAll('tbody tr');

        let arrCurrentSelect = [];

        try{
            tr.forEach(item => {
                const selectMaterial = item.querySelector('select.select__product');

                let materialId = selectMaterial.options[selectMaterial.selectedIndex]?.dataset.id;

                if(isEdit === true){
                    if($(item).attr('id') != 'addr0'){
                        arrCurrentSelect.push(materialId*1);
                    }
                }else{
                    arrCurrentSelect.push(materialId*1);
                }
            });
            let differentValues = arrayDifference(arrCurrentSelect, listMaterial);

            tr.forEach(item => {
                const selectMaterial = item.querySelector('select.select__product');
                selectMaterial.querySelectorAll('option').forEach(option => {
                    if (differentValues.includes(option.dataset.id*1)) {
                        option.disabled = false;
                    } else {
                        option.disabled = true;
                    }
                })
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
            console.log(item);
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
});
