<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IdeaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Idea extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'image',
        'links',
    ];

    protected $attributes = [
        'status' => IdeaStatus::Pending->value,
        'links' => '',
    ];

    /**
     * Cast status to enum
     */
    protected $casts = [
        'status' => IdeaStatus::class,
        'links' => 'array',
    ];

    /**
     * Get the user who owns the idea
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all steps related to the idea
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }
}
