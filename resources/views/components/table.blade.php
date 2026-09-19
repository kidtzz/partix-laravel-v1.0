@props(['headers' => [], 'variant' => 'kasir', 'title' => null, 'subtitle' => null])

@php
    $containerClass = $variant === 'kasir' ? 'kasir-table-container' : 'table-container';
    $tableClass = $variant === 'kasir' ? 'kasir-table' : '';
@endphp

<style>
    /* Kasir/POS Specific Table Premium Theme (Compact & Neat) */
    .kasir-table-wrapper {
        background: var(--surface-color, rgba(255, 255, 255, 0.85));
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: var(--radius-lg, 12px);
        box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color, rgba(255, 255, 255, 0.6));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin-bottom: 1rem;
        width: 100%;
    }

    .kasir-table-header {
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color, rgba(0,0,0,0.05));
        gap: 12px;
        flex-wrap: wrap;
    }

    .kasir-table-title-group h3 {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        color: var(--text-main, #111827);
    }

    .kasir-table-title-group p {
        margin: 0;
        font-size: 12px;
        color: var(--text-muted, #6B7280);
    }

    .kasir-table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .kasir-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        flex: 1;
    }
    
    .kasir-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        white-space: nowrap;
    }

    .kasir-table th {
        font-size: 12px;
        font-weight: 400;
        color: var(--text-muted, #4B5563);
        background: rgba(0,0,0,0.04);
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-solid, #E5E7EB);
        position: sticky;
        top: 0;
        z-index: 10;
        backdrop-filter: blur(4px);
        white-space: nowrap;
    }
    
    .kasir-table td {
        padding: 12px 20px;
        font-size: 12px;
        color: var(--text-main, #111827);
        font-weight: 400;
        border-bottom: 1px solid var(--border-solid, rgba(229, 231, 235, 0.4));
        transition: background 0.15s ease;
        white-space: nowrap;
    }

    /* Striped Rows (Selang-seling seperti Excel) */
    .kasir-table tbody tr:nth-child(even) {
        background: rgba(0, 0, 0, 0.025);
    }
    
    .kasir-table tbody tr:nth-child(odd) {
        background: transparent;
    }

    .kasir-table tbody tr:hover {
        background: var(--primary-light, rgba(79, 70, 229, 0.08)) !important;
    }
    
    .kasir-table tbody tr:last-child td {
        border-bottom: none;
    }

    .kasir-table-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--border-color, rgba(0,0,0,0.05));
        background: rgba(0,0,0,0.015);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: var(--text-muted, #6B7280);
    }

    /* Scrollbar for the table */
    .kasir-table-container::-webkit-scrollbar { height: 8px; width: 8px; }
    .kasir-table-container::-webkit-scrollbar-track { background: transparent; }
    .kasir-table-container::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.4); border-radius: 10px; }
    .kasir-table-container::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.7); }
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .kasir-table-wrapper {
            margin-bottom: 0.5rem;
            border-radius: 8px;
        }
        .kasir-table-header {
            padding: 12px;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .kasir-table-actions {
            width: 100%;
        }
        .kasir-table-actions > * {
            flex: 1;
            justify-content: center;
        }
        .kasir-table th, .kasir-table td {
            padding: 10px 12px;
            font-size: 11px;
        }
    }
</style>

<div class="kasir-table-wrapper">
    @if($title || isset($actions))
    <div class="kasir-table-header">
        <div class="kasir-table-title-group">
            @if($title)<h3>{{ $title }}</h3>@endif
            @if($subtitle)<p>{{ $subtitle }}</p>@endif
        </div>
        @if(isset($actions))
        <div class="kasir-table-actions">
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif

    <div class="{{ $containerClass }}">
        <table class="{{ $tableClass }}" {{ $attributes }}>
            @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($pagination))
    <div class="kasir-table-footer">
        {{ $pagination }}
    </div>
    @endif
</div>

