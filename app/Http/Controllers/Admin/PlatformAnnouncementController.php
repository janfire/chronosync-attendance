<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalAnnouncement;
use Illuminate\Http\Request;

class PlatformAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = GlobalAnnouncement::latest()->get();
        return view('admin.superadmin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.superadmin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        GlobalAnnouncement::create($validated);

        return redirect()->route('superadmin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit(GlobalAnnouncement $announcement)
    {
        return view('admin.superadmin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, GlobalAnnouncement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $announcement->update($validated);

        return redirect()->route('superadmin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(GlobalAnnouncement $announcement)
    {
        $announcement->delete();

        return redirect()->route('superadmin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
