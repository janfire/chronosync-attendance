<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemErrorLog;
use Illuminate\Http\Request;

class PlatformAuditController extends Controller
{
    public function index()
    {
        $logs = SystemErrorLog::with(['tenant', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.superadmin.audit.index', compact('logs'));
    }

    public function show(SystemErrorLog $log)
    {
        $log->load(['tenant', 'user']);
        return view('admin.superadmin.audit.show', compact('log'));
    }

    public function resolve(SystemErrorLog $log)
    {
        $log->update(['status' => 'resolved']);
        return back()->with('success', 'Error marked as resolved.');
    }

    public function resolveAll()
    {
        SystemErrorLog::where('status', 'new')->update(['status' => 'resolved']);
        return back()->with('success', 'All new errors marked as resolved.');
    }
}
