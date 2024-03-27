<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">

            </span>
            <span class="app-brand-text demo menu-text fw-bold">Rush Hub</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ Request::is('/') ? 'active open' : '' }}">
            <a href="{{ route('home') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div data-i18n="الرئيسية">الرئيسية</div>
            </a>
        </li>

        <!-- Actions -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">الحركات</span>
        </li>
        <li class="menu-item {{ Request::is('product_exchange/*', 'product_exchange') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-transfer-out"></i>
                <div data-i18n="التحويلات">التحويلات</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('product_exchange') ? 'active' : '' }}">
                    <a href="{{ route('exchanged product') }}" class="menu-link">
                        <div data-i18n="عرض التحويلات">عرض التحويلات</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('product_exchange/create') ? 'active' : '' }}">
                    <a href="{{ route('create exchange product') }}" class="menu-link">
                        <div data-i18n="تحويل اصناف">تحويل اصناف</div>
                    </a>
                </li>
            </ul>
        <li class="menu-item {{ Request::is('product_increase/*', 'product_increase') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-transfer-in"></i>
                <div data-i18n="الاضافات">الاضافات</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('product_increase') ? 'active' : '' }}">
                    <a href="{{ route('increased product') }}" class="menu-link">
                        <div data-i18n="عرض الاضافات">عرض الاضافات</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('product_increase/create') ? 'active' : '' }}">
                    <a href="{{ route('create increase product') }}" class="menu-link">
                        <div data-i18n="اضافة اصناف">اضافة اصناف</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Inventory -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">المخزون</span>
        </li>

        <li class="menu-item {{ Request::is('branch-inventory/*', 'branch-inventory') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-transfer-out"></i>
                <div data-i18n="الفروع">الفروع</div>
            </a>
            <ul class="menu-sub">
                @foreach ($branches as $branch)
                    <li class="menu-item {{ Request::is('branch-iventory/' . $branch->id) ? 'active' : '' }}">
                        <a href="{{ route('inventory', $branch->id) }}" class="menu-link">
                            <div data-i18n="مخزن {{ $branch->name }}">مخزن {{ $branch->name }}</div>
                        </a>
                    </li>
                @endforeach

            </ul>
        </li>
        <!-- Suppliers -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">الموردين</span>
        </li>
        <li class="menu-item {{ Request::is('supplier/*', 'supplier') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-truck-delivery"></i>
                <div data-i18n="الموردين">الموردين</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('supplier') ? 'active' : '' }}">
                    <a href="{{ route('show suppliers') }}" class="menu-link">
                        <div data-i18n="عرض الموردين">عرض الموردين</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('supplier/create') ? 'active' : '' }}">
                    <a href="{{ route('create supplier') }}" class="menu-link">
                        <div data-i18n="اضافة مورد">اضافة مورد</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Settings -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">الاعدادت</span>
        </li>
        <li class="menu-item {{ Request::is('branch/*', 'branch') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-building-warehouse"></i>
                <div data-i18n="المخازن الفرعية">المخازن الفرعية</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('branch') ? 'active' : '' }}">
                    <a href="{{ route('show branches') }}" class="menu-link">
                        <div data-i18n="عرض المخازن الفرعية">عرض المخازن الفرعية</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('branch/create') ? 'active' : '' }}">
                    <a href="{{ route('create branch') }}" class="menu-link">
                        <div data-i18n="اضافة مخزن فرعي">اضافة مخزن فرعي</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ Request::is('product/*', 'product') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon ti ti-box"></i>
                <div data-i18n="الاصناف">الاصناف</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('product') ? 'active' : '' }}">
                    <a href="{{ route('show products') }}" class="menu-link">
                        <div data-i18n="عرض الاصناف">عرض الاصناف</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('product/create') ? 'active' : '' }}">
                    <a href="{{ route('create product') }}" class="menu-link">
                        <div data-i18n="تعريف صنف">تعريف صنف</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ Request::is('category/*', 'category') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon ti ti-category"></i>
                <div data-i18n="الاقسام">الاقسام</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('category') ? 'active' : '' }}">
                    <a href="{{ route('show categories') }}" class="menu-link">
                        <div data-i18n="عرض الاقسام">عرض الاقسام</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('category/create') ? 'active' : '' }}">
                    <a href="{{ route('create category') }}" class="menu-link">
                        <div data-i18n="اضافة قسم">اضافة قسم</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ Request::is('unit/*', 'unit') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon ti ti-weight"></i>
                <div data-i18n="الوحدات">الوحدات</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('unit') ? 'active' : '' }}">
                    <a href="{{ route('show units') }}" class="menu-link">
                        <div data-i18n="عرض الوحدات">عرض الوحدات</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('unit/create') ? 'active' : '' }}">
                    <a href="{{ route('create unit') }}" class="menu-link">
                        <div data-i18n="اضافة وحدة">اضافة وحدة</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
