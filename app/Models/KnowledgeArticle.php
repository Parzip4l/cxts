<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeArticle extends Model
{
    use HasFactory;

    public const TYPE_TROUBLESHOOTING = 'troubleshooting';
    public const TYPE_FAQ = 'faq';
    public const TYPE_WORKAROUND = 'workaround';
    public const TYPE_KNOWN_ERROR = 'known_error';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'article_number',
        'title',
        'article_type',
        'category',
        'status',
        'owner_user_id',
        'content',
        'summary',
        'published_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_TROUBLESHOOTING => 'Troubleshooting',
            self::TYPE_FAQ => 'FAQ',
            self::TYPE_WORKAROUND => 'Workaround',
            self::TYPE_KNOWN_ERROR => 'Known Error',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'knowledge_article_ticket')->withTimestamps();
    }

    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'knowledge_article_problem')->withTimestamps();
    }
}
