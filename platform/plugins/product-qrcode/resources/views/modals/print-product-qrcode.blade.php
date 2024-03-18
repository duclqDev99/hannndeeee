@php
    $quantity = $qrcodeTemporary['quantity_product'];

    $chunkSize = 200;
    $totalChunks = ceil($quantity / $chunkSize);

@endphp

<div class="modal fade" id="modal-print-qrcode" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Tùy chọn in mã</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Tên</th>
                    <th scope="col">Số lượng</th>
                    {{-- <th scope="col">Số lần in</th> --}}
                    {{-- <th scope="col">In lần cuối</th> --}}
                    <th scope="col">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                    @for($i= 0; $i < $totalChunks ; $i++)
                        @if($i < $totalChunks - 1)
                            <tr>
                                <th scope="row">{{$i + 1}}</th>
                                <td>{{$qrcodeTemporary->products['name']}}</td>
                                <td>{{$chunkSize}}</td>
                                {{-- <td>1</td>
                                <td>22/12/2023</td> --}}
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-trigger-print-qrcode-version"
                                        data-target="{{$export}}"
                                        data-url-confirm="{{$dataUrlConfirm}}"
                                        data-data="{{ $qrcodeTemporary}}"
                                        :tooltip="trans('In QrCode')"
                                        size="sm"
                                        type-button="print"
                                        chunk-id = "{{$i}}"
                                        chunk-size = "{{$chunkSize}}"
                                    >
                                        <i class="fa fa-print" ></i>
                                    </button>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <th scope="row">{{$i + 1}}</th>
                                <td>{{$qrcodeTemporary->products['name']}}</td>
                                <td>{{$quantity - (($i) * $chunkSize) }}</td>
                                {{-- <td>1</td>
                                <td>22/12/2023</td> --}}
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-trigger-print-qrcode-version"
                                        data-target="{{$export}}"
                                        data-data="{{ $qrcodeTemporary}}"
                                        data-url-confirm="{{$dataUrlConfirm}}"
                                        :tooltip="trans('In QrCode')"
                                        size="sm"
                                        type-button="print"
                                        chunk-id = "{{$i}}"
                                        chunk-size = "{{$chunkSize}}"
                                    >
                                        <i class="fa fa-print" ></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @endfor
                </tbody>
              </table>
        </div>
        <div class="modal-footer">
          <button type="button" aria-label="Close" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
        </div>
      </div>
    </div>
  </div>
