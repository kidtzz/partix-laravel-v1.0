@props(['label' => null, 'marginBottom' => '16px'])

<div {{ $attributes->merge(['class' => 'input-group', 'style' => 'margin-bottom: ' . $marginBottom . ';']) }}>
    @if($label)
        <label>{!! $label !!}</label>
    @endif
    {{ $slot }}
</div>
