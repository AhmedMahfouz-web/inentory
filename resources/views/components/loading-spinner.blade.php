@props([
    'size' => 'md',
    'color' => 'primary',
    'text' => null,
    'overlay' => false
])

@php
    $sizes = [
        'sm' => 'spinner-border-sm',
        'md' => '',
        'lg' => 'spinner-border-lg'
    ];
    
    $sizeClass = $sizes[$size] ?? '';
@endphp

@if($overlay)
<div class="loading-overlay position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background: rgba(255,255,255,0.8); z-index: 9999;">
@endif

<div class="d-flex align-items-center justify-content-center {{ $overlay ? '' : 'p-3' }}">
    <div class="spinner-border text-{{ $color }} {{ $sizeClass }}" role="status">
        <span class="visually-hidden">جاري التحميل...</span>
    </div>
    
    @if($text)
        <span class="ms-2">{{ $text }}</span>
    @endif
</div>

@if($overlay)
</div>
@endif

<style>
.spinner-border-lg {
    width: 3rem;
    height: 3rem;
}

.loading-overlay {
    backdrop-filter: blur(2px);
}
</style>
