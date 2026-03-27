<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionEvidence extends Model
{
    use HasFactory;

    protected $table = 'inspection_evidences';

    protected $fillable = [
        'inspection_id',
        'inspection_item_id',
        'uploaded_by_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function inspectionItem(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
