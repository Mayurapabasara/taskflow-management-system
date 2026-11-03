<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'title', 'is_completed', 'assigned_to'])]
class Task extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'is_completed' => false,
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }
}
