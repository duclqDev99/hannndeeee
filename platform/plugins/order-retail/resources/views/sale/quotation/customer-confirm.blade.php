
@php
    $customerConform = get_action(
        \Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_CONFIRM_QUOTATION,
        $item->order->id,
    );
@endphp

<div class="w-100 d-flex gap-3 align-items-centet justify-content-center">
    <input class="form-check-input form-control" onclick="return false;" type="checkbox"
      @if ($customerConform->status == 'confirmed')
          checked
      @endif
    >
    {{-- <div class="editable-buttons">
        <button type="submit" class="btn btn-primary btn-sm editable-submit">
            <i class="fa fa-check" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm editable-cancel">
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
    </div> --}}
</div>
