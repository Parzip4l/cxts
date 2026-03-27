<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'inspection_template_item_id',
        'sequence',
        'item_label',
        'item_type',
        'expected_value',
        'result_value',
        'result_status',
        'notes',
        'checked_at',
        'checked_by_id',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplateItem::class, 'inspection_template_item_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(InspectionEvidence::class);
    }
}
