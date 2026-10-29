@extends('layouts.errors')

@section('content')
@include('snippets.error_card', ['icon' => 'block', 'color' => 'tx-danger', 'code' => 'Error', 'message' => $message])
@endsection
