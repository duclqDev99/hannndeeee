$(document).ready(function () {
    function formatVND(val) {
        if (val === "" || val === null) {
            return "0 VND";
        }
        var result = Number(val).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

        return result;
    }

    $(document).on('input', '#quantity-input', function () {
        var totalquantity = 0;
        var totalprice = 0;
        var tableRows = $('#detail tbody').find('tr');
        var plan_id = $('#plan_id').val();

        tableRows.each(function () {
            var priceCell = $(this).find('td').eq(2);
            var quantity = parseFloat($(this).find('td:eq(4) input').val(), 10) || 0;
            var priceSpan = priceCell.find('span');
            var priceValue = parseFloat(priceSpan.text().replace(/[^\d.]/g, '')) || 0;
            // var materialId = $(this).find('td').eq(1).find('.wrap-img').attr('material_id');
            // console.log(materialId);
            totalquantity += quantity;
            totalprice += quantity * priceValue;
        })

        $('#total-quantity').text(totalquantity);
        $('#total-price').text('$' + formatVND(totalprice));
    });
    $(document).on('click', '#detail_plan_material', function (e) {
        e.preventDefault();

        var hrefValue = $(this).attr('href');
        var parts = hrefValue.split('/');
        var number = parts[parts.length - 1];
        var planNumber = parseInt(number, 10);
        $('#detail tbody').empty();
        $('#detail_all tbody').empty();
        $.ajax({
            url: '/admin/goods-issue/plan-material/' + planNumber,
            method: 'GET',
            success: function (res) {
                var totalprice = 0;
                var totalquantity = 0;
                var status = res[0].material_plan.status;
                var hasPermission = parseInt($('#permission__user').val());
                if (status !== 'pending') { //|| hasPermission !== 1

                    $('#accept, #denied').css('display', 'none');
                    // console.log(hasPermission);
                } else {
                    $('#accept, #denied').css('display', 'block');
                }
                for (var item in res) {

                    if (res.hasOwnProperty(item)) {

                        materialPlan = res[item];
                        totalprice += materialPlan.quantity * materialPlan.material.price
                        totalquantity += materialPlan.quantity;

                        var newRow = '<tr>' +
                            '<td class="width-60-px min-width-60-px vertical-align-t">' +
                            '    <div class="wrap-img">' +
                            '        <img class="thumb-image thumb-image-cartorderlist" src="/storage/' + materialPlan.material.image +
                            '" alt="' + materialPlan.material.name + '">' +
                            '    </div>' +
                            '</td>' +
                            '<td class="pl5 p-r5 min-width-200-px">' +
                            '    <a  id="route"  class="text-underline hover-underline pre-line" href="' + materialPlan.material_plan.id + '" ' +
                            '    title="' + materialPlan.material.name + '" target="_blank"  >' +
                            '        ' + materialPlan.material.name +
                            '    </a>' +
                            '    &nbsp;' +


                            '<td class="pl5 p-r5 text-end">' +
                            '    <div class="inline_block">' +
                            '        <span>$' + formatVND(materialPlan.material.price) + '</span>' +
                            '    </div>' +
                            '</td>' +
                            '<td class="pl5 p-r5 text-center">x</td>';
                        if (status === 'pending' && hasPermission === 1) {
                            newRow += '<td class="pl5 p-r5">' +
                                '    <span><input type="number"  material_id="' + materialPlan.material.id + '"  class = "form-control" id ="quantity-input" value= "' + materialPlan.quantity + '" /></span>' +
                                '</td>';
                        }
                        else {
                            newRow += '<td class="pl5 p-r5">' +
                                '    <span>' + materialPlan.quantity + '</span>' +
                                '</td>';
                        }
                        newRow += '<td class="pl5 text-end">' +
                            '</td>' +
                            '</tr>';
                        $('#detail tbody').append(newRow);
                    }
                }
                var newRowAll = ' <tr>' +
                    ' <td class="text-end color-subtext">' +
                    'Quantity</td>' +
                    '<td class="text-end pl10">' +
                    '<span id="total-quantity" >' + totalquantity + '</span>' +
                    '  </td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="text-end color-subtext">' +
                    'Sub amount</td>' +
                    '<td class="text-end pl10">' +
                    '<span  id="total-price">$' + formatVND(totalprice) + '</span>' +
                    '</td>' +
                    '</tr>';
                $('#detail_all tbody').append(newRowAll);

            }

        })
        $('#plan_id').val(planNumber);
        $('#exampleModal').modal('show');
    })
    $(document).on('click', '#accept', function () {

        var tableRows = $('#detail tbody').find('tr');
        var plan_id = $('#plan_id').val();
        var materials = [];
        tableRows.each(function () {
            var quantity = $(this).find('td:eq(4) input').val();
            var materialId = $(this).find('td:eq(4) input').attr('material_id');
            materials.push({
                quantity: quantity,
                material_id: materialId
            });
        });
        var data = {
            material_plans_id: plan_id,
            materials: materials
        }
        $.ajax({
            method: "POST",
            url: "/admin/check_inventories/create",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: data,
            success: function (res) {
                if (res.error === false) {
                    toastr.success(res.message);

                    window.location.href = "/admin/goods-issue";
                }
                else {
                    toastr.error(res.message);
                    return;
                }

            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    })
    $(document).on('click', '#denied', function () {
        var plan_id = $('#plan_id').val();
        var data = {
            status : 'denied'
        }
        $.ajax({
            method: "POST",
            url: "/admin/goods-issue/denied/" + plan_id,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: data,
            success: function (res) {
                console.log(res)
                if (res.error === false) {
                    toastr.success(res.message);
                    window.location.href = "/admin/goods-issue";
                }
                else {
                    toastr.error(res.message);
                    return;
                }

            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    })

})

