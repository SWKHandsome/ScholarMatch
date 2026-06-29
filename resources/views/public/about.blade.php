@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <header class="mb-12">
            <h1 class="text-4xl sm:text-5xl font-bold text-on-surface">About ScholarMatch</h1>
            <p class="mt-4 text-lg text-on-surface-variant">
                A Result-Based and Socioeconomic Scholarship Recommendation Platform for Malaysian Students
            </p>
        </header>

        <div class="space-y-12">
            <!-- Problem Statement -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">The Problem</h2>
                <div class="prose prose-slate max-w-none text-on-surface-variant">
                    <p>Malaysian students often struggle to find suitable scholarships because scholarship information is scattered across government portals, university websites, private institutions, foundations, and scholarship listing platforms.</p>
                    <p>Many scholarships contain multiple eligibility requirements, such as nationality, academic results, household income, income category, study level, field of study, institution type, and application deadline. Students may not know whether they qualify because comparing these requirements manually is time-consuming and confusing.</p>
                    <p>This issue is especially important for lower-income students, such as B40 students, who may depend on financial support to continue higher education. ScholarMatch aims to reduce this problem by providing a centralized and explainable scholarship matching system.</p>
                </div>
            </section>

            <!-- Solution -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Our Solution</h2>
                <div class="prose prose-slate max-w-none text-on-surface-variant">
                    <p>ScholarMatch is a web-based scholarship recommendation platform that helps Malaysian students identify scholarships matching their academic results and socioeconomic background.</p>
                    <p>The system is not only a scholarship listing website. It is an <strong>explainable eligibility-matching platform</strong> that compares student profiles with scholarship rules and classifies each scholarship as:</p>
                    <ul class="list-disc list-inside mt-4 space-y-2">
                        <li><strong>Eligible</strong> — Passes all hard rules, scores 80–100</li>
                        <li><strong>Partially Eligible</strong> — Passes all hard rules, scores 50–79</li>
                        <li><strong>Not Suitable</strong> — Fails any hard rule, or scores 0–49</li>
                    </ul>
                    <p>Each recommendation includes detailed explanation messages so students can understand exactly why they qualify or do not qualify.</p>
                </div>
            </section>

            <!-- Methodology -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Recommendation Methodology</h2>
                <div class="prose prose-slate max-w-none text-on-surface-variant">
                    <h3 class="text-lg font-medium text-on-surface mb-2">Phase 1: Hard Eligibility Filtering</h3>
                    <p>The system first checks strict scholarship requirements. If a student fails any hard rule, the scholarship is classified as <strong>Not Suitable</strong>, regardless of score.</p>
                    <p>Hard rules may include:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Nationality</li>
                        <li>Required income category (B40/M40/T20)</li>
                        <li>Maximum household income</li>
                        <li>Required study level</li>
                        <li>Application deadline validity</li>
                        <li>Required field of study (if marked hard)</li>
                        <li>Required institution type (if marked hard)</li>
                    </ul>

                    <h3 class="text-lg font-medium text-on-surface mb-2 mt-6">Phase 2: Soft Scoring and Ranking</h3>
                    <p>Soft scoring only runs after the student passes all hard rules.</p>
                    <table class="w-full mt-4 border-collapse">
                        <thead>
                            <tr class="border-b border-outline-variant">
                                <th class="text-left p-2 font-medium text-on-surface">Criterion</th>
                                <th class="text-left p-2 font-medium text-on-surface">Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-outline-variant/50">
                                <td class="p-2 text-on-surface-variant">Academic result match</td>
                                <td class="p-2 font-medium text-on-surface">40 points</td>
                            </tr>
                            <tr class="border-b border-outline-variant/50">
                                <td class="p-2 text-on-surface-variant">Field of study match</td>
                                <td class="p-2 font-medium text-on-surface">25 points</td>
                            </tr>
                            <tr class="border-b border-outline-variant/50">
                                <td class="p-2 text-on-surface-variant">Institution type match</td>
                                <td class="p-2 font-medium text-on-surface">20 points</td>
                            </tr>
                            <tr class="border-b border-outline-variant/50">
                                <td class="p-2 text-on-surface-variant">Income priority match</td>
                                <td class="p-2 font-medium text-on-surface">15 points</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-on-surface">Total</td>
                                <td class="p-2 font-bold text-on-surface">100 points</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-4">Classification after hard filters are passed:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li><strong>80–100:</strong> Eligible</li>
                        <li><strong>50–79:</strong> Partially Eligible</li>
                        <li><strong>0–49:</strong> Not Suitable</li>
                    </ul>
                    <p class="mt-2">Results are sorted from highest score to lowest score.</p>
                </div>
            </section>

            <!-- Tech Stack -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Technology Stack</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="card p-4">
                        <h3 class="font-medium text-on-surface mb-2">Backend</h3>
                        <ul class="list-disc list-inside text-on-surface-variant space-y-1">
                            <li>Laravel 11 (PHP 8.2+)</li>
                            <li>MySQL / SQLite</li>
                            <li>Eloquent ORM</li>
                            <li>Laravel Breeze (Auth)</li>
                            <li>Pest PHP (Testing)</li>
                        </ul>
                    </div>
                    <div class="card p-4">
                        <h3 class="font-medium text-on-surface mb-2">Frontend</h3>
                        <ul class="list-disc list-inside text-on-surface-variant space-y-1">
                            <li>Blade Templates</li>
                            <li>Tailwind CSS</li>
                            <li>Vite (Build)</li>
                            <li>Inter Font</li>
                            <i>Vanilla JS (Minimal)</i>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Project Info -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Project Information</h2>
                <dl class="space-y-3 text-on-surface-variant">
                    <div class="flex justify-between">
                        <dt class="font-medium text-on-surface">Project Type</dt>
                        <dd>Final Year Project (FYP) Web Application</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-on-surface">Target Users</dt>
                        <dd>Malaysian students seeking scholarships</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-on-surface">Version</dt>
                        <dd>2.0</dd>
                    </div>
                </dl>
            </section>

            <!-- Future Potential -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Future Business Potential</h2>
                <div class="prose prose-slate max-w-none text-on-surface-variant">
                    <p>ScholarMatch can remain free for students while exploring future sustainability through:</p>
                    <ul class="list-disc list-inside mt-4 space-y-2">
                        <li>Premium scholarship listings for providers</li>
                        <li>Lead generation for private universities</li>
                        <li>Sponsored scholarship campaigns</li>
                        <li>Analytics dashboards for institutions</li>
                        <li>Application preparation services</li>
                    </ul>
                    <p class="mt-4">These are future possibilities and are not part of the MVP.</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection