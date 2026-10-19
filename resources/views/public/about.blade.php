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
                <h2 class="text-2xl font-semibold mb-4">Our Solution</h2>
                <p class="text-on-surface-variant">ScholarMatch compares official academic results and student profiles with administrator-defined scholarship requirements.</p>
                <ul class="list-disc list-inside mt-4 space-y-2">
                    <li><strong>Eligible</strong> — All applicable automatic requirements are met.</li>
                    <li><strong>Eligible !</strong> — Automatic requirements are met, with special conditions to verify with the provider.</li>
                    <li><strong>Not Eligible</strong> — At least one applicable requirement is not met.</li>
                </ul>
            </section>
            <!-- Methodology -->
            <section>
                <h2 class="text-2xl font-semibold mb-4">Recommendation Methodology</h2>
                <ol class="list-decimal list-inside space-y-3 text-on-surface-variant">
                    <li>Complete a student profile and enter official results. SPM requires completed exams and official results; CGPA-based levels require a latest official CGPA, or final CGPA if completed.</li>
                    <li>Select the study level seeking funding separately from the qualification supplying the results.</li>
                    <li>The system checks each applicable requirement: nationality, funding level, accepted latest qualification, study status and year, income, academics, field and institution.</li>
                    <li>Every applicable requirement must pass for eligibility. Each result shows the requirement, student information and outcome.</li>
                    <li>Expand ! on an Eligible result to read any special conditions that need checking with the provider.</li>
                </ol>
                <p class="mt-4 text-on-surface-variant">Open scholarships appear by nearest deadline within each decision group. Scholarship providers make the final award decisions.</p>
            </section>

            <!-- Tech Stack -->
            <section>
                <h2 class="text-2xl font-semibold text-on-surface mb-4">Technology Stack</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="card p-4">
                        <h3 class="font-medium text-on-surface mb-2">Backend</h3>
                        <ul class="list-disc list-inside text-on-surface-variant space-y-1">
                            <li>Laravel 13 (PHP 8.3+)</li>
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
                        <dd>3.0</dd>
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
