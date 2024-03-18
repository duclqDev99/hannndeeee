$(document).ready(function() {
    $(document).on('click', '.edit-not-quantity', function(e) {
        e.preventDefault();

        var $this = $(this);
        var value = '';
        var id = $this.data('pk');
        var url = $this.data('url');

        var $input = $('<input type="number" placeholder="Nhập số lượng đã bán" class="editable-input" />').val(value);
        var $form = $('<form class="form-inline editableform"></form>').append($input);
        var $saveButton = $('<button type="submit" class="btn btn-primary btn-sm editable-submit" style="margin-left: 5px"><i class="fa fa-check" aria-hidden="true"></i></button>');
        var $cancelButton = $('<button type="button" class="cancel-btn btn btn-default btn-sm editable-cancel ml-1" style="margin-left: 5px"><i class="fa fa-times" aria-hidden="true"></i></button>');

        $form.append($saveButton);
        $form.append($cancelButton);

        $this.replaceWith($form);

        $form.on('submit', function(e) {
            e.preventDefault();
            var updatedValue = $input.val();
            var isValid = true;
            switch(true) {
                case (updatedValue <= 0):
                    Botble.showError('Số lượng bán không được nhỏ hơn hoặc bằng 0 !!!');
                    isValid = false;
                    break;
                case (updatedValue > Number($this.text())):
                    Botble.showError('Số lượng bán nhiều hơn số lượng còn trong kho!!!');
                    isValid = false;
                    break;
                // default:
                //     console.log("Giá trị không xác định");
            }
            if (isValid) {
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
                            // setModal(res)
                            Botble.showSuccess('Chỉnh sửa thành công');
                            if ($('.buttons-reload')) {$('.buttons-reload').click();}
                            $form.replaceWith($this);
                        } else {
                            Botble.showError(res.message);
                            $form.replaceWith($this);
                        }
                    },
                    error: (res) => {
                        Botble.handleError(res)
                    },
                })
            }

            });
        $cancelButton.click(function() {
            $form.replaceWith($this);
        });
    });
});
