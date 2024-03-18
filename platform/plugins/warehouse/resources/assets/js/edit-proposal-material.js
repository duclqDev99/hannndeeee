
let isValid = true;
let rowNumber = 1;
let allMaterial = [];
let selectedMaterialId = [];
let lengthMaterial = 0;
function materialOptions(selectElement, idMaterial = null) {
    let warehouse = $('#choose-warehouse').val();
    if (warehouse != '' || warehouse != 0) {
        $.ajax({
            url: '/admin/materials/getMaterialByWarehouse/' + warehouse,
            method: 'GET',
            success: function (data) {
                lengthMaterial = (data.length)
                $.each(data, function (index, material) {
                    allMaterial[material.id] = {
                        name: material.name,
                        code: material.code,
                        price: material.price,
                        quantity: material.pivot.quantity,
                        unit: material.unit
                    }
                    if (material.id != idMaterial) {
                        var option = $('<option value="' + material.id + '" price= "' + material.price + '" quantity="' +
                            material.pivot.quantity + '" code="' + material.code + '" unit="' + material.unit + '" >' + material.name + '</option>');
                    }

                    selectElement.append(option);
                });

            },
            error: function (error) {
                console.error('Error fetching material data:', error);
            }
        })
    }
}
function addNewRow(rowNumber) {

    const newRow = $("#addr0").clone();
    newRow.attr("id", "addr" + rowNumber);
    newRow.find('td:first-child').html(rowNumber);
    $(newRow).css("display", "table-row");
    let options = {
        width: '100%',
        templateResult: function (data) {
            var $result = $("<span></span>");
            $result.text(data.text);
            return $result;
        },
        matcher: function (params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }

            if (typeof data.text === 'undefined') {
                return null;
            }

            var text = data.text.toLowerCase();
            var term = params.term.toLowerCase();
            var hidden = '';

            if (data?.element.getAttribute('code') != null) {
                hidden = data.element.getAttribute('code').toLowerCase();
                if (text.indexOf(term) > -1 || hidden.indexOf(term) > -1) {
                    return data;
                }
            }
            return null;
        }
    }
    let select2Ele = $(newRow).find("select")
    let parent = select2Ele.closest('div[data-select2-dropdown-parent]') || select2Ele.closest('.modal')
    if (parent.length) {
        options.dropdownParent = parent
        options.minimumResultsForSearch = -1
    }

    var dataSelect = {};

    for (const [key, value] of Object.entries(allMaterial)) {
        dataSelect[key] = { id: key, text: value.name, price: value.price, code: value.code, quantity: value.quantity, unit: value.unit };
        if (selectedMaterialId.includes(key)) {
            dataSelect[key].disabled = true;
        }
    }
    $.each(dataSelect, function (index, value) {
        let option = $('<option value="' + index + '" price= "' + value.price + '" quantity="' +
            value.quantity + '" code="' + value.code + '" unit="' + value.unit + '" >' + value.text + '</option>').attr('disabled', value.disabled);
        select2Ele.append(option);
    });
    select2Ele.select2(options)
    newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');
    $('#tab_logic').append(newRow[0]);
    rowNumber++;
}

function getMaterialOutInfo(idMaterial) {
    $.ajax({
        url: '/admin/proposal-goods-issue/getMaterialOutInfo/' + idMaterial,
        method: 'GET',
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function (res) {
            var idOut = (res['process']['warehouse_out_id']);
            if (res['process']['is_processing_house'] == 1) {
                $('.select_processing').val(idOut).trigger('change.select2');;
                $('#warehouse').css('display', 'none')
                $('#processing_house').css('display', 'block')
            } else {
                $('#warehouse').css('display', 'block')
                $('#processing_house').css('display', 'none')
                $('.select_warehouse_out').val(idOut).trigger('change.select2');
            }
            var listMaterial = getListMaterialProposalOut(idMaterial, function (data) {
                listMaterial = data;
                $.each(listMaterial, function (index, rowData) {
                    addNewRow(rowNumber);
                    rowNumber++;
                    let row = $('#addr' + (index + 1))
                    let selectMaterial = row.find("select");
                    if (!selectMaterial.hasClass('select2-hidden-accessible')) {
                        selectMaterial.select2();
                    }
                    selectMaterial.val(rowData.materials.id);
                    let optionExists = selectMaterial.find('option[value="' + rowData.materials.id + '"]').length > 0;
                    if (!optionExists) {
                        let newOption = new Option(rowData.materials.name, rowData.materials.id, true, true);
                        selectMaterial.append(newOption);
                        materialOptions(selectMaterial, rowData.materials.id);
                    }
                    row.find('.select_material').attr('prev', rowData.materials.id)
                    selectedMaterialId.push(rowData.materials.id.toString());
                    row.find('.select_material').val(rowData.materials.id)
                    row.find('.input__code').val(rowData.material_code);
                    row.find('.input_quantity').val(rowData.material_quantity).attr({
                        "max": rowData.quantity,
                        "min": 1
                    });
                    row.find('.select_unit').val(rowData.material_unit);
                    row.find('.select_quantity_stock').val(rowData.quantity);
                    row.find('.select_price').val(rowData.material_price);
                })

            })
        },
        error: function (error) {
            console.error('Error fetching material data:', error);
        }
    })
}
function getAllProcessHouse() {
    $.ajax({
        url: '/api/v1/getAllProcessingHouse',
        method: 'GET',
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function (res) {
            let data = res['process']
            var dropdown = $('.select_processing');


            $.each(data, function (index, warehouse) {
                dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
            });

        },
        error: function (error) {
            console.error('Error fetching material data:', error);
        }
    });
}

function getAllWarehouse() {
    $.ajax({
        url: '/api/v1/getAllWarehouse/',
        method: 'GET',
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function (res) {
            let data = res['process']
            var dropdown = $('.select_warehouse_out');
            $.each(data, function (index, warehouse) {
                dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
            });
            let idMaterialOut = $('#material_out_id').val()
            getMaterialOutInfo(idMaterialOut)
            let warehouse = $('#choose-warehouse').val();

            $(`.select_warehouse_out option[value='${warehouse}']`).prop('disabled', true);
        },
        error: function (error) {
            console.error('Error fetching material data:', error);
        }
    });
}
function getListMaterialProposalOut(idProposal, callback) {
    $.ajax({
        url: '/api/v1/getListMaterialProposalOut/' + idProposal,
        method: 'GET',
        success: function (res) {
            callback(res['data']);
        }
    })

}
function isValidValue(value) {
    return value !== undefined && value !== null && value.trim() !== '';
}
toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 1000,
    hideDuration: 1000,
    timeOut: 10000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

$(document).ready(function () {
    getAllProcessHouse();
    getAllWarehouse()

    $("#add_row").click(function () {
        var currentRow = $("#addr" + (rowNumber - 1));
        if (rowNumber > 1) {
            var materialValue = parseInt(currentRow.find("select[name='material[]']").val());
            var quantityValue = parseInt(currentRow.find("#quantity").val());
            if (materialValue === "" || quantityValue === "" || isNaN(materialValue) || isNaN(quantityValue)) {
                toastr.clear();
                toastr['warning']('Vui lòng nhập đầy đủ thông tin ');
                return false;
            }
        }
        if (rowNumber < lengthMaterial + 1) {
            addNewRow(rowNumber)
            rowNumber += 1;
        }
        else {
            toastr.clear();
            toastr['warning']('Đã vượt qua số lượng nguyên phụ liệu trong kho ');
            return false;
        }


    });



    $(document).on("click", ".delete-row", function () {
        var value = $(this).closest('tr').find('.select_material').val();
        $(this).closest('tr').remove();
        var index = selectedMaterialId.indexOf(value);
        if (index !== -1) {
            selectedMaterialId.splice(index, 1);
        }
        $("#tab_logic tbody tr").each(function (idx) {
            $(this).find('td:first-child').html(idx);
        });
        rowNumber = $("#tab_logic tbody tr").length - 1;
        if (rowNumber == 0) {
            rowNumber = 1;
        }
    });


    var selectedOptions = [];
    $(document).on('change', '.select_material', function (e) {
        let warehouse = $('#choose-warehouse').val();
        let currentElement = $(this);
        let selectedValue = currentElement.val();
        let exitEarly = false;
        let selectedOption = currentElement.find('option:selected');
        let code = selectedOption.attr('code');
        let unit = selectedOption.attr('unit');
        let price = selectedOption.attr('price');
        let quantity = selectedOption.attr('quantity');
        $('tr:not("#addr0") .select_material').each(function () {
            var selectValue = $(this).val();
            if (!$(this).is(currentElement)) {
                $(this).find('option').each(function () {
                    if ($(this).attr('value') === selectedValue) {
                        $(this).prop('disabled', true);
                    }
                });
            }
            if ($(this).is(currentElement) && $.inArray(selectValue, selectedMaterialId) !== -1) {
                e.preventDefault();
                toastr.clear()
                toastr['warning']('Sản phẩm đã tồn tại')
                currentElement.val(currentElement.attr('prev')).trigger('change.select2');
                exitEarly = true;
                return false;
            }
        });

        if (exitEarly) return;
        let previousValue = currentElement.attr('prev');
        $('tr:not("#addr0") .select_product').not(currentElement).each(function () {
            $(this).find('option[value="' + previousValue + '"]').prop('disabled', false);
        });
        currentElement.closest('tr').find('.select_material').attr('prev', currentElement.val());
        currentElement.closest('tr').find('.input__code').val(code);
        currentElement.closest('tr').find('.select_unit').val(unit);
        currentElement.closest('tr').find('.select_price').val(price);
        currentElement.closest('tr').find('.select_quantity_stock').val(quantity);
        currentElement.closest('tr').find('.input_quantity').attr({
            "max": quantity,
            "min": 1
        });
        currentElement.attr('prev', currentElement.val());
        let tBodyForm = currentElement.closest('tbody');
        selectedMaterialId = tBodyForm.find('tr:not("#addr0") .select_material').map(function () { return this.value; }).get();

    });
    $("#general_order_code").attr('readonly', true);
    $(document).on('input', '.input_quantity', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
        if (quantity_input == '' || quantity_input == 0) {
            toastr.clear();
            toastr['warning']('Không được trống');
        }
        if (quantity_input < min) {
            $(this).val(min)

        }
        if (quantity_input > max) {
            $(this).val(max)
            toastr.clear();
            toastr['warning']('Số lượng vượt quá trong kho');
        }
    })
    $('#choose-warehouse').on('change', function () {
        let warehouse = $(this).val();
        selectedMaterialId = [];
        allMaterial = [];
        let warehouse_out = $('#warehouse_out').val()
        if (warehouse === warehouse_out) {
            $('#warehouse_out').val(0).trigger('change.select2')
        }
        $('.select_warehouse_out option').prop('disabled', false);
        $(`.select_warehouse_out option[value='${warehouse}']`).prop('disabled', true);
        $(".material-row:not('#addr0')").remove()
        rowNumber = 1
        addNewRow(rowNumber)
        if (warehouse == 0) {
            $('#warehouse_out').val(0).trigger('change.select2')
            toastr.clear();
            toastr['warning']('Vui lòng chọn kho ');
        }
        else {
            let material = $('tbody tr:not("#addr0") .select_material')
            materialOptions(material);
            let select2Ele = $('#addr1').find("select.select_material")
            let options = {
                width: '100%',
                templateResult: function (data) {
                    // Use templateResult to include both display text and hidden attribute in the search
                    var $result = $("<span></span>");
                    $result.text(data.text);
                    return $result;
                },
                matcher: function (params, data) {
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

                    if (data?.element.getAttribute('code') != null) {
                        hidden = data.element.getAttribute('code').toLowerCase();
                        if (text.indexOf(term) > -1 || hidden.indexOf(term) > -1) {
                            return data;
                        }
                    }
                    return null;
                }
            }


            select2Ele.select2(options)
        }
    })
    $('input[name="is_processing_house"]').on('change', function () {
        var selectedValue = $(this).val();
        if (selectedValue == 1) {
            $('#warehouse').css('display', 'none')
            $('#processing_house').css('display', 'block')
        }
        else {
            $('#warehouse').css('display', 'block')
            $('#processing_house').css('display', 'none')
        }
    })
    $("form button[type='submit']").on("click", function (e) {
        e.preventDefault();
        var inputFields = $("form input[name^='input__'], form select[name^='material'], form input[name^='quantity']").slice(4);
        var isValid = true;
        $('#tab_logic tbody tr:not("#addr0")').each(function () {
            const $row = $(this);
            const quantity = parseFloat($row.find('#quantity').val());
            const quantityStock = parseFloat($row.find('#quantityStock').val());
            if (quantity > quantityStock) {
                isValid = false;
                toastr.clear();
                toastr['error']('Số lượng không được lớn hơn số lượng tồn kho.', { timeOut: 3000 });
                return false;
            } else if (quantity <= 0 || quantity == '') {
                isValid = false;
                toastr.clear();
                toastr['error']('Số lượng phải lớn hơn 0.', { timeOut: 3000 });
                return false;
            } else if (isNaN(quantity)) {
                isValid = false;
                toastr.clear();
                toastr['error']('Số lượng bắt không được để trống.', { timeOut: 3000 });
                return false;
            }
            else if (isNaN(quantityStock)) {
                isValid = false;
                toastr.clear();
                toastr['error']('Vui lòng chọn nguyên phụ liệu.', { timeOut: 3000 });
                return false;
            }
        });
        if ($('#tab_logic tbody tr:not("#addr0")').length === 0) {
            isValid = false;
            toastr.clear();
            toastr['error']('Không có nguyên phụ liệu được chọn.', { timeOut: 3000 });
        }
        console.log($('#tab_logic tbody tr:not("#addr0")').length);
        if (isValid) {
            $(this).closest('form').submit();
        }
    });

});
