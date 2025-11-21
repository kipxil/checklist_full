@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Add New Restaurant</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('restaurants.index') }}">Restaurants</a></li>
                        <li class="breadcrumb-item" aria-current="page">Create</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>New Restaurant Details</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('restaurants.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Restaurant Code (Unique)</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. 209, NGN, XFH"
                                value="{{ old('code') }}" required>
                            <small class="text-muted">Used for system logic. Keep it short.</small>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Restaurant Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. 209 Dining"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Restaurant</button>
                            <a href="{{ route('restaurants.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
