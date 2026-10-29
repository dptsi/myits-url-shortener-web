@extends('layouts.errors')

@section('content')
@include('snippets.error_card', ['icon' => 'error', 'color' => 'tx-danger', 'code' => '500', 'message' => 'Terjadi kesalahan pada server. Silakan hubungi administrator jika masalah berlanjut.'])
@endsection
