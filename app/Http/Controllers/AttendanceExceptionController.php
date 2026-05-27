<?php

namespace App\Http\Controllers;

use App\Models\AttendanceException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceExceptionCreated;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceExceptionController extends Controller
{
    public function index()
    {
        // Admin inbox: show pending exceptions
        $exceptions = AttendanceException::with(['user', 'requester'])
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->paginate(25);

        return view('admin.approvals.exceptions', compact('exceptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:missed_punch,incorrect_time,manual_correction,other',
            'note' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();

        $exception = AttendanceException::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'note' => $request->note,
            'status' => 'pending',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'tenant_id' => $user->tenant_id,
        ]);

        // Notify all managers (users who can manage users) in this tenant
        try {
            $managers = User::where('tenant_id', $user->tenant_id)->get()->filter(function ($u) {
                return method_exists($u, 'canManageUsers') ? $u->canManageUsers() : ($u->role === 'admin');
            });

            if ($managers->isNotEmpty()) {
                $emails = $managers->pluck('email')->filter()->unique()->values()->all();
                if (!empty($emails)) {
                    Mail::to($emails)->send(new AttendanceExceptionCreated($exception));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send attendance exception notification: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Request submitted. Your manager(s) have been notified.');
    }

    public function approve(Request $request, AttendanceException $exception)
    {
        if (!Auth::check() || !Auth::user()->canManageUsers()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $exception->status = 'approved';
        $exception->approved_by = Auth::id();
        $exception->handled_at = now();
        $exception->save();

        // Apply a minimal automatic correction for common cases (missed_punch)
        try {
            if ($exception->type === 'missed_punch') {
                $user = $exception->user;

                // Allow admin to provide a correction timestamp
                $requestedCorrection = $request->input('correction_timestamp');
                $date = $exception->requested_at ? Carbon::parse($exception->requested_at)->toDateString() : today()->toDateString();

                // Find the latest clock-in for that day
                $clockIn = AttendanceLog::where('user_id', $user->id)
                    ->whereDate('timestamp', $date)
                    ->where('action', 'clock_in')
                    ->orderBy('timestamp', 'desc')
                    ->first();

                if ($clockIn) {
                    // Check if a clock_out already exists after this clock_in
                    $hasClockOut = AttendanceLog::where('user_id', $user->id)
                        ->where('action', 'clock_out')
                        ->where('timestamp', '>', $clockIn->timestamp)
                        ->exists();

                    if (!$hasClockOut) {
                        // Determine clock_out time: prefer admin-supplied value
                        if ($requestedCorrection) {
                            $clockOutTime = Carbon::parse($requestedCorrection);
                        } else {
                            $clockOutTime = $exception->requested_at ? Carbon::parse($exception->requested_at) : Carbon::parse($clockIn->timestamp)->addHours(8);
                        }

                        if ($clockOutTime->lessThanOrEqualTo($clockIn->timestamp)) {
                            $clockOutTime = Carbon::now();
                        }

                        AttendanceLog::create([
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'action' => 'clock_out',
                            'timestamp' => $clockOutTime,
                            'tenant_id' => $user->tenant_id,
                        ]);

                        // Record applied correction in meta
                        $meta = $exception->meta ?? [];
                        $meta['applied_correction'] = [
                            'type' => 'clock_out_created',
                            'timestamp' => $clockOutTime->toDateTimeString(),
                            'applied_by' => Auth::id(),
                        ];
                        $exception->meta = $meta;
                        $exception->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to apply attendance correction on approve: ' . $e->getMessage(), ['exception_id' => $exception->id]);
        }

        Log::info('Attendance exception approved', ['id' => $exception->id, 'by' => Auth::id()]);

        return redirect()->back()->with('success', 'Request approved.');
    }

    public function reject(Request $request, AttendanceException $exception)
    {
        if (!Auth::check() || !Auth::user()->canManageUsers()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $exception->status = 'rejected';
        $exception->approved_by = Auth::id();
        $exception->handled_at = now();
        $exception->save();

        Log::info('Attendance exception rejected', ['id' => $exception->id, 'by' => Auth::id()]);

        return redirect()->back()->with('success', 'Request rejected.');
    }
}
