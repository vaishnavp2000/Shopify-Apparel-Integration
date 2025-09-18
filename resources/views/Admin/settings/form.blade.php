@extends('admin.layouts.app')
@section('page-title', 'Settings')
@section('head')

@endsection
@section('content')
<div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
</div>
    
<div class="row flex-column-reverse flex-md-row">
    <div class="col-md-12">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="mb-4">
                    <div class="card mb-4">
                        <form id="settings_form" action="{{route('admin.settings.store')}}" method="post">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    @foreach($settings as $setting)
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->title }}</label>
                                            @if($setting->field_type == 'select')
                                                <select name="{{ $setting->code }}" class="form-select">
                                                    @if(!empty($setting->dataset))
                                                        @foreach(json_decode($setting->dataset) as $dataset)
                                                            <option value="{{ $dataset->id }}" {{ ($dataset->id==$setting->value) ? 'selected' : '' }}>{{ $dataset->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>

                                                @elseif($setting->field_type == 'textarea')
                                                    <textarea class="form-control" rows="4" name="{{ $setting->code }}">{{ $setting->value }}</textarea>

                                                @elseif($setting->field_type == 'password')
                                                    <div class="input-group position-relative">
                                                        <input autocomplete="off" type="password" name="{{ $setting->code }}" class="form-control password-field"
                                                            value="{{ $setting->value }}" placeholder="{{ $setting->placeholder }}">
                                                        <span class="fa fa-fw toggle-password fa-eye-slash position-absolute"
                                                            style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></span>
                                                    </div>

                                                @else
                                                    <input autocomplete="off" type="{{ $setting->field_type }}" name="{{ $setting->code }}" class="form-control"
                                                        value="{{ $setting->value }}" placeholder="{{ $setting->placeholder }}">
                                                @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="col-md-12">
                                        <input type="hidden" value="{{ $platform }}" name="platform">
                                        <div class="mb-3">
                                          
                                            <button type="submit" class="btn btn-primary mb-3 pull-right settings_btn">Save</button>
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
@endsection
@section('script')
<script>
    $(document).ready(function(){
        $('#settings_form').on('submit',function(){
            $('.settings_btn').text('Saving...');
        });
    });

    @if(Session::has('success_message'))
        $(document).ready( function () {
            Swal.fire({
                icon: 'success',
                title: '{{ Session::get('success_message') }}',
                // text: 'we will notify through email once the adjustment is complete',
                footer: ''
            })
        })
    @endif
    @if(Session::has('error_message'))
        $(document).ready( function () {
            Swal.fire({
                icon: 'error',
                title: '{{ Session::get('error_message') }}',
                // text: 'we will notify through email once the adjustment is complete',
                footer: ''
            })
        })
    @endif
$('.toggle-password').click(function() {
            var show = $(this).hasClass('fa-eye-slash');
            $(this).toggleClass('fa-eye-slash');
            $(this).toggleClass('fa-eye');
            $(this).toggleClass('text-primary');
            $(this).parent().find('.form-control').attr('type', show ? 'text' : 'password');
        });
</script>
@endsection

