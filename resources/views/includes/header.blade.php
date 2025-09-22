<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar" style="right: 16.25rem; left:0">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center me-3">
            <div class="nav-item d-flex align-items-center">
                <i class="ti ti-search ti-md"></i>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" 
                       placeholder="البحث في المنتجات..." aria-label="Search..." id="global-search">
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Quick Actions -->
            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" 
                   data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-layout-grid-add ti-md"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end py-0">
                    <div class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">الإجراءات السريعة</h5>
                        </div>
                    </div>
                    <div class="dropdown-shortcuts-list scrollable-container">
                        <div class="row row-bordered overflow-visible g-0">
                            <div class="dropdown-shortcuts-item col">
                                <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                    <i class="ti ti-plus fs-4"></i>
                                </span>
                                <a href="{{ route('product-requests.create') }}" class="stretched-link">طلب منتجات</a>
                                <small class="text-muted mb-0">إنشاء طلب جديد</small>
                            </div>
                            <div class="dropdown-shortcuts-item col">
                                <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                    <i class="ti ti-box fs-4"></i>
                                </span>
                                <a href="{{ route('create product') }}" class="stretched-link">منتج جديد</a>
                                <small class="text-muted mb-0">إضافة منتج</small>
                            </div>
                        </div>
                        <div class="row row-bordered overflow-visible g-0">
                            <div class="dropdown-shortcuts-item col">
                                <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                    <i class="ti ti-transfer-out fs-4"></i>
                                </span>
                                <a href="{{ route('create exchange product') }}" class="stretched-link">تحويل منتجات</a>
                                <small class="text-muted mb-0">بين الفروع</small>
                            </div>
                            <div class="dropdown-shortcuts-item col">
                                <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                    <i class="ti ti-chart-bar fs-4"></i>
                                </span>
                                <a href="{{ route('monthly starts report') }}" class="stretched-link">التقارير</a>
                                <small class="text-muted mb-0">تقارير شاملة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Notifications -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <span class="position-relative">
                        <i class="ti ti-bell ti-md"></i>
                        <span class="badge badge-center rounded-pill bg-danger badge-notifications" id="notification-count" style="display: none;">0</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                    <div class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">التنبيهات</h5>
                            <a href="javascript:void(0)" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" 
                               data-bs-placement="top" title="تحديد الكل كمقروء" onclick="markAllNotificationsAsRead()">
                                <i class="ti ti-mail-opened fs-4"></i>
                            </a>
                        </div>
                    </div>
                    <div class="dropdown-notifications-list scrollable-container" id="notifications-list">
                        <div class="text-center p-4">
                            <i class="ti ti-bell-off text-muted"></i>
                            <p class="text-muted mb-0">لا توجد تنبيهات جديدة</p>
                        </div>
                    </div>
                    <div class="dropdown-menu-footer border-top">
                        <a href="javascript:void(0)" class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1">
                            <span class="align-middle">عرض جميع التنبيهات</span>
                        </a>
                    </div>
                </ul>
            </li>

            <!-- System Status -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link" href="javascript:void(0);" id="system-status" title="حالة النظام">
                    <span class="position-relative">
                        <i class="ti ti-activity ti-md text-success"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge badge-dot bg-success border-2 border-white rounded-pill"></span>
                    </span>
                </a>
            </li>
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('img/avatars/1.jpg') }}" alt class="h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('img/avatars/1.jpg') }}" alt class="h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                    <small class="text-muted">{{ auth()->user()->roles[0]->name }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>

                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button style="display: flex; align-items:center;" class="dropdown-item" type="submit">
                                <i class="menu-icon ti ti-logout me-2 "></i>
                                <span class="align-middle">تسجيل خروج</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

<!-- Header JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global search functionality
    const globalSearch = document.getElementById('global-search');
    if (globalSearch) {
        let searchTimeout;
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = this.value.trim();
                if (query.length >= 2) {
                    performGlobalSearch(query);
                }
            }, 300);
        });
    }

    // Load notifications
    loadNotifications();
    
    // Update notifications every 60 seconds
    setInterval(loadNotifications, 60000);
    
    // System status check
    checkSystemStatus();
    setInterval(checkSystemStatus, 300000); // Every 5 minutes
});

function performGlobalSearch(query) {
    console.log('Searching for:', query);
    
    // Show search results dropdown
    showSearchResults(query);
}

function showSearchResults(query) {
    // Create or get search results dropdown
    let searchDropdown = document.getElementById('search-results-dropdown');
    if (!searchDropdown) {
        searchDropdown = document.createElement('div');
        searchDropdown.id = 'search-results-dropdown';
        searchDropdown.className = 'dropdown-menu show position-absolute';
        searchDropdown.style.cssText = 'top: 100%; left: 0; right: 0; max-height: 400px; overflow-y: auto; z-index: 9999;';
        
        const searchContainer = document.querySelector('#global-search').parentElement;
        searchContainer.style.position = 'relative';
        searchContainer.appendChild(searchDropdown);
    }

    // Show loading
    searchDropdown.innerHTML = '<div class="dropdown-item text-center"><i class="ti ti-loader ti-spin"></i> جاري البحث...</div>';

    // Perform search
    fetch(`{{ route('api.search') }}?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results.length > 0) {
                searchDropdown.innerHTML = data.results.map(result => `
                    <a href="${result.url}" class="dropdown-item d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="${result.icon} text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${result.title}</div>
                            <small class="text-muted">${result.subtitle}</small>
                            ${result.badge ? `<span class="badge bg-${result.badge_color} ms-2">${result.badge}</span>` : ''}
                        </div>
                    </a>
                `).join('') + `
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item text-center">
                        <small class="text-muted">عرض ${data.results.length} من النتائج</small>
                    </div>
                `;
            } else {
                searchDropdown.innerHTML = '<div class="dropdown-item text-center text-muted">لا توجد نتائج</div>';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            searchDropdown.innerHTML = '<div class="dropdown-item text-center text-danger">خطأ في البحث</div>';
        });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#global-search') && !e.target.closest('#search-results-dropdown')) {
            searchDropdown.remove();
        }
    });
}

function loadNotifications() {
    const notificationCount = document.getElementById('notification-count');
    const notificationsList = document.getElementById('notifications-list');
    
    // Load notifications from API
    fetch('{{ route("api.notifications") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notifications = data.notifications;
                const unreadCount = data.unread_count;
                
                // Update notification count badge
                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount;
                    notificationCount.style.display = 'inline-block';
                } else {
                    notificationCount.style.display = 'none';
                }
                
                // Update notifications list
                if (notifications.length > 0) {
                    notificationsList.innerHTML = notifications.map(notification => {
                        const typeIcons = {
                            'request': 'ti-file-text',
                            'urgent': 'ti-alert-triangle',
                            'warning': 'ti-alert-triangle',
                            'danger': 'ti-x-circle',
                            'success': 'ti-check-circle'
                        };
                        
                        const typeColors = {
                            'request': 'primary',
                            'urgent': 'danger',
                            'warning': 'warning',
                            'danger': 'danger',
                            'success': 'success'
                        };
                        
                        return `
                            <li class="list-group-item list-group-item-action dropdown-notifications-item ${notification.unread ? 'unread' : ''}" 
                                data-notification-id="${notification.id}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-${typeColors[notification.type] || 'primary'}">
                                                <i class="ti ${typeIcons[notification.type] || 'ti-bell'}"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${notification.title}</h6>
                                        <p class="mb-0">${notification.message}</p>
                                        <small class="text-muted">${notification.time}</small>
                                    </div>
                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                        ${notification.unread ? `
                                            <a href="javascript:void(0)" class="dropdown-notifications-read" 
                                               onclick="markNotificationAsRead('${notification.id}')">
                                                <span class="badge badge-dot bg-primary"></span>
                                            </a>
                                        ` : ''}
                                    </div>
                                </div>
                                ${notification.url ? `
                                    <div class="mt-2">
                                        <a href="${notification.url}" class="btn btn-sm btn-outline-primary">عرض التفاصيل</a>
                                    </div>
                                ` : ''}
                            </li>
                        `;
                    }).join('');
                } else {
                    notificationsList.innerHTML = `
                        <div class="text-center p-4">
                            <i class="ti ti-bell-off text-muted"></i>
                            <p class="text-muted mb-0">لا توجد تنبيهات جديدة</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationsList.innerHTML = `
                <div class="text-center p-4">
                    <i class="ti ti-alert-triangle text-danger"></i>
                    <p class="text-danger mb-0">خطأ في تحميل التنبيهات</p>
                </div>
            `;
        });
}

function markNotificationAsRead(notificationId) {
    fetch('{{ route("api.notifications.mark-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread styling
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                const readButton = notificationElement.querySelector('.dropdown-notifications-read');
                if (readButton) {
                    readButton.remove();
                }
            }
            
            // Update notification count
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllNotificationsAsRead() {
    fetch('{{ route("api.notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function checkSystemStatus() {
    const systemStatus = document.getElementById('system-status');
    const statusIcon = systemStatus.querySelector('i');
    const statusDot = systemStatus.querySelector('.badge-dot');
    
    // Mock system status - replace with actual health check
    const isHealthy = true;
    
    if (isHealthy) {
        statusIcon.className = 'ti ti-activity ti-md text-success';
        statusDot.className = 'position-absolute top-0 start-100 translate-middle badge badge-dot bg-success border-2 border-white rounded-pill';
        systemStatus.title = 'النظام يعمل بشكل طبيعي';
    } else {
        statusIcon.className = 'ti ti-activity ti-md text-danger';
        statusDot.className = 'position-absolute top-0 start-100 translate-middle badge badge-dot bg-danger border-2 border-white rounded-pill';
        systemStatus.title = 'يوجد مشاكل في النظام';
    }
}
</script>

<style>
.dropdown-shortcuts-item {
    padding: 1rem;
    text-align: center;
    border-right: 1px solid rgba(0,0,0,0.08);
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.dropdown-shortcuts-item:hover {
    background-color: rgba(0,0,0,0.04);
}

.dropdown-shortcuts-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0,0,0,0.08);
    margin: 0 auto;
}

.dropdown-notifications-item.unread {
    background-color: rgba(0,123,255,0.05);
}

.badge-notifications {
    font-size: 0.625rem;
    min-width: 18px;
    height: 18px;
    top: -8px;
    right: -8px;
}

.scrollable-container {
    max-height: 300px;
    overflow-y: auto;
}

#global-search {
    width: 200px;
    transition: width 0.3s ease;
}

#global-search:focus {
    width: 250px;
}

@media (max-width: 768px) {
    #global-search {
        width: 150px;
    }
    
    #global-search:focus {
        width: 180px;
    }
}
</style>
