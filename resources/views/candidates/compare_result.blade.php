@extends('layouts/layoutMaster')

@section('title', 'Comparison Result')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Head-to-Head /</span> Analysis
        </h4>
        <a href="{{ route('candidates.compare.select') }}" class="btn btn-label-secondary">Start New Comparison</a>
    </div>

    <!-- The Fighters -->
    <div class="row mb-4">
        <div class="col-md-5 text-center">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <h3 class="text-primary">{{ $c1->name }}</h3>
                    <span class="badge bg-label-primary fs-6">{{ $c1->party }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <div class="bg-label-secondary rounded-circle p-3 fw-bold fs-4">VS</div>
        </div>
        <div class="col-md-5 text-center">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <h3 class="text-warning">{{ $c2->name }}</h3>
                    <span class="badge bg-label-warning fs-6">{{ $c2->party }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Verdict -->
    <div class="card mb-4 bg-primary text-white">
        <div class="card-body text-center">
            <h5 class="text-white mb-2"><i class="ti ti-gavel me-2"></i>AI Verdict: The Core Difference</h5>
            <p class="fs-5 mb-0 fst-italic">"{{ $aiData['verdict_summary'] }}"</p>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="20%">Issue</th>
                        <th width="35%" class="text-primary">{{ $c1->name }}</th>
                        <th width="35%" class="text-warning">{{ $c2->name }}</th>
                        <th width="10%">Context</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($aiData['comparison_table'] as $row)
                    <tr>
                        <td><strong>{{ $row['topic'] }}</strong></td>
                        <td>
                            <span class="text-wrap d-block" style="max-width: 300px;">
                                {{ $row['candidate_a_stance'] }}
                            </span>
                        </td>
                        <td>
                            <span class="text-wrap d-block" style="max-width: 300px;">
                                {{ $row['candidate_b_stance'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary" data-bs-toggle="tooltip" title="{{ $row['winner_context'] }}">
                                <i class="ti ti-info-circle"></i> Insight
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
