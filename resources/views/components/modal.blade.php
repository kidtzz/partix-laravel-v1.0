@props(['id', 'title' => '', 'maxWidth' => '500px', 'titleId' => ''])

<div class="modal-overlay" id="{{ $id }}">
    <div class="modal-content" style="max-width: {{ $maxWidth }};">
        @if($title)
        <div class="modal-header">
            <h3 @if($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('{{ $id }}').classList.remove('active')">
                <i class='bx bx-x'></i>
            </button>
        </div>
        @endif
        
        <div class="modal-body">
            {{ $body ?? $slot }}
        </div>
        
        @isset($footer)
        <div class="modal-footer" style="margin-top: 16px; text-align: right;">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>
