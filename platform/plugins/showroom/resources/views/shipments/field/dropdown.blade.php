<select class="btn action-item dropdown-toggle" id="{{$id ?? ''}}" >
    @foreach($data as $id => $name)
        <option value="{{ $id }}" >{{ $name }}</option>
    @endforeach
</select>
