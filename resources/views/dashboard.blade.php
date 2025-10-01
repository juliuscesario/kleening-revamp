@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="page-body">
        <div class="container-xl">

            @if(in_array(strtolower(Auth::user()->role), ['owner', 'co_owner']))
                {{-- Owner & co_owner Dashboard --}}
                @include('partials.dashboard.owner-coowner')
            @elseif(Auth::user()->role === 'admin')
                {{-- Admin Dashboard --}}
                @include('partials.dashboard.admin')
            @elseif(Auth::user()->role === 'staff')
                {{-- Staff Dashboard --}}
                @include('partials.dashboard.staff')
            @endif

        </div>
    </div>


@endsection