@extends('layouts.app')

@section('content')
    <div class="">
        <div class="card">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-6">
                        <span class="h3 font-bold"><strong>Crawl History</strong></span>
                    </div>
                    <div class="col-6 text-right">
                        <span class="mr-2 inline-block">Last date: {{ $lastCrawl->finished_date ?? 'empty' }}</span>
                        <a href="{{ route('crawlData') }}" class="btn btn-danger">CRAWL</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr class="font-weight-bold text-center">
                            <th>#</th>
                            <th class="col-1">Success</th>
                            <th class="col-1">Fail</th>
                            <th class="col-1">Total</th>
                            <th class="col-3">Start date</th>
                            <th class="col-3">End date</th>
                            <th class="col-2"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($crawls as $index => $crawl)
                            <tr class="text-center">
                                <td>{{ ($crawls->currentPage() - 1) * $crawls->perPage() + $index + 1 }}</td>
                                <td>{{ $crawl->task_done }}</td>
                                <td>{{ $crawl->total_task - $crawl->task_done }}</td>
                                <td>{{ $crawl->total_task }}</td>
                                <td>{{ $crawl->started_date }}</td>
                                <td>{{ $crawl->finished_date }}</td>
                                <td><a href="{{ route('downloadFile', ['file' => $crawl->file]) }}">Download file</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                {!! $crawls->links() !!}
            </div>
        </div>
    </div>
@endsection
