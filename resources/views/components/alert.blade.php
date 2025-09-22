@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
    'title' => null
])

@php
    $classes = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'primary' => 'alert-primary'
    ];
    
    $icons = [
        'success' => 'ti-check-circle',
        'error' => 'ti-alert-circle',
        'warning' => 'ti-alert-triangle',
        'info' => 'ti-info-circle',
        'primary' => 'ti-bell'
    ];
    
    $alertClass = $classes[$type] ?? 'alert-info';
    $alertIcon = $icon ?? $icons[$type] ?? 'ti-info-circle';
@endphp

<div class="alert {{ $alertClass }} {{ $dismissible ? 'alert-dismissible' : '' }} fade show" role="alert">
    <div class="d-flex align-items-center">
        @if($alertIcon)
            <i class="ti {{ $alertIcon }} me-2"></i>
        @endif
        
        <div class="flex-grow-1">
            @if($title)
                <h6 class="alert-heading mb-1">{{ $title }}</h6>
            @endif
            
            <div>{{ $slot }}</div>
        </div>
        
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
</div>
