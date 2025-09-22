@extends('layouts.dashboard')

@section('title', 'إدارة صلاحيات الفروع')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">إدارة صلاحيات الفروع للمستخدمين</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>المستخدم</th>
                                    <th>الدور</th>
                                    <th>الفروع المخصصة</th>
                                    <th>الصلاحيات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->roles->isNotEmpty())
                                                <span class="badge bg-primary">{{ $user->roles->first()->name }}</span>
                                            @else
                                                <span class="text-muted">لا يوجد دور</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->userBranches->isNotEmpty())
                                                @foreach($user->userBranches as $userBranch)
                                                    <span class="badge bg-info me-1 mb-1">{{ $userBranch->branch->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">لا توجد فروع مخصصة</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->userBranches->isNotEmpty())
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($user->userBranches as $userBranch)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <small class="text-muted">{{ $userBranch->branch->name }}:</small>
                                                            @if($userBranch->can_request)
                                                                <span class="badge bg-success">طلبات</span>
                                                            @endif
                                                            @if($userBranch->can_manage)
                                                                <span class="badge bg-warning">إدارة</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('user-branches.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-edit"></i>
                                                تعديل الصلاحيات
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Overview -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">نظرة عامة على الفروع</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($branches as $branch)
                            <div class="col-md-4 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $branch->name }}</h6>
                                        <div class="mt-2">
                                            <small class="text-muted">المستخدمون المخولون:</small>
                                            <div class="mt-1">
                                                @if($branch->userBranches->isNotEmpty())
                                                    @foreach($branch->userBranches as $userBranch)
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="badge bg-label-primary">{{ $userBranch->user->name }}</span>
                                                            <div>
                                                                @if($userBranch->can_request)
                                                                    <i class="ti ti-file-text text-success" title="يمكنه إنشاء طلبات"></i>
                                                                @endif
                                                                @if($userBranch->can_manage)
                                                                    <i class="ti ti-settings text-warning" title="يمكنه إدارة الفرع"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">لا يوجد مستخدمون مخولون</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
