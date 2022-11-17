@extends('layouts.errors')

@section('content')
<h1>403</h1>
<p>Anda tidak berhak mengakses halaman ini.</p>
<a href="{{ url('/') }}" class="btn btn-primary"><span class="fa fa-arrow-left"></span> Kembali</a>
@endsection
