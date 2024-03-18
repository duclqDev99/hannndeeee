
<form action="{{route('ecommerce.settings.shipping.pick-address')}}" method="POST">
    @csrf
    <x-core::form-group>
        <x-core::form.label class="required">Chọn showroom</x-core::form.label>
        {!! Form::customSelect('showroom_code', $showrooms) !!}
    </x-core::form-group>

    <input type="hidden" name="service_type">
    <div id="form-body"></div>
</form>