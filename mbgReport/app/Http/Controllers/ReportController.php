<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Report;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Daftar laporan: pelapor hanya melihat miliknya, role lain melihat semua
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Report::with(['user', 'distribution.school'])->latest();

        if ($user->role === 'pelapor') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->whereHas('distribution', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        // withQueryString menjaga filter tetap aktif saat pindah halaman
        $reports = $query->paginate(5)->withQueryString();
        $schools = School::orderBy('name')->get();

        return view('reports.index', compact('reports', 'schools'));
    }

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

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil dikirim.');
    }

    public function edit(Report $report)
    {
        $this->ensureOwner($report);

        // BR-01: hanya boleh diubah saat status masih "dikirim"
        if ($report->status !== 'dikirim') {
            return redirect()->route('reports.index')
                ->withErrors(['report' => 'Laporan sudah ditinjau dan tidak dapat diubah.']);
        }

        $report->load('distribution.school');

        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $this->ensureOwner($report);

        // BR-01 dicek lagi di server, walau tombol edit sudah disembunyikan
        if ($report->status !== 'dikirim') {
            return redirect()->route('reports.index')
                ->withErrors(['report' => 'Laporan sudah ditinjau dan tidak dapat diubah.']);
        }

        $data = $request->validate([
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'complaint' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $distribution = $report->distribution;

        if (Carbon::parse($data['incident_date'])->lt(Carbon::parse($distribution->distribution_date))) {
            return back()
                ->withErrors(['incident_date' => 'Tanggal kejadian tidak boleh lebih awal dari tanggal distribusi.'])
                ->withInput();
        }

        $report->update($data);

        return redirect()->route('reports.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    // Ownership: hanya pelapor pemilik laporan
    private function ensureOwner(Report $report): void
    {
        abort_unless(
            auth()->user()->role === 'pelapor' && $report->user_id === auth()->id(),
            403
        );
    }
}