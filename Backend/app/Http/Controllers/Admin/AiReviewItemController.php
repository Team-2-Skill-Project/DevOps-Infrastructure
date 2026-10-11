<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AiReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiReviewListRequest;
use App\Http\Resources\AiReviewItemResource;
use App\Models\AiReviewItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AiReviewItemController extends Controller
{
    public function index(AiReviewListRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = AiReviewItem::query()->with('reviewer:id,name');

        foreach (['status', 'entity_type', 'source'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }
        if (array_key_exists('reason', $filters)) {
            $query->where('trigger_reason', $filters['reason']);
        }
        if (array_key_exists('reviewer_id', $filters)) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }
        if (array_key_exists('confidence_min', $filters)) {
            $query->where('confidence', '>=', $filters['confidence_min']);
        }
        if (array_key_exists('confidence_max', $filters)) {
            $query->where('confidence', '<=', $filters['confidence_max']);
        }
        if (array_key_exists('created_from', $filters)) {
            $query->where('created_at', '>=', $filters['created_from']);
        }
        if (array_key_exists('created_to', $filters)) {
            $query->where('created_at', '<=', $filters['created_to']);
        }
        if (array_key_exists('search', $filters)) {
            $search = addcslashes($filters['search'], '%_\\');
            $query->where(function (Builder $query) use ($search, $filters): void {
                $query->where('source', 'like', "%{$search}%");
                if (ctype_digit($filters['search'])) {
                    $query->orWhere('entity_id', (int) $filters['search']);
                }
            });
        }

        $query->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [AiReviewStatus::PENDING->value])
            ->oldest('created_at')
            ->oldest('id');

        return AiReviewItemResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function show(Request $request, AiReviewItem $aiReviewItem): AiReviewItemResource
    {
        return new AiReviewItemResource($aiReviewItem->load(['reviewer:id,name', 'audits']));
    }
}
