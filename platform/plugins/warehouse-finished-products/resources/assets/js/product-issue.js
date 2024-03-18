// const { min } = require("lodash");

// document.addEventListener("DOMContentLoaded", (event) => {
//     var commonTblOpts = {
//         scrollX: true,
//         scrollY: 100,
//         paging: false,
//         info: false,
//         "columnDefs": [
//             { "orderable": false, "targets": [3] }
//         ],
//         language: {
//             emptyTable: "Không có dữ liệu.",
//             zeroRecords: "Không tìm thấy dữ liệu."
//         }
//     };

//     var tbl = $('.table-add-batch').DataTable(commonTblOpts);
//     var tblForm = $('.table-add-batch-form').DataTable($.extend({}, commonTblOpts, {
//         "columnDefs": [
//             { "orderable": false, "targets": [0, 1, 2, 3] }
//         ], "order": []
//     }));

//     function handleNextBtnClick(btn, srcTbl, tgtPrefix, arrowClsRemove, arrowClsAdd, quantityExpected) {
//         var row = btn.closest('tr');
//         var prodId = btn.find('input[name="product-id"]').val();
//         var rowData = srcTbl.row(row).data();
//         var batchQty = parseInt(row.find('#batch-quantity').val(), 10);
//         var batchId = row.find('.batch-id').val();
//         var OrderId = row.find('.order-product').val();
//         if (tgtPrefix == 'product-id-') {
//             var tgtTbl = $('#' + 'product-id-' + prodId).DataTable();
//             var totalQty = 0;
//             tgtTbl.rows().every(function () {
//                 var tr = $(this.node());
//                 var qtyVal = tr.find('#quantity').val();
//                 totalQty += parseInt(qtyVal, 10) || 0;
//             });
//             rowData.splice(3, 0, `<input type="number" name="productDetai[${batchId}][quantity]" id="quantity" value="${Math.min(batchQty, quantityExpected - totalQty)}" class="value-quantity text-center" max="${batchQty}" min="1" >
//             <input type="hidden" name="productDetai[${batchId}][issueProductId]" value="${OrderId}">`);

//         }
//         else {

//             rowData.splice(3, 1);
//         }
//         srcTbl.row(row).remove().draw();
//         var tgtTbl = $('#' + tgtPrefix + prodId).DataTable();
//         tgtTbl.row.add(rowData).draw();
//         tgtTbl.$('i.' + arrowClsRemove).removeClass(arrowClsRemove).addClass(arrowClsAdd);
//     }

//     function updateBatchQty(tblEl, deductQty, overrideQty = null, tgtPrefix = null) {
//         if (isNaN(deductQty)) {
//             console.error('Invalid deduction quantity');
//             return;
//         }
//         let newQty = overrideQty !== null ? parseFloat(overrideQty) : Math.max(tblEl.data('quantity') + deductQty, 0);
//         if (tgtPrefix) {
//             var prodId = tblEl.find('input[name="product-id"]').val();
//             var tgtTbl = $('#' + tgtPrefix + prodId);
//             tblEl = tgtTbl
//         }

//         tblEl.data('quantity', newQty);
//     }

//     $('.table-add-batch').on('click', '.next-table-btn', function () {
//         var row = $(this).closest('tr');
//         var tblAddBatch = row.closest('.table-add-batch');
//         var batchQty = parseInt(row.find('#batch-quantity').val(), 10);
//         var expQty = parseInt(tblAddBatch.find('.quantity-expected').val(), 10);
//         var prodId = tblAddBatch.find('input[name="product-id"]').val();
//         var tgtTbl = $('#' + 'product-id-' + prodId).DataTable();
//         var totalQty = 0;
//         tgtTbl.rows().every(function () {
//             var tr = $(this.node());
//             var qtyVal = tr.find('#quantity').val();
//             totalQty += parseInt(qtyVal, 10) || 0;
//         });
//         if (!tblAddBatch.data('isInitialized')) {
//             tblAddBatch.data({ 'quantity': 0, 'lengthRow': tblAddBatch.find('tbody tr').length, 'isInitialized': true });
//         }
//         if (totalQty < expQty) {
//             handleNextBtnClick($(this), tbl, 'product-id-', 'fa-arrow-right', 'fa-arrow-left', expQty);
//             updateBatchQty(tblAddBatch, batchQty);
//         } else {
//             toastr.clear();
//             toastr.warning('Đã đủ số lượng để xuất kho', 'Warning', { timeOut: 3000, closeButton: true });
//         }
//     });

//     $('.table-add-batch-form').on('click', '.next-table-btn', function () {
//         var row = $(this).closest('tr');
//         var tblAddBatch = row.closest('.table-add-batch-form');
//         var totalQty = tblAddBatch.find('tbody tr').toArray().reduce((sum, tr) => sum + parseInt($(tr).find('#quantity').val()), 0);
//         var batchQty = parseInt(row.find('#batch-quantity').val());
//         var expQty = parseInt(tblAddBatch.find('.expected-export').val(), 10);

//         updateBatchQty(tblAddBatch, batchQty, totalQty - batchQty, 'product-id-batch-');
//         handleNextBtnClick($(this), tblForm, 'product-id-batch-', 'fa-arrow-left', 'fa-arrow-right', expQty);
//     });

//     const setTotalQuantity = () => {
//         let total = 0;
//         $('input[data-name="quantity"]').each(function () {
//             total += +$(this).val();

//         })
//         $('#total-quantity').html(total);
//     }
//     $(document).off('click');
//     $(document).on('click', '.quantity_reduce_btn', function () {
//         let curr_quantity = $(this).next().val();
//         let min = $(this).next().prop('min');
//         const default_value = $(this).next().attr('default-value');
//         const batch_id = $(this).next().data('batch-id');
//         const valueSet = +curr_quantity - 1;
//         if (parseInt(curr_quantity) > parseInt(min)) {
//             $(this).next().val(valueSet);
//             if (valueSet == default_value) {
//                 $(`.collapse[data-batch-id="${batch_id}"]`).collapse("hide");
//                 $(`textarea[data-batch-id="${batch_id}"]`).val('');
//             } else {
//                 $(`.collapse[data-batch-id="${batch_id}"]`).collapse("show");
//             }
//             setTotalQuantity();
//         }
//     })
//     $(document).on('click', '.quantity_increment_btn', function () {
//         const curr_quantity = $(this).prev().val();
//         const max = $(this).prev().prop('max');
//         const default_value = $(this).prev().attr('default-value');
//         const batch_id = $(this).prev().data('batch-id');
//         const valueSet = parseInt(curr_quantity) + 1;
//         if (parseInt(curr_quantity) < parseInt(max)) {

//             $(this).prev().val(valueSet);
//             if (valueSet == default_value) {
//                 $(`.collapse[data-batch-id="${batch_id}"]`).collapse("hide");
//                 $(`textarea[data-batch-id="${batch_id}"]`).val('');
//             } else {
//                 $(`.collapse[data-batch-id="${batch_id}"]`).collapse("show");
//             }
//             setTotalQuantity();
//         }
//     })
//     $('#submit_btn').click(function (e) {
//         e.preventDefault();
//         var isValid = true;
//         $('.dataTables_scrollBody .table-add-batch-form').each(function () {
//             var tbl = $(this).DataTable();
//             var totalQty = 0;
//             var row = $(this).closest('tr');
//             var expQty = parseFloat(row.find('.expected-export').val());
//             tbl.rows().every(function () {
//                 var tr = $(this.node());
//                 var qtyVal = tr.find('#quantity').val();
//                 totalQty += parseInt(qtyVal, 10) || 0;
//             });
//             if (totalQty != expQty) {
//                 isValid = false;
//             }
//         })
//         if (isValid) {
//             $('.form-2').submit();
//         }
//         else {
//             toastr.clear();
//             toastr.warning('Chưa xuất đủ số lượng yêu cầu', 'Warning', { timeOut: 3000, closeButton: true });
//         }

//     })
//     $('#open_scan_modal').on('click', function () {
//         var isValid = true;
//         $('.dataTables_scrollBody .table-add-batch-form').each(function () {
//             var tbl = $(this).DataTable();
//             var totalQty = 0;
//             var row = $(this).closest('tr');
//             var expQty = parseFloat(row.find('.expected-export').val());
//             tbl.rows().every(function () {
//                 var tr = $(this.node());
//                 var qtyVal = tr.find('#quantity').val();
//                 totalQty += parseInt(qtyVal, 10) || 0;
//             });
//             if (totalQty != expQty) {
//                 isValid = false;
//             }
//         })
//         if (isValid) {
//             $('#QrScanModal').modal('show');
//         }
//         else {
//             toastr.clear();
//             toastr.warning('Chưa xuất đủ số lượng yêu cầu', 'Warning', { timeOut: 3000, closeButton: true });
//         }
//     });
//     $(document).on('keyup', 'input[type="number"]', _.debounce(function () {
//         const change_value = $(this).val();
//         const default_value = $(this).attr('default-value');
//         const min = $(this).prop('min');
//         const max = $(this).prop('max');
//         const batch_id = $(this).data('batch-id');

//         if (parseInt(change_value) < parseInt(min)) {
//             $(this).val(min);
//         }
//         if (parseInt(change_value) > parseInt(max)) {
//             $(this).val(max);
//         }
//         var closestTr = $(this).closest('tr.item__product');
//         var productId = closestTr.find('#product_id').val();
//         var detailIssueId = closestTr.find('#orderProductId').val();
//         var warehouse_id = closestTr.find('#warehouse_id').val();
//         var quantityStock = closestTr.find('#quantityStock').val();
//         var table = closestTr.next('tr').find('#table-add');
//         var data = {
//             product_id: productId,
//             warehouse_id: warehouse_id,
//             quantity: $(this).val(),
//             quantityStock: quantityStock,
//             detailIssueId: detailIssueId
//         };
//         // $.ajax({
//         //     method: "get",
//         //     url: "/admin/product-issue/get-more-quantity",
//         //     ansys: false,
//         //     headers: {
//         //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
//         //     },
//         //     data: data,
//         //     success: function (res) {

//         //         table.find('tr').remove();
//         //         $.each(res, function (index, value) {
//         //             var newRow = `<tr>
//         //                                 <td><input type="hidden"
//         //                                 name="productDetai[${value.batch_id}][issueProductId]"
//         //                                 value=" ${data.detailIssueId} "><input type="hidden"
//         //                                 name="productDetai[${value.batch_id}][quantity_actual]"
//         //                                 value=" ${value.quantity} "> ${value.batch_code}</td>
//         //                                 <td>Số lượng: ${value.quantity}</td>
//         //                               </tr>`;
//         //             table.append(newRow);
//         //         });

//         //     },
//         //     error: function (xhr, status, error) {
//         //         console.error(error);
//         //     }
//         // });
//         var collapseElement = $(`.collapse[data-batch-id="${batch_id}"]`);
//         var textareaElement = collapseElement.find('textarea');
//         if (parseInt($(this).val()) == parseInt(default_value)) {
//             collapseElement.collapse("hide");
//             textareaElement.val('');
//             textareaElement.prop('required', false);
//         } else {
//             collapseElement.collapse("show");
//             textareaElement.prop('required', true);
//         }
//         totalQty = 0;
//         $('input[data-name="quantity"]').each(function () {
//             var quantity = parseInt($(this).val()) || 0;
//             totalQty += quantity;
//         });
//         $('#table-wrapper .widget__amount').text(totalQty);
//     }, 500))
// })
