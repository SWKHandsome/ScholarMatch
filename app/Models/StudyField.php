<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyField extends Model
{
    protected $fillable = ['name', 'seed_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function defaultRows(): array
    {
        return array_map(fn ($name) => ['name' => $name, 'seed_name' => $name, 'is_active' => true], config('scholarship.fields_of_study'));
    }

    public static function names(): array
    {
        return self::orderBy('name')->pluck('name')->all();
    }

    public static function selectableNames(array $retained = []): array
    {
        return self::where(fn ($query) => $query->where('is_active', true)->orWhereIn('name', $retained))
            ->orderBy('name')->pluck('name')->all();
    }

    /** Keep bundled demo rules aligned with administrator renames of default fields. */
    public static function resolveSeedNames(array $names): array
    {
        $renames = self::whereIn('seed_name', $names)->pluck('name', 'seed_name');

        return array_map(fn ($name) => $renames[$name] ?? $name, $names);
    }
}
