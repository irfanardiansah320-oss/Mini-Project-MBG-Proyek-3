<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Report;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // Alur status: dari status ini, hanya boleh ke status "to", oleh role "role"
    private const TRANSITIONS = [
        'dikirim' => ['to' => 'ditinjau', 'role' => 'pemantau'],
        'ditinjau' => ['to' => 'perlu_diteruskan', 'role' => 'pemantau'],
        'perlu_diteruskan' => ['to' => 'diteruskan', 'role' => 'koordinator'],
    ];

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
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'evidence.mimes' => 'Bukti harus berupa file JPG, JPEG, PNG, atau PDF.',
            'evidence.max' => 'Ukuran bukti maksimal 2 MB.',
            'evidence.uploaded' => 'Bukti gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ]);

        $distribution = Distribution::findOrFail($data['distribution_id']);

        if (Carbon::parse($data['incident_date'])->lt(Carbon::parse($distribution->distribution_date))) {
            return back()
                ->withErrors(['incident_date' => 'Tanggal kejadian tidak boleh lebih awal dari tanggal distribusi.'])
                ->withInput();
        }

        $path = $request->hasFile('evidence')
            ? $request->file('evidence')->store('evidence')
            : null;

        Report::create([
            'user_id' => auth()->id(),
            'distribution_id' => $distribution->id,
            'incident_date' => $data['incident_date'],
            'complaint' => $data['complaint'],
            'evidence_path' => $path,
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

        if ($request->hasFile('evidence')) {
            if ($report->evidence_path) {
                Storage::delete($report->evidence_path);
            }
            $data['evidence_path'] = $request->file('evidence')->store('evidence');
        }
        unset($data['evidence']);

        $report->update($data);

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

        public function show(Report $report)
    {
        $user = auth()->user();

        // Pelapor hanya boleh melihat laporan miliknya
        abort_unless($user->role !== 'pelapor' || $report->user_id === $user->id, 403);

        $report->load(['user', 'distribution.school']);

        // Laporan lain pada distribusi yang sama (hanya untuk pemantau/koordinator)
        $related = collect();
        if ($user->role !== 'pelapor') {
            $related = Report::where('distribution_id', $report->distribution_id)
                ->where('id', '!=', $report->id)
                ->get();
        }

        $step = self::TRANSITIONS[$report->status] ?? null;
        $canAct = $step && $user->role === $step['role'];

        $logs = $user->role !== 'pelapor'
            ? $report->logs()->with('user')->latest()->get()
            : collect();

        return view('reports.show', compact('report', 'related', 'step', 'canAct', 'logs'));
    }

    public function updateStatus(Request $request, Report $report)
    {
        $user = auth()->user();

        // Pelapor tidak punya kewenangan mengubah status sama sekali
        abort_unless(in_array($user->role, ['pemantau', 'koordinator']), 403);

        $data = $request->validate([
            'status' => ['required', 'in:ditinjau,perlu_diteruskan,diteruskan'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $step = self::TRANSITIONS[$report->status] ?? null;

        // BR-03: status hanya boleh maju satu langkah sesuai urutan
        if (! $step || $step['to'] !== $data['status']) {
            return back()->withErrors(['status' => 'Perubahan status tidak sesuai alur laporan.']);
        }

        // BR-02: langkah ini hanya boleh dilakukan role yang berwenang
        abort_unless($user->role === $step['role'], 403, 'Anda tidak berwenang melakukan perubahan ini.');

        // Dua langkah ini wajib disertai catatan
        if (in_array($data['status'], ['perlu_diteruskan', 'diteruskan']) && blank($data['note'] ?? null)) {
            return back()->withErrors(['note' => 'Catatan wajib diisi untuk langkah ini.']);
        }

        $report->status = $data['status'];

        if ($data['status'] === 'perlu_diteruskan') {
            $report->review_note = $data['note'];
        }
        if ($data['status'] === 'diteruskan') {
            $report->forward_note = $data['note'];
        }

        $report->save();

        return redirect()->route('reports.show', $report)
            ->with('success', 'Status laporan berhasil diperbarui.');
    }

    // File bukti disimpan privat, hanya bisa dibuka lewat route ini
    public function evidence(Report $report)
    {
        $user = auth()->user();

        abort_unless($user->role !== 'pelapor' || $report->user_id === $user->id, 403);
        abort_unless($report->evidence_path && Storage::exists($report->evidence_path), 404);

        return Storage::response($report->evidence_path);
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