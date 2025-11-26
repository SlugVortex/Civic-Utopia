@extends('layouts/layoutMaster')

@section('title', 'Terms of Service')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="mb-0">Terms of Service</h4>
                    <small class="text-muted">Effective Date: November 2025</small>
                </div>
                <div class="card-body pt-4">
                    <h5>1. Acceptance of Terms</h5>
                    <p>By accessing and using CivicUtopia, you agree to comply with and be bound by these Terms of Service. If you do not agree, you may not use our services.</p>

                    <h5>2. Community Guidelines</h5>
                    <p>CivicUtopia is a space for constructive democratic dialogue. We have zero tolerance for:</p>
                    <ul>
                        <li>Hate speech, harassment, or bullying.</li>
                        <li>Incitement of violence or illegal acts.</li>
                        <li>Deliberate misinformation or disinformation.</li>
                    </ul>
                    <p>We utilize <strong>Azure Content Safety</strong> AI to automatically flag and hide content that violates these rules. Repeated violations will result in account suspension.</p>

                    <h5>3. AI Disclaimers</h5>
                    <p>Our platform uses advanced Artificial Intelligence to summarize laws, analyze candidate stances, and draft documents.</p>
                    <ul>
                        <li><strong>Not Legal Advice:</strong> AI summaries of bills and laws are for educational purposes only and do not constitute legal advice.</li>
                        <li><strong>Neutrality:</strong> While we strive for objective AI analysis, models can reflect biases present in training data. Always verify critical information with official sources.</li>
                    </ul>

                    <h5>4. User Content</h5>
                    <p>You retain ownership of the content you post. However, you grant CivicUtopia a license to display, translate, and distribute your content within the platform (e.g., showing your pothole report to other users).</p>

                    <h5>5. Limitation of Liability</h5>
                    <p>CivicUtopia is provided "as is." We are not liable for any actions taken by government officials based on reports submitted through our platform, nor for any inaccuracies in AI-generated text.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
