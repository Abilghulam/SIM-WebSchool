<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentPromotion;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class EnrollmentPromotionLogController extends BaseController
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // kamu sudah punya gate ini dipakai di promote
        $this->authorize('manageSchoolData');

        $q = EnrollmentPromotion::query()
            ->with([
                'fromYear:id,name',
                'toYear:id,name',
                'executor:id,name,role_label',
            ])
            ->orderByDesc('executed_at')
            ->orderByDesc('id');

        // filters
        if ($fromId = (int) $request->get('from_school_year_id')) {
            $q->where('from_school_year_id', $fromId);
        }

        if ($toId = (int) $request->get('to_school_year_id')) {
            $q->where('to_school_year_id', $toId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $q->where('status', $status);
        }

        // search: nama TA / executor
        if ($search = $request->string('search')->trim()->toString()) {
            $q->where(function ($qq) use ($search) {
                $qq->whereHas('fromYear', fn ($x) => $x->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('toYear', fn ($x) => $x->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('executor', fn ($x) => $x->where('name', 'like', "%{$search}%"));
            });
        }

        $promotions = $q->paginate(15)->withQueryString();

        $schoolYears = SchoolYear::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name', 'is_active']);

        $statusOptions = [
            '' => 'Semua Status',
            'success' => 'Success',
            'failed' => 'Failed',
        ];

        return view('enrollments.promotions-index', compact(
            'promotions',
            'schoolYears',
            'statusOptions'
        ));
    }

    public function show(EnrollmentPromotion $promotion)
    {
        $this->authorize('manageSchoolData');

        $promotion->load([
            'fromYear:id,name',
            'toYear:id,name',
            'executor:id,name,role_label',
            'items.fromClassroom.major',
            'items.toClassroom.major',
        ]);

        return view('enrollments.promotions-show', compact('promotion'));
    }
}
