<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationListRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(NotificationListRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user('api');
        $filters = $request->validated();
        $query = Notification::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->latest('id');

        if (filter_var($request->input('unread_only'), FILTER_VALIDATE_BOOLEAN)) {
            $query->unread();
        }

        return NotificationResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return response()->json(['data' => ['count' => Notification::query()->where('user_id', $user->id)->unread()->count()]]);
    }

    public function markRead(Request $request, int $notification): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $record = $user->appNotifications()->whereKey($notification)->first();

        if ($record === null) {
            return response()->json(['message' => __('notifications.not_found')], 404);
        }

        $record->markAsRead();

        return response()->json(['data' => (new NotificationResource($record->refresh()))->resolve($request)]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $timestamp = now();
        $updatedCount = Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => $timestamp, 'updated_at' => $timestamp]);

        return response()->json(['data' => ['updated_count' => $updatedCount]]);
    }
}
