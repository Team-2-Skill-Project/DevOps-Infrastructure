<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogListRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(AuditLogListRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = AuditLog::query()->with('actor:id,name')->orderByDesc('created_at')->orderByDesc('id');

        foreach (['actor_id', 'action', 'entity_type', 'entity_id', 'source'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = mb_strtolower(trim((string) $filters['search']));
            $query->where(function ($query) use ($search): void {
                $query->whereRaw('LOWER(action) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(entity_type) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(source) LIKE ?', ["%{$search}%"]);
            });
        }

        return AuditLogResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }
}
