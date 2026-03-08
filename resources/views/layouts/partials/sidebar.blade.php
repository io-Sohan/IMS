<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <i class="bi bi-box-seam"></i>
        <span>IMS Admin</span>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a class="nav-link {{ request()->routeIs('categories') ? 'active' : '' }}" href="{{ route('categories') }}">
            <i class="bi bi-tags"></i>
            <span>Categories</span>
        </a>
        <a class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}" href="{{ route('products') }}">
            <i class="bi bi-box"></i>
            <span>Products</span>
        </a>
        <a class="nav-link {{ request()->routeIs('stocks') ? 'active' : '' }}" href="{{ route('stocks') }}">
            <i class="bi bi-archive"></i>
            <span>Product Stock</span>
        </a>
         <a class="nav-link {{ request()->routeIs('customers') ? 'active' : '' }}" href="{{ route('customers') }}">
            <i class="bi bi-people"></i>
            <span>Customers</span>
        </a>
        <a class="nav-link {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}">
            <i class="bi bi-receipt"></i>
            <span>POS / Invoice</span>
        </a>
        <a class="nav-link {{ request()->routeIs('invoices') ? 'active' : '' }}" href="{{ route('invoices') }}">
            <i class="bi bi-list-ul"></i>
            <span>Invoices</span>
        </a>
    </nav>

    <div class="sidebar-footer mt-auto">
        <button type="button" class="nav-link border-0 bg-transparent w-100 text-start" id="logoutBtn"
            onclick="doLogout()">
            <i class="bi bi-box-arrow-right"></i>
            <span>Log out</span>
        </button>
    </div>
</aside>

