<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Problem extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_KNOWN_ERROR = 'known_error';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'problem_number',
        'title',
        'description',
        'status',
        'owner_user_id',
        'ticket_priority_id',
        'symptom',
        'root_cause',
        'workaround',
        'permanent_fix',
        'is_known_error',
        'action_item',
        'target_resolution_at',
        'resolved_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'is_known_error' => 'boolean',
        'target_resolution_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_INVESTIGATING => 'Investigating',
            self::STATUS_KNOWN_ERROR => 'Known Error',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'ticket_priority_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'problem_ticket')->withTimestamps();
    }

    public function knowledgeArticles(): BelongsToMany
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'knowledge_article_problem')->withTimestamps();
    }
}
