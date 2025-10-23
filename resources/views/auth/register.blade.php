@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create your account</h2>
                    
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input id="name" name="name" type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter your name"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" name="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Create a password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" 
                                   class="form-control" 
                                   placeholder="Confirm your password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="text-decoration-none">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection