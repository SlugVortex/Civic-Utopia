@extends('layouts/layoutMaster')

@section('title', 'Account settings - Account')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Account Settings /</span> Account
</h4>

<div class="row">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-md-row mb-4">
      <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="icon-base ri-user-3-line me-1 ri-20px"></i> Account</a></li>
      {{-- You can add links to other settings pages later --}}
      {{-- <li class="nav-item"><a class="nav-link" href="#"><i class="icon-base ri-notification-4-line me-1 ri-20px"></i> Notifications</a></li> --}}
      {{-- <li class="nav-item"><a class="nav-link" href="#"><i class="icon-base ri-link-m me-1 ri-20px"></i> Connections</a></li> --}}
    </ul>
    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="d-block w-px-120 h-px-120 rounded" id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="ri-upload-2-line d-block d-sm-none"></i>
              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
            </label>
            <button type="button" class="btn btn-outline-danger account-image-reset mb-3">
              <i class="ri-refresh-line d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <div class="text-muted small">Allowed JPG, GIF or PNG. Max size of 800K</div>
          </div>
        </div>
      </div>
      <hr class="my-0">
      <div class="card-body">
        <form id="formAccountSettings" method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('patch')

          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="name" class="form-label">Full Name</label>
              <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus />
               @error('name')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="text" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="john.doe@example.com" />
               @error('email')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="mt-2">
            <button type="submit" class="btn btn-primary me-2">Save changes</button>
            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
          </div>
        </form>
      </div>
      <!-- /Account -->
    </div>
    <div class="card">
      <h5 class="card-header">Delete Account</h5>
      <div class="card-body">
        <div class="mb-3 col-12 mb-0">
          <div class="alert alert-warning">
            <h6 class="alert-heading mb-1">Are you sure you want to delete your account?</h6>
            <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
          </div>
        </div>
        <form id="formAccountDeactivation" method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" required />
                <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Confirm Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                @error('password', 'userDeletion')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
