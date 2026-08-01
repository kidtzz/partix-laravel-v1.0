@props(['title', 'icon' => ''])

<div {{ $attributes->merge(['class' => 'view-header flex justify-between items-center mb-4']) }}>
    <h2 class="view-title mb-0">
        @if($icon)
            <i class='{{ $icon }}'></i> 
        @endif
        {{ $title }}
    </h2>
    
    @if(isset($slot) && trim($slot) !== '')
        {{ $slot }}
    @endif
</div>
