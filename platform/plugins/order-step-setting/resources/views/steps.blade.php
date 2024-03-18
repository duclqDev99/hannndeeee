<td colspan="10" class="p-0">
    <div class="collapse">
        <div class="card card-body">
            <div class="d-flex align-items-start justify-content-around" style="gap: 40px">
                {{-- <div class="text-center flex-grow-1 " data-bs-toggle="tooltip" data-placement="right" title="Đã hoàn thành">
                    <span class="btn bg-green-300 text-white fw-bolder w-100">1. Yêu cầu sản xuất</span>
                    <div class="mt-3">
                        <p class="text-green-300">Quản lý sale duyệt YCSX</p>
                        <p class="text-green-300">HGF duyệt YCSX</p>
                    </div>
                </div>
                <div class="text-center flex-grow-1 " data-bs-toggle="tooltip" data-placement="right" title="Đang thực hiện">
                    <span class="btn bg-blue-100 text-white fw-bolder w-100">1. Báo giá </span>
                    <div class="mt-3">
                        <p class="text-green-300">Quản lý sale duyệt báo giá</p>
                        <p class="text-muted">Khách hàng duyệt báo giá</p>
                    </div>
                </div> --}}
                {{-- <div class="text-center flex-grow-1 ">
                    <span class="btn bg-gray-300 fw-bolder w-100">1. Hợp đồng </span>
                    <div class="mt-3">
                        <p class="text-muted">Khách hàng ký hợp đồng</p>
                        <p class="text-muted">Kế toán nhận cọc</p>
                    </div>
                </div>
                <div class="text-center flex-grow-1 ">
                    <span class="btn bg-gray-300 fw-bolder w-100">1. Đơn đặt hàng</span>
                    <div class="mt-3">
                        <p class="text-muted">HGF xác nhận sản xuất</p>
                        <p class="text-muted">HGF giao hàng </p>
                    </div>
                </div> --}}
                @foreach ($steps as $key => $step)
                    <div class="text-center flex-grow-1 ">
                        <span class="btn py-2 fw-bolder w-100 border-0
                        @if ($step->is_ready && !$step->is_completed)
                          bg-blue-300 text-white
                        @elseif($step?->is_completed)
                          bg-green-300 text-white
                        @else
                          bg-gray-300
                        @endif">
                        {{$key + 1}}. {{$step->title}} {{$step?->completed}}
                    </span>
                        <div class="mt-3">
                            @foreach ($step->actions as $action)
                               <p
                                    data-id="{{$action->id}}" 
                                    role="button" 
                                    class="show_detail_status_btn 
                                    @if ($step->is_ready)
                                        @if ($action->status == $action->valid_status)
                                           text-green-300
                                        @else
                                           text-danger
                                        @endif
                                    @else
                                      text-muted 
                                    @endif"
                                    data-bs-toggle="modal"
                                    data-bs-target="#statusDetailModal"
                               >{{$action->title}}
                            </p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</td>

<style>
    .bg-gray-300 {
        background-color: #f3f4f6 !important;
    }

    .bg-green-300 {
        background-color: #22c55e !important;
    }

    .text-green-300 {
        color: #22c55e !important;
    }

    .bg-blue-300 {
        background-color: #3B82F6 !important;
    }
</style>
