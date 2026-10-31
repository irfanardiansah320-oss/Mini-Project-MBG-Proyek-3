<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Report;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create()
    {
        abort_unless(auth()->user()->role === 'pelapor', 403);

        $schools = School::orderBy('name')->get();

        return view('reports.create', compact('schools'));
    }

    // Dipanggil oleh fetch() saat sekolah dipilih
    public function distributions(School $school)
    {
        abort_unless(auth()->user()->role === 'pelapor', 403);

        return response()->json(
            $school->distributions()
                ->orderByDesc('distribution_date')
                ->get(['id', 'distribution_date', 'menu'])
        );
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'pelapor', 403);

        $data = $request->validate([
            'distribution_id' => ['required', 'exists:distributions,id'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'complaint' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $distribution = Distribution::findOrFail($data['distribution_id']);

        // Aturan data: kejadian tidak boleh lebih awal dari distribusi
        if (Carbon::parse($data['incident_date'])->lt(Carbon::parse($distribution->distribution_date))) {
            return back()
                ->withErrors(['incident_date' => 'Tanggal kejadian tidak boleh lebih awal dari tanggal distribusi.'])
                ->withInput();
        }

        Report::create([
            'user_id' => auth()->id(),
            'distribution_id' => $distribution->id,
            'incident_date' => $data['incident_date'],
            'complaint' => $data['complaint'],
            'status' => 'dikirim',
        ]);

        return redirect()->route('reports.create')
            ->with('success', 'Laporan berhasil dikirim.');
    }
}