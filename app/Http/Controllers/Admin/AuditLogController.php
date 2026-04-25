<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * AuditLogController — read-only listing of mutation audit trail.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 8.1)
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->string('event')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', (int) $request->input('user_id')))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $events = AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => [
                'event' => $request->string('event')->toString(),
                'user_id' => $request->input('user_id'),
            ],
        ]);
    }
}
