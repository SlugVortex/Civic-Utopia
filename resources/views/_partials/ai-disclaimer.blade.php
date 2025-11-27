{{-- AI Disclaimer Alert --}}
<div class="alert alert-dismissible fade show d-flex align-items-start p-3 mt-3 border rounded"
     role="alert"
     style="background-color: var(--bs-warning-bg-subtle, #fff3cd);
            border-color: var(--bs-warning-border-subtle, #ffe69c) !important;
            color: var(--bs-emphasis-color, #212529);">
    <i class="ri-sparkling-line me-2 mt-1 fs-4" style="color: var(--bs-warning-text-emphasis, #997404); flex-shrink: 0;"></i>
    <div class="flex-grow-1">
        <strong class="d-block mb-1">AI-Assisted Content</strong>
        <small class="d-block" style="line-height: 1.5;">
            Some of this content was generated using Azure OpenAI to analyze public documents and data.
            While AI strives for accuracy, it may occasionally produce incomplete or biased information.
            <strong>Always verify critical information with official sources.</strong>
            <a href="javascript:void(0);"
               class="fw-semibold text-decoration-underline"
               style="color: var(--bs-warning-text-emphasis, #997404);"
               data-bs-toggle="modal"
               data-bs-target="#aiTransparencyModal">
                Learn about our responsible AI approach
            </a>
        </small>
    </div>
    <button type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
            style="flex-shrink: 0;"></button>
</div>

{{-- AI Transparency & Ethics Modal --}}
<div class="modal fade" id="aiTransparencyModal" tabindex="-1" aria-labelledby="aiTransparencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--bs-primary-bg-subtle, #cfe2ff); border-bottom: 1px solid var(--bs-border-color, #dee2e6);">
                <h5 class="modal-title d-flex align-items-center" id="aiTransparencyModalLabel">
                    <i class="ri-robot-2-line me-2" style="color: var(--bs-primary-text-emphasis, #084298);"></i>
                    Our Responsible AI Framework
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="lead mb-4">
                    CivicUtopia leverages <strong>Azure AI services</strong> with built-in safety and transparency mechanisms to provide reliable, ethical assistance.
                </p>

                <div class="row g-3">
                    {{-- Grounding --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start p-3 rounded" style="background-color: var(--bs-light-bg-subtle, #f8f9fa);">
                            <div class="me-3" style="color: var(--bs-primary-text-emphasis, #084298);">
                                <i class="ri-search-eye-line fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-2">
                                    <i class="ri-checkbox-circle-line text-primary me-1"></i>
                                    Fact-Based Grounding
                                </h6>
                                <p class="mb-0 small text-body-secondary">
                                    Our AI doesn't speculate. It retrieves information from <strong>Bing Search</strong>,
                                    <strong>Azure AI Search</strong>, and indexed official documents to ensure responses
                                    are grounded in verifiable sources.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Safety Filters --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start p-3 rounded" style="background-color: var(--bs-light-bg-subtle, #f8f9fa);">
                            <div class="me-3" style="color: var(--bs-success-text-emphasis, #0a3622);">
                                <i class="ri-shield-check-line fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-2">
                                    <i class="ri-checkbox-circle-line text-success me-1"></i>
                                    Content Safety & Moderation
                                </h6>
                                <p class="mb-0 small text-body-secondary">
                                    Every request and response passes through <strong>Azure AI Content Safety</strong>
                                    filters to detect and prevent harmful content including hate speech, violence,
                                    self-harm, and misinformation.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Model Info --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start p-3 rounded" style="background-color: var(--bs-light-bg-subtle, #f8f9fa);">
                            <div class="me-3" style="color: var(--bs-info-text-emphasis, #055160);">
                                <i class="ri-brain-line fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-2">
                                    <i class="ri-checkbox-circle-line text-info me-1"></i>
                                    Advanced Language Models
                                </h6>
                                <p class="mb-0 small text-body-secondary">
                                    Powered by <strong>Azure OpenAI Service (GPT-4o)</strong> for sophisticated
                                    reasoning, analysis, and natural language understanding while maintaining
                                    enterprise-grade security and compliance.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Transparency --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start p-3 rounded" style="background-color: var(--bs-light-bg-subtle, #f8f9fa);">
                            <div class="me-3" style="color: var(--bs-secondary-text-emphasis, #2b2f32);">
                                <i class="ri-eye-line fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-2">
                                    <i class="ri-checkbox-circle-line text-secondary me-1"></i>
                                    Transparency & Accountability
                                </h6>
                                <p class="mb-0 small text-body-secondary">
                                    We disclose when AI is used, cite sources when available, and acknowledge
                                    limitations. Our AI serves as a tool to <em>augment</em> your research—not
                                    replace your judgment.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Important Notice --}}
                <div class="alert alert-warning d-flex align-items-start mt-4 mb-0 border"
                     style="background-color: var(--bs-warning-bg-subtle, #fff3cd); border-color: var(--bs-warning-border-subtle, #ffe69c) !important;">
                    <i class="ri-alert-line me-2 mt-1 flex-shrink-0" style="color: var(--bs-warning-text-emphasis, #997404);"></i>
                    <div class="small">
                        <strong>Your Responsibility:</strong> AI is a powerful assistant, but you remain the decision-maker.
                        Cross-reference important information with official government sources, consult professionals
                        when needed, and exercise critical thinking.
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--bs-border-color, #dee2e6);">
                <a href="https://learn.microsoft.com/en-us/azure/ai-services/openai/concepts/content-filter"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-outline-secondary btn-sm me-auto">
                    <i class="ri-external-link-line me-1"></i>
                    Azure AI Documentation
                </a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    I Understand
                </button>
            </div>
        </div>
    </div>
</div>
