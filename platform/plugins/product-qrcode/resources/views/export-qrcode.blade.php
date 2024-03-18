@php
    $maxRows = max(array_map('count', $collection));
@endphp

<table>
    <thead>
    <tr>
    @foreach($headings as $heading)
        <th style="background-color: #6ECCAF; width: 400px; text-align: center; font-weight: bold; color: #ffffff">{{$heading}}</th>
    @endforeach

    </tr>
    </thead>
    <tbody>
        @for ($row = 0; $row < $maxRows; $row++)
            <tr>
                @foreach ($collection as $column)
                    <td style="width: 400px;">{{$row < count($column) ? $column[$row] : ''}}</td>
                @endforeach
            </tr>
        @endfor
    </tbody>
</table>
