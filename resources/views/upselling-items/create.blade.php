@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Add New Menu Item</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item"><a href="{{ route('upselling-items.index') }}">Master Menu</a></li>
                        <li class="breadcrumb-item" aria-current="page">Add New</li>
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
                    <h5>New Menu Details</h5>
                </div>
                <div class="card-body">

                    {{-- Tampilkan Error Validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('upselling-items.store') }}" method="POST">
                        @csrf

                        {{-- 1. Restaurant --}}
                        <div class="form-group mb-3">
                            <label class="form-label">Restaurant</label>
                            <select name="restaurant_id" class="form-select" required>
                                <option value="" selected disabled>-- Select Restaurant --</option>
                                @foreach ($restaurants as $rest)
                                    <option value="{{ $rest->id }}"
                                        {{ old('restaurant_id') == $rest->id ? 'selected' : '' }}>
                                        {{ $rest->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. Type (Food/Beverage) --}}
                        <div class="form-group mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="food" {{ old('type') == 'food' ? 'selected' : '' }}>Food</option>
                                <option value="beverage" {{ old('type') == 'beverage' ? 'selected' : '' }}>Beverage</option>
                            </select>
                        </div>

                        {{-- 3. Item Name --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Menu Item Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Wagyu Steak A5"
                                value="{{ old('name') }}" required>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Menu Item</button>
                            <a href="{{ route('upselling-items.index') }}" class="btn btn-light">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
