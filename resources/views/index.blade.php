@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='css/index.css' />
@endsection

@section('content')

<h3 class='title'><strong>Type your URL here</strong></h3>

<form method='POST' action='/shorten' role='form'>
    <input type='url' autocomplete='off'
        class='form-control long-link-input' placeholder='http://' name='link-url' />

    <div class='row' id='options' ng-cloak>
        <p>Customize link</p>

        <div>
            <div class='custom-link-text'>
                <h2 class='site-url-field'>{{env('APP_ADDRESS')}}/</h2>
                <input type='text' placeholder="your custom URL" autocomplete="off" class='form-control custom-url-field' name='custom-ending' />
                <a href='#' class='btn btn-success check-btn' id='check-link-availability'>Check Availability</a>
            </div>
            <div>
                <div id='link-availability-status'></div>
            </div>
        </div>
    </div>
    <input type='submit' class='btn btn-primary' id='shorten' value='Shorten' />
    {{-- <a href='#' class='btn btn-success' id='show-link-options'>Link Options</a> --}}
    <input type="hidden" name='_token' value='{{csrf_token()}}' />
</form>

@endsection

@section('js')
<script src='js/index.js'></script>
@endsection
