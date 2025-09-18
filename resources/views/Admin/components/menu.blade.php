<div class="menu">
    <div class="menu-header">
        <a href="{{route('admin.dashboard')}}" class="menu-header-logo">
            <img src="{{ url('assets/images/logo.png') }}" alt="logo">
        </a>
        <a href="#" class="btn btn-sm menu-close-btn">
            <i class="bi bi-x"></i>
        </a>
    </div>
    <div class="menu-body" tabindex="6" style="overflow: hidden; outline: none;">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center" data-bs-toggle="dropdown">
                <div class="avatar me-3">
                    <span class="avatar-text rounded-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div style="width: 90%;">
                    <div class="fw-bold">{{auth()->user()->name}}</div>
                    <small class="text-muted">{{auth()->user()->email}}</small>
                </div>
                <div class="">
                    <i class="bi bi-gear"></i>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a href="{{ route('admin.profile.index') }}" class="dropdown-item d-flex align-items-center">
                    <i class="bi bi-person dropdown-item-icon"></i> Profile
                </a>
                <a href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                    class="dropdown-item d-flex align-items-center"
                    style="color: ;">
                    <i class="bi bi-box-arrow-right dropdown-item-icon"></i> Logout
                </a>

                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        <ul>

            <li>
                <a href="{{route('admin.dashboard')}}" @if (request()->fullUrl() ==
                    route('admin.dashboard')) class="active" @endif>
                    <span class="nav-link-icon">
                        <i class="bi bi-bar-chart"></i>
                    </span>
                    <span>Dashboard</span>

                </a>
            </li>
         
            <li>
               <a href="{{route('admin.product.index')}}" @if (request()->fullUrl() ==
                    route('admin.product.index')) class="active" @endif>
                    <span class="nav-link-icon">
                        <i class="bi bi-bag"></i>
                    </span>
                    <span>Products</span>

                </a>
            </li>
           <li>
               <a href="{{route('admin.order.index')}}" @if (request()->fullUrl() ==
                    route('admin.order.index')) class="active" @endif>
                    <span class="nav-link-icon">
                        <i class="bi bi-cart"></i>
                    </span>
                    <span>Orders</span>

                </a>
            </li>
            <li>
                <a href="{{route('admin.settings.index',['platform' => 'settings'])}}"
                    @if (request()->fullUrl() == route('admin.settings.index', ['platform' => 'settings'])) class="active" @endif>
                    <span class="nav-link-icon">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                    </span>
                    <span>Systems</span>
                </a>
                <ul>
              <li><a href="{{ route('admin.settings.index', ['platform' => 'shopify']) }}" @if (request()->fullUrl() ==
                    route('admin.settings.index', ['platform' => 'shopify'])) class="active" @endif>Shopify</a>
                </li>
                <li><a href="{{ route('admin.settings.index', ['platform' => 'apparelmagic']) }}" @if (request()->fullUrl() ==
                    route('admin.settings.index', ['platform' => 'apparelmagic'])) class="active" @endif>Apparelmagic</a>
                </li>
                </ul>
             </li>
           

            <!-- <li>
                <a href="">
                    <span class="nav-link-icon">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                    </span>
                    <span>Systems</span>
                </a>
            </li> -->

        </ul>
    </div>
    <div id="ascrail2005" class="nicescroll-rails nicescroll-rails-vr"
        style="width: 8px; z-index: 998; cursor: default; position: absolute; top: 98px; left: 342px; height: 152px; opacity: 0; display: block;">
        <div class="nicescroll-cursors"
            style="position: relative; top: 59px; float: right; width: 6px; height: 32px; background-color: rgb(66, 66, 66); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;">
        </div>
    </div>
    <div id="ascrail2005-hr" class="nicescroll-rails nicescroll-rails-hr"
        style="height: 8px; z-index: 998; top: 241.6px; left: 0px; position: absolute; cursor: default; display: none; width: 342px; opacity: 0;">
        <div class="nicescroll-cursors"
            style="position: absolute; top: 0px; height: 6px; width: 350px; background-color: rgb(66, 66, 66); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px; left: 0px;">
        </div>
    </div>
</div>