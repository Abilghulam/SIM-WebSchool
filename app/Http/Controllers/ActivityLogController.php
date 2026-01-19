<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $q = Activity::query()
            ->where('log_name', 'domain')
            ->latest('id')
            ->with(['causer', 'subject']);

        if ($request->filled('event')) {
            $q->where('event', $request->string('event')->toString());
        }

        if ($request->filled('causer_id')) {
            $q->where('causer_id', (int) $request->input('causer_id'))
              ->where('causer_type', \App\Models\User::class);
        }

        if ($request->filled('subject_type')) {
            $q->where('subject_type', $request->string('subject_type')->toString());
        }

        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $activities = $q->paginate(20)->withQueryString();

        // pilihan filter yang praktis (ambil dari DB, aman dan simpel)
        $events = Activity::query()
            ->select('event')
            ->where('log_name', 'domain')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $subjectTypes = Activity::query()
            ->select('subject_type')
            ->where('log_name', 'domain')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        return view('activity-logs.index', compact('activities', 'events', 'subjectTypes'));
    }

    public function show(Activity $activity)
    {
        abort_if($activity->log_name !== 'domain', 404);

        $props = $activity->properties ? $activity->properties->toArray() : [];
        $old = Arr::get($props, 'old', []);
        $attributes = Arr::get($props, 'attributes', []);

        return view('activity-logs.show', [
            'activity' => $activity->load('causer', 'subject'),
            'properties' => $props,
            'old' => is_array($old) ? $old : [],
            'attributes' => is_array($attributes) ? $attributes : [],
        ]);
    }
}
