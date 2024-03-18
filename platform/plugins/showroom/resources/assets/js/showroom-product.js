

$(document).on('click', '.btn.btn-sm.btn-icon.btn-secondary', function() {
    let tr = $(this).closest('tr');
    let nextTr = tr.next('#collapseAdd');
    console.log(nextTr);

    if (nextTr.length === 0) {
        // Nếu hàng mở rộng chưa tồn tại, tạo nó
        nextTr = $('<tr id="collapseAdd"><td colspan="12"  class="hiddenRow"><div id="demo2" class="accordian-body collapse">Demo2</div></td></tr>');
        tr.after(nextTr);
        nextTr.find('.accordian-body').collapse('show'); // Sử dụng Bootstrap để hiển thị
    } else {
        if (nextTr.is(':visible')) {
            nextTr.remove(); // Xóa hàng mở rộng
        } else {
            nextTr.find('.accordian-body').collapse('toggle'); // Sử dụng Bootstrap để toggle
        }
    }
});









