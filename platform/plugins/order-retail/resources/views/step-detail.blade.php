@if ($stepDetail)
    <div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">Hành động</th>
                    <th scope="col">Người thực hiện</th>
                    <th scope="col">Thời gian thực hiện</th>
                    <th scope="col">Trạng thái</th>
                </tr>
            </thead>
            <tbody id="body">
                <tr>
                    <td>{{ $stepDetail?->actionSetting?->title }}</td>
                    <td>{{ $stepDetail->handler ? $stepDetail->handler->first_name . ' ' . $stepDetail->handler->last_name : '---' }}
                    </td>
                    <td>{{ $stepDetail->handled_at ? \Carbon\Carbon::parse($stepDetail?->handled_at)->format('d-m-Y, H:i') : '---' }}
                    </td>
                    <td>{{ \Botble\OrderStepSetting\Enums\ActionStatusEnum::getLabel($stepDetail->status) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @if ($stepDetail->note)
        <div class="w-100 my-3" style="border-bottom: 1px solid #f3f4f6"></div>
        <div class="mt-3">
            <span class="fw-bolder">Ghi chú</span>
            <div class="card">
                <div class="card-body p-3">
                    <span class="card-text">{{ $stepDetail->note }}</span>
                </div>
            </div>
            {{-- <div class="form-group">
                <label class="fw-bolder mb-1" for="exampleFormControlTextarea1">Ghi chú</label>
                <textarea class="form-control text-start" id="exampleFormControlTextarea1" rows="3" readonly> </textarea>
            </div> --}}
        </div>
    @endif
@else
    Trống
@endif
