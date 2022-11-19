@extends('layouts.app')

@section('content')
    <div class="">

        <div id="home-status"></div>

        <div class="card">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-4">
                        <span class="h5 font-bold"><strong>Crawl History</strong></span>
                    </div>
                    <div class="col-8 col-reserver text-right">
                        <span class="mr-2 inline-block">Lastest:
                            @if (isset($lastCrawl->finished_date))
                                @if ($lastCrawl->file)
                                    <a href="{{ route('downloadFile', ['file' => $lastCrawl->file]) }}">
                                        {{ $lastCrawl->finished_date }}
                                    </a>
                                @else
                                    {{ $lastCrawl->finished_date }}
                                @endif
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
                            <th class="col-2">Export file</th>
                            <th class="col-2">Error file</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if (isset($crawls) && count($crawls) > 0)
                            @foreach ($crawls as $index => $crawl)
                                <tr class="text-center">
                                    <td>{{ ($crawls->currentPage() - 1) * $crawls->perPage() + $index + 1 }}</td>
                                    <td>{{ $crawl->task_done }}</td>
                                    <td>{{ $crawl->task_fail }}</td>
                                    <td>{{ $crawl->total_task }}</td>
                                    <td>{{ $crawl->started_date }}</td>
                                    <td>{{ $crawl->finished_date }}</td>
                                    <td>
                                        @if ($crawl->file)
                                            <a href="{{ route('downloadFile', ['file' => $crawl->file]) }}">
                                                Download
                                            </a>
                                        @else
                                            Empty
                                        @endif
                                    </td>
                                    <td>
                                        @if ($crawl->file_error)
                                            <a href="{{ route('downloadFile', ['file' => $crawl->file_error]) }}">
                                                Download
                                            </a>
                                        @else
                                            Emty
                                        @endif
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

@section('script-footer')
    @if ($bus && !$bus->finishedAt)
        <script>
            getStatus();
            const handleProcess = setInterval(() => getStatus(), 2500);

            function getStatus() {
                $.ajax({
                    type: 'get',
                    url: '{{ route('getStatus') }}',
                    success: function(data) {
                        const job = data.data;
                        if (job) {
                            const process = job.progress;
                            let processHtml = "";
                            if (process === 100) {
                                clearInterval(handleProcess);
                                processHtml = `
                                    <div class="alert alert-primary alert-dismissible fade show mb-4" role="alert">
                                        Crawl data completed
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    `
                                setTimeout(() => location.reload(), 500);
                            } else {
                                processHtml = `<div class="card p-3 mb-4">
                                    <div>
                                        <div class="d-flex justify-content-between">
                                            <div>${job.name}</div>
                                            <div>Total jobs: ${job.processedJobs}/${job.totalJobs}</div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: ${process}%;" aria-valuenow="${process}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                ${process}%
                                            </div>
                                        </div>
                                     </div>
                                </div>`;
                            }

                            $('#home-status').html(processHtml);
                        }
                    }
                });
            }
        </script>
    @endif
@endsection
