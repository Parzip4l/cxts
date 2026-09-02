<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRelationship extends Model
{
    use HasFactory;

    public const TYPE_DEPENDS_ON = 'depends_on';
    public const TYPE_SUPPORTS = 'supports';

    protected $fillable = [
        'asset_id',
        'related_asset_id',
        'relationship_type',
        'notes',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DEPENDS_ON => 'Depends On',
            self::TYPE_SUPPORTS => 'Supports',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function relatedAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'related_asset_id');
    }
}
