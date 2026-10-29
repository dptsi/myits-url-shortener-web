@extends('layouts.errors')

@section('content')
@include('snippets.error_card', ['icon' => 'lock', 'color' => 'tx-warning', 'code' => '403', 'message' => 'Anda tidak berhak mengakses halaman ini.'])
@endsection
