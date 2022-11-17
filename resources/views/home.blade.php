@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-right">
                <a href="{{ route('crawlData') }}" class="btn btn-danger">CRAWL</a>
            </div>
        </div>
    </div>
@endsection
