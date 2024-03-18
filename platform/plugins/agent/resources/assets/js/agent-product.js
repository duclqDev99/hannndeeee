


// waitForElementToExist('.badge.bg-success.text-success-fg').then(element => {
//     $(document).on('click',element,function(){
//         let tr=   $(this).closest('tr')
//         let nextTr = tr.next('.collapse-row');

//         if (nextTr.length === 0) {
//             // Nếu hàng mở rộng chưa tồn tại, tạo nó
//             nextTr = $('<tr><td colspan="6" class="hiddenRow"><div id="demo2" class="accordian-body collapse">Demo2</div></td></tr>');
//             console.log(nextTr);
//             tr.after(nextTr);
//             nextTr.find('.accordian-body').collapse('show'); // Sử dụng Bootstrap để hiển thị
//         } else {
//             // Nếu hàng mở rộng đã tồn tại, ẩn hoặc hiển thị nó
//             nextTr.find('.accordian-body').collapse('toggle'); // Sử dụng Bootstrap để toggle
//         }
//     })
// });
function fetchDataAndPopulate(id, callback) {
    if (id && id > 0) {
        $.ajax({
            url: route('agent-product.detail', { id: id }),
            type: 'get',
            success: function (data) {
                callback(data);
            },
        });
    }
}
$(document).ready(function () {

    $(document).on('click', '.see-detail', function () {
        let button = $(this);
        let tr = button.closest('tr');
        let nextTr = tr.next('.collapseAdd');
        const id = tr.find('.column-key-0').text();
    
        // Thêm biểu tượng "đang tải"
        let loadingIcon = $('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        button.prepend(loadingIcon);
        button.addClass('disabled').prop('disabled', true); // Vô hiệu hóa nút
    
        if (nextTr.length === 0) {
            fetchDataAndPopulate(id, function (data) {
                if (data.products.length > 0) {
                    const rows = data.products.map(item =>
                        `<tr>
                            <td width="30%">Màu: ${item.color} - Size ${item.size}</td>
                            <td width="60%">${item.stock.map(detail => `${detail.stock} - Số lượng: ${detail.quantity}`).join('<br>')}</td>
                            <td width="10%">${item.total}</td>
                        </tr>`).join('');
    
                    nextTr = $(`<tr class="collapseAdd"><td colspan="12"><div class="accordion-body collapse" id="demo2"><table class="table"><tbody>${rows}</tbody></table></div></td></tr>`);
                    tr.after(nextTr);
                    nextTr.find('.accordion-body').collapse('toggle');
                } else {
                    const noProductMessage = '<tr><td colspan="12" class="text-center">Không có sản phẩm.</td></tr>';
                    nextTr = $(`<tr class="collapseAdd">${noProductMessage}</tr>`);
                    tr.after(nextTr);
                }
                // Loại bỏ biểu tượng "đang tải" và bật lại nút sau khi hoàn tất xử lý
                loadingIcon.remove();
                button.removeClass('disabled').prop('disabled', false);
            });
        } else {
            nextTr.find('.accordion-body').collapse('toggle');
            loadingIcon.remove();
            button.removeClass('disabled').prop('disabled', false);
        }
    });

    $(document).on('hidden.bs.collapse', '.collapseAdd .accordian-body', function () {
        $(this).closest('tr.collapseAdd').hide();
    });

    $(document).on('show.bs.collapse', '.collapseAdd .accordian-body', function () {
        $(this).closest('tr.collapseAdd').show();
    });


})





