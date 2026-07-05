<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->has('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(30);

        return response()->json([
            'logs' => $logs
        ]);
    }
}
