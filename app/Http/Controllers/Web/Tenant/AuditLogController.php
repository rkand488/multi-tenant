<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $query = AuditLog::on('central')
            ->where('tenant_id', $user->tenant_id)
            ->with('user:id,name')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where('auditable_type', 'like', "%{$search}%");
        }

        if ($event = $request->string('event')->toString()) {
            $query->where('event', $event);
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

        $logs = $query->paginate(25)->through(fn (AuditLog $log) => [
            'id' => $log->id,
            'event' => $log->event,
            'auditable_type' => $log->auditable_type,
            'auditable_id' => $log->auditable_id,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at?->toISOString(),
            'user' => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name] : null,
        ]);

        // Unique team members who appear in the audit log
        $teamMembers = User::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('Tenant/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'event', 'user_id', 'date_from', 'date_to']),
            'eventTypes' => ['created', 'updated', 'deleted', 'restored'],
            'teamMembers' => $teamMembers,
        ]);
    }
}
