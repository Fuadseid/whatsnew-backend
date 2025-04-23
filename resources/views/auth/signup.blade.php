@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Sign Up</h2>
        
        <form method="POST" action="{{ route('signup') }}">
            @csrf

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
                @error('username') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Account Type</label>
                <select name="user_type" class="form-control" required>
                    @foreach($userTypes as $type)
                        <option value="{{ $type }}">
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
@endsection