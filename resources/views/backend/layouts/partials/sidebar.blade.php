<div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="https://ui-avatars.com/api/?name=مدير+النظام&background=007bff&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
            <a href="#" class="d-block">مدير النظام</a>
        </div>
    </div>

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            
            {{-- لوحة التحكم --}}
            <li class="nav-item">
                <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>لوحة التحكم العامة</p>
                </a>
            </li>

            {{-- هيكل النظام (المخازن - المنتجات - الموردين) --}}
            <li class="nav-header text-uppercase">هيكل النظام</li>
            
            <li class="nav-item">
                <a href="{{ route('admin.governorates.index') }}" class="nav-link {{ request()->routeIs('admin.governorates.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-warehouse"></i>
                    <p>المحافظات</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.warehouses.index') }}" class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-warehouse"></i>
                    <p>المخازن</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-store"></i>
                    <p>الادارات</p>
                </a>
            </li> 

            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-boxes"></i>
                    <p>المنتجات</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-truck"></i>
                    <p>الموردين</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.drivers.index') }}" class="nav-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-clock"></i>
                    <p>المناديب</p>
                </a>
            </li>

            {{-- إدارة الأجهزة --}}
            <li class="nav-item">
                <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-microchip"></i>
                    <p>الأجهزة</p>
                </a>
            </li>

            {{-- العمليات اليومية --}}
            <li class="nav-header text-uppercase">العمليات اليومية</li>

            <li class="nav-item">
                <a href="{{ route('admin.receiving_orders.index') }}" class="nav-link {{ request()->routeIs('admin.receiving_orders.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-dolly"></i>
                    <p>الشحنات الواردة</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.transfers.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.transfers.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-exchange-alt"></i>
                    <p>تحويلات المخازن</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="" class="nav-link {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>جرد وحركة المخزون</p>
                </a>
            </li>

            <li class="nav-item has-treeview {{ request()->routeIs('admin.inventories.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('admin.inventories.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>
                        الأرصدة والتقارير
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.inventories.index') }}" 
                           class="nav-link {{ request()->routeIs('admin.inventories.index') ? 'active' : '' }}">
                            <i class="fas fa-warehouse nav-icon"></i>
                            <p>الأرصدة للفرعيات</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.all_warehouses') }}" 
                           class="nav-link {{ request()->routeIs('admin.all_warehouses') ? 'active' : '' }}">
                            <i class="fas fa-boxes nav-icon"></i>
                            <p>مراقبة الأرصدة العامة</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="" class="nav-link {{ request()->routeIs('admin.inventories.total') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie nav-icon"></i>
                            <p>الرصيد الكلي</p>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- المدارس --}}
            <li class="nav-item">
                <a href="{{ route('admin.schools.index') }}" class="nav-link {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-school"></i>
                    <p>المدارس</p>
                </a>
            </li>

            {{-- منصرف الادارات (يومي) --}}
            <li class="nav-item has-treeview {{ request()->routeIs('admin.department_allocations.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('admin.department_allocations.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-truck-loading"></i>
                    <p>
                        منصرف الادارات (يومي)
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.department_allocations.index') }}" class="nav-link {{ request()->routeIs('admin.department_allocations.index') ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>كل أوامر الصرف</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.department_allocations.create') }}" class="nav-link {{ request()->routeIs('admin.department_allocations.create') ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>صرف جديد</p>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- منصرف المدارس (يومي) --}}
            <li class="nav-item has-treeview {{ request()->routeIs('admin.distribution_orders.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('admin.distribution_orders.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-truck-loading"></i>
                    <p>
                        منصرف المدارس (يومي)
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.distribution_orders.index') }}" class="nav-link {{ request()->routeIs('admin.distribution_orders.index') ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>كل أوامر الصرف</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.distribution_orders.create') }}" class="nav-link {{ request()->routeIs('admin.distribution_orders.create') ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>صرف جديد</p>
                        </a>
                    </li>
                </ul>
            </li> 

            {{-- إعدادات النظام --}}
            <li class="nav-header text-uppercase">النظام</li>

            <li class="nav-item">
                <a href="" class="nav-link">
                    <i class="nav-icon fas fa-cogs"></i>
                    <p>إعدادات النظام</p>
                </a>
            </li>

            {{-- تسجيل الخروج --}}
            <li class="nav-item mt-4">
                <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <p>تسجيل الخروج</p>
                </a>
                <form id="logout-form" action="" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>
</div>