@extends('layouts/layoutMaster')

@section('title', 'Privacy Policy')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="mb-0">Privacy Policy</h4>
                    <small class="text-muted">Last Updated: November 2025</small>
                </div>
                <div class="card-body pt-4">
                    <h5>1. Introduction</h5>
                    <p>Welcome to CivicUtopia ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, and share your information when you use our AI-powered civic engagement platform.</p>

                    <h5>2. Information We Collect</h5>
                    <ul>
                        <li><strong>Personal Account Data:</strong> Name, email address, and profile picture.</li>
                        <li><strong>Civic Contributions:</strong> Posts, comments, ballot annotations, and issue reports you create.</li>
                        <li><strong>Voice Data:</strong> If you use our Voice Navigation or "Read Aloud" features, your audio is processed temporarily by Azure Speech Services to transcribe your intent. We do not store raw audio recordings permanently.</li>
                        <li><strong>Location Data:</strong> If you use "Civic Lens" or "Local News," we process your GPS coordinates to provide localized content. This data is used instantly and not tracked historically.</li>
                    </ul>

                    <h5>3. How We Use AI</h5>
                    <p>CivicUtopia utilizes Artificial Intelligence (Azure OpenAI, Azure Vision) to enhance your experience. By using this platform, you acknowledge that:</p>
                    <ul>
                        <li>Your inputs (text and images) may be processed by AI models to generate summaries, translations, or analyses.</li>
                        <li>AI-generated content should be verified, as models can occasionally produce inaccuracies ("hallucinations").</li>
                    </ul>

                    <h5>4. Data Sharing</h5>
                    <p>We do not sell your personal data. We share data only with:</p>
                    <ul>
                        <li><strong>Microsoft Azure:</strong> As our cloud and AI provider, strictly for processing requests.</li>
                        <li><strong>Government Agencies:</strong> Only when you explicitly submit a formal "Civic Lens" report (e.g., emailing a pothole report to the NWA).</li>
                    </ul>

                    <h5>5. Contact Us</h5>
                    <p>If you have questions about this policy, please contact our Data Protection Officer at privacy@civicutopia.ai.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
