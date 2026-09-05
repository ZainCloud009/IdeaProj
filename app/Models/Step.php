<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Step extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'idea_id',
        'description',
        'completed',
    ];

    protected $attributes = [
        'completed' => false,
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    /**
     * The idea this step belongs to
     */
    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }
}
