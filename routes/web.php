<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\AcademicResultController as StudentAcademicResultController;
use App\Http\Controllers\Student\RecommendationController as StudentRecommendationController;
use App\Http\Controllers\Student\SavedScholarshipController as StudentSavedScholarshipController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\ScholarshipRuleController as AdminScholarshipRuleController;
use App\Http\Controllers\Admin\IncomeCategoryController as AdminIncomeCategoryController;
use App\Http\Controllers\Admin\RecommendationLogController as AdminRecommendationLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::get('/about', function () {
    return view('public.about');
})->name('about');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [StudentProfileController::class, 'update'])->name('profile.update');

    Route::get('/academic-result', [StudentAcademicResultController::class, 'edit'])->name('academic-result.edit');
    Route::patch('/academic-result', [StudentAcademicResultController::class, 'update'])->name('academic-result.update');

    Route::get('/recommendations', [StudentRecommendationController::class, 'index'])->name('recommendations');

    Route::get('/saved-scholarships', [StudentSavedScholarshipController::class, 'index'])->name('saved-scholarships.index');
    Route::post('/saved-scholarships', [StudentSavedScholarshipController::class, 'store'])->name('saved-scholarships.store');
    Route::delete('/saved-scholarships/{scholarship}', [StudentSavedScholarshipController::class, 'destroy'])->name('saved-scholarships.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('scholarships', AdminScholarshipController::class);
    Route::resource('scholarships.rules', AdminScholarshipRuleController::class)->except(['show', 'destroy']);
    Route::resource('income-categories', AdminIncomeCategoryController::class)->except(['create', 'edit', 'show']);
    Route::get('recommendation-logs', [AdminRecommendationLogController::class, 'index'])->name('recommendation-logs.index');
Route::get('recommendation-logs/{recommendationLog}', [AdminRecommendationLogController::class, 'show'])->name('recommendation-logs.show');
});

require __DIR__.'/auth.php';