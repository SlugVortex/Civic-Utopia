@extends('layouts/layoutMaster')

@section('title', 'Admin Command Center')


@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Admin /</span> Command Center
        </h4>
        <span class="badge bg-label-primary">{{ now()->format('l, F j, Y') }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <!-- AI Insights Row - FINAL FIX -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <!-- Icon -->
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ri-robot-2-line ri-26px"></i>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow-1" style="min-width: 0;">
                        <!-- Header -->
                        <div class="mb-3">
                            <h5 class="card-title mb-1 d-flex align-items-center" style="font-size: 1.1rem;">
                                <i class="ri-sparkle-line me-2" style="font-size: 1.15rem;"></i>
                                AI Community Analysis
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Automated insights based on recent user activity</p>
                        </div>

                        <!-- Metrics Row -->
                        <div class="row g-2 mb-3">
                            <!-- Sentiment Card -->
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="card bg-label-primary mb-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Sentiment</small>
                                            <i class="ri-emotion-line text-primary" style="font-size: 1.1rem; opacity: 0.4;"></i>
                                        </div>
                                        <h3 class="mb-0 fw-bold" style="font-size: 1.4rem;">{{ $aiAnalysis['sentiment'] ?? 'N/A' }}</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Concerns Card -->
                            <div class="col-12 col-sm-6 col-md-9">
                                <div class="card bg-label-info mb-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Top Concerns</small>
                                            <i class="ri-alert-line text-info flex-shrink-0 ms-2" style="font-size: 1.1rem; opacity: 0.4;"></i>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2" style="overflow: hidden;">
                                            @if(isset($aiAnalysis['concerns']) && is_array($aiAnalysis['concerns']))
                                                @foreach($aiAnalysis['concerns'] as $concern)
                                                    <span class="badge bg-info" style="font-size: 0.7rem; padding: 0.35rem 0.6rem; font-weight: 500; line-height: 1.4;">{{ $concern }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted" style="font-size: 0.85rem;">Gathering data...</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Quote -->
                        <div class="alert alert-primary mb-0 p-3" role="alert" style="background-color: rgba(105, 108, 255, 0.08); border: 1px solid rgba(105, 108, 255, 0.2); border-radius: 0.375rem;">
                            <div class="d-flex gap-3">
                                <i class="ri-information-line flex-shrink-0 text-primary" style="font-size: 1.2rem; margin-top: 2px; opacity: 0.8;"></i>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <p class="mb-0 fst-italic" style="font-size: 0.875rem; line-height: 1.6; color: #566a7f; word-wrap: break-word;">
                                        "{{ $aiAnalysis['summary'] ?? 'System is gathering data to provide intelligent insights about your community...' }}"
                                    </p>
                                </div>
                            </div>
                        </div>
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
                    <h5 class="card-title mb-0">Active Community Polls</h5>
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
                                        $totalVotes = $poll->votes ? $poll->votes->count() : 0;
                                        $optionVotes = $option->votes ? $option->votes->count() : 0;
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
                            <p>No active polls.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SUGGESTIONS MANAGEMENT -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pending Suggestions</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Suggestion</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSuggestions as $suggestion)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <small class="fw-bold">{{ $suggestion->user->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block text-truncate" style="max-width: 200px;" title="{{ $suggestion->title }}">{{ $suggestion->title }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.suggestions.update', $suggestion->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-icon btn-success" title="Approve"><i class="ri-check-line"></i></button>
                                        </form>

                                        <form action="{{ route('admin.suggestions.update', $suggestion->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-icon btn-warning" title="Reject"><i class="ri-close-line"></i></button>
                                        </form>

                                        <form action="{{ route('admin.suggestions.destroy', $suggestion->id) }}" method="POST" onsubmit="return confirm('Delete permanently?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-icon btn-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <p class="mb-0">No pending suggestions.</p>
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
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="addPollOption()">+ Add Option</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Launch Poll</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Poll Option Logic
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

    // ApexCharts Logic
    const cardColor = '#fff';
    const headingColor = '#566a7f';
    const labelColor = '#a1acb8';
    const borderColor = '#eceef1';

    // Activity Chart - WITH DEBUGGING
    const activityEl = document.querySelector('#activityChart');
    const activityData = @json($chartData);

    console.log('Activity Element:', activityEl);
    console.log('Activity Data:', activityData);
    console.log('Activity Data Structure:', {
        hasData: activityData?.data,
        dataLength: activityData?.data?.length,
        labels: activityData?.labels
    });

    if(activityEl) {
        if(activityData && activityData.data && activityData.data.length > 0) {
            console.log('✅ Rendering Activity Chart');
            const config = {
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: { show: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                series: [{ name: 'Posts', data: activityData.data }],
                xaxis: {
                    categories: activityData.labels,
                    labels: { style: { colors: labelColor } }
                },
                colors: ['#696cff'],
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.7, opacityTo: 0.2 }
                }
            };

            try {
                new ApexCharts(activityEl, config).render();
                console.log('✅ Activity Chart rendered successfully');
            } catch(e) {
                console.error('❌ Error rendering Activity Chart:', e);
            }
        } else {
            console.log('⚠️ No activity data, showing placeholder');
            activityEl.innerHTML = '<p class="text-center text-muted py-5">Not enough data to display chart.</p>';
        }
    } else {
        console.error('❌ Activity chart element not found!');
    }

    // Topic Chart - WITH DEBUGGING
    const topicEl = document.querySelector('#topicChart');
    const topicData = @json($topicDistribution);

    console.log('Topic Element:', topicEl);
    console.log('Topic Data:', topicData);

    if(topicEl) {
        if(topicData && topicData.length > 0) {
            console.log('✅ Rendering Topic Chart');
            const config = {
                chart: { height: 300, type: 'donut' },
                labels: topicData.map(t => t.name),
                series: topicData.map(t => t.count),
                colors: ['#696cff', '#71dd37', '#03c3ec', '#8592a3', '#ff3e1d'],
                legend: { position: 'bottom' }
            };

            try {
                new ApexCharts(topicEl, config).render();
                console.log('✅ Topic Chart rendered successfully');
            } catch(e) {
                console.error('❌ Error rendering Topic Chart:', e);
            }
        } else {
            console.log('⚠️ No topic data, showing placeholder');
            topicEl.innerHTML = '<p class="text-center text-muted py-5">No topics created yet.</p>';
        }
    } else {
        console.error('❌ Topic chart element not found!');
    }
});
</script>
@endpush
@endsection

