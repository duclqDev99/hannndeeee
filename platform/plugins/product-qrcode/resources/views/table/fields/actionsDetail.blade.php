@php
@endphp

<div class="btn-list">
    @if($item->status != 'cancelled')
        @if (request()->user()->isSuperUser() || Auth::guard()->user()->hasPermission('product-qrcode.edit'))
            <div class="modal fade" id="updateStatus{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Lý do hủy mã</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <form method="POST" action="{{$routeUpdateStatus}}">
                        @csrf
                        <input name="times_product_id" type="text" value="{{$item->times_product_id}}" hidden/>
                        <input name="id" type="text" value="{{$item->id}}" hidden/>
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label required">Lý do</label>
                        <textarea name="reason" class="form-control" id="message-text"></textarea>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Gửi</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </form>
                    </div>
                    {{-- <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Gửi</button>
                    </div> --}}
                </div>
                </div>
            </div>
            <x-core::button
                type="button"
                type-button="create"
                color="danger"
                class="action-update-detail1"
                :data-target="$routeUpdateStatus"
                data-data="{{ $item}}"
                icon="fa-solid fa-ban"
                :icon-only="true"
                :tooltip="trans('Hủy bỏ')"
                size="sm"
                data-bs-toggle="modal"
                data-bs-target="#updateStatus{{$item->id}}"
                data-bs-whatever="@mdo"
            />
        @endif
    @endif

</div>
<div id="modalDescription"></div>
