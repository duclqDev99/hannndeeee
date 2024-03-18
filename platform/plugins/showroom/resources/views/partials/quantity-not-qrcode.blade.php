<a
    class="edit-not-quantity"
    data-type="select"
    data-source="{{ route('roles.list.json') }}"
    data-pk="{{ $item->id }}"
    data-url="{{ route('showroom-warehouse.reduce-quantity') }}"
    data-value="{{ $item?->id ?: 0 }}"
    data-title="{{ trans('core/acl::users.assigned_role') }}"
    href="#"
>
    {{ $item?->quantity_not_qrcode ?: 0 }}
</a>

{{-- <script>
    $(document).ready(function() {
    // Sử dụng event delegation
    $(document).on('click', '.edit-not-quantity', function(e) {
        e.preventDefault();

        var $this = $(this);
        var value = $this.text();
        var id = $this.data('pk');
        var url = $this.data('url');

        // Tạo input và các nút
        var $input = $('<input type="text" class="editable-input" />').val(value);
        var $form = $('<form class="form-inline editableform"></form>').append($input);
        var $saveButton = $('<button type="submit" class="btn btn-primary btn-sm editable-submit" style="margin-left: 5px"><i class="fa fa-check" aria-hidden="true"></i></button>');
        var $cancelButton = $('<button type="button" class="cancel-btn btn btn-default btn-sm editable-cancel ml-1" style="margin-left: 5px"><i class="fa fa-times" aria-hidden="true"></i></button>');

        // Thêm input và các nút vào form
        $form.append($saveButton);
        $form.append($cancelButton);

        // Thay thế thẻ a bằng form
        $this.replaceWith($form);

        // Xử lý sự kiện submit form
        $form.on('submit', function(e) {
            e.preventDefault();
            var updatedValue = $input.val();
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    id,
                    quantity: updatedValue,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (res.error == false) {
                        setModal(res)
                        Botble.initResources()
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
            })
                console.log(updatedValue, id);
                // Gửi dữ liệu lên server...
            });

        // Xử lý sự kiện click cho nút hủy
        $cancelButton.click(function() {
            // Quay lại hiển thị thẻ a
            $form.replaceWith($this);
        });
    });
});
</script> --}}
