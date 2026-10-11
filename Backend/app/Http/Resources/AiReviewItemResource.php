<?php

namespace App\Http\Resources;

use App\Models\AiReviewAudit;
use App\Models\AiReviewItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiReviewItem */
class AiReviewItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type->value,
            'entity_id' => $this->entity_id,
            'operation' => $this->operation->value,
            'trigger_reason' => $this->trigger_reason->value,
            'confidence' => $this->confidence,
            'status' => $this->status->value,
            'proposed_value' => $this->proposed_value,
            'canonical_value' => $this->canonical_value,
            'source' => $this->source,
            'corrected_value' => $this->corrected_value,
            'decision_reason' => $this->decision_reason,
            'reviewer_notes' => $this->reviewer_notes,
            'reviewer' => $this->whenLoaded('reviewer', fn (): ?array => $this->reviewer === null ? null : [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'audits' => $this->whenLoaded('audits', fn () => $this->audits->map(fn (AiReviewAudit $audit): array => [
                'id' => $audit->id,
                'action' => $audit->action->value,
                'reviewer_id' => $audit->reviewer_id,
                'original_proposal' => $audit->original_proposal,
                'canonical_before' => $audit->canonical_before,
                'applied_value' => $audit->applied_value,
                'decision_reason' => $audit->decision_reason,
                'reviewer_notes' => $audit->reviewer_notes,
                'created_at' => $audit->created_at,
            ])->values()->all()),
        ];
    }
}
