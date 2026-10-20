<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Scholarship extends Model
{
    use HasFactory;

    public const DEADLINE_TYPES = [
        'unconfirmed' => 'Check provider',
        'fixed' => 'Published scholarship deadline',
        'admission' => 'Admission deadline',
        'relative' => 'Calculated from provider rule',
        'rolling' => 'Rolling applications',
        'intake' => 'Intake-based deadline',
        'automatic' => 'Automatic award',
    ];

    protected $fillable = [
        'name',
        'provider',
        'description',
        'award_type',
        'deadline',
        'deadline_type',
        'application_link',
        'is_active',
        'special_requirements',
        'catalog_key',
        'source_url',
        'source_checked_at',
        'application_notes',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_active' => 'boolean',
        'source_checked_at' => 'date',
    ];

    public function rule(): HasOne
    {
        return $this->hasOne(ScholarshipRule::class);
    }

    public function savedScholarships(): HasMany
    {
        return $this->hasMany(SavedScholarship::class);
    }

    public function recommendationLogs(): HasMany
    {
        return $this->hasMany(RecommendationLog::class);
    }

    public function isExpired(): bool
    {
        return $this->deadline !== null && $this->deadline->toDateString() < now()->toDateString();
    }

    public function isDemo(): bool
    {
        return str_starts_with($this->catalog_key ?? '', 'demo-');
    }

    public function deadlineLabel(): string
    {
        return $this->deadline?->format('M d, Y')
            ?? (self::DEADLINE_TYPES[$this->deadline_type] ?? 'Check provider');
    }
}
