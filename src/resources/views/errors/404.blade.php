@extends('layouts.errors')

@section('content')
@include('snippets.error_card', ['icon' => 'link_off', 'code' => '404', 'message' => 'Halaman atau tautan yang Anda cari tidak bisa kami temukan. Mohon cek URL.'])
@endsection
