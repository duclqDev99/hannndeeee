
$(document).ready(function () {
    $('#materialSelect').on('change', function () {
        var value = $(this).val();
        var text = $("#materialSelect option:selected").text();
        var image_material = $(this).find('option:selected').attr('image_material');
        var price = $(this).find('option:selected').attr('price');
        var url = "/admin/materials/getThumbAttribute/ " + image_material;
        if (value !== 'None') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function (res) {
                    $('#selectedMaterials tbody').append('<tr><td>' + value + '</td><td>' + text +
                        '</td><td ><input class ="form-control" type="number" value="' + 1 + '"/>' +
                        '</td><td class="text-center">' + price +
                        '</td> <td><img src="' + res + '" width="80" height="80"/></td><td><button class="btn btn-danger delete-btn" data-value="' + value + '"><i class="fa fa-trash" aria-hidden="true"></i></button></td></tr>');
                }
            })

        }
        $(this).find('option').each(function (i) {

            if ($(this).val() == value) {
                $(this).remove();
                return false;
            }
        });
    })
    $('#selectedMaterials').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var value = $(this).data('value');
        var text = $(this).closest('tr').find('td:eq(1)').text();
        $('#materialSelect').append('<option value="' + value + '">' + text + '</option>');
        $(this).closest('tr').remove();
    });
    $('#type').on('change', function () {
        var type = $(this).val();
        if (type == 'IN') {
            $('#form-request, #create_material').css('display', 'block');
        } else if (type == 'OUT') {
            $('#create_material').css('display', 'none');
            $('#form-request').css('display', 'block');
        } else {
            $('#form-request, #create_material').css('display', 'none');
        }
    });
    $('#save_plan_material').on('click', function (e) {
        e.preventDefault();
        var materials = [];
        var inventory_id = $('#inventory').val()
        var tableRows = $('tbody tr');
        var materials = [];
        var title = $('#title').val()
        var type = $('#type').val()
        var date_proposal = $('#date_proposal').val();
        if (date_proposal === '' ) {
            Botble.showError('Chọn ngày dự kiến trước khi tiếp tục');
            return;
        }
        else if(new Date(date_proposal) <= new Date()) {
            Botble.showError('Ngày dự kiến sau hôm nay');
            return;
        }
        if (type === '') {
            Botble.showError('Chọn trạng thái đề xuất trước khi thực hiện thao tác');
            return;
        }
        else {
            tableRows.each(function () {
                var id = $(this).find('td:eq(0)').text();
                var quantity = $(this).find('td:eq(2) input').val();
                if (quantity <= 0) {
                    Botble.showError('Số lượng cần phải lơn hơn 0 tại ID: ' + id);
                    return;
                }

                if (id !== '' && parseInt(id) > 0 && quantity !== '' && parseInt(quantity) > 0) {
                    materials.push({
                        material_id: id,
                        quantity: quantity,
                    });
                }

            });
            if (materials.length <= 0) {
                Botble.showError('Chưa có sản phẩm trong danh sách');
                return;
            }
            var data = {
                type: type,
                inventory_id: inventory_id,
                materials: materials,
                date_proposal: date_proposal,
                title: title
            };

            $.ajax({
                method: "POST",
                url: "/admin/goods-issue/create",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: data,
                success: function (res) {
                    if (res.error === false) {
                        Botble.showSuccess(res.message)
                        window.location.href = "/admin/goods-issue";
                    }
                    else {
                        Botble.showError(res.message);
                        return;
                    }

                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }



    })
    function check_quantity_stock(data_check){
        var data_check = {
            quantity: quantity,
            inventory_id: inventory_id
        }
        $.ajax({
            method: "GET",
            url: "/admin/goods-issue/check-quantity-stock" + id,
            data: data_check,
            success:function(res){
            }

        })
    }
})

