<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'description',
        'award_type',
        'deadline',
        'application_link',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_active' => 'boolean',
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
        return $this->deadline < now()->toDateString();
    }
}