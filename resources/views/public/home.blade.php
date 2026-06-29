@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary/5 via-background to-secondary/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-32">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-on-surface tracking-tight">
                    Find Scholarships That
                    <span class="text-primary"> Match You</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-on-surface-variant max-w-2xl mx-auto">
                    ScholarMatch helps Malaysian students discover scholarships based on academic results and socioeconomic background.
                    Get explainable eligibility results — Eligible, Partially Eligible, or Not Suitable — with clear reasons for each match.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}"
                        class="w-full sm:w-auto btn btn-primary text-lg px-8 py-3 font-medium">
                        Get Started Free
                    </a>
                    <a href="#how-it-works"
                        class="w-full sm:w-auto btn btn-outline text-lg px-8 py-3 font-medium">
                        How It Works
                    </a>
                </div>
            </div>

            <!-- Trust indicators -->
            <div class="mt-16 flex flex-wrap items-center justify-center gap-8 text-sm text-on-surface-variant">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    20+ Scholarships
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    B40/M40/T20 Matching
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Explainable Results
                </span>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-on-surface">How It Works</h2>
                <p class="mt-4 text-on-surface-variant max-w-2xl mx-auto">
                    Three simple steps to find your best scholarship matches
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-primary">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-on-surface mb-2">Create Your Profile</h3>
                    <p class="text-on-surface-variant">
                        Enter your nationality, state, household income, dependents, institution type, and field of study.
                        We auto-classify your income category (B40/M40/T20).
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-primary">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-on-surface mb-2">Add Academic Results</h3>
                    <p class="text-on-surface-variant">
                        Input your SPM As/credits or CGPA based on your education level (SPM, STPM, Foundation, Matriculation, Diploma, Undergraduate).
                        Mark results as Official or Pending.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center p-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-primary">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-on-surface mb-2">View Matches</h3>
                    <p class="text-on-surface-variant">
                        Get instant recommendations with scores (0-100), status badges, and detailed explanations for every scholarship.
                        Save favorites for later review.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20 bg-background">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-on-surface">Why ScholarMatch?</h2>
                <p class="mt-4 text-on-surface-variant max-w-2xl mx-auto">
                    Built for Malaysian students with transparency at its core
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="card p-6">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-on-surface mb-2">Hard Filter First</h3>
                    <p class="text-on-surface-variant text-sm">Strict eligibility rules (nationality, income, study level, deadline) filter out unsuitable scholarships before scoring.</p>
                </div>

                <div class="card p-6">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-on-surface mb-2">Soft Scoring & Ranking</h3>
                    <p class="text-on-surface-variant text-sm">Academic (40), Field (25), Institution (20), Income (15) = 100 total. Transparent weighted scoring.</p>
                </div>

                <div class="card p-6">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-on-surface mb-2">Explainable Results</h3>
                    <p class="text-on-surface-variant text-sm">Every recommendation shows exactly why you qualify or don't — no black box algorithms.</p>
                </div>

                <div class="card p-6">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-on-surface mb-2">Admin Configurable</h3>
                    <p class="text-on-surface-variant text-sm">Scholarship rules, income thresholds, and seed data managed via admin panel — no code changes needed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to Find Your Scholarship?</h2>
            <p class="text-primary-100 mb-8 max-w-2xl mx-auto">Create your profile in minutes and get personalized, explainable scholarship recommendations.</p>
            <a href="{{ route('register') }}" class="btn btn-primary bg-white text-primary hover:bg-primary-50 text-lg px-8 py-3 font-medium">
                Start Free
            </a>
            <p class="mt-6 text-primary-200 text-sm">Already have an account? <a href="{{ route('login') }}" class="underline hover:text-white">Sign in</a></p>
        </div>
    </section>
</div>
@endsection