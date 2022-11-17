@extends('layouts.errors')

@section('content')
<h1>404</h1>
<p>Halaman yang Anda cari tidak bisa kami temukan. Mohon cek URL.</p>
<a href="{{ url('/') }}" class="btn btn-primary"><span class="fa fa-arrow-left"></span> Kembali</a>
@endsection
