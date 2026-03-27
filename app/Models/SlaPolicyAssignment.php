<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_policy_id',
        'ticket_type',
        'category_id',
        'subcategory_id',
        'detail_subcategory_id',
        'service_item_id',
        'priority_id',
        'impact',
        'urgency',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(TicketSubcategory::class, 'subcategory_id');
    }

    public function detailSubcategory(): BelongsTo
    {
        return $this->belongsTo(TicketDetailSubcategory::class, 'detail_subcategory_id');
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_item_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }
}
