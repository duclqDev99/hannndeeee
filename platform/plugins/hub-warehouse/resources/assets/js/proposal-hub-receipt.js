// let rowNumber = 1;
// let allProduct = [];
// let selectedProductId = [];

// function getAllHub() {
//     $.ajax({
//         url: route('hub-warehouse.getHub'),
//         method: 'GET',
//         success: function (res) {
//             let dropdown = $('.select_hub');
//             $.each(res, function (index, hub) {
//                 dropdown.append('<option value="' + hub.id + '">' + hub.name + '</option>');
//             });
//         },
//         error: function (error) {
//             console.error('Error fetching material data:', error);
//         }
//     });
// }

// function getProductInFormProposal(idProposal) {
//     $.ajax({
//         url: route('proposal-hub-recepit.proposal', { proposal_id: idProposal }),
//         method: 'GET',
//         async: false,
//         success: function (res) {
//             console.log(res);
//             var idHubReceipt = (res['idHubReceipt']);
//             $('#hub_id').val(idHubReceipt).trigger('change.select2');
//             getWarehouseForHub(idHubReceipt, $('#warehouse_receipt_id'), res['proposal']['warehouse_receipt_id']);
//             // var idHub = res['idHub']
//             // var idWarehouse = res['idWarehouse']
//             var warehouseProduct = $('.select_warehouse_product');
//             warehouseProduct.val(res.proposal.warehouse_id);
//             let listProduct = res['data']
//             $.each(listProduct, function (index, rowData) {
//                 addNewRow(rowNumber);
//                 rowNumber++;
//                 let row = $('#addr' + (index + 1))
//                 let selectProduct = row.find('.select_product');
//                 if (!selectProduct.hasClass('select2-hidden-accessible')) {
//                     selectProduct.select2();
//                 }
//                 selectProduct.val(rowData.product_id);
//                 let optionExists = selectProduct.find('option[value="' + rowData.product_id + '"]').length > 0;
//                 if (!optionExists) {
//                     let newOption = new Option(rowData.product_name + ' - ' + rowData.attribute
//                         , rowData.product_id, true, true);
//                     selectProduct.append(newOption);
//                     getProductByWarehouse(selectProduct, res['proposal']['warehouse_type'], res['proposal']['warehouse_id'], rowData.product_id);
//                 }
//                 row.find('.select_product').attr('prev', rowData.product_id)
//                 selectedProductId.push(rowData.product_id.toString());
//                 row.find('.select_product').val(rowData.product_id)
//                 row.find('.input__code').val(rowData.sku);
//                 checkQuantityProduct(res['proposal']['warehouse_id'], rowData.product_id).then(quantity => {
//                     row.find('#quantityStock').val(quantity);
//                     row.find('.input_quantity').val(rowData.quantity).attr({
//                         "max": quantity,
//                         "min": 1
//                     });

//                 }).catch(error => {
//                     console.error("Error in fetching quantity:", error);
//                 });

//             })
//             rowNumber = listProduct.length;
//         },
//         error: function (error) {
//             console.error('Error fetching material data:', error);
//         }
//     })
// }
// function checkQuantityProduct(warehouse, productId) {
//     return new Promise((resolve, reject) => {
//         $.ajax({
//             url: route('finished-products.checkQuantity', { warehouseId: warehouse, id: productId }),
//             method: 'GET',
//             success: function (res) {
//                 resolve(res.data.quantity);
//             },
//             error: function (err) {
//                 reject(err);
//             }
//         });
//     });
// }
// function getAllWarehouseProduct() {
//     $.ajax({
//         url: route('warehouse-finished-products.getAllWarehouse'),
//         method: 'GET',
//         success: function (res) {
//             let dropdown = $('.select_warehouse_product');
//             $.each(res.data, function (index, warehouse) {
//                 dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
//             });
//             var proposal_id = $('#proposal_id').val();
//             if(proposal_id)
//             {
//                 getProductInFormProposal(proposal_id);
//             }
//         },
//         error: function (error) {
//             console.error('Error fetching material data:', error);
//         }
//     });
// }
// function getProductByWarehouse(selectElement, model, id, idProduct = null) {
//     $.ajax({
//         url: route('hub-warehouse.getProductByWarehouse', { id: id }),
//         method: 'GET',
//         data: {
//             model: model
//         },
//         success: function (res) {

//             let data = res.data.products;
//             lengthProduct = data.length;
//             $.each(data, function (index, product) {
//                 let attribute = [];
//                 $.each(product.variation_product_attributes, function (attrIndex, attr) {
//                     attribute.push(attr.title);
//                 });
//                 allProduct[product.id] = {
//                     name: product.name,
//                     attr: attribute,
//                     sku: product.sku,
//                     quantity: product.pivot.quantity
//                 };
//                 if (product.id != idProduct) {
//                     var option = '<option value="' + product.id
//                         + '" sku= "' + product.sku + '" quantity="' +
//                         product.pivot.quantity + '">' + product.name + ' - ' + attribute.join(', ') + '</option>';
//                 }
//                 selectElement.append(option);

//             });

//         },
//         error: function (error) {
//             console.error('Error fetching material data:', error);
//         }
//     });

// }
// function addNewRow(rowNumber) {
//     const newRow = $("#addr0").clone();
//     newRow.attr("id", "addr" + rowNumber);
//     newRow.find('td:first-child').html(rowNumber);
//     $(newRow).css("display", "table-row");
//     let options = {
//         width: '100%',
//     }
//     let select2Ele = $(newRow).find("select")
//     let parent = select2Ele.closest('div[data-select2-dropdown-parent]') || select2Ele.closest('.modal')
//     if (parent.length) {
//         options.dropdownParent = parent
//         options.minimumResultsForSearch = -1
//     }

//     select2Ele.select2(options)
//     let dataSelect = {};
//     for (const [key, value] of Object.entries(allProduct)) {
//         dataSelect[key] = { id: key, text: value.name, attr: value.attr, sku: value.sku, quantity: value.quantity };
//         if (selectedProductId.includes(key)) {
//             dataSelect[key].disabled = true;
//         }
//     }
//     $.each(dataSelect, function (index, value) {
//         let option = $('<option value="' + index + '" sku= "' + value.sku + '" quantity="' +
//             value.quantity + '">' + value.text + ' - ' + value.attr.join(', ') + '</option>').attr('disabled', value.disabled);
//         select2Ele.append(option);
//     });
//     if (rowNumber > 1 || warehouse > 0) {
//         newRow.append('<td class="text-center"><button class="btn btn-danger delete-row btn-lg"><i class="fa fa-trash" aria-hidden="true"></i></button></td>');
//     }
//     $('#tab_logic').append(newRow[0]);
//     rowNumber++;
// }

// function getWarehouseForHub(idHub, element, idWarehouse = null) {
//     $.ajax({
//         url: route('warehouse-stock.getWarehouse', { id: idHub }),
//         method: 'GET',
//         success: function (res) {
//             let data = res
//             let dropdown = element;
//             dropdown.empty()
//             if (data.length > 0) {
//                 dropdown.append('<option value="0">Chọn kho </option>');
//             }
//             else {
//                 dropdown.append('<option value="0">Vui lòng chọn hub khác - hub không có kho</option>');
//             }
//             $.each(data, function (index, hub) {
//                 dropdown.append('<option value="' + hub.id + '">' + hub.name + '</option>');
//             });
//             if (idWarehouse) {
//                 element.val(idWarehouse).trigger('change.select2');
//             }
//         },
//         error: function (error) {
//             console.error('Error fetching material data:', error);
//         }
//     });
// }
// $(document).ready(function () {
//     var proposal_id = $('#proposal_id').val();
//     getAllWarehouseProduct()
//     if (proposal_id != '') {

//     }
//     else {
//         addNewRow(rowNumber);
//     }
//     getAllHub();
//     $('.select_warehouse_product, .select_warehouse_out, .select_hub_warehouse').on('change', function () {
//         selectedProductId = [];
//         allProduct = [];
//         $(".material-row:not('#addr0')").remove()
//         let model = $(this).attr('data-model')
//         var id = $(this).val()
//         rowNumber = 1
//         addNewRow(rowNumber)
//         getProductByWarehouse($('tbody tr:not("#addr0") .select_product'), model, id);
//     });
//     $('input[name="is_warehouse"]').on('change', function () {
//         let selectedValue = $(this).val();

//         if (selectedValue == 1) {
//             $('#warehouse').css('display', 'none')
//             $('#hub').css('display', 'none')
//             $('#warehouse-hub-other').css('display', 'block')
//         }
//         else if (selectedValue == 2) {
//             $('#warehouse').css('display', 'none')
//             $('#hub').css('display', 'block')
//             $('#warehouse-hub-other').css('display', 'none')
//         }
//         else {
//             $('#warehouse-hub-other').css('display', 'none')
//             $('#warehouse').css('display', 'block')
//             $('#hub').css('display', 'none')

//         }
//     })
//     $('#hub_id').on('change', function () {
//         let hub = $(this).val();
//         let selectHub = $('.select_hub').val();
//         if (hub == selectHub) {
//             $('.select_hub').val(0).trigger('change.select2');
//             $('.select_hub_warehouse').empty();
//             $('.select_hub_warehouse').append('<option value="0">Vui lòng chọn hub </option>')
//         }
//         $('.select_hub option').prop('disabled', false);
//         $(`.select_hub option[value='${hub}']`).prop('disabled', true);
//         getWarehouseForHub(hub, $('#warehouse_receipt_id'));
//     })
//     $('#warehouse_receipt_id').on('change', function () {
//         let warehouse = $(this).val();
//         if (warehouse == $('#warehouse_out').val()) {
//             $('#warehouse_out').val(0).trigger('change.select2');
//         }
//         $('#warehouse_out option').prop('disabled', false);
//         $(`#warehouse_out option[value='${warehouse}']`).prop('disabled', true);
//     })
//     $('.select_hub').on('change', function () {
//         let selectHub = $(this).val();
//         getWarehouseForHub(selectHub, $('.select_hub_warehouse'));
//     })

//     //chọn sản phẩm show thông tin
//     $(document).on('change', '.select_product', function (e) {
//         let currentElement = $(this);
//         let selectedOption = currentElement.find('option:selected');
//         let skuValue = selectedOption.attr('sku');
//         let quantity = selectedOption.attr('quantity');
//         let exitEarly = false;
//         let selectedValue = currentElement.val();

//         $('tr:not("#addr0") .select_product').each(function (index, value) {
//             let selectValue = $(this).val();
//             if (!$(this).is(currentElement)) {
//                 $(this).find('option').each(function () {
//                     if ($(this).attr('value') === selectedValue) {
//                         $(this).prop('disabled', true);
//                     }
//                 });
//             }
//             if ($(this).is(currentElement) && $.inArray(selectValue, selectedProductId) !== -1) {
//                 e.preventDefault();
//                 toastr.clear()
//                 toastr['warning']('Sản phẩm đã tồn tại')
//                 currentElement.val(currentElement.attr('prev')).trigger('change.select2');
//                 exitEarly = true;
//                 return false;
//             }
//         });

//         if (exitEarly) return;
//         let previousValue = currentElement.attr('prev');
//         $('tr:not("#addr0") .select_product').not(currentElement).each(function () {
//             $(this).find('option[value="' + previousValue + '"]').prop('disabled', false);
//         });
//         currentElement.closest('tr').find('.select_product').attr('prev', currentElement.val());
//         currentElement.closest('tr').find('.input__code').val(skuValue);
//         currentElement.closest('tr').find('.quantity-stock').val(quantity);
//         currentElement.closest('tr').find('.input_quantity').attr({
//             "max": quantity,
//             "min": 1
//         });
//         currentElement.attr('prev', currentElement.val());
//         let tBodyForm = currentElement.closest('tbody');
//         selectedProductId = tBodyForm.find('tr:not("#addr0") .select_product').map(function () { return this.value; }).get();

//     });
//     $("#add_row").click(function () {
//         console.log(rowNumber);
//         let currentRow = $("#addr" + (rowNumber));
//         let productValue = parseInt(currentRow.find("select[name='product[]']").val());
//         let quantityValue = parseInt(currentRow.find('#quantity').val()); // Assuming 'quantity' is an ID, not a name
//         if (!productValue || !quantityValue) {
//             toastr.clear();
//             toastr['warning']('Vui lòng nhập đủ thông tin');
//             return;
//         }
//         rowNumber++;
//         addNewRow(rowNumber);
//     })
//     $(document).on("click", ".delete-row ", function () {
//         $(this).closest('tr').remove();
//         let value = $(this).closest('tr').find('.select_product').val();
//         let index = selectedProductId.indexOf(value);
//         if (index !== -1) {
//             selectedProductId.splice(index, 1);
//         }
//         $("#tab_logic tbody tr").each(function (idx) {
//             $(this).find('td:first-child').html(idx);
//         });
//         rowNumber = $("#tab_logic tbody tr").length - 1;
//         if (rowNumber == 0) {
//             rowNumber = 1;
//         }

//     });
//     $(document).on('input', '.input_quantity', function () {
//         let quantity_input = parseInt($(this).val());
//         let min = parseInt($(this).attr('min'));
//         let max = parseInt($(this).attr('max'));

//         if (quantity_input == '' || quantity_input == 0) {
//             $(this).val(min)
//             toastr.clear()
//             toastr['warning']('Không được để trống')
//         }
//         if (quantity_input < min) {
//             $(this).val(min)
//             toastr.clear()
//             toastr['warning']('Không được < 1 ')
//         }
//         if (quantity_input > max) {
//             $(this).val(max)
//             toastr.clear()
//             toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho ')
//         }
//     })
// })
