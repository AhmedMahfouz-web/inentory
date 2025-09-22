@extends('layouts.dashboard')

@section('title', 'تعديل صلاحيات الفروع - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تعديل صلاحيات الفروع - {{ $user->name }}</h5>
                    <a href="{{ route('user-branches.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        العودة
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('user-branches.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
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
                                                        <span class="badge bg-primary">{{ $user->roles->first()->name }}</span>
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
                                <h6 class="mb-3">اختر الفروع والصلاحيات:</h6>
                                
                                @if($branches->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        لا توجد فروع متاحة في النظام
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="select-all">
                                                            <label class="form-check-label" for="select-all"></label>
                                                        </div>
                                                    </th>
                                                    <th>اسم الفرع</th>
                                                    <th width="120">يمكنه إنشاء طلبات</th>
                                                    <th width="120">يمكنه إدارة الفرع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($branches as $branch)
                                                    @php
                                                        $userBranch = $userBranches->where('branch_id', $branch->id)->first();
                                                        $isAssigned = $userBranch !== null;
                                                        $canRequest = $isAssigned ? $userBranch->can_request : false;
                                                        $canManage = $isAssigned ? $userBranch->can_manage : false;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input branch-checkbox" 
                                                                       type="checkbox" 
                                                                       name="branches[]" 
                                                                       value="{{ $branch->id }}"
                                                                       id="branch_{{ $branch->id }}"
                                                                       {{ $isAssigned ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="branch_{{ $branch->id }}"></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <label for="branch_{{ $branch->id }}" class="form-label mb-0 cursor-pointer">
                                                                {{ $branch->name }}
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input permission-checkbox" 
                                                                       type="checkbox" 
                                                                       name="can_request[]" 
                                                                       value="{{ $branch->id }}"
                                                                       id="can_request_{{ $branch->id }}"
                                                                       {{ $canRequest ? 'checked' : '' }}
                                                                       {{ !$isAssigned ? 'disabled' : '' }}>
                                                                <label class="form-check-label" for="can_request_{{ $branch->id }}"></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input permission-checkbox" 
                                                                       type="checkbox" 
                                                                       name="can_manage[]" 
                                                                       value="{{ $branch->id }}"
                                                                       id="can_manage_{{ $branch->id }}"
                                                                       {{ $canManage ? 'checked' : '' }}
                                                                       {{ !$isAssigned ? 'disabled' : '' }}>
                                                                <label class="form-check-label" for="can_manage_{{ $branch->id }}"></label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
                                <a href="{{ route('user-branches.index') }}" class="btn btn-outline-secondary">
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
    const selectAllCheckbox = document.getElementById('select-all');
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        branchCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            togglePermissionCheckboxes(checkbox);
        });
    });

    // Individual branch checkbox functionality
    branchCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            togglePermissionCheckboxes(this);
            updateSelectAllState();
        });
    });

    function togglePermissionCheckboxes(branchCheckbox) {
        const branchId = branchCheckbox.value;
        const canRequestCheckbox = document.getElementById(`can_request_${branchId}`);
        const canManageCheckbox = document.getElementById(`can_manage_${branchId}`);

        if (branchCheckbox.checked) {
            // Enable permission checkboxes and check "can_request" by default
            canRequestCheckbox.disabled = false;
            canManageCheckbox.disabled = false;
            canRequestCheckbox.checked = true;
        } else {
            // Disable and uncheck permission checkboxes
            canRequestCheckbox.disabled = true;
            canManageCheckbox.disabled = true;
            canRequestCheckbox.checked = false;
            canManageCheckbox.checked = false;
        }
    }

    function updateSelectAllState() {
        const checkedBranches = document.querySelectorAll('.branch-checkbox:checked').length;
        const totalBranches = branchCheckboxes.length;
        
        selectAllCheckbox.checked = checkedBranches === totalBranches;
        selectAllCheckbox.indeterminate = checkedBranches > 0 && checkedBranches < totalBranches;
    }

    // Initialize state
    branchCheckboxes.forEach(checkbox => {
        togglePermissionCheckboxes(checkbox);
    });
    updateSelectAllState();
});
</script>
@endsection
