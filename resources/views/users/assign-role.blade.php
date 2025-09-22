@extends('layouts.dashboard')

@section('title', 'تعيين دور للمستخدم - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تعيين دور للمستخدم - {{ $user->name }}</h5>
                    <a href="{{ route('show users') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        العودة
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.assign-role', $user) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-label-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md bg-primary me-3">
                                                <span class="avatar-initial rounded-circle">
                                                    {{ substr($user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->username }}</small>
                                                @if($user->roles->isNotEmpty())
                                                    <div class="mt-1">
                                                        <span class="badge bg-success">الدور الحالي: {{ $user->roles->first()->name }}</span>
                                                    </div>
                                                @else
                                                    <div class="mt-1">
                                                        <span class="badge bg-warning">لا يوجد دور محدد</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">اختر الدور:</h6>
                                
                                @if($roles->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        لا توجد أدوار متاحة في النظام. يرجى إنشاء الأدوار أولاً.
                                    </div>
                                @else
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="role" 
                                                           value="{{ $role->name }}"
                                                           id="role_{{ $role->id }}"
                                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <i class="ti ti-user-check fs-2 text-primary mb-2"></i>
                                                                <h6 class="mb-1">{{ $role->name }}</h6>
                                                                <small class="text-muted">
                                                                    {{ $role->permissions->count() }} صلاحية
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="role" 
                                                       value=""
                                                       id="role_none"
                                                       {{ $user->roles->isEmpty() ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_none">
                                                    <div class="card border-danger">
                                                        <div class="card-body text-center">
                                                            <i class="ti ti-user-x fs-2 text-danger mb-2"></i>
                                                            <h6 class="mb-1 text-danger">إزالة الدور</h6>
                                                            <small class="text-muted">
                                                                بدون صلاحيات
                                                            </small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ti ti-check me-1"></i>
                                    حفظ التغييرات
                                </button>
                                <a href="{{ route('show users') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to card labels
    const cardLabels = document.querySelectorAll('.form-check-label');
    cardLabels.forEach(label => {
        label.addEventListener('click', function() {
            // Remove active class from all cards
            cardLabels.forEach(l => {
                const card = l.querySelector('.card');
                card.classList.remove('border-primary', 'bg-light');
            });
            
            // Add active class to clicked card
            const card = this.querySelector('.card');
            const radio = this.previousElementSibling;
            if (radio.checked || radio.value === '') {
                if (radio.value === '') {
                    card.classList.add('border-danger');
                } else {
                    card.classList.add('border-primary', 'bg-light');
                }
            }
        });
    });
    
    // Set initial active state
    const checkedRadio = document.querySelector('input[name="role"]:checked');
    if (checkedRadio) {
        const label = checkedRadio.nextElementSibling;
        const card = label.querySelector('.card');
        if (checkedRadio.value === '') {
            card.classList.add('border-danger');
        } else {
            card.classList.add('border-primary', 'bg-light');
        }
    }
});
</script>
@endsection
