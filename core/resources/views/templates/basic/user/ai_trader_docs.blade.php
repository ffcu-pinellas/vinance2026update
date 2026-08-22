@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-robot me-2"></i> AI Trader Documentation
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Getting Started -->
                    <section class="mb-5">
                        <h2 class="h4 border-bottom pb-2">
                            <i class="fas fa-play-circle text-primary me-2"></i> Getting Started
                        </h2>
                        <div class="ms-4">
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px">1</span>
                                </div>
                                <div>
                                    <h5 class="h6">Connect Your Telegram</h5>
                                    <p class="mb-0">Click the "Connect Telegram" button in your dashboard and start a chat with our bot.</p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px">2</span>
                                </div>
                                <div>
                                    <h5 class="h6">Activate Trading</h5>
                                    <p class="mb-0">Send <code>/start</code> to the bot and follow the activation steps.</p>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px">3</span>
                                </div>
                                <div>
                                    <h5 class="h6">Monitor Trades</h5>
                                    <p class="mb-0">View your active trades and history in the dashboard.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FAQ Section -->
                    <section class="mb-5">
                        <h2 class="h4 border-bottom pb-2">
                            <i class="fas fa-question-circle text-primary me-2"></i> Frequently Asked Questions
                        </h2>
                        <div class="accordion" id="faqAccordion">
                            <!-- FAQ Item 1 -->
                            <div class="accordion-item border-0 mb-2">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        How do I know if my bot is active?
                                    </button>
                                </h3>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>When successfully connected, your dashboard will show a green "Connected" status indicator. You'll also receive a confirmation message on Telegram.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 2 -->
                            <div class="accordion-item border-0 mb-2">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Can I change trading strategies?
                                    </button>
                                </h3>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Currently, the AI Trader uses our optimized default strategy. Future updates will allow strategy customization.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 3 -->
                            <div class="accordion-item border-0">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        How are profits calculated?
                                    </button>
                                </h3>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Profits are calculated based on the difference between entry and exit prices, minus any trading fees. You can view detailed breakdowns in your trade history.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Support -->
                    <section>
                        <h2 class="h4 border-bottom pb-2">
                            <i class="fas fa-headset text-primary me-2"></i> Need More Help?
                        </h2>
                        <p>Contact our support team for assistance:</p>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <a href="mailto:support@vinance.pro">support@vinance.pro</a>
                            </li>
                            <li>
                                <i class="fab fa-telegram me-2 text-muted"></i>
                                <a href="https://t.me/VinanceSupport" target="_blank">@VinanceSupport</a>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
        
        <!-- Quick Links Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i> Quick Links
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item border-0 px-0">
                            <a href="{{ route('user.ai.trader') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-circle-right text-primary me-2"></i> Back to AI Trader
                            </a>
                        </li>
                        <li class="list-group-item border-0 px-0">
                            <a href="{{ route('ticket.index') }}" class="text-decoration-none">
                                <i class="fas fa-ticket-alt text-primary me-2"></i> Open Support Ticket
                            </a>
                        </li>
                        <li class="list-group-item border-0 px-0">
                            <a href="https://t.me/Vinance_AI_TraderBot" target="_blank" class="text-decoration-none">
                                <i class="fab fa-telegram text-primary me-2"></i> Telegram Bot
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Video Tutorial -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-video me-2"></i> Video Tutorial
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="https://www.youtube.com/embed/YOUR_VIDEO_ID" title="AI Trader Tutorial" allowfullscreen></iframe>
                    </div>
                    <a href="https://youtu.be/YOUR_VIDEO_ID" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fab fa-youtube me-2"></i> Watch on YouTube
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(78, 115, 223, 0.1);
        color: #4e73df;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
</style>
@endpush