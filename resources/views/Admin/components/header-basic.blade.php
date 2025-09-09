<div class="header">
    <div class="menu-toggle-btn"> <!-- Menu close button for mobile devices -->
        <a href="#">
            <i class="bi bi-list"></i>
        </a>
    </div>

    <!-- Logo -->
    <a href="{{route('admin.dashboard')}}" class="logo">
        <img width="100" src="../../assets/images/logo.svg" alt="logo">
    </a>
    <!-- ./ Logo -->
    <div class="page-title">@yield('page-title')</div>
    @php use Illuminate\Support\Str; @endphp
    @if(Str::contains(Route::currentRouteName(), ['index', 'microsites','club_order_list']) && Route::currentRouteName() !== 'admin.account.index')
        <form class="search-form">
            <div class="input-group">
                <button class="btn btn-outline-light" type="button" id="button-addon1">
                    <i class="bi bi-search"></i>
                </button>

                <input type="text" class="form-control searchInput" placeholder="Search..." aria-label="Example text with button addon" aria-describedby="button-addon1">
                <a href="#" class="btn btn-outline-light close-header-search-bar">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    @endif
   <div class="header-bar ms-auto">
        <ul class="navbar-nav justify-content-end">
            <li class="nav-item" style="background: #e3e3e3;border-radius: 7px;padding: 7px 20px;padding-top: 10px;">
                <!-- @yield('header-action-button') -->
              
              
                <div style="float: left; margin-right: 15px" id="sync-container">
                    @if(isset($activeSyncLog) && $activeSyncLog)
                    <div class="">
                        <p style="margin-top: 0; margin-bottom: 0.5rem; /* font-weight: 500; */ line-height: 1.2;">Last Sync at</p>
                        <p style="margin-top: 0;margin-bottom: 0.5rem;font-weight: 500;line-height: 1.2;color: #117dc0;">Refresh in Progress</p>
                    </div>
                    @elseif(isset($lastSyncLog) && $lastSyncLog)
                    <div class="">
                        <p style="margin-top: 0; margin-bottom: 0.5rem; /* font-weight: 500; */ line-height: 1.2;">Last Sync at</p>
                        <p style="margin-top: 0;margin-bottom: 0.5rem;font-weight: 500;line-height: 1.2;color: #117dc0;">{{ \Carbon\Carbon::parse($lastSyncLog->updated_at)->format('m-d-Y h:i:s A')}}</p>
                    </div>
                    @else
                    <!-- <div class="">
                        <p style="margin-top: 0; margin-bottom: 0.5rem; /* font-weight: 500; */ line-height: 1.2;">Last Sync at </p>
                        <p style="margin-top: 0;margin-bottom: 0.5rem;font-weight: 500;line-height: 1.2;color: #117dc0;">Never Synced</p>
                    </div> -->
                    @endif
                </div>
            </li>

        </ul>
    </div>
    <!-- <div class="header-bar ms-auto">
        <ul class="navbar-nav justify-content-end">
            <li class="nav-item">
                <a href="#" class="nav-link" data-sidebar-target="#notifications">
                    <i class="bi bi-bell"></i>
                </a>
            </li>
           
            <li class="nav-item ms-3">
                            </li>
        </ul>
    </div> -->
    <!-- Header mobile buttons -->
    <div class="header-mobile-buttons">
        <a href="#" class="search-bar-btn">
            <i class="bi bi-search"></i>
        </a>
        <a href="#" class="actions-btn">
            <i class="bi bi-three-dots"></i>
        </a>
    </div>
    <!-- ./ Header mobile buttons -->
</div>