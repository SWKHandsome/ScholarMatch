<?php

namespace App\Models;

use App\Services\ScholarshipRecommendationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'scholarship_id', 'status', 'explanation', 'evaluation_version',
        'requirement_results', 'failed_requirements', 'requires_verification', 'special_requirements',
    ];

    protected $casts = [
        'explanation' => 'array', 'requirement_results' => 'array', 'failed_requirements' => 'array',
        'requires_verification' => 'boolean', 'legacy_evaluation' => 'array',
    ];

    public function isCurrentEvaluation(): bool
    {
        return $this->evaluation_version === ScholarshipRecommendationService::ALGORITHM_VERSION;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
