@extends('layouts.public')

@section('content')
@php
    $steps = [
        ['Complete Your Profile', 'Add your personal and household information to help us find relevant scholarships.'],
        ['Add Your Academic Results', 'Enter official results and the study level you want funded. You can decide your field and institution later.'],
        ['Explore Your Matches', 'Read eligibility explanations, review provider conditions and save scholarships that interest you.'],
    ];
    $features = [
        ['Personalised Matching', 'Find scholarships based on your academic results, income and intended study plans.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Know What to Check', 'See provider conditions and programme requirements that still need checking before you apply.', 'M9 12l2 2 4-4M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Clear Explanations', 'See which requirements are met, not met or not checked, with reasons for each result.', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Save for Later', 'Keep scholarships that interest you in one place and return when you are ready to review them.', 'M7 3h10a2 2 0 012 2v16l-7-4-7 4V5a2 2 0 012-2z'],
    ];
@endphp
<div class="bg-background">
    <section class="relative overflow-hidden bg-gradient-to-br from-primary/5 via-background to-secondary/5" aria-labelledby="home-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="text-center max-w-3xl mx-auto">
                <h1 id="home-heading" class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight text-on-surface tracking-tight">
                    Find Scholarships That <span class="text-primary">Match You</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl leading-relaxed sm:leading-relaxed text-on-surface-variant max-w-2xl mx-auto">
                    Discover scholarships based on your academic results, financial background and study plans.
                    Understand which requirements you meet and what you still need to check.
                </p>
                <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto btn btn-primary text-lg px-8 py-3 font-medium">Get Started Free</a>
                    <a href="#how-it-works" class="w-full sm:w-auto btn btn-outline text-lg px-8 py-3 font-medium">How It Works</a>
                </div>
            </div>
            <ul class="mt-12 mx-auto grid w-fit max-w-full gap-4 text-sm leading-relaxed text-on-surface-variant sm:flex sm:flex-wrap sm:justify-center sm:gap-x-8">
                @foreach(['Academic & Income Matching', 'Clear Eligibility Explanations', 'Save Scholarships for Later'] as $benefit)
                    <li class="flex items-start gap-2 text-left">
                        <svg class="mt-0.5 w-4 h-4 shrink-0 text-success" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>{{ $benefit }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section id="how-it-works" class="py-16 sm:py-20 bg-white" aria-labelledby="steps-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12">
                <h2 id="steps-heading" class="text-3xl sm:text-4xl font-bold leading-tight text-on-surface">How It Works</h2>
                <p class="mt-4 leading-relaxed text-on-surface-variant max-w-2xl mx-auto">Three steps to explore scholarships that fit your background and plans.</p>
            </div>
            <ol class="grid md:grid-cols-3 gap-6 lg:gap-10">
                @foreach($steps as [$title, $description])
                    <li class="p-6 max-w-sm w-full mx-auto text-center">
                        <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6" aria-hidden="true">
                            <span class="text-2xl font-bold text-primary">{{ $loop->iteration }}</span>
                        </div>
                        <h3 class="text-xl font-semibold leading-snug text-on-surface mb-3 md:min-h-14 xl:min-h-0">{{ $title }}</h3>
                        <p class="max-w-sm mx-auto leading-relaxed text-on-surface-variant">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-background" aria-labelledby="features-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12">
                <h2 id="features-heading" class="text-3xl sm:text-4xl font-bold leading-tight text-on-surface">Why ScholarMatch?</h2>
                <p class="mt-4 leading-relaxed text-on-surface-variant max-w-2xl mx-auto">Clear information to help you take your next step.</p>
            </div>
            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach($features as [$title, $description, $icon])
                    <div class="card p-6 text-center">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mx-auto mb-5">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold leading-snug text-on-surface mb-3">{{ $title }}</h3>
                        <p class="leading-relaxed text-on-surface-variant">{{ $description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-primary" aria-labelledby="get-started-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 id="get-started-heading" class="text-3xl sm:text-4xl font-bold leading-tight text-white mb-4">Ready to Explore Your Matches?</h2>
            <p class="text-white/90 leading-relaxed mb-8 max-w-xl mx-auto">Create an account, add your information and explore scholarship matches with clear explanations.</p>
            <a href="{{ route('register') }}" class="btn btn-primary bg-white text-primary hover:bg-primary-50 text-lg px-8 py-3 font-medium">Start Free</a>
            <p class="mt-6 text-white/90 text-sm leading-relaxed">Already have an account? <a href="{{ route('login') }}" class="underline hover:text-white">Sign in</a></p>
        </div>
    </section>
</div>
@endsection
