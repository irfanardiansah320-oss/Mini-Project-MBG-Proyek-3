<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['pemantau', 'koordinator']), 403);

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $filter = function ($query) use ($request) {
            $query->when($request->filled('from'), fn ($q) => $q->whereDate('reports.incident_date', '>=', $request->from))
                  ->when($request->filled('to'), fn ($q) => $q->whereDate('reports.incident_date', '<=', $request->to));
        };

        $total = Report::query()->tap($filter)->count();
        $processing = Report::query()->tap($filter)
            ->whereIn('status', ['dikirim', 'ditinjau', 'perlu_diteruskan'])->count();
        $done = Report::query()->tap($filter)->where('status', 'diteruskan')->count();

        $rows = DB::table('reports')
            ->join('distributions', 'distributions.id', '=', 'reports.distribution_id')
            ->join('schools', 'schools.id', '=', 'distributions.school_id')
            ->tap($filter)
            ->groupBy('schools.id', 'schools.name')
            ->orderBy('schools.name')
            ->select(
                'schools.name',
                DB::raw('count(*) as total'),
                DB::raw("sum(reports.status = 'dikirim') as dikirim"),
                DB::raw("sum(reports.status = 'ditinjau') as ditinjau"),
                DB::raw("sum(reports.status = 'perlu_diteruskan') as perlu_diteruskan"),
                DB::raw("sum(reports.status = 'diteruskan') as diteruskan"),
            )
            ->get();

        return view('recap.index', compact('total', 'processing', 'done', 'rows'));
    }
}