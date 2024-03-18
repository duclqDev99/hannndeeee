


$(document).ready(function() {
    $(document).ready(function() {
        $('.delete-button').click(function() {
            if (confirm('Bạn có chắc chắn muốn xóa không?')) {
                var url = $(this).data('target');
                console.log(url);
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
});
