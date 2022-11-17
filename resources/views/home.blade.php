@extends('layouts.app')

@section('content')
    <div class="">
        <div class="card">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-4">
                        <span class="h5 font-bold"><strong>Crawl History</strong></span>
                    </div>
                    <div class="col-8 col-reserver text-right">
                        <span class="mr-2 inline-block">Last date:
                            @if (isset($lastCrawl->finished_date))
                                <a href="{{ route('downloadFile', ['file' => $lastCrawl->file]) }}">
                                    {{ $lastCrawl->finished_date }}
                                </a>
                            @else
                                empty
                            @endif
                        </span>
                        <a href="{{ route('crawlData') }}" class="btn btn-danger"><strong>CRAWL</strong></a>
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
                        @if (isset($crawls) && count($crawls) > 0)
                            @foreach ($crawls as $index => $crawl)
                                <tr class="text-center">
                                    <td>{{ ($crawls->currentPage() - 1) * $crawls->perPage() + $index + 1 }}</td>
                                    <td>{{ $crawl->task_done }}</td>
                                    <td>{{ $crawl->total_task - $crawl->task_done }}</td>
                                    <td>{{ $crawl->total_task }}</td>
                                    <td>{{ $crawl->started_date }}</td>
                                    <td>{{ $crawl->finished_date }}</td>
                                    <td>
                                        <a href="{{ route('downloadFile', ['file' => $crawl->file]) }}">Download file</a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="7">Data not found!</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                @if (isset($crawls) && count($crawls) > 0)
                    {!! $crawls->links() !!}
                @endif
            </div>
        </div>
    </div>
@endsection
