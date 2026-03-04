<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\ActivityLog;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityLog::on('central')
            ->with('user:id,name')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($action = $request->string('action')->toString()) {
            $query->where('action', $action);
        }

        if ($userId = $request->string('user_id')->toString()) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(25)->through(fn (ActivityLog $log) => [
            'id' => $log->id,
            'action' => $log->action,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'meta' => $log->meta,
            'created_at' => $log->created_at?->toISOString(),
            'user' => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name] : null,
        ]);

        $teamMembers = User::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);

        $eventTypes = ActivityLog::on('central')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->filter()
            ->values();

        return Inertia::render('Tenant/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action', 'user_id', 'date_from', 'date_to']),
            'eventTypes' => $eventTypes,
            'teamMembers' => $teamMembers,
        ]);
    }
}
