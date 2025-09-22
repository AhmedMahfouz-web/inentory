@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'primary',
    'subtitle' => null,
    'trend' => null,
    'trendDirection' => null,
    'link' => null
])

@php
    $cardClass = "bg-label-{$color}";
    $trendClass = $trendDirection === 'up' ? 'text-success' : ($trendDirection === 'down' ? 'text-danger' : 'text-muted');
    $trendIcon = $trendDirection === 'up' ? 'ti-trending-up' : ($trendDirection === 'down' ? 'ti-trending-down' : 'ti-minus');
@endphp

<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            @if($icon)
                                <div class="avatar-initial {{ $cardClass }} rounded">
                                    <i class="ti {{ $icon }}"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <span class="fw-semibold d-block mb-1">{{ $title }}</span>
                    
                    <h3 class="card-title text-nowrap mb-2">{{ $value }}</h3>
                    
                    @if($subtitle)
                        <small class="text-muted">{{ $subtitle }}</small>
                    @endif
                    
                    @if($trend)
                        <div class="d-flex align-items-center mt-2">
                            <i class="ti {{ $trendIcon }} {{ $trendClass }} me-1"></i>
                            <small class="{{ $trendClass }}">{{ $trend }}</small>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($link)
                <div class="mt-3">
                    <a href="{{ $link }}" class="btn btn-sm btn-outline-{{ $color }}">
                        <i class="ti ti-eye me-1"></i>
                        عرض التفاصيل
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
