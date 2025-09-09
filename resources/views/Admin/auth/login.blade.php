@extends('admin.layouts.auth')

@section('page-title', 'Login')

@section('content')

<div class="form-wrapper">
    <div class="container">
        <div class="card">
            <div class="row g-0">
                <div class="col">
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <div class="d-block d-lg-none text-center text-lg-start">
                                <img width="120" src="{{ url('assets/images/logo.svg') }}" alt="logo">
                            </div>
                            <div class="my-5 text-center text-lg-start">
                                <h1 class="display-8">Sign In</h1>
                                <p class="text-muted">Sign in to  Admin Dashboard</p>
                            </div>
                            <form class="mb-5" method="POST" action="{{ route('admin.login') }}">
                                @csrf
                                @if(Session::has('failure_Message'))
                                    <div class="alert alert-danger">
                                        {{ Session::get('failure_Message') }}
                                    </div>
                                    @php
                                        Session::forget('failure_Message');
                                    @endphp
                                @endif
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter password" required autocomplete="current-password">
                                    @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="text-center text-lg-start">
                                    @if (Route::has('admin.password.request'))
                                    <p class="small">Can't access your account? <a href="{{ route('admin.password.request') }}">Reset your password now</a>.</p>
                                    @endif
                                    <button class="btn btn-primary">Sign In</button>
                                </div>
                            </form>
                            {{-- <div class="social-links justify-content-center">
                                    <a href="#">
                                        <i class="ti-google bg-google"></i> Sign in with Google
                                    </a>
                                    <a href="#">
                                        <i class="ti-facebook bg-facebook"></i> Sign in with Facebook
                                    </a>
                                </div>
                                <p class="text-center d-block d-lg-none mt-5 mt-lg-0">
                                    Don't have an account? <a href="#">Sign up</a>.
                                </p> --}}
                        </div>
                    </div>
                </div>
                <div class="col d-none d-lg-flex border-start align-items-center justify-content-between flex-column text-center">
                    <div class="logo">
                        <img width="225" src="{{ url('assets/images/logo.svg') }}" alt="logo">
                    </div>
                    <div>
                        {{-- <h3 class="fw-bold">Think GYRA</h3> --}}
                        <p class="lead my-5" style="font-size: 1rem">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Distinctio obcaecati eum enim quam eius aliquid, </p>
                        <p class="lead my-5" style="font-size: 1rem">velit rerum praesentium ipsam necessitatibus omnis consequuntur fuga quod, fugiat aliquam suscipit debitis nulla tenetur. </p>
                        {{-- <a href="#" class="btn btn-primary">Sign Up</a> --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection