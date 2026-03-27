<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_open',
        'is_in_progress',
        'is_closed',
        'is_active',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_in_progress' => 'boolean',
        'is_closed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
