$(document).ready(function () {
    const formMTproposal = $('form#botble-warehouse-forms-purchase-goods-form');
    const formMTproposalNode = document.querySelector('form#botble-warehouse-forms-purchase-goods-form');

    let ARR_MATERIAL = [];//Mảng chứa tất cả id nguyên phụ liệu của kho được chọn
    let ARR_MATERIAL_SELECT = [];//Mảng chứa id nguyên phụ liệu của các select đang được chọn ở phần nhập từ kho đến kho

    let arrAllProduct;

    if (formMTproposal.is(':visible')) {
        let urlApi = formMTproposal.find('input#url_api').val();

        let rowNumber = 1;

        let arrAllAgency = getListAgency(urlApi);
        arrAllProduct = getListProduct(urlApi);

        const selectElementSupplier = $('.select__agency');

        populateSelectOptionsSupplier(selectElementSupplier, arrAllAgency);

        const selectMaterial = formMTproposal.find('select.select__material');
        populateSelectOptions(formMTproposal, selectMaterial, arrAllProduct);

        //Add các id của các nguyên phụ liệu vào mảng
        getValueAndAddToArray(arrAllProduct, 'id', ARR_MATERIAL);

        setOptionMaterialWhenChange(formMTproposalNode, ARR_MATERIAL);//Gọi hàm kiểm tra nguyên liệu được chọn

        rowNumber = 1;
        $("#add_row").click(function () {
            const newRow = $("#addr0").clone();
            const newRowId = 'addr' + rowNumber;

            const proposal_element = formMTproposalNode.querySelector('input#proposal_id');

            let so_thu_tu = rowNumber + 1;

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                so_thu_tu = rowNumber;

                $("#addr0").find('input').removeAttr("required");
                $("#addr0").find('select').removeAttr("required");
            }

            newRow.html(newRow.html()).find('td:first-child').html(so_thu_tu);
            newRow.attr('id', newRowId);

            newRow.html(newRow.html()).find('select.select__material').hide();
            newRow.html(newRow.html()).find('select.select__material').html('');
            newRow.html(newRow.html()).find('input.name__material').prop('disabled', false);
            newRow.html(newRow.html()).find('input.name__material').prop('hidden', false);

            newRow.html(newRow.html()).find('input.name__material').prop('name', `material[${so_thu_tu}][name]`);
            newRow.html(newRow.html()).find('input.name__material').prop('required', true);
            newRow.html(newRow.html()).find('select.select__material').prop('name', `material[${so_thu_tu}][name]`);
            newRow.html(newRow.html()).find('select.select__agency').prop('name', `material[${so_thu_tu}][supplier_id]`);
            newRow.html(newRow.html()).find('input.input__quantity').prop('name', `material[${so_thu_tu}][quantity]`);
            newRow.html(newRow.html()).find('input.input__quantity').prop('required', true);
            newRow.html(newRow.html()).find('input.input__code').prop('name', `material[${so_thu_tu}][code]`);
            newRow.html(newRow.html()).find('input.input__code').prop('readonly', false);
            newRow.html(newRow.html()).find('input.input__unit').prop('name', `material[${so_thu_tu}][unit]`);
            newRow.html(newRow.html()).find('input.input__price').prop('name', `material[${so_thu_tu}][price]`);

            newRow.html(newRow.html()).find('input.value_id__material').prop('name', `material[${so_thu_tu}][material_id]`);

            newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

            $('#tab_logic').append(newRow);

            formMTproposalNode.querySelector(`tr#${newRowId}`).style.display = 'table-row';

            updateRowNumber(formMTproposalNode)

            rowNumber++;
        });

        //Thêm nguyên phụ liệu có sẵn
        $(document).on("click", '#add_new', function () {
            const newRow = $("#addr0").clone();
            const newRowId = 'addr' + rowNumber;

            let so_thu_tu = rowNumber + 1;

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                so_thu_tu = rowNumber;
            }

            newRow.html(newRow.html()).find('td:first-child').html(so_thu_tu);
            newRow.attr('id', newRowId);

            newRow.find('input.name__material').hide();
            newRow.find('input.name__material').prop('required', false);
            newRow.find('select.select__material').prop('disabled', false);
            newRow.find('select.select__material').prop('hidden', false);

            newRow.find('input.name__material').prop('name', `material[${rowNumber}-old][name]`);
            newRow.find('select.select__material').prop('name', `material[${rowNumber}-old][name]`);
            newRow.find('select.select__agency').prop('name', `material[${rowNumber}-old][supplier_id]`);
            newRow.find('input.input__quantity').prop('name', `material[${rowNumber}-old][quantity]`);
            newRow.find('input.input__quantity').prop('required', true);
            newRow.find('input.input__code').prop('name', `material[${rowNumber}-old][code]`);
            newRow.find('input.input__code').prop('readonly', true);
            newRow.find('input.input__unit').prop('name', `material[${rowNumber}-old][unit]`);
            newRow.find('input.input__unit').prop('readonly', true);
            newRow.find('input.input__price').prop('name', `material[${rowNumber}-old][price]`);
            newRow.find('input.input__price').prop('readonly', true);

            newRow.find('input.value_id__material').prop('name', `material[${rowNumber}-old][material_id]`);

            newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

            let options = setOptionSearchWithCode([])

            newRow.find('select.select__material').select2(options);

            let infoCurrentMaterial = getInfoByIdMaterial(arrAllProduct, newRow.find('select.select__material :selected').attr('data-id'));

            if(typeof infoCurrentMaterial != undefined)
            {
                newRow.find('input.input__code').val(infoCurrentMaterial[0]);
                newRow.find('input.value_id__material').val(infoCurrentMaterial[1]);
                newRow.find('input.input__unit').val(infoCurrentMaterial[2]);
                newRow.find('input.input__price').val(infoCurrentMaterial[3]);
            }

            newRow.css("display", "table-row");

            $('#tab_logic').append(newRow);

            updateRowNumber(formMTproposalNode)

            rowNumber++;

            setOptionMaterialWhenChange(formMTproposalNode, ARR_MATERIAL)
        })

        $(document).on("click", ".delete-row ", function () {
            let valueToRemove = $(this).closest('tr').find('select.select__material :selected').attr('data-id');
            // Tìm index của giá trị cần xoá trong mảng
            let indexToRemove = ARR_MATERIAL_SELECT.indexOf(valueToRemove);

            // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
            if (indexToRemove !== -1) {
                // Xoá giá trị tại indexToRemove
                ARR_MATERIAL_SELECT.splice(indexToRemove, 1);
            }

            $(this).closest('tr').remove();

            updateRowNumber(formMTproposalNode);
            //Gọi hàm set các option của từng select nguyên phụ liệu
            setOptionMaterialWhenChange(formMTproposalNode, ARR_MATERIAL);
        });

        const expectedDate = formMTproposalNode.querySelector('input[name=expected_date]');

        if(expectedDate)
        {
            // Lấy ngày hiện tại
            var currentDate = new Date();
            // Tạo Flatpickr
            var flatpickrInput = flatpickr(expectedDate, {
                dateFormat: 'd-m-Y',
                onClose: function(selectedDates, dateStr, instance) {
                    // Kiểm tra xem ngày đã chọn có lớn hơn ngày hiện tại hay không
                    if (selectedDates.length > 0 && selectedDates[0] < currentDate) {
                        // Nếu ngày nhỏ hơn ngày hiện tại, hiển thị thông báo
                        alert('Vui lòng chọn ngày dự kiến bằng hoặc lớn hơn ngày hiện tại.');
                        // Focus lại vào ô nhập ngày
                        instance.setDate(currentDate);
                    }
                }
            });
        }


        let currentModelProposal = null;

        //Show option when edit
        const proposal_element = formMTproposalNode.querySelector('input#proposal_id');
        if(proposal_element)
        {
            let proposal_id = proposal_element.value;
            if(proposal_id != 0)
            {
                currentModelProposal = getInfoProposalGoodsById(urlApi, proposal_id);

                if(currentModelProposal != null)
                {
                    //Set body table
                    const tbodyTable = formMTproposalNode.querySelector('table.table__delivery tbody');
                    if(tbodyTable)
                    {
                        rowNumber = 1;
                        currentModelProposal.proposal_detail.forEach((material, index) => {
                            console.log(material);
                            const newRow = $($(tbodyTable)).find("#addr0").clone();
                            const newRowId = 'addr' + (rowNumber);

                            newRow.find('td:first-child').html(rowNumber);
                            newRow.attr('id', newRowId);

                            if(material.material_id != null)
                            {
                                newRow.find('input.name__material').hide();
                                newRow.find('select.select__material').prop('disabled', false);
                                newRow.find('select.select__material').prop('hidden', false);
                                newRow.find('input.name__material').prop('required', false);
                               
                                newRow.find('input.name__material').prop('name', `material[${rowNumber}-old][name]`);
                                newRow.find('select.select__agency').prop('name', `material[${rowNumber}-old][supplier_id]`);
                                newRow.find('input.input__quantity').prop('name', `material[${rowNumber}-old][quantity]`);
                                newRow.find('input.input__code').prop('name', `material[${rowNumber}-old][code]`);
                                newRow.find('input.input__code').prop('readonly', false);
                                newRow.find('input.input__unit').prop('name', `material[${rowNumber}-old][unit]`);
                                newRow.find('input.input__price').prop('name', `material[${rowNumber}-old][price]`);
    
                                newRow.find('input.value_id__material').prop('name', `material[${rowNumber}-old][material_id]`);

                                let options = setOptionSearchWithCode([])
                                
                                newRow.find('select.select__material option').each(function(){
                                    if($(this).attr('data-id') == material.material_id){
                                        $(this).prop('selected', true);

                                        newRow.find('input.value_id__material').val( material.material_id);
                                    }
                                })

                                newRow.find('select.select__material').select2(options)
                                
                            }else{
                                newRow.find('select.select__material').hide();
                                newRow.find('input.name__material').prop('disabled', false);
                                newRow.find('input.name__material').prop('hidden', false);
                                newRow.find('select.select__material').prop('required', false);
                                newRow.find('input.name__material').prop('name', `material[${rowNumber}][name]`);
                                newRow.find('select.select__agency').prop('name', `material[${rowNumber}][supplier_id]`);
                                newRow.find('input.input__quantity').prop('name', `material[${rowNumber}][quantity]`);
                                newRow.find('input.input__code').prop('name', `material[${rowNumber}][code]`);
                                newRow.find('input.input__code').prop('readonly', false);
                                newRow.find('input.input__unit').prop('name', `material[${rowNumber}][unit]`);
                                newRow.find('input.input__price').prop('name', `material[${rowNumber}][price]`);
    
                                newRow.find('input.value_id__material').prop('name', `material[${rowNumber}][material_id]`);
                            }


                            newRow.find('input.name__material').val(material.material_name);
                            newRow.find('input.input__code').val(material.material_code);
                            newRow.find('input.input__quantity').val(material.material_quantity);
                            newRow.find('input.input__unit').val(material.material_unit);
                            newRow.find('input.input__price').val(material.material_price);

                            newRow.find('select.select__agency option').each(function(){
                                if($(this).val() == material.supplier_id){
                                    $(this).prop('selected', true);
                                }
                            })

                            newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');

                            $($(formMTproposalNode)).find('#tab_logic').append(newRow);

                            rowNumber++;
                        })

                        tbodyTable.querySelector('tr#addr0 input.name__material').required = false;
                        tbodyTable.querySelector('tr#addr0 input.input__quantity').required = false;
                        tbodyTable.querySelector('tr#addr0').style.display = 'none';

                        //Gọi hàm set các option của từng select nguyên phụ liệu
                        setOptionMaterialWhenChange(formMTproposalNode, ARR_MATERIAL);
                    }
                }
            }
        }

        //Validate for request
        const btnSubmitSaveExit = formMTproposalNode.querySelector('.btn-list button[value=save]');
        const btnSubmitSave = formMTproposalNode.querySelector('.btn-list button[value=apply]');


        btnSubmitSaveExit.addEventListener('click', function(){
            preventDefaultForm(formMTproposalNode)

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                if(currentModelProposal != null)
                {
                    const tbodyTable = formMTproposalNode.querySelector('table.table__delivery tbody');
                    tbodyTable.querySelector('tr#addr0').remove();
                }
            }

            checkValidate(urlApi, formMTproposalNode);
        })

        btnSubmitSave.addEventListener('click', function(){
            preventDefaultForm(formMTproposalNode)

            let proposal_id = proposal_element.value;
            if(proposal_id != 0){
                if(currentModelProposal != null)
                {
                    const tbodyTable = formMTproposalNode.querySelector('table.table__delivery tbody');
                    tbodyTable.querySelector('tr#addr0').remove();
                }
            }

            checkValidate(urlApi, formMTproposalNode);
        })

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


    function populateSelectOptionsSupplier(selectElement, data) {
        selectElement.empty();
        $.each(data, function (index, material) {
            selectElement.append('<option value="' + material.id + '">' + material.name + '</option>');
        });
    }

    function preventDefaultForm(form){
        form.onsubmit = () => {
            return false;
        }
    }

    function checkValidate(urlApi, form)
    {
        let inputDate = form.querySelector('input[name=expected_date]');

        // Lấy ngày hiện tại
        var ngayHienTai = new Date();

        // Trích xuất năm, tháng và ngày
        var nam = ngayHienTai.getFullYear();
        var thang = ('0' + (ngayHienTai.getMonth() + 1)).slice(-2);  // Thêm '0' ở trước nếu tháng chỉ có một chữ số
        var ngay = ('0' + ngayHienTai.getDate()).slice(-2);  // Thêm '0' ở trước nếu ngày chỉ có một chữ số

        // Định dạng theo Y-m-d
        var ngayHienTaiYMD = nam + '-' + thang + '-' + ngay;

        ngayHienTai = new Date(ngayHienTaiYMD);

        // Lấy giá trị ngày nhập vào từ một input (giả sử có một input với id là 'inputNgay')
        var ngayNhap = new Date(parseDate(inputDate.value));

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
        // So sánh ngày nhập vào với ngày hiện tại
        if (ngayNhap.getTime() >= ngayHienTai.getTime()) {
            const formData = new FormData(form);

            // Chuyển FormData thành đối tượng JavaScript
            let data = {};
            formData.forEach((value, key) => {
                // Nếu key đã tồn tại trong đối tượng data, thì thêm value vào mảng
                setValueByPath(data, key, value);
            });

            // Gửi dữ liệu qua API sử dụng Fetch API
            $.ajax({
                url: `${urlApi}validate/purchase-goods`,
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
                let messageHeader = response.msg

                if(response.error_code === 1)
                {
                    toastr['error'](messageHeader, 'Thông báo');

                    activeButtonSubmit(form);
                }else{
                    toastr['success'](messageHeader, 'Thông báo');

                    form.submit()
                }

            });
        } else {
            toastr['error']('Ngày dự kiến phải lớn hơn hoặc bằng ngày hiện tại.', 'Thông báo');
            inputDate.focus();
            activeButtonSubmit(form);
        }
    }

    function formatDateToISO(dateString) {
        // Chuyển đổi định dạng "dd-mm-yyyy" sang "yyyy-mm-dd"
        const [day, month, year] = dateString.split('-');
        const isoDate = `${year}-${month}-${day}`;
        return isoDate;
    }
    
    function parseDate(dateString) {
        // Chuyển đổi đối tượng Date từ định dạng "dd-mm-yyyy"
        const isoDate = formatDateToISO(dateString);
        const dateObject = new Date(isoDate);
        return dateObject;
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

    function activeButtonSubmit(form)
    {
        let timeOut = setTimeout(()=>{
            const btnSubmitSaveExit = form.querySelector('.btn-list button[value=save]');
            const btnSubmitSave = form.querySelector('.btn-list button[value=apply]');
            if (btnSubmitSaveExit.classList.contains('disabled')) {
                // Loại bỏ lớp 'example-class'
                btnSubmitSaveExit.classList.remove('disabled');
                btnSubmitSave.classList.remove('disabled');
                console.log('Class removed!');
            }
        }, 200)
    }

    //Function get infomation of proposal purchase by id
    function getInfoProposalGoodsById(urlApi, id)
    {
        let modelProposal;
        $.ajax({
            url: `${urlApi}proposal-goods/info/${id}`,
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

    function populateSelectOptions(elTable, selectElement, data) {
        selectElement.empty();

        if(typeof data !== 'undefined' && data.length > 0)
        {
            const inputElementName = $(elTable).find('.name__material');
            const inputElementQty = $(elTable).find('.input__quantity');
            const inputElementCode = $(elTable).find('.input__code');
            const inputElementUnit = $(elTable).find('.input__unit');
            const inputElementPrice = $(elTable).find('.input__price');
            const selectElementSupplier = $(elTable).find('.select__agency');
            const inputMaterialId = $(elTable).find('.value_id__material');
            const inputQuantityStock = $(elTable).find('input.input__quantity_material');

            // selectElement.append('<option value="">Danh sách nguyên phụ liệu</option>');
            selectElement.prop('name', `material[1][name]`);

            inputElementName.prop('name', `material[1][name]`);
            inputElementQty.prop('name', `material[1][quantity]`);
            inputElementCode.prop('name', `material[1][code]`);
            inputElementUnit.prop('name', `material[1][unit]`);
            inputElementPrice.prop('name', `material[1][price]`);
            selectElementSupplier.prop('name', `material[1][supplier_id]`);
            inputMaterialId.prop('name', `material[1][material_id]`);
            inputMaterialId.val(data[0].id);

            let optionSelect = [];

            $.each(data, function (index, material) {
                try{
                    selectElement.append('<option data-code="' + material.code + '" data-unit="' + material.unit + '" data-id="' + material.id + '" value="' + material.name + '">' + material.name + '</option>');
                    optionSelect.push({
                        'id': material.id,
                        'text': material.name,
                        "data-id": material.code
                    });
                }catch(err)
                {
                    console.log(err);
                }
            });

            // Kiểm tra xem giá trị có trong mảng hay không
            if (!ARR_MATERIAL_SELECT.includes(selectElement.find(':selected').attr('data-id'))) {
                // Nếu giá trị không có trong mảng, thêm vào
                ARR_MATERIAL_SELECT.push(selectElement.find(':selected').attr('data-id'));
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


    function getInfoByIdMaterial(list, id)
    {
        let code = [];

        for (let index = 0; index < list.length; index++) {
            const element = list[index];
            if(id == element.id)
            {
                code[0] = element.code;
                code[1] = element.id;
                code[2] = element.unit;
                code[3] = element.price;
                break;
            }

        }
        return code;
    }

    //Get pre value before change selected
    let previousValue;

    // Lấy giá trị trước khi thay đổi
    $(document).on("select2:open", '.select__material', function () {
        previousValue = $(this).find(':selected').attr('data-id');
    });

    //Thay đổi thông tin tương ứng khi chọn nguyên liệu mới
    $(document).on('change', '.select__material', function () {
        currentElement = $(this);

        let selectElement = currentElement.closest('tr').find("select.select__material");
        const inputMaterialId = currentElement.closest('tr').find('input.value_id__material');
        const inputMaterialUnit = currentElement.closest('tr').find('.input__unit');
        const inputMaterialCode = currentElement.closest('tr').find('.input__code');
        const inputMaterialPrice = currentElement.closest('tr').find('.input__price');

        let curMaterialId = selectElement.find(':selected').attr('data-id');

        let codeOfMaterial = getInfoByIdMaterial(arrAllProduct, curMaterialId)[0];
        let idMaterial = getInfoByIdMaterial(arrAllProduct, curMaterialId)[1];
        let materialUnit = getInfoByIdMaterial(arrAllProduct, curMaterialId)[2];
        let materialPrice = getInfoByIdMaterial(arrAllProduct, curMaterialId)[3];

        inputMaterialCode.val(codeOfMaterial);
        inputMaterialId.val(idMaterial);
        inputMaterialUnit.val(materialUnit);
        inputMaterialPrice.val(materialPrice);

        // Tìm index của giá trị cần xoá trong mảng
        let indexToRemove = ARR_MATERIAL_SELECT.indexOf(previousValue);

        // Kiểm tra xem giá trị cần xoá có tồn tại trong mảng hay không
        if (indexToRemove !== -1) {
            // Xoá giá trị tại indexToRemove
            ARR_MATERIAL_SELECT.splice(indexToRemove, 1);

            // Thêm giá trị mới vào mảng
            ARR_MATERIAL_SELECT.push(curMaterialId);
        }

        setOptionMaterialWhenChange(formMTproposalNode, ARR_MATERIAL)
    });

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
                const selectMaterial = item.querySelector('select.select__material');

                let materialId = selectMaterial.options[selectMaterial.selectedIndex]?.dataset.id;

                if($(item).attr('id') != 'addr0'){
                    arrCurrentSelect.push(materialId*1);
                }
            });
            let differentValues = arrayDifference(arrCurrentSelect, listMaterial);

            tr.forEach(item => {
                const selectMaterial = item.querySelector('select.select__material');
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
