<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'education_level',
        'spm_as',
        'spm_credits',
        'cgpa',
        'result_status',
    ];

    protected $casts = [
        'cgpa' => 'decimal:2',
        'spm_as' => 'integer',
        'spm_credits' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}