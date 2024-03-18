'use strict'

let ViettelPost = ViettelPost || {}

ViettelPost.init = () => {
    $(document).on('show.bs.modal', '#viettel-post-view-n-create-transaction', function (e) {
        const $self = $(e.currentTarget)
        const $related = $(e.relatedTarget)
        $self.find('.modal-body').html('')

        $.ajax({
            type: 'GET',
            url: $related.data('url'),
            beforeSend: () => {
                $related.addClass('button-loading')
            },
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    $self.find('.modal-body').html(res.data.html)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
            complete: () => {
                $related.removeClass('button-loading')
            },
        })
    })

    $(document).on('click', '#viettel-post-view-n-create-transaction .create-transaction', function (e) {
        const $self = $(e.currentTarget)

        $.ajax({
            type: 'POST',
            url: $self.data('url'),
            beforeSend: () => {
                $self.addClass('button-loading')
            },
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    $('[data-bs-target="#viettel-post-view-n-create-transaction"]').addClass('d-none')
                    $('#viettel-post-view-n-create-transaction').modal('hide')
                    Botble.showSuccess(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
            complete: () => {
                $self.removeClass('button-loading')
            },
        })
    })

    $(document).on('click', '#viettel-post-view-n-create-transaction .get-new-rates', function (e) {
        const $self = $(e.currentTarget)

        $.ajax({
            type: 'GET',
            url: $self.data('url'),
            beforeSend: () => {
                $self.addClass('button-loading')
            },
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    Botble.showSuccess(res.message)
                    $self.addClass('d-none')
                    $self.parent().append(res.data.html)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
            complete: () => {
                $self.removeClass('button-loading')
            },
        })
    })

    $(document).on('submit', '.update-rate-shipment', function (e) {
        e.preventDefault()
        const $self = $(e.currentTarget)
        const $button = $self.find('button[type=submit]')

        $.ajax({
            type: 'POST',
            url: $self.prop('action'),
            data: $self.serializeArray(),
            beforeSend: () => {
                $button.addClass('button-loading')
            },
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    Botble.showSuccess(res.message)
                    $('#viettel-post-view-n-create-transaction').find('.modal-body').html(res.data.html)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
            complete: () => {
                $button.removeClass('button-loading')
            },
        })
    })
}

$(() => {
    ViettelPost.init()
})
