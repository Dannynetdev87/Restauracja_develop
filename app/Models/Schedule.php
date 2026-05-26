<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    public const STATE_PLANNED = 'planned';

    public const STATE_RUNNING = 'running';

    public const STATE_FINISHED = 'finished';

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function startsAt(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function endsAt(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    public function startsAtDateTime(): Carbon
    {
        return $this->date->copy()->setTimeFromTimeString((string) $this->start_time);
    }

    public function endsAtDateTime(): Carbon
    {
        return $this->date->copy()->setTimeFromTimeString((string) $this->end_time);
    }

    public function state(): string
    {
        $now = now();

        if ($now->lt($this->startsAtDateTime())) {
            return self::STATE_PLANNED;
        }

        if ($now->betweenIncluded($this->startsAtDateTime(), $this->endsAtDateTime())) {
            return self::STATE_RUNNING;
        }

        return self::STATE_FINISHED;
    }
}
