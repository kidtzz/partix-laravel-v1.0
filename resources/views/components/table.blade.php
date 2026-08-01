@props(['headers' => []])

<div class="table-container" style="flex: 1;">
    <table {{ $attributes }}>
        @if(count($headers) > 0)
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        {{ $slot }}
    </table>
</div>
