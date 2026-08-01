@props(['padding' => '24px', 'flex' => false, 'display' => 'block'])

<div {{ $attributes->merge(['class' => 'glass-card', 'style' => "padding: {$padding}; display: " . ($flex ? 'flex' : $display) . ";" . ($flex ? 'flex: 1; overflow: hidden; flex-direction: column;' : '')]) }}>
    {{ $slot }}
</div>
