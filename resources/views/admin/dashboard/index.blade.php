@extends('layouts/layoutMaster')

@section('title', 'Admin Command Center')

{{-- Use ApexCharts (Matches your theme) --}}
@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Admin /</span> Command Center
        </h4>
        <span class="badge bg-label-primary">{{ now()->format('l, F j, Y') }}</span>
    </div>

    <!-- AI Insights Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-white text-primary"><i class="ri-robot-2-line"></i></span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title text-white mb-1">AI Community Analysis</h5>
                            <p class="mb-2 opacity-75">Automated insights based on the last 50 user posts.</p>

                            <div class="d-flex gap-4 mt-3 flex-wrap">
                                <div class="bg-white bg-opacity-25 rounded p-2 px-3">
                                    <small class="d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Sentiment</small>
                                    <span class="fs-5">{{ $aiAnalysis['sentiment'] ?? 'Analyzing...' }}</span>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded p-2 px-3 flex-grow-1">
                                    <small class="d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Top Concerns</small>
                                    @if(isset($aiAnalysis['concerns']) && is_array($aiAnalysis['concerns']))
                                        @foreach($aiAnalysis['concerns'] as $concern)
                                            <span class="badge bg-white text-primary me-1">{{ $concern }}</span>
                                        @endforeach
                                    @else
                                        <span>No sufficient data available yet.</span>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-3 mb-0 fst-italic">"{{ $aiAnalysis['summary'] ?? 'System is gathering data...' }}"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Users</h6>
                        <h3 class="mb-0">{{ $totalUsers }}</h3>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success"><i class="ri-user-smile-line"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Posts</h6>
                        <h3 class="mb-0">{{ $totalPosts }}</h3>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-info"><i class="ri-article-line"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Posts Today</h6>
                        <h3 class="mb-0">{{ $postsToday }}</h3>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning"><i class="ri-time-line"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active Polls</h6>
                        <h3 class="mb-0">{{ $activePolls->count() }}</h3>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger"><i class="ri-bar-chart-box-line"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Engagement Activity (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    {{-- ApexChart Container --}}
                    <div id="activityChart"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Topic Distribution</h5>
                </div>
                <div class="card-body">
                    {{-- ApexChart Container --}}
                    <div id="topicChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- POLLS MANAGEMENT -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Community Polls</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPollModal">
                        <i class="ri-add-line me-1"></i> New Poll
                    </button>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($activePolls as $poll)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-truncate" style="max-width: 70%;">{{ $poll->question }}</h6>
                                <span class="badge bg-label-info">Expires: {{ $poll->expires_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-2">
                                @foreach($poll->options as $option)
                                    @php
                                        $totalVotes = $poll->votes->count();
                                        $optionVotes = $option->votes->count();
                                        $percent = $totalVotes > 0 ? round(($optionVotes / $totalVotes) * 100) : 0;
                                    @endphp
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $option->label }}</span>
                                            <span>{{ $optionVotes }} votes ({{ $percent }}%)</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="ri-bar-chart-2-line ri-3x mb-2"></i>
                            <p>No active polls. Create one to engage users.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SUGGESTIONS MANAGEMENT -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pending User Suggestions</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Suggestion</th>
                                <th class="text-center">Votes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSuggestions as $suggestion)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs me-2">
                                            <img src="{{ $suggestion->user->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" class="rounded-circle">
                                        </div>
                                        <small>{{ $suggestion->user->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold d-block text-truncate" style="max-width: 150px;">{{ $suggestion->title }}</span>
                                    <small class="text-muted text-truncate d-block" style="max-width: 150px;">{{ $suggestion->description }}</small>
                                </td>
                                <td class="text-center"><span class="badge bg-label-secondary">{{ $suggestion->votes->count() }}</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- APPROVE --}}
                                        <form action="{{ route('admin.suggestions.update', $suggestion->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-icon btn-success" title="Approve/Show"><i class="ri-check-line"></i></button>
                                        </form>

                                        {{-- REJECT (Hide) --}}
                                        <form action="{{ route('admin.suggestions.update', $suggestion->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-icon btn-warning" title="Reject/Hide"><i class="ri-close-line"></i></button>
                                        </form>

                                        {{-- DELETE (Permanently Remove) --}}
                                        <form action="{{ route('admin.suggestions.destroy', $suggestion->id) }}" method="POST" onsubmit="return confirm('Delete this suggestion permanently?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-icon btn-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="ri-lightbulb-line ri-3x mb-2"></i>
                                    <p>No pending suggestions.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Poll Modal -->
<div class="modal fade" id="createPollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.polls.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Create New Poll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" name="question" class="form-control" placeholder="e.g. Should we renovate the town square?" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Options</label>
                    <div id="poll-options-container">
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 1" required>
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 2" required>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="addPollOption()">+ Add Another Option</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Launch Poll</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dynamic Poll Options
    window.addPollOption = function() {
        const container = document.getElementById('poll-options-container');
        const count = container.querySelectorAll('input').length + 1;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'options[]';
        input.className = 'form-control mb-2 animate__animated animate__fadeIn';
        input.placeholder = 'Option ' + count;
        container.appendChild(input);
        input.focus();
    }

    // 2. ApexCharts Implementation (Fixes Blank Charts)
    const cardColor = '#fff';
    const headingColor = '#566a7f';
    const labelColor = '#a1acb8';
    const borderColor = '#eceef1';

    // Activity Chart (Line)
    const activityChartEl = document.querySelector('#activityChart');
    if(activityChartEl) {
        const activityConfig = {
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            series: [{
                name: 'New Posts',
                data: {!! json_encode($chartData['data']) !!}
            }],
            xaxis: {
                categories: {!! json_encode($chartData['labels']) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: labelColor, fontSize: '13px' } }
            },
            yaxis: {
                labels: { style: { colors: labelColor, fontSize: '13px' } }
            },
            grid: { borderColor: borderColor },
            colors: ['#696cff'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] } }
        };
        new ApexCharts(activityChartEl, activityConfig).render();
    }

    // Topic Chart (Donut)
    const topicChartEl = document.querySelector('#topicChart');
    if(topicChartEl) {
        const topicData = {!! json_encode($topicDistribution) !!};
        const topicConfig = {
            chart: { height: 300, type: 'donut' },
            labels: topicData.map(t => t.name),
            series: topicData.map(t => t.count),
            colors: ['#696cff', '#71dd37', '#03c3ec', '#8592a3', '#ff3e1d'],
            stroke: { show: false, curve: 'straight' },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return parseInt(val) + '%'; }
            },
            legend: {
                show: true,
                position: 'bottom',
                markers: { offsetX: -3 },
                itemMargin: { vertical: 3, horizontal: 10 }
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            name: { fontSize: '1.5rem', fontFamily: 'Public Sans' },
                            value: { fontSize: '1rem', fontFamily: 'Public Sans', color: headingColor },
                            total: {
                                show: true,
                                fontSize: '0.9rem',
                                color: labelColor,
                                label: 'Topics',
                                formatter: function (w) { return topicData.length; }
                            }
                        }
                    }
                }
            }
        };
        new ApexCharts(topicChartEl, topicConfig).render();
    }
});
</script>
@endsection
