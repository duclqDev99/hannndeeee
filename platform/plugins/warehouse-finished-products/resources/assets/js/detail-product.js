
function fetchDataAndPopulate(id, callback) {
    if(id && id > 0)
    {
        $.ajax({
            url: route('finished-products.detail', { id: id }),
            type: 'post',
            success: function (data) {
                callback(data);
            },
        });
    }
}
$(document).ready(function () {
    $(document).on('click', 'tr', function () {
        let tr = $(this);
        let nextTr = tr.next('.collapseAdd');

        const id = tr.find('.column-key-0').text();

        if (nextTr.length === 0) {
            // Fetch data and create the collapsible row
            if(id>0)
            {
                fetchDataAndPopulate(id, function (data) {
                    const rows = data.products.map(item =>
                        `<tr>
                                <td>Màu: ${item.color} - Size ${item.size}</td>
                                <td>${item.stock.map(detail => `${detail.stock} - Số lượng: ${detail.quantity}`).join('<br>')}</td>
                                <td>${item.total}</td>
                            </tr>`).join('');
                    nextTr = $(`<tr class="collapseAdd"><td colspan="12"><div class="accordian-body collapse" id="demo2"><table class="table"><tbody>${rows}</tbody></table></div></td></tr>`);
                    tr.after(nextTr);
                    nextTr.find('.accordian-body').collapse('toggle');
                });

            }
        } else {
            nextTr.find('.accordian-body').collapse('toggle');
        }
    });

    $(document).on('hidden.bs.collapse', '.collapseAdd .accordian-body', function () {
        $(this).closest('tr.collapseAdd').hide();
    });

    $(document).on('show.bs.collapse', '.collapseAdd .accordian-body', function () {
        $(this).closest('tr.collapseAdd').show();
    });
});

